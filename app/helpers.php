<?php

if (!function_exists('media_url')) {
    /**
     * Gera URL completa para arquivos de mídia do CDN
     *
     * @param string|null $path
     * @return string
     */
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

if (!function_exists('vinyl_image_url')) {
    /**
     * Gera URL de imagem de vinyl com fallback automático
     *
     * @param string|null $imagePath
     * @param string|null $fallback
     * @return string
     */
    function vinyl_image_url($imagePath, $fallback = null) {
        if (!$imagePath) {
            return $fallback ?: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyMEg0NFY0NEgyMFYyMFoiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+CjxwYXRoIGQ9Ik0yOCAzMkwzMiAyOEwzNiAzMkwzMiAzNkwyOCAzMloiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+';
        }

        return media_url($imagePath);
    }
}

if (!function_exists('get_vinyl_image_url')) {
    /**
     * Função alternativa para gerar URL de imagem de vinyl
     * Usa apenas funções nativas do Laravel
     *
     * @param string|null $imagePath
     * @return string
     */
    function get_vinyl_image_url($imagePath) {
        if (!$imagePath) {
            return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyMEg0NFY0NEgyMFYyMFoiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+CjxwYXRoIGQ9Ik0yOCAzMkwzMiAyOEwzNiAzMkwzMiAzNkwyOCAzMloiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+';
        }

        // Se a URL já está completa, retornar como está
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        $mediaUrl = config('filesystems.disks.media.url');
        return rtrim($mediaUrl, '/') . '/' . ltrim($imagePath, '/');
    }
}