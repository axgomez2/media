<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MarketAnalysisController extends Controller
{
    /**
     * Exibe a página principal de análise de mercado com formulário,
     * tabela de dados e gráficos.
     */
    public function index()
    {
        // Dados para a tabela de histórico
        $analyses = MarketAnalysis::orderBy('analysis_date', 'desc')->paginate(15);

        // Instância para o formulário de criação
        $newAnalysis = new MarketAnalysis(['analysis_date' => Carbon::today()->format('Y-m-d')]);

        // Dados para os gráficos (últimos 60 dias)
        $chartDataRaw = MarketAnalysis::where('analysis_date', '>=', Carbon::now()->subDays(60))
                               ->orderBy('analysis_date', 'asc')
                               ->get();

        $chartData = [
            'labels' => $chartDataRaw->pluck('analysis_date')->map(fn($date) => $date->format('d/m')),
            'total_listings' => $chartDataRaw->pluck('total_listings'),
            'br_listings' => $chartDataRaw->pluck('br_listings'),
            'us_listings' => $chartDataRaw->pluck('us_listings'),
            'gb_listings' => $chartDataRaw->pluck('gb_listings'),
            'de_listings' => $chartDataRaw->pluck('de_listings'),
            'fr_listings' => $chartDataRaw->pluck('fr_listings'),
            'it_listings' => $chartDataRaw->pluck('it_listings'),
            'jp_listings' => $chartDataRaw->pluck('jp_listings'),
            'ca_listings' => $chartDataRaw->pluck('ca_listings'),
            'be_listings' => $chartDataRaw->pluck('be_listings'),
            'se_listings' => $chartDataRaw->pluck('se_listings'),
        ];

        return view('admin.market-analysis.index', [
            'analyses' => $analyses,
            'analysis' => $newAnalysis, // Para o formulário de criação
            'chartData' => $chartData,
        ]);
    }

    /**
     * Salva um novo registro de análise no banco de dados.
     */
    public function store(Request $request)
    {
        $validatedData = $this->validateRequest($request);
        MarketAnalysis::create($validatedData);

        return redirect()->route('admin.market-analysis.index')
                         ->with('success', 'Análise de mercado salva com sucesso!');
    }

    /**
     * Atualiza um registro de análise existente.
     */
    public function update(Request $request, MarketAnalysis $marketAnalysis)
    {
        $validatedData = $this->validateRequest($request, $marketAnalysis->id);
        $marketAnalysis->update($validatedData);

        return redirect()->route('admin.market-analysis.index')
                         ->with('success', 'Análise de mercado atualizada com sucesso!');
    }

    /**
     * Remove um registro de análise do banco de dados.
     */
    public function destroy(MarketAnalysis $marketAnalysis)
    {
        $marketAnalysis->delete();

        return redirect()->route('admin.market-analysis.index')
                         ->with('success', 'Registro de análise excluído com sucesso!');
    }

    /**
     * Valida os dados do formulário para store e update.
     */
    private function validateRequest(Request $request, $ignoreId = null)
    {
        $rules = [
            'analysis_date' => 'required|date|unique:market_analysis,analysis_date',
            'total_listings' => 'required|integer|min:0',
            'br_listings' => 'nullable|integer|min:0',
            'gb_listings' => 'nullable|integer|min:0',
            'de_listings' => 'nullable|integer|min:0',
            'us_listings' => 'nullable|integer|min:0',
            'fr_listings' => 'nullable|integer|min:0',
            'it_listings' => 'nullable|integer|min:0',
            'jp_listings' => 'nullable|integer|min:0',
            'ca_listings' => 'nullable|integer|min:0',
            'be_listings' => 'nullable|integer|min:0',
            'se_listings' => 'nullable|integer|min:0',
        ];

        if ($ignoreId) {
            $rules['analysis_date'] .= ',' . $ignoreId;
        }

        // Garante que campos nulos sejam salvos como 0
        $data = $request->validate($rules);
        foreach ($data as $key => $value) {
            if ($value === null && $key !== 'analysis_date') {
                $data[$key] = 0;
            }
        }

        return $data;
    }

    /**
     * Tenta coletar dados automaticamente do Discogs via web scraping
     */
    public function autoCollect()
    {
        try {
            $today = Carbon::today()->format('Y-m-d');

            // Verificar se já existe registro para hoje
            if (MarketAnalysis::whereDate('analysis_date', $today)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe um registro para hoje. Use a edição manual se necessário.'
                ]);
            }

            // Coletar dados automaticamente
            $data = $this->collectDiscogsData();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível coletar os dados automaticamente. Use a inserção manual.'
                ]);
            }

            // Salvar os dados
            $data['analysis_date'] = $today;
            MarketAnalysis::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Dados coletados e salvos automaticamente!',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao coletar dados: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Coleta dados do Discogs via web scraping
     */
    private function collectDiscogsData()
    {
        $countries = [
            'Brazil' => 'br_listings',
            'United States' => 'us_listings',
            'United Kingdom' => 'gb_listings',
            'Germany' => 'de_listings',
            'France' => 'fr_listings',
            'Italy' => 'it_listings',
            'Japan' => 'jp_listings',
            'Canada' => 'ca_listings',
            'Belgium' => 'be_listings',
            'Sweden' => 'se_listings',
        ];

        try {
            // Buscar total mundial
            $totalListings = $this->scrapeDiscogsData();
            if (!$totalListings) {
                return null;
            }

            $data = ['total_listings' => $totalListings];

            // Buscar por país (com delay para evitar bloqueio)
            foreach ($countries as $country => $field) {
                $countryListings = $this->scrapeDiscogsData($country);
                $data[$field] = $countryListings ?? 0;
                sleep(1); // Delay de 1 segundo entre requisições para não sobrecarregar
            }

            return $data;

        } catch (\Exception $e) {
            \Log::error('Erro ao coletar dados do Discogs: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Faz o scraping da página do Discogs
     */
    private function scrapeDiscogsData($country = null)
    {
        try {
            $url = 'https://www.discogs.com/sell/list?format=Vinyl';
            if ($country) {
                $url .= '&ships_from=' . urlencode($country);
            }

            // Adicionar um user agent aleatório para evitar bloqueios
            $userAgents = [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Safari/605.1.15',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:102.0) Gecko/20100101 Firefox/102.0',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36'
            ];
            
            $randomUserAgent = $userAgents[array_rand($userAgents)];

            $response = \Http::withHeaders([
                'User-Agent' => $randomUserAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ])->timeout(30)->get($url);

            if (!$response->successful()) {
                \Log::warning('Resposta não bem-sucedida do Discogs. Status: ' . $response->status());
                return null;
            }

            $html = $response->body();

            // Procurar pelo padrão que mostra o total
            $patterns = [
                '/\b(\d{1,3}(?:,\d{3})*)\s+(?:results|items|listings)/i',
                '/<strong[^>]*>.*?of\s+([\d,]+)<\/strong>/i',
                '/of\s+([\d,]+)\s*(?:results|items)/i',
                '/found\s+([\d,]+)\s*(?:results|items)/i'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $totalStr = $matches[1];
                    return (int) str_replace(',', '', $totalStr);
                }
            }

            // Se nenhum padrão encontrado, registre parte do HTML para debug
            \Log::warning('Nenhum padrão encontrado no HTML do Discogs. Fragmento do HTML: ' . substr($html, 0, 500) . '...');
            return null;

        } catch (\Exception $e) {
            \Log::error('Erro no scraping: ' . $e->getMessage());
            return null;
        }
    }
}
