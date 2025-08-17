<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the 2FA setup page
     */
    public function show()
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return view('admin.auth.two-factor.status', compact('user'));
        }

        // Generate secret key for new setup
        $secretKey = $this->google2fa->generateSecretKey();

        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        return view('admin.auth.two-factor.setup', compact('secretKey', 'qrCodeUrl', 'user'));
    }

    /**
     * Enable 2FA for the user
     */
    public function enable(Request $request)
    {
        $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ], [
            'secret.required' => 'Chave secreta é obrigatória.',
            'code.required' => 'Código de verificação é obrigatório.',
            'code.size' => 'Código deve ter exatamente 6 dígitos.',
        ]);

        $user = Auth::user();
        $secret = $request->input('secret');
        $code = $request->input('code');

        // Verify the code
        if (!$this->google2fa->verifyKey($secret, $code)) {
            throw ValidationException::withMessages([
                'code' => 'Código de verificação inválido.'
            ]);
        }

        // Enable 2FA for the user
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        // Generate recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();

        Log::info('2FA habilitado para usuário', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('admin.two-factor.show')
            ->with('success', 'Autenticação de dois fatores habilitada com sucesso!')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Disable 2FA for the user
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
            'code' => 'required|string|size:6',
        ], [
            'password.required' => 'Senha atual é obrigatória.',
            'password.current_password' => 'Senha atual incorreta.',
            'code.required' => 'Código de verificação é obrigatório.',
            'code.size' => 'Código deve ter exatamente 6 dígitos.',
        ]);

        $user = Auth::user();
        $code = $request->input('code');

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->back()->with('error', 'Autenticação de dois fatores não está habilitada.');
        }

        // Verify the code
        $secret = decrypt($user->two_factor_secret);
        if (!$this->google2fa->verifyKey($secret, $code)) {
            throw ValidationException::withMessages([
                'code' => 'Código de verificação inválido.'
            ]);
        }

        // Disable 2FA
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        Log::info('2FA desabilitado para usuário', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('admin.two-factor.show')
            ->with('success', 'Autenticação de dois fatores desabilitada com sucesso!');
    }

    /**
     * Show recovery codes
     */
    public function recoveryCodes()
    {
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.two-factor.show')
                ->with('error', 'Autenticação de dois fatores não está habilitada.');
        }

        $recoveryCodes = $user->two_factor_recovery_codes ?? [];

        return view('admin.auth.two-factor.recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ], [
            'password.required' => 'Senha atual é obrigatória.',
            'password.current_password' => 'Senha atual incorreta.',
        ]);

        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.two-factor.show')
                ->with('error', 'Autenticação de dois fatores não está habilitada.');
        }

        $recoveryCodes = $user->generateRecoveryCodes();

        Log::info('Códigos de recuperação regenerados', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('admin.two-factor.recovery-codes')
            ->with('success', 'Códigos de recuperação regenerados com sucesso!')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Código de verificação é obrigatório.',
            'code.size' => 'Código deve ter exatamente 6 dígitos.',
        ]);

        if (!session('2fa_user_id')) {
            return redirect()->route('login')->with('error', 'Sessão expirada. Faça login novamente.');
        }

        $user = \App\Models\User::find(session('2fa_user_id'));
        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->route('login')->with('error', 'Usuário inválido.');
        }

        $code = $request->input('code');
        $secret = decrypt($user->two_factor_secret);

        if ($this->google2fa->verifyKey($secret, $code)) {
            // Code is valid, complete login
            Auth::login($user, session('2fa_remember', false));

            $user->resetLoginAttempts();
            $user->updateLastLogin($request->ip());

            // Clear 2FA session data
            session()->forget(['2fa_user_id', '2fa_remember']);

            Log::info('Login com 2FA realizado com sucesso', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        // Invalid code, increment attempts
        $user->incrementLoginAttempts();

        Log::warning('Tentativa de login com código 2FA inválido', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'attempts' => $user->login_attempts
        ]);

        throw ValidationException::withMessages([
            'code' => 'Código de verificação inválido.'
        ]);
    }

    /**
     * Verify recovery code during login
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ], [
            'recovery_code.required' => 'Código de recuperação é obrigatório.',
        ]);

        if (!session('2fa_user_id')) {
            return redirect()->route('login')->with('error', 'Sessão expirada. Faça login novamente.');
        }

        $user = \App\Models\User::find(session('2fa_user_id'));
        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->route('login')->with('error', 'Usuário inválido.');
        }

        $recoveryCode = $request->input('recovery_code');

        if ($user->useRecoveryCode($recoveryCode)) {
            // Recovery code is valid, complete login
            Auth::login($user, session('2fa_remember', false));

            $user->resetLoginAttempts();
            $user->updateLastLogin($request->ip());

            // Clear 2FA session data
            session()->forget(['2fa_user_id', '2fa_remember']);

            Log::info('Login com código de recuperação realizado com sucesso', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->intended(route('admin.dashboard'))
                ->with('warning', 'Você usou um código de recuperação. Considere regenerar seus códigos de recuperação.');
        }

        // Invalid recovery code, increment attempts
        $user->incrementLoginAttempts();

        Log::warning('Tentativa de login com código de recuperação inválido', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'attempts' => $user->login_attempts
        ]);

        throw ValidationException::withMessages([
            'recovery_code' => 'Código de recuperação inválido.'
        ]);
    }
}
