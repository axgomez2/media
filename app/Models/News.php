<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'gallery_images',
        'topics',
        'status',
        'meta_description',
        'meta_keywords',
        'published_at',
        'author_id',
        'featured'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'topics' => 'array',
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'views_count' => 'integer'
    ];

    /**
     * Relacionamento com o autor (User)
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope para notícias publicadas
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope para notícias em destaque
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope para busca
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para filtrar por tópico
     */
    public function scopeByTopic($query, $topic)
    {
        return $query->whereJsonContains('topics', $topic);
    }

    /**
     * Accessor para URL da imagem de destaque
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        if (str_starts_with($this->featured_image, 'http')) {
            return $this->featured_image;
        }

        return asset('storage/' . $this->featured_image);
    }

    /**
     * Accessor para URLs das imagens da galeria
     */
    public function getGalleryImageUrlsAttribute(): array
    {
        if (!$this->gallery_images) {
            return [];
        }

        return collect($this->gallery_images)->map(function ($image) {
            if (str_starts_with($image, 'http')) {
                return $image;
            }
            return asset('storage/' . $image);
        })->toArray();
    }

    /**
     * Accessor para status formatado
     */
    public function getStatusFormattedAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Rascunho',
            'published' => 'Publicado',
            'archived' => 'Arquivado',
            default => $this->status
        };
    }

    /**
     * Accessor para cor do status
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'yellow',
            'published' => 'green',
            'archived' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Accessor para tempo de leitura estimado
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // 200 palavras por minuto
    }

    /**
     * Boot method para gerar slug automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);
            }
        });

        static::updating(function ($news) {
            if ($news->isDirty('title') && empty($news->getOriginal('slug'))) {
                $news->slug = Str::slug($news->title);
            }
        });
    }
}
