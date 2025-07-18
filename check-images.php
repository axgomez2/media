<?php
/**
 * Script de verificação rápida de imagens para produção
 * Execute: php check-images.php
 */

require_once 'vendor/autoload.php';

// Carregar configurações do Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Verificação Rápida de Imagens - RDV Discos\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Verificar configurações
$mediaRoot = config('filesystems.disks.media.root');
$mediaUrl = config('filesystems.disks.media.url');

echo "📁 Diretório de mídia: {$mediaRoot}\n";
echo "🌐 URL de mídia: {$mediaUrl}\n\n";

// Verificar se o diretório existe
if (!is_dir($mediaRoot)) {
    echo "❌ ERRO: Diretório de mídia não existe!\n";
    echo "   Crie o diretório: mkdir -p {$mediaRoot}\n";
    exit(1);
}

// Verificar permissões
if (!is_readable($mediaRoot)) {
    echo "❌ ERRO: Diretório de mídia não é legível!\n";
    echo "   Ajuste as permissões: chmod 755 {$mediaRoot}\n";
    exit(1);
}

echo "✅ Diretório de mídia está acessível\n\n";

// Verificar algumas imagens de exemplo
echo "🎵 Verificando imagens dos discos...\n";

try {
    $vinyls = \App\Models\VinylMaster::whereNotNull('cover_image')
        ->limit(10)
        ->get();
    
    $found = 0;
    $missing = 0;
    
    foreach ($vinyls as $vinyl) {
        $imagePath = $mediaRoot . '/' . $vinyl->cover_image;
        
        if (file_exists($imagePath)) {
            $found++;
            echo "✅ {$vinyl->title} - OK\n";
        } else {
            $missing++;
            echo "❌ {$vinyl->title} - FALTANDO: {$vinyl->cover_image}\n";
        }
    }
    
    echo "\n📊 Resumo da amostra:\n";
    echo "   ✅ Encontradas: {$found}\n";
    echo "   ❌ Faltantes: {$missing}\n\n";
    
    if ($missing > 0) {
        echo "💡 Soluções:\n";
        echo "   1. Execute: php artisan images:check --fix\n";
        echo "   2. Verifique se as imagens foram migradas corretamente\n";
        echo "   3. Verifique as permissões do diretório\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO ao verificar banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar espaço em disco
$freeBytes = disk_free_space($mediaRoot);
$totalBytes = disk_total_space($mediaRoot);
$usedBytes = $totalBytes - $freeBytes;

$freeGB = round($freeBytes / 1024 / 1024 / 1024, 2);
$totalGB = round($totalBytes / 1024 / 1024 / 1024, 2);
$usedGB = round($usedBytes / 1024 / 1024 / 1024, 2);

echo "💾 Espaço em disco:\n";
echo "   Total: {$totalGB} GB\n";
echo "   Usado: {$usedGB} GB\n";
echo "   Livre: {$freeGB} GB\n\n";

echo "✅ Verificação concluída!\n";