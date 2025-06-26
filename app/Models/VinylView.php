<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VinylView extends Model
{
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vinyl_master_id',
        'user_uuid',
        'ip_address',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
        'browser',
        'platform',
        'device_type',
        'viewed_at',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Obtém o disco relacionado a esta visualização.
     */
    public function vinyl()
    {
        return $this->belongsTo(VinylMaster::class, 'vinyl_master_id');
    }

    /**
     * Obtém o usuário relacionado a esta visualização, se houver.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'id');
    }

    /**
     * Registra uma nova visualização para um disco.
     *
     * @param VinylMaster $vinyl O disco visualizado
     * @param User|null $user O usuário atual, se autenticado
     * @param string|null $ipAddress O endereço IP do usuário
     * @return VinylView
     */
    public static function recordView(VinylMaster $vinyl, $user = null, $request = null)
    {
        $data = [
            'vinyl_master_id' => $vinyl->id,
            'user_uuid' => $user ? $user->id : null,
            'viewed_at' => now(),
        ];
        
        if ($request) {
            $data['ip_address'] = $request->ip();
            
            // Informações básicas do user agent
            $userAgent = $request->userAgent();
            
            // Extração simples de informações do navegador
            if (strpos($userAgent, 'Chrome') !== false) {
                $data['browser'] = 'Chrome';
            } elseif (strpos($userAgent, 'Firefox') !== false) {
                $data['browser'] = 'Firefox';
            } elseif (strpos($userAgent, 'Safari') !== false) {
                $data['browser'] = 'Safari';
            } elseif (strpos($userAgent, 'Edge') !== false) {
                $data['browser'] = 'Edge';
            } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
                $data['browser'] = 'Internet Explorer';
            } else {
                $data['browser'] = 'Outro';
            }
            
            // Extração simples de informações da plataforma
            if (strpos($userAgent, 'Windows') !== false) {
                $data['platform'] = 'Windows';
            } elseif (strpos($userAgent, 'Mac') !== false) {
                $data['platform'] = 'Mac';
            } elseif (strpos($userAgent, 'Linux') !== false) {
                $data['platform'] = 'Linux';
            } elseif (strpos($userAgent, 'Android') !== false) {
                $data['platform'] = 'Android';
            } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
                $data['platform'] = 'iOS';
            } else {
                $data['platform'] = 'Outro';
            }
            
            // Extração simples do tipo de dispositivo
            if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false || strpos($userAgent, 'iPhone') !== false) {
                $data['device_type'] = 'mobile';
            } elseif (strpos($userAgent, 'iPad') !== false || strpos($userAgent, 'Tablet') !== false) {
                $data['device_type'] = 'tablet';
            } else {
                $data['device_type'] = 'desktop';
            }
        }
        
        return static::create($data);
    }
}
