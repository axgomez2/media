<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'user_id' => 'string'
    ];

    /**
     * Relacionamento com ClientUser
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    /**
     * Accessor para endereço completo formatado
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->street . ', ' . $this->number;

        if ($this->complement) {
            $address .= ', ' . $this->complement;
        }

        $address .= ', ' . $this->neighborhood . ', ' . $this->city . ' - ' . $this->state;
        $address .= ', CEP: ' . $this->zip_code;

        return $address;
    }

    /**
     * Accessor para CEP formatado
     */
    public function getFormattedZipCodeAttribute(): string
    {
        $zipCode = preg_replace('/\D/', '', $this->zip_code);

        if (strlen($zipCode) === 8) {
            return substr($zipCode, 0, 5) . '-' . substr($zipCode, 5);
        }

        return $this->zip_code;
    }

    /**
     * Scope para endereços padrão
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope para buscar por cidade
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Scope para buscar por estado
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope para buscar por CEP
     */
    public function scopeByZipCode($query, $zipCode)
    {
        $cleanZipCode = preg_replace('/\D/', '', $zipCode);
        return $query->where('zip_code', 'like', "%{$cleanZipCode}%");
    }

    /**
     * Método para definir como endereço padrão
     */
    public function setAsDefault(): void
    {
        // Remove o padrão de outros endereços do mesmo usuário
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Define este como padrão
        $this->update(['is_default' => true]);
    }

    /**
     * Accessor para endereço resumido
     */
    public function getShortAddressAttribute(): string
    {
        return $this->street . ', ' . $this->number . ', ' . $this->neighborhood . ', ' . $this->city;
    }
}
