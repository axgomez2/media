<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Log};
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Por favor, insira um email válido.',
            'password.required' => 'O campo senha é obrigatório.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            Log::warning('Tentativa de login com email inexistente', [
                'email' => $credentials['email'],
                'ip' => $request->ip()
            ]);

            throw ValidationException::withMessages([
                'email' => 'As credenciais fornecidas são inválidas.'
            ]);
        }

        // Check if user is locked
        if ($user->isLocked()) {
            $lockTime = $user->locked_until->diffForHumans();

            Log::warning('Tentativa de login com conta bloqueada', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'locked_until' => $user->locked_until
            ]);

            throw ValidationException::withMessages([
                'email' => "Conta temporariamente bloqueada devido a muitas tentativas de login. Tente novamente {$lockTime}."
            ]);
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            $user->incrementLoginAttempts();

            Log::warning('Tentativa de login com senha incorreta', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'attempts' => $user->login_attempts
            ]);

            throw ValidationException::withMessages([
                'password' => 'As credenciais fornecidas são inválidas.'
            ]);
        }

        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            // Store user ID and remember preference in session for 2FA verification
            session([
                '2fa_user_id' => $user->id,
                '2fa_remember' => $remember
            ]);

            Log::info('Redirecionando para verificação 2FA', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->route('two-factor.verify');
        }

        // Login without 2FA
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->resetLoginAttempts();
        $user->updateLastLogin($request->ip());

        Log::info('Login realizado com sucesso', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            Log::info('Logout realizado', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logout realizado com sucesso.');
    }

    /**
     * Show 2FA verification form
     */
    public function showTwoFactorForm()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login')->with('error', 'Sessão expirada. Faça login novamente.');
        }

        return view('auth.two-factor');
    }
}
