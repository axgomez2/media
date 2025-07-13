<?php

namespace App\Console\Commands;

use App\Services\MarketAnalysisService;
use App\Models\MarketAnalysis;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MarketAnalysisCommand extends Command
{
    protected $signature = 'market:analyze {--force : Forçar nova análise mesmo se já existir uma para hoje}';
    protected $description = 'Executar análise diária do mercado Discogs focada em volumes por país';

    public function handle()
    {
        $this->info('🎵 Iniciando análise do mercado Discogs...');

        $today = Carbon::now()->format('Y-m-d');
        $force = $this->option('force');

        // Verificar se já existe análise para hoje
        if (!$force && MarketAnalysis::whereDate('analysis_date', $today)->exists()) {
            $this->warn('⚠️  Análise já foi executada hoje. Use --force para forçar nova análise.');
            return;
        }

        // Se force for usado, deletar análise existente
        if ($force) {
            MarketAnalysis::whereDate('analysis_date', $today)->delete();
            $this->info('🗑️  Análise anterior removida');
        }

        $service = app(MarketAnalysisService::class);

        $this->info('📊 Coletando dados do marketplace...');

        $success = $service->performDailyAnalysis();

        if ($success) {
            $this->info('✅ Análise concluída com sucesso!');

            // Mostrar estatísticas resumidas
            $stats = $service->getDashboardStats();
            if ($stats['has_data']) {
                $this->info('📈 Estatísticas:');
                $this->info("   • Total de discos à venda: {$stats['total_listings']}");
                $this->info("   • Discos no Brasil: {$stats['brazil_listings']}");
                $this->info("   • País líder: {$stats['top_country']}");
            }
        } else {
            $this->error('❌ Falha ao executar análise');
        }
    }
}
