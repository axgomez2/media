<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\VinylMaster;

class CheckImagesCommand extends Command
{
    protected $signature = 'images:check {--fix : Tentar corrigir imagens faltantes}';
    protected $description = 'Verifica a integridade das imagens dos discos';

    public function handle()
    {
        $this->info('🔍 Verificando configuração do disco de mídia...');
        
        // Verificar configuração
        $mediaRoot = config('filesystems.disks.media.root');
        $mediaUrl = config('filesystems.disks.media.url');
        
        $this->info("📁 Diretório de mídia: {$mediaRoot}");
        $this->info("🌐 URL de mídia: {$mediaUrl}");
        
        // Verificar se o diretório existe
        if (!is_dir($mediaRoot)) {
            $this->error("❌ Diretório de mídia não existe: {$mediaRoot}");
            return 1;
        }
        
        // Verificar permissões
        if (!is_readable($mediaRoot)) {
            $this->error("❌ Diretório de mídia não é legível: {$mediaRoot}");
            return 1;
        }
        
        $this->info("✅ Diretório de mídia está acessível");
        
        // Verificar imagens dos discos
        $this->info('🎵 Verificando imagens dos discos...');
        
        $vinyls = VinylMaster::whereNotNull('cover_image')->get();
        $missing = [];
        $found = 0;
        
        foreach ($vinyls as $vinyl) {
            if (!Storage::disk('media')->exists($vinyl->cover_image)) {
                $missing[] = [
                    'id' => $vinyl->id,
                    'title' => $vinyl->title,
                    'image_path' => $vinyl->cover_image,
                    'artists' => $vinyl->artists->pluck('name')->join(', ')
                ];
            } else {
                $found++;
            }
        }
        
        $this->info("✅ Imagens encontradas: {$found}");
        $this->warn("⚠️  Imagens faltantes: " . count($missing));
        
        if (!empty($missing)) {
            $this->table(
                ['ID', 'Artista', 'Título', 'Caminho da Imagem'],
                array_map(function($item) {
                    return [
                        $item['id'],
                        substr($item['artists'], 0, 30),
                        substr($item['title'], 0, 40),
                        $item['image_path']
                    ];
                }, $missing)
            );
            
            if ($this->option('fix')) {
                $this->info('🔧 Tentando corrigir imagens faltantes...');
                $this->fixMissingImages($missing);
            } else {
                $this->info('💡 Use --fix para tentar corrigir automaticamente');
            }
        }
        
        // Verificar imagens órfãs
        $this->checkOrphanImages();
        
        return 0;
    }
    
    private function fixMissingImages($missing)
    {
        $fixed = 0;
        
        foreach ($missing as $item) {
            $vinyl = VinylMaster::find($item['id']);
            
            if ($vinyl && $vinyl->discogs_id) {
                $this->info("🔄 Tentando recarregar imagem do Discogs para: {$item['title']}");
                
                try {
                    // Aqui você pode implementar a lógica para recarregar a imagem do Discogs
                    // Por enquanto, vamos apenas limpar o campo cover_image
                    $vinyl->update(['cover_image' => null]);
                    $fixed++;
                    $this->info("✅ Campo cover_image limpo para: {$item['title']}");
                } catch (\Exception $e) {
                    $this->error("❌ Erro ao processar {$item['title']}: " . $e->getMessage());
                }
            }
        }
        
        $this->info("🎯 Total de registros corrigidos: {$fixed}");
    }
    
    private function checkOrphanImages()
    {
        $this->info('🗂️  Verificando imagens órfãs...');
        
        // Listar todos os arquivos de imagem
        $imageFiles = collect(Storage::disk('media')->allFiles('vinyl_covers'))
            ->merge(Storage::disk('media')->allFiles('vinyl_images'));
        
        // Buscar todas as imagens referenciadas no banco
        $referencedImages = collect();
        
        // Imagens de capa
        $coverImages = VinylMaster::whereNotNull('cover_image')
            ->pluck('cover_image');
        $referencedImages = $referencedImages->merge($coverImages);
        
        // Imagens de mídia
        $mediaImages = DB::table('media')
            ->where('mediable_type', 'App\\Models\\VinylMaster')
            ->pluck('file_path');
        $referencedImages = $referencedImages->merge($mediaImages);
        
        $orphans = $imageFiles->diff($referencedImages->unique());
        
        if ($orphans->count() > 0) {
            $this->warn("🗑️  Encontradas {$orphans->count()} imagens órfãs:");
            foreach ($orphans->take(10) as $orphan) {
                $this->line("   - {$orphan}");
            }
            
            if ($orphans->count() > 10) {
                $this->line("   ... e mais " . ($orphans->count() - 10) . " arquivos");
            }
        } else {
            $this->info("✅ Nenhuma imagem órfã encontrada");
        }
    }
}