<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Helper global para URLs de imagem do CDN
        if (!function_exists('media_url')) {
            function media_url($path = null) {
                if (!$path) {
                    return config('filesystems.disks.media.url');
                }

                // Se a URL já está completa, retornar como está
                if (str_starts_with($path, 'http')) {
                    return $path;
                }

                $mediaUrl = config('filesystems.disks.media.url');
                return rtrim($mediaUrl, '/') . '/' . ltrim($path, '/');
            }
        }

        // Helper para imagem com fallback
        if (!function_exists('vinyl_image_url')) {
            function vinyl_image_url($imagePath, $fallback = null) {
                if (!$imagePath) {
                    return $fallback ?: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyMEg0NFY0NEgyMFYyMFoiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+CjxwYXRoIGQ9Ik0yOCAzMkwzMiAyOEwzNiAzMkwzMiAzNkwyOCAzMloiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+';
                }

                return media_url($imagePath);
            }
        }
    }
}
