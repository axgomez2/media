<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OptimizeImageLoading
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Apenas processar rotas de imagem
        if (!$request->is('media-externa/*')) {
            return $response;
        }
        
        // Adicionar headers de cache para imagens
        $response->headers->set('Cache-Control', 'public, max-age=3600, immutable');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        
        // Adicionar header de compressão se disponível
        if (function_exists('gzencode') && strpos($request->header('Accept-Encoding'), 'gzip') !== false) {
            $response->headers->set('Content-Encoding', 'gzip');
        }
        
        return $response;
    }
}