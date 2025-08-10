<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'chart_date',
        'social_links',
        'dj_photo',
        'is_active',
        'position'
    ];

    protected $casts = [
        'chart_date' => 'date',
        'social_links' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Relacionamento com as faixas da playlist
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(PlaylistTrack::class)->orderBy('position');
    }

    /**
     * Scope para playlists de DJ
     */
    public function scopeDj($query)
    {
        return $query->where('type', 'dj');
    }

    /**
     * Scope para playlists de Chart
     */
    public function scopeChart($query)
    {
        return $query->where('type', 'chart');
    }

    /**
     * Scope para playlists ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenação por posição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('created_at', 'desc');
    }

    /**
     * Accessor para o tipo formatado
     */
    public function getTypeFormattedAttribute(): string
    {
        return match($this->type) {
            'dj' => 'DJ Set',
            'chart' => 'Chart',
            default => $this->type
        };
    }

    /**
     * Accessor para verificar se é playlist de DJ
     */
    public function getIsDjAttribute(): bool
    {
        return $this->type === 'dj';
    }

    /**
     * Accessor para verificar se é playlist de Chart
     */
    public function getIsChartAttribute(): bool
    {
        return $this->type === 'chart';
    }

    /**
     * Accessor para contagem de faixas
     */
    public function getTracksCountAttribute(): int
    {
        return $this->tracks()->count();
    }

    /**
     * Accessor para URL da foto do DJ
     */
    public function getDjPhotoUrlAttribute(): ?string
    {
        if (!$this->dj_photo) {
            return null;
        }

        // Se já é uma URL completa, retorna como está
        if (str_starts_with($this->dj_photo, 'http')) {
            return $this->dj_photo;
        }

        // Caso contrário, gera URL do storage
        return asset('storage/' . $this->dj_photo);
    }

    /**
     * Accessor para verificar se tem foto do DJ
     */
    public function getHasDjPhotoAttribute(): bool
    {
        return !empty($this->dj_photo);
    }
}
