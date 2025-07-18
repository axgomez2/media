<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    public function show($path)
    {
        try {
            // Log para debug em produção
            Log::info('Tentando acessar imagem: ' . $path);
            
            // Verifica se o arquivo existe no disco 'media'
            if (!Storage::disk('media')->exists($path)) {
                Log::warning('Imagem não encontrada: ' . $path);
                Log::info('Diretório raiz do disco media: ' . Storage::disk('media')->path(''));
                
                // Retorna imagem padrão em vez de 404
                return $this->getDefaultImage();
            }

            // Pega o arquivo do disco
            $file = Storage::disk('media')->get($path);

            // Determina o tipo MIME do arquivo
            $type = Storage::disk('media')->mimeType($path);

            Log::info('Imagem servida com sucesso: ' . $path);

            // Retorna a resposta com o arquivo e o tipo MIME correto
            return response($file, 200)
                ->header('Content-Type', $type)
                ->header('Cache-Control', 'public, max-age=3600'); // Cache por 1 hora
                
        } catch (\Exception $e) {
            Log::error('Erro ao servir imagem: ' . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->getDefaultImage();
        }
    }
    
    /**
     * Retorna uma imagem padrão quando a imagem não é encontrada
     */
    private function getDefaultImage()
    {
        // Cria uma imagem SVG simples como placeholder
        $svg = '<svg width="64" height="64" xmlns="http://www.w3.org/2000/svg">
            <rect width="64" height="64" fill="#f3f4f6"/>
            <text x="32" y="32" text-anchor="middle" dy=".3em" font-family="Arial" font-size="10" fill="#9ca3af">
                Sem Imagem
            </text>
        </svg>';
        
        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400'); // Cache por 24 horas
    }
}
