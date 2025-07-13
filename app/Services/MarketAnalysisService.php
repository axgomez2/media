<?php

namespace App\Services;

use App\Models\MarketAnalysis;
use App\Services\DiscogsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class MarketAnalysisService
{
    protected $discogsService;

    // Países principais para monitoramento
    protected $targetCountries = [
        'United States',
        'United Kingdom',
        'Germany',
        'Italy',
        'France',
        'Japan',
        'Netherlands',
        'Canada',
        'Australia',
        'Brazil'
    ];

    public function __construct(DiscogsService $discogsService)
    {
        $this->discogsService = $discogsService;
    }

    /**
     * Coleta dados de volume do Discogs usando web scraping para obter listings reais.
     * Agora obtém o número correto de anúncios (listings) e não apenas releases.
     */
    public function performDailyAnalysis(): bool
    {
        $today = Carbon::now()->format('Y-m-d');
        Log::info('Iniciando coleta de dados de listings do Discogs.', ['date' => $today]);

        try {
            // Mapeia o nome do país para a coluna correspondente na tabela
            $countryColumnMap = [
                'Brazil' => 'br_listings',
                'United Kingdom' => 'gb_listings',
                'Germany' => 'de_listings',
                'United States' => 'us_listings',
                'France' => 'fr_listings',
                'Italy' => 'it_listings',
                'Japan' => 'jp_listings',
                'Canada' => 'ca_listings',
                'Belgium' => 'be_listings',
                'Sweden' => 'se_listings',
            ];

            // 1. Obter o total de listings de vinil no mundo (sem filtro de país)
            $totalListings = $this->discogsService->getMarketAnalysisData();
            if ($totalListings === null) {
                Log::error('Não foi possível obter o total de listings mundial.');
                return false;
            }

            Log::info('Total mundial de listings obtido', ['total' => $totalListings]);

            // 2. Coleta dados por país
            $countryDataForDb = [];
            foreach ($countryColumnMap as $countryName => $columnName) {
                Log::info('Buscando dados para país', ['country' => $countryName]);

                $countryTotal = $this->discogsService->getMarketAnalysisData($countryName);
                $countryDataForDb[$columnName] = $countryTotal ?? 0;

                Log::info('Dados obtidos para país', [
                    'country' => $countryName,
                    'total' => $countryTotal ?? 0
                ]);

                // Delay maior para evitar bloqueio
                sleep(2); // 2 segundos entre requisições
            }

            // Prepara o array final de dados para o updateOrCreate
            $analysisData = array_merge(
                ['total_listings' => $totalListings],
                $countryDataForDb
            );

            // Salva os dados coletados no banco
            MarketAnalysis::updateOrCreate(
                ['analysis_date' => $today],
                $analysisData
            );

            Log::info('Dados de listings do Discogs salvos com sucesso.', [
                'total_listings' => $totalListings,
                'countries_data' => $countryDataForDb
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Exceção durante a coleta de dados de listings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o total de releases de vinil na API do Discogs.
     */
    private function fetchTotalFromApi(string $country = null): ?int
    {
        $params = [
            'type' => 'release',
            'format' => 'vinyl',
            'per_page' => 1,
            'token' => config('services.discogs.token'),
        ];

        if ($country) {
            $params['country'] = $country;
        }

        try {
            $response = Http::get('https://api.discogs.com/database/search', $params);
            if ($response->successful()) {
                return $response->json()['pagination']['items'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao chamar a API de busca do Discogs.', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Busca o breakdown por país usando a API.
     */
    private function fetchCountryBreakdownFromApi(): array
    {
        $breakdown = [];
        // A API permite filtrar por país, então iteramos para obter o total de cada um.
        foreach ($this->targetCountries as $country) {
            $total = $this->fetchTotalFromApi($country);
            if ($total !== null) {
                $breakdown[$country] = $total;
                usleep(500000); // Delay para não exceder o rate limit.
            }
        }
        return $breakdown;
    }

    /**
     * Coletar dados de volume do marketplace do Discogs.
     */
    protected function collectMarketplaceVolumeData(): array
    {
        Log::info('Coletando totais de vinil no Discogs via database/search');
        $data = [];

        try {
            // 1. Obter o total de listagens de vinil no mundo
            $totalListings = $this->fetchWorldwideTotal();
            if ($totalListings === null) {
                Log::error('Não foi possível obter o total de listagens mundial.');
                return [];
            }
            Log::info('Total de listagens de vinil no mundo', ['total' => $totalListings]);

            // 2. Obter o breakdown por país
            $countriesData = $this->fetchCountryBreakdown();

            $data['marketplace_stats'] = [
                'total_vinyl_listings' => $totalListings,
                'countries_data' => $countriesData,
                'collected_at' => Carbon::now()->toISOString(),
            ];

            return $data;

        } catch (\Exception $e) {
            Log::error('Erro ao coletar dados do marketplace: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca o total de listagens de vinil no mundo.
     */
    private function fetchWorldwideTotal(): ?int
    {
        return $this->fetchDiscogsTotal();
    }

    /**
     * Busca o total de listagens para cada país alvo.
     */
    private function fetchCountryBreakdown(): array
    {
        $countriesData = [];
        foreach ($this->targetCountries as $country) {
            $total = $this->fetchDiscogsTotal($country);
            if ($total !== null) {
                $countriesData[$country] = $total;
            }
            // Delay para respeitar o rate limit da API do Discogs (60 req/min)
            usleep(500000); // 0.5 segundos
        }
        return $countriesData;
    }

    /**
     * Função que "lê" a página de vendas do Discogs para extrair o número total de anúncios.
     * Optamos por esta abordagem (web scraping) porque a API oficial do Discogs retorna o número
     * de "releases" (títulos únicos), e não o total de "listings" (anúncios individuais),
     * que é o dado correto para esta análise de mercado.
     *
     * Se um país é fornecido, a busca é filtrada por esse país.
     */
    private function fetchDiscogsTotal(string $country = null): ?int
    {
        $url = 'https://www.discogs.com/sell/list?format=Vinyl';
        if ($country) {
            $url .= '&ships_from=' . urlencode($country);
        }

        try {
            // É crucial simular um navegador com um User-Agent comum para evitar ser bloqueado.
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
            ])->get($url);

            if ($response->successful()) {
                $html = $response->body();

                // A expressão regular procura pelo padrão "1-25 of X,XXX,XXX" dentro da tag <strong class="pagination_total">.
                if (preg_match('/<strong class="pagination_total"[^>]*>.*? of ([\d,]+)<\/strong>/i', $html, $matches)) {
                    $totalStr = $matches[1];
                    // Remove as vírgulas e converte para um número inteiro.
                    return (int) str_replace(',', '', $totalStr);
                }

                Log::warning('Não foi possível encontrar o padrão do total de anúncios no HTML do Discogs.', [
                    'country' => $country,
                    'url' => $url
                ]);
            } else {
                 Log::warning('Falha ao tentar ler a página de vendas do Discogs.', [
                    'country' => $country,
                    'status' => $response->status(),
                    'url' => $url
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ocorreu uma exceção ao tentar fazer o scraping da página do Discogs.', [
                'country' => $country,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Processar dados coletados focando em volumes por país
     */
    protected function processVolumeData(array $marketData): array
    {
        $countryBreakdown = [];
        $categoryBreakdown = [];
        $priceRanges = ['0-25' => 0, '25-50' => 0, '50-100' => 0, '100-200' => 0, '200+' => 0];
        $labelBreakdown = [];
        $decadeBreakdown = [];
        $prices = [];
        $brazilListings = 0;
        $brazilPrices = [];
        $topReleases = [];
        $trendingArtists = [];

        // Usar dados estimados de volume por país
        if (isset($marketData['marketplace_stats']['countries_data'])) {
            $countryBreakdown = $marketData['marketplace_stats']['countries_data'];
            $brazilListings = $countryBreakdown['Brazil'] ?? 0;
        }

        // Processar amostra de releases para outras estatísticas
        foreach ($marketData as $key => $release) {
            if ($key === 'marketplace_stats') continue;

            $numForSale = $release['num_for_sale'] ?? 0;

            // Processar estilos/categorias
            if (isset($release['styles'])) {
                foreach ($release['styles'] as $style) {
                    $categoryBreakdown[$style] = ($categoryBreakdown[$style] ?? 0) + $numForSale;
                }
            }

            // Processar preços
            if (isset($release['median_price']) && $release['median_price'] > 0) {
                $price = $release['median_price'];
                $prices[] = $price;

                // Classificar em faixas de preço
                if ($price <= 25) {
                    $priceRanges['0-25'] += $numForSale;
                } elseif ($price <= 50) {
                    $priceRanges['25-50'] += $numForSale;
                } elseif ($price <= 100) {
                    $priceRanges['50-100'] += $numForSale;
                } elseif ($price <= 200) {
                    $priceRanges['100-200'] += $numForSale;
                } else {
                    $priceRanges['200+'] += $numForSale;
                }
            }

            // Processar selo
            if (isset($release['labels']) && is_array($release['labels'])) {
                foreach ($release['labels'] as $label) {
                    $labelName = $label['name'] ?? 'Unknown';
                    $labelBreakdown[$labelName] = ($labelBreakdown[$labelName] ?? 0) + $numForSale;
                }
            }

            // Processar década
            if (isset($release['year']) && $release['year'] > 0) {
                $decade = floor($release['year'] / 10) * 10;
                $decadeBreakdown[$decade . 's'] = ($decadeBreakdown[$decade . 's'] ?? 0) + $numForSale;
            }

            // Top releases
            if ($numForSale > 0) {
                $topReleases[] = [
                    'title' => $release['title'] ?? 'Unknown',
                    'artist' => isset($release['artists'][0]['name']) ? $release['artists'][0]['name'] : 'Unknown',
                    'year' => $release['year'] ?? null,
                    'listings' => $numForSale,
                    'price' => $release['median_price'] ?? 0,
                ];
            }

            // Trending artists
            if (isset($release['artists']) && is_array($release['artists'])) {
                foreach ($release['artists'] as $artist) {
                    $artistName = $artist['name'] ?? 'Unknown';
                    if (!isset($trendingArtists[$artistName])) {
                        $trendingArtists[$artistName] = 0;
                    }
                    $trendingArtists[$artistName] += $numForSale;
                }
            }
        }

        // Ordenar dados
        arsort($categoryBreakdown);
        arsort($countryBreakdown);
        arsort($labelBreakdown);
        arsort($decadeBreakdown);
        arsort($trendingArtists);

        usort($topReleases, function($a, $b) {
            return $b['listings'] <=> $a['listings'];
        });

        // Calcular estatísticas
        $totalListings = isset($marketData['marketplace_stats']['total_vinyl_listings']) ?
                        $marketData['marketplace_stats']['total_vinyl_listings'] :
                        array_sum($countryBreakdown);

        $avgPrice = !empty($prices) ? array_sum($prices) / count($prices) : 0;
        $medianPrice = !empty($prices) ? $this->calculateMedian($prices) : 0;
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 0;
        $brazilAvgPrice = !empty($brazilPrices) ? array_sum($brazilPrices) / count($brazilPrices) : null;

        return [
            'country_breakdown' => $countryBreakdown,
            'category_breakdown' => array_slice($categoryBreakdown, 0, 20, true),
            'price_ranges' => $priceRanges,
            'label_breakdown' => array_slice($labelBreakdown, 0, 20, true),
            'decade_breakdown' => $decadeBreakdown,
            'total_listings' => $totalListings,
            'avg_price' => round($avgPrice, 2),
            'median_price' => round($medianPrice, 2),
            'min_price' => round($minPrice, 2),
            'max_price' => round($maxPrice, 2),
            'brazil_listings' => $brazilListings,
            'brazil_avg_price' => $brazilAvgPrice ? round($brazilAvgPrice, 2) : null,
            'top_releases' => array_slice($topReleases, 0, 10),
            'trending_artists' => array_slice($trendingArtists, 0, 15, true),
        ];
    }

    /**
     * Calcular mediana de um array de valores
     */
    protected function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);

        if ($count === 0) {
            return 0;
        }

        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        } else {
            return $values[$middle];
        }
    }

    /**
     * Obter estatísticas resumidas para dashboard
     */
    public function getDashboardStats(): array
    {
        $latest = MarketAnalysis::getLatest();

        if (!$latest) {
            return [
                'message' => 'Nenhuma análise disponível ainda',
                'has_data' => false,
            ];
        }

        $summaryStats = MarketAnalysis::getSummaryStats();

        // Encontrar o país com mais listings
        $countryData = [
            'Brasil' => $latest->br_listings,
            'Reino Unido' => $latest->gb_listings,
            'Alemanha' => $latest->de_listings,
            'Estados Unidos' => $latest->us_listings,
            'França' => $latest->fr_listings,
            'Itália' => $latest->it_listings,
            'Japão' => $latest->jp_listings,
            'Canadá' => $latest->ca_listings,
            'Bélgica' => $latest->be_listings,
            'Suécia' => $latest->se_listings,
        ];

        arsort($countryData);
        $topCountry = array_key_first($countryData);

        return [
            'has_data' => true,
            'latest_date' => $latest->analysis_date->format('d/m/Y'),
            'total_listings' => number_format($latest->total_listings),
            'brazil_listings' => number_format($latest->br_listings),
            'top_country' => $topCountry,
            'growth' => $summaryStats['growth'] ?? null,
        ];
    }
}
