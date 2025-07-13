<?php

namespace App\Http\Controllers;

use App\Models\MarketAnalysis;
use App\Services\MarketAnalysisService;
use App\Services\DiscogsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MarketAnalysisController extends Controller
{
    protected $marketAnalysisService;

    public function __construct(MarketAnalysisService $marketAnalysisService)
    {
        $this->marketAnalysisService = $marketAnalysisService;
    }

    /**
     * Exibir dashboard principal
     */
    public function index()
    {
        $latest = MarketAnalysis::getLatest();

        if (!$latest) {
            return view('admin.market-analysis.index', [
                'hasData' => false,
                'totalListings' => 0,
                'avgPrice' => 'R$ 0,00',
                'brazilListings' => 0,
                'latestDate' => 'Nunca',
                'countryBreakdown' => [],
                'categoryBreakdown' => [],
                'totalListingsRaw' => 0,
                'topCountry' => 'N/A',
                'topCategory' => 'N/A',
                'brazilListingsRaw' => 0,
            ]);
        }

        // Criar breakdown de países baseado nos campos da nova estrutura
        $countryBreakdown = [
            'Brasil' => $latest->br_listings,
            'Estados Unidos' => $latest->us_listings,
            'Reino Unido' => $latest->gb_listings,
            'Alemanha' => $latest->de_listings,
            'França' => $latest->fr_listings,
            'Itália' => $latest->it_listings,
            'Japão' => $latest->jp_listings,
            'Canadá' => $latest->ca_listings,
            'Bélgica' => $latest->be_listings,
            'Suécia' => $latest->se_listings,
        ];
        arsort($countryBreakdown);

        $categoryBreakdown = []; // Não temos mais categorias na nova estrutura
        $totalListingsRaw = $latest->total_listings;
        $brazilListingsRaw = $latest->br_listings;

        return view('admin.market-analysis.index', [
            'hasData' => true,
            'totalListings' => number_format($totalListingsRaw),
            'avgPrice' => 'R$ 0,00', // Campo não existe mais na nova estrutura
            'brazilListings' => number_format($brazilListingsRaw),
            'latestDate' => $latest->analysis_date->format('d/m/Y'),
            'countryBreakdown' => $countryBreakdown,
            'categoryBreakdown' => $categoryBreakdown,
            'totalListingsRaw' => $totalListingsRaw,
            'topCountry' => array_key_first($countryBreakdown) ?: 'N/A',
            'topCategory' => array_key_first($categoryBreakdown) ?: 'N/A',
            'brazilListingsRaw' => $brazilListingsRaw,
        ]);
    }

    /**
     * Exibir gráficos e visualizações
     */
    public function charts()
    {
        $analyses = MarketAnalysis::orderBy('analysis_date', 'desc')->take(30)->get();

        return view('admin.market-analysis.charts', [
            'analyses' => $analyses,
            'latest' => $analyses->first(),
        ]);
    }

    /**
     * Exibir histórico de análises
     */
    public function history(Request $request)
    {
        $days = $request->get('days', 30);
        $analyses = MarketAnalysis::where('analysis_date', '>=', now()->subDays($days))
                                  ->orderBy('analysis_date', 'desc')
                                  ->paginate(15);

        return view('admin.market-analysis.history', [
            'analyses' => $analyses,
            'days' => $days,
        ]);
    }

    /**
     * Forçar nova análise
     */
    public function forceAnalysis()
    {
        try {
            Artisan::call('market:analyze', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Análise executada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar análise: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar dados para CSV
     */
    public function exportCsv()
    {
        $analyses = MarketAnalysis::orderBy('analysis_date', 'desc')->get();

        $filename = 'market_analysis_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($analyses) {
            $file = fopen('php://output', 'w');

            // Cabeçalho
            fputcsv($file, [
                'Data',
                'Total Listagens',
                'Preço Médio',
                'Preço Mediano',
                'Listagens Brasil',
                'Preço Médio Brasil',
                'País Líder',
                'Categoria Principal'
            ]);

            // Dados
            foreach ($analyses as $analysis) {
                $countryBreakdown = $analysis->country_breakdown ?: [];
                $categoryBreakdown = $analysis->category_breakdown ?: [];

                fputcsv($file, [
                    $analysis->analysis_date->format('d/m/Y'),
                    $analysis->total_listings,
                    'R$ ' . number_format($analysis->avg_price, 2, ',', '.'),
                    'R$ ' . number_format($analysis->median_price, 2, ',', '.'),
                    $analysis->brazil_listings,
                    $analysis->brazil_avg_price ? 'R$ ' . number_format($analysis->brazil_avg_price, 2, ',', '.') : 'N/A',
                    array_key_first($countryBreakdown) ?: 'N/A',
                    array_key_first($categoryBreakdown) ?: 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API: Dados da análise mais recente
     */
    public function apiLatest()
    {
        $latest = MarketAnalysis::getLatest();

        if (!$latest) {
            return response()->json([
                'error' => 'Nenhuma análise disponível'
            ], 404);
        }

        return response()->json([
            'date' => $latest->analysis_date->format('Y-m-d'),
            'total_listings' => $latest->total_listings,
            'avg_price' => $latest->avg_price,
            'median_price' => $latest->median_price,
            'brazil_listings' => $latest->brazil_listings,
            'brazil_avg_price' => $latest->brazil_avg_price,
            'country_breakdown' => $latest->country_breakdown,
            'category_breakdown' => $latest->category_breakdown,
            'price_ranges' => $latest->price_ranges,
        ]);
    }

    /**
     * API: Evolução temporal dos dados
     */
    public function apiEvolution()
    {
        $analyses = MarketAnalysis::orderBy('analysis_date', 'asc')->get();

        $evolution = $analyses->map(function($analysis) {
            return [
                'date' => $analysis->analysis_date->format('Y-m-d'),
                'total_listings' => $analysis->total_listings,
                'avg_price' => $analysis->avg_price,
                'brazil_listings' => $analysis->brazil_listings,
                'top_country' => array_key_first($analysis->country_breakdown ?: []),
                'top_category' => array_key_first($analysis->category_breakdown ?: []),
            ];
        });

        return response()->json($evolution);
    }

    /**
     * API: Comparação entre países
     */
    public function apiCountryComparison()
    {
        $latest = MarketAnalysis::getLatest();

        if (!$latest || !$latest->country_breakdown) {
            return response()->json([
                'error' => 'Dados não disponíveis'
            ], 404);
        }

        $countries = [];
        $total = array_sum($latest->country_breakdown);

        foreach ($latest->country_breakdown as $country => $count) {
            $countries[] = [
                'country' => $country,
                'listings' => $count,
                'percentage' => round(($count / $total) * 100, 2),
            ];
        }

        return response()->json($countries);
    }
}
