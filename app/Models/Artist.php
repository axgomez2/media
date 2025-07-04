<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Artist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'discogs_id',
        'profile',
        'images',
        'discogs_url'
    ];

    protected $casts = [
        'images' => 'array',
        'discogs_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($artist) {
            if (empty($artist->slug)) {
                $artist->slug = Str::slug($artist->name);
            }
        });

        static::saving(function ($artist) {
            // Se images for uma string, tenta decodificar
            if (is_string($artist->images)) {
                $artist->images = json_decode($artist->images, true);
            }

            // Se images for um array, garante o formato correto
            if (is_array($artist->images) && !empty($artist->images)) {
                // Garante que o caminho da imagem está no formato correto
                $firstImage = $artist->images[0];
                if (is_array($firstImage) && isset($firstImage['url'])) {
                    // Já está no formato correto, não precisa fazer nada
                } else {
                    // Converte para o novo formato
                    $artist->images = [
                        [
                            'url' => is_array($firstImage) ? ($firstImage['url'] ?? $firstImage) : $firstImage,
                            'type' => 'primary'
                        ]
                    ];
                }
            }
        });
    }

    public function vinylMasters()
    {
        return $this->belongsToMany(VinylMaster::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Obter a URL da imagem do artista
     *
     * @return string|null URL da imagem ou null
     */
    public function getImageUrlAttribute()
    {
        // Verifica se temos imagens no formato array
        if (!empty($this->images) && is_array($this->images)) {
            $firstImage = $this->images[0];

            // Verifica se está no formato novo (array com url)
            if (is_array($firstImage) && isset($firstImage['url'])) {
                $imagePath = $firstImage['url'];
            } else {
                // Formato antigo (string direta)
                $imagePath = is_array($firstImage) ? ($firstImage['url'] ?? $firstImage) : $firstImage;
            }

            // Se for uma URL completa, retorna direto
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $imagePath;
            }

            // Caso contrário, assume que é um caminho do storage
            return asset('storage/' . $imagePath);
        }

        return null;
    }
}
