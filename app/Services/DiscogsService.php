<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscogsService
{
    /**
     * Busca discos no Discogs
     *
     * @param string $query Query de busca
     * @return array Resultados da busca
     * @throws \Exception
     */
    public function search(string $query): array
    {
        try {
            $response = Http::get('https://api.discogs.com/database/search', [
                'q' => $query,
                'type' => 'release',
                'token' => config('services.discogs.token'),
            ]);

            if (!$response->successful()) {
                throw new \Exception('Falha ao buscar dados da API do Discogs: ' . $response->body());
            }

            return $response->json()['results'] ?? [];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar no Discogs: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém detalhes de um lançamento específico do Discogs
     *
     * @param string $releaseId ID do lançamento no Discogs
     * @return array|null Dados do lançamento ou null se não encontrado
     */
    public function getRelease(string $releaseId): ?array
    {
        try {
            // Log a consulta sendo realizada
            Log::info("Consultando release do Discogs", ['release_id' => $releaseId]);

            // Obter informações do release
            $response = Http::get("https://api.discogs.com/releases/{$releaseId}", [
                'token' => config('services.discogs.token'),
            ]);

            if (!$response->successful()) {
                Log::error("Falha ao obter release do Discogs", [
                    'release_id' => $releaseId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $releaseData = $response->json();

            // Log com as informações básicas do release para debug
            Log::info("Dados básicos do release obtidos", [
                'release_id' => $releaseId,
                'title' => $releaseData['title'] ?? 'N/A',
                'year' => $releaseData['year'] ?? 'N/A',
                'num_images' => isset($releaseData['images']) ? count($releaseData['images']) : 0,
            ]);

            // Remover os vídeos da resposta (não são necessários para o cadastro)
            if (isset($releaseData['videos'])) {
                unset($releaseData['videos']);
            }

            // Buscar informações de preço (usar BRL para obter valores diretamente em reais)
            Log::info("Consultando estatísticas de mercado do Discogs", ['release_id' => $releaseId]);

            $marketResponse = Http::get("https://api.discogs.com/marketplace/stats/{$releaseId}", [
                'token' => config('services.discogs.token'),
                'curr_abbr' => 'BRL' // Solicitar valores em reais
            ]);

            // Verificar se obtivemos sucesso na consulta de estatísticas de mercado
            if (!$marketResponse->successful()) {
                Log::error("Falha ao obter estatísticas de mercado do Discogs", [
                    'release_id' => $releaseId,
                    'status' => $marketResponse->status(),
                    'body' => $marketResponse->body()
                ]);
            }

            // Registrar a resposta completa para debug com formato mais legível
            $marketData = $marketResponse->json();
            Log::info('Resposta detalhada do Discogs para market stats:', [
                'release_id' => $releaseId,
                'status_code' => $marketResponse->status(),
                'num_for_sale' => $marketData['num_for_sale'] ?? 'não disponível',
                'lowest_price' => $marketData['lowest_price'] ?? 'não disponível',
                'highest_price' => $marketData['highest_price'] ?? 'não disponível',
                'median_price' => $marketData['median_price'] ?? 'não disponível',
                'raw_response' => $marketData
            ]);

            // Buscar informações de vendas específicas do Brasil
            $brazilListings = $this->getBrazilListings($releaseId);
            $releaseData['brazil_listings'] = $brazilListings;

            if ($marketResponse->successful()) {
                $marketData = $marketResponse->json();

                // Verificar se temos dados de preço válidos
                $hasValidPriceData = isset($marketData['lowest_price']) ||
                                     isset($marketData['median_price']) ||
                                     isset($marketData['highest_price']);

                Log::info('Validade dos dados de preço:', [
                    'release_id' => $releaseId,
                    'has_valid_data' => $hasValidPriceData ? 'Sim' : 'Não',
                    'tipos_disponiveis' => array_keys($marketData)
                ]);

                // Adicionar os valores originais retornados pela API para debug
                $releaseData['raw_market_data'] = $marketData;

                // Preço mais baixo - usar exatamente o valor retornado pela API
                $lowestPrice = isset($marketData['lowest_price']['value']) && is_numeric($marketData['lowest_price']['value'])
                    ? (float)$marketData['lowest_price']['value']
                    : 0;
                $releaseData['lowest_price'] = $lowestPrice;

                // Preço médio - usar exatamente o valor retornado pela API
                $medianPrice = isset($marketData['median_price']['value']) && is_numeric($marketData['median_price']['value'])
                    ? (float)$marketData['median_price']['value']
                    : 0;
                $releaseData['median_price'] = $medianPrice;

                // Preço mais alto - usar exatamente o valor retornado pela API
                $highestPrice = isset($marketData['highest_price']['value']) && is_numeric($marketData['highest_price']['value'])
                    ? (float)$marketData['highest_price']['value']
                    : 0;
                $releaseData['highest_price'] = $highestPrice;

                // Número de cópias à venda - usar exatamente o valor retornado pela API
                $forSaleCount = isset($marketData['num_for_sale'])
                    ? (int)$marketData['num_for_sale']
                    : 0;
                $releaseData['num_for_sale'] = $forSaleCount;

                // Verificar se temos informações de vendedores brasileiros
                $brazilInfo = $releaseData['brazil_listings'] ?? null;
                $hasBrazilSellers = isset($brazilInfo['has_brazil_sellers']) ? (bool)$brazilInfo['has_brazil_sellers'] : false;
                $brazilLowestPrice = isset($brazilInfo['lowest_price']) ? (float)$brazilInfo['lowest_price'] : 0;
                $brazilMedianPrice = isset($brazilInfo['median_price']) ? (float)$brazilInfo['median_price'] : 0;

                // --- NOVA LÓGICA DE PRECIFICAÇÃO ---

                // Constantes para o cálculo de preço
                define('DOMESTIC_SHIPPING_COST', 25.00); // Custo estimado para frete nacional
                define('INTERNATIONAL_SHIPPING_COST', 100.00); // Custo estimado para frete internacional
                define('IMPORT_TAX_RATE', 0.96); // 96% de imposto de importação

                // Determinar preço sugerido com base na disponibilidade no Brasil
                if ($hasBrazilSellers && $brazilMedianPrice > 0) {
                    // CENÁRIO 1: Disco disponível no Brasil
                    $releaseData['suggested_price'] = $brazilMedianPrice + DOMESTIC_SHIPPING_COST;
                    $releaseData['price_source'] = 'Preço Médio BR + Frete Nacional';

                } else {
                    // CENÁRIO 2: Disco precisa ser importado
                    if ($medianPrice > 0) {
                        $baseCost = $medianPrice + INTERNATIONAL_SHIPPING_COST;
                        $taxes = $baseCost * IMPORT_TAX_RATE;
                        $releaseData['suggested_price'] = $baseCost + $taxes;
                        $releaseData['price_source'] = 'Custo de Importação (Médio Global + Frete + Impostos)';
                    } else if ($lowestPrice > 0) {
                        // Fallback se não houver preço médio global, usa o mais baixo
                        $baseCost = $lowestPrice + INTERNATIONAL_SHIPPING_COST;
                        $taxes = $baseCost * IMPORT_TAX_RATE;
                        $releaseData['suggested_price'] = $baseCost + $taxes;
                        $releaseData['price_source'] = 'Custo de Importação (Mínimo Global + Frete + Impostos)';
                    } else {
                        $releaseData['suggested_price'] = 0; // Nenhum dado de preço disponível
                        $releaseData['price_source'] = 'Sem dados de preço';
                    }
                }

                // Garantir que o preço sugerido não seja zero, como último recurso
                if ($releaseData['suggested_price'] <= 0) {
                    $releaseData['suggested_price'] = 50.00; // Valor padrão mínimo
                    $releaseData['price_source'] = 'Padrão (sem dados)';
                }

                Log::info('Preços calculados (Nova Lógica):', [
                    'global_min' => $releaseData['lowest_price'],
                    'global_median' => $releaseData['median_price'],
                    'brazil_min' => $brazilLowestPrice,
                    'brazil_median' => $brazilMedianPrice,
                    'sugerido' => $releaseData['suggested_price'],
                    'fonte' => $releaseData['price_source'],
                ]);
            }

            return $releaseData;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do Discogs: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca e faz download de uma imagem do Discogs
     *
     * @param string $imageUrl URL da imagem
     * @return string|null Conteúdo da imagem ou null em caso de erro
     */
    public function fetchImage(string $imageUrl): ?string
    {
        try {
            $response = Http::get($imageUrl);

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar imagem do Discogs: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém detalhes de um artista no Discogs
     *
     * @param string $artistId ID do artista no Discogs
     * @return array|null Dados do artista ou null se não encontrado
     */
    public function getArtistDetails(string $artistId): ?array
    {
        try {
            if (empty($artistId)) {
                return null;
            }

            $response = Http::get("https://api.discogs.com/artists/{$artistId}", [
                'token' => config('services.discogs.token'),
            ]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do artista no Discogs: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém detalhes de uma gravadora no Discogs
     *
     * @param string $labelId ID da gravadora no Discogs
     * @return array|null Dados da gravadora ou null se não encontrado
     */
    public function getLabelDetails(string $labelId): ?array
    {
        try {
            if (empty($labelId)) {
                return null;
            }

            $response = Http::get("https://api.discogs.com/labels/{$labelId}", [
                'token' => config('services.discogs.token'),
            ]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados da gravadora no Discogs: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém informações sobre discos à venda no Brasil para um lançamento específico
     *
     * @param string $releaseId ID do lançamento no Discogs
     * @return array Informações sobre vendas no Brasil
     */
    public function getBrazilListings(string $releaseId): array
    {
        try {
            // Primeiro tentamos obter diretamente a quantidade à venda na API do Discogs, independente do país
            // Buscar informações gerais do mercado para este lançamento
            $marketResponse = Http::get("https://api.discogs.com/marketplace/stats/{$releaseId}", [
                'token' => config('services.discogs.token'),
                'curr_abbr' => 'BRL' // Solicitar valores em reais
            ]);

            // Registrar os dados recebidos para este disco específico
            Log::info('Tentando obter dados de mercado para ID: ' . $releaseId, [
                'resposta' => $marketResponse->successful() ? $marketResponse->json() : 'Falha ao obter resposta'
            ]);

            // Agora fazer a busca de anúncios no Brasil usando a API de marketplace
            $listingsResponse = Http::get("https://api.discogs.com/marketplace/releases/{$releaseId}/listings", [
                'token' => config('services.discogs.token'),
                'currency' => 'BRL',
                'sort' => 'price',
                'per_page' => 100
            ]);

            if (!$listingsResponse->successful()) {
                Log::error('Falha ao consultar API do Discogs para listagens: ' . $listingsResponse->body());
                return [
                    'count' => 0,
                    'listings' => [],
                    'lowest_price' => 0,
                    'median_price' => 0,
                    'highest_price' => 0,
                    'has_brazil_sellers' => false
                ];
            }

            $data = $listingsResponse->json();

            // Logando os dados completos para debug
            Log::info('Dados completos de marketplace para o disco ' . $releaseId, [
                'total_listings' => count($data['listings'] ?? []),
                'pagination' => $data['pagination'] ?? [],
            ]);

            $listings = $data['listings'] ?? [];

            // Filtrar anúncios do Brasil (verificando tanto country quanto location)
            // e logando informações sobre vendedores para debug
            $brazilListings = [];
            $sellerInfo = [];

            foreach ($listings as $listing) {
                $isFromBrazil = false;
                $country = $listing['seller']['country'] ?? '';
                $location = $listing['seller']['location'] ?? '';

                // Adicionar informação do vendedor para debug
                $sellerInfo[] = [
                    'country' => $country,
                    'location' => $location,
                    'price' => $listing['price']['value'] ?? 0,
                    'condition' => $listing['condition'] ?? '',
                ];

                // Verificar se é do Brasil com mais robustez
                $normalizedCountry = strtolower(trim($country));
                if (in_array($normalizedCountry, ['brazil', 'brasil', 'br'])) {
                    $isFromBrazil = true;
                } elseif (!empty($location)) {
                    $normalizedLocation = strtolower(trim($location));
                    $brazilianIdentifiers = [
                        'brazil', 'brasil', ', br', ' sp,', ' rj,', ' mg,', ' rs,', ' pr,', ' sc,', ' df,',
                        'sao paulo', 'rio de janeiro', 'belo horizonte', 'curitiba', 'porto alegre', 'florianopolis'
                    ];

                    foreach ($brazilianIdentifiers as $identifier) {
                        if (strpos($normalizedLocation, $identifier) !== false) {
                            $isFromBrazil = true;
                            break; // Encontrou um identificador, não precisa continuar
                        }
                    }
                }

                if ($isFromBrazil) {
                    $brazilListings[] = $listing;
                }
            }

            // Logando informações de vendedores para debug
            Log::info('Informações de todos os vendedores para o disco ' . $releaseId, [
                'total_sellers' => count($sellerInfo),
                'sellers_info' => $sellerInfo
            ]);

            // Calcular informações úteis
            $count = count($brazilListings);
            $lowestPrice = 0;
            $medianPrice = 0;
            $highestPrice = 0;

            if ($count > 0) {
                // Coletar todos os preços dos anúncios brasileiros
                $prices = array_map(function($listing) {
                    $value = $listing['price']['value'] ?? 0;
                    // Converter para número se for string
                    return is_numeric($value) ? (float)$value : 0;
                }, $brazilListings);

                // Filtrar preços válidos (acima de zero)
                $prices = array_filter($prices, function($price) {
                    return $price > 0;
                });

                if (!empty($prices)) {
                    // Preço mais baixo
                    $lowestPrice = min($prices);

                    // Preço mais alto
                    $highestPrice = max($prices);

                    // Preço mediano (ordenar e pegar o do meio)
                    sort($prices);
                    $middle = floor(count($prices) / 2);
                    $medianPrice = $prices[$middle];
                }
            }

            Log::info('Encontrados ' . $count . ' anúncios no Brasil para o disco ID ' . $releaseId, [
                'min' => $lowestPrice,
                'median' => $medianPrice,
                'max' => $highestPrice
            ]);

            return [
                'count' => $count,
                'listings' => $brazilListings,
                'lowest_price' => $lowestPrice,
                'median_price' => $medianPrice,
                'highest_price' => $highestPrice,
                'has_brazil_sellers' => ($count > 0)
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar anúncios do Brasil no Discogs: ' . $e->getMessage());
            return [
                'count' => 0,
                'listings' => [],
                'lowest_price' => 0,
                'median_price' => 0,
                'highest_price' => 0,
                'has_brazil_sellers' => false
            ];
        }
    }

    /**
     * Obtém dados de análise de mercado do Discogs
     * Usa web scraping para obter o número real de listings (anúncios), não apenas releases
     *
     * @param string|null $country País para filtrar (opcional)
     * @return int|null Número de listings ou null em caso de erro
     */
    public function getMarketAnalysisData(?string $country = null): ?int
    {
        try {
            // URL base para listagens de vinil
            $url = 'https://www.discogs.com/sell/list?format=Vinyl';

            // Adicionar filtro de país se especificado
            if ($country) {
                $url .= '&ships_from=' . urlencode($country);
            }

            Log::info('Buscando dados de mercado do Discogs', [
                'url' => $url,
                'country' => $country
            ]);

            // Fazer requisição simulando um navegador real
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Cache-Control' => 'max-age=0'
            ])
            ->timeout(30)
            ->get($url);

            if (!$response->successful()) {
                Log::error('Falha ao acessar página do Discogs', [
                    'status' => $response->status(),
                    'country' => $country
                ]);
                return null;
            }

            $html = $response->body();

            // Procurar pelo padrão que mostra o total de listings
            // O Discogs mostra algo como "1-25 of 50,123,456" na paginação
            $patterns = [
                // Padrão principal: <strong class="pagination_total">1-25 of 50,123,456</strong>
                '/<strong[^>]*class=["\']pagination_total["\'][^>]*>.*?of\s+([\d,]+)<\/strong>/i',
                // Padrão alternativo: pode estar em um span
                '/<span[^>]*class=["\']pagination_total["\'][^>]*>.*?of\s+([\d,]+)<\/span>/i',
                // Outro padrão possível
                '/of\s+([\d,]+)\s+<\/strong>/i',
                // Padrão mais genérico
                '/\bof\s+([\d,]+)\s*(?:<\/|results|items|listings)/i'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $totalStr = $matches[1];
                    $total = (int) str_replace(',', '', $totalStr);

                    Log::info('Total de listings encontrado', [
                        'country' => $country,
                        'total' => $total,
                        'pattern' => $pattern
                    ]);

                    return $total;
                }
            }

            // Se não encontrou com os padrões acima, tentar encontrar o elemento de paginação de outra forma
            // Procurar por data attributes ou outras estruturas
            if (preg_match('/data-pagination-total=["\'](\d+)["\']/', $html, $matches)) {
                $total = (int) $matches[1];
                Log::info('Total encontrado via data attribute', [
                    'country' => $country,
                    'total' => $total
                ]);
                return $total;
            }

            Log::warning('Não foi possível extrair o total de listings do HTML', [
                'country' => $country,
                'html_sample' => substr($html, 0, 1000) // Log dos primeiros 1000 caracteres para debug
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados de mercado do Discogs', [
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
