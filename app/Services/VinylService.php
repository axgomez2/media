<?php

namespace App\Services;

use App\Models\{VinylMaster, Artist, Style, Product, ProductType, RecordLabel, Track, Weight, Dimension, VinylSec, Media};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class VinylService
{
    /**
     * @var DiscogsService
     */
    protected $discogsService;

    /**
     * @var ImageService
     */
    protected $imageService;

    /**
     * @param DiscogsService $discogsService
     * @param ImageService $imageService
     */
    public function __construct(DiscogsService $discogsService, ImageService $imageService)
    {
        $this->discogsService = $discogsService;
        $this->imageService = $imageService;
    }

    /**
     * Cria ou atualiza um registro principal de vinyl
     *
     * @param array $releaseData Dados do lançamento do Discogs
     * @param int $selectedCoverIndex Índice da imagem de capa selecionada
     * @return VinylMaster Registro do vinyl
     * @throws \Exception
     */
    public function createOrUpdateVinylMaster(array $releaseData, $selectedCoverIndex = 0): VinylMaster
    {
        try {
            // Validar dados essenciais
            if (empty($releaseData['id']) || empty($releaseData['title'])) {
                throw new \Exception('Dados do disco incompletos (ID e título são obrigatórios)');
            }

            // Extrair dados do release de forma segura com valores padrão
            $discogsId = (string) $releaseData['id'];
            $title = $releaseData['title'] ?? '';
            $year = $releaseData['year'] ?? null;
            $country = $releaseData['country'] ?? null;
            $description = $releaseData['notes'] ?? null;
            $discogsUrl = $releaseData['uri'] ?? null;

            // Processar imagem de capa usando o índice selecionado pelo usuário
            $coverImage = null;
            if (!empty($releaseData['images']) && is_array($releaseData['images'])) {
                // Validar o índice selecionado
                $totalImages = count($releaseData['images']);
                if ($selectedCoverIndex < 0 || $selectedCoverIndex >= $totalImages) {
                    $selectedCoverIndex = 0; // Usar a primeira imagem como fallback
                }

                // Obter a imagem no índice selecionado
                $selectedImage = $releaseData['images'][$selectedCoverIndex];

                if (isset($selectedImage['uri'])) {
                    // Obter o conteúdo da imagem via DiscogsService
                    $imageContent = $this->discogsService->fetchImage($selectedImage['uri']);

                    // Se tiver conteúdo, salvar a imagem
                    if ($imageContent) {
                        $coverImage = $this->imageService->saveImageFromContents(
                            $imageContent,
                            $discogsId
                        );
                    }
                }

                // Fallback: Se não conseguimos obter a imagem selecionada, tentar a primeira imagem
                if (empty($coverImage) && $selectedCoverIndex !== 0 && isset($releaseData['images'][0]['uri'])) {
                    $imageContent = $this->discogsService->fetchImage($releaseData['images'][0]['uri']);
                    if ($imageContent) {
                        $coverImage = $this->imageService->saveImageFromContents(
                            $imageContent,
                            $discogsId
                        );
                    }
                }
            }

            // Extrair nome da gravadora se disponível
            $labelName = null;
            if (!empty($releaseData['labels']) && is_array($releaseData['labels']) && !empty($releaseData['labels'][0]['name'])) {
                $labelName = $releaseData['labels'][0]['name'];
            }

            // Verificar se já existe um disco com este ID do Discogs
            $existingVinyl = VinylMaster::where('discogs_id', $discogsId)->first();

            if ($existingVinyl) {
                // Se já existe, apenas atualizar os outros campos, mantendo o slug original
                $existingVinyl->update([
                    'title' => $title,
                    'release_year' => $year,
                    'country' => $country,
                    'description' => $description,
                    'discogs_url' => $discogsUrl,
                ]);

                // Atualizar a imagem apenas se for fornecida uma nova
                if ($coverImage) {
                    $existingVinyl->cover_image = $coverImage;
                    $existingVinyl->save();
                }

                return $existingVinyl;
            } else {
                // Usar o slug único fornecido pelo controller, se existir
                $slug = $releaseData['_unique_slug'] ?? null;

                // Se não tiver sido fornecido um slug único, criar um
                if (empty($slug)) {
                    // Para novos registros, garantir slug único
                    $baseSlug = Str::slug($title);

                    // Se o slug estiver vazio, usar fallback
                    if (empty($baseSlug)) {
                        $baseSlug = 'vinyl-' . substr(md5($title . time()), 0, 8);
                    }

                    // Adicionar timestamp e parte do ID do Discogs para garantir unicidade absoluta
                    $slug = $baseSlug . '-' . time() . '-' . substr($discogsId, -4);
                }

                // Criar novo vinyl com slug único garantido
                $newVinyl = new VinylMaster([
                    'discogs_id' => $discogsId,
                    'title' => $title,
                    'slug' => $slug,
                    'release_year' => $year,
                    'country' => $country,
                    'description' => $description,
                    'cover_image' => $coverImage,
                    'discogs_url' => $discogsUrl,
                ]);

                $newVinyl->save();
                return $newVinyl;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar/atualizar disco: ' . $e->getMessage());
            throw $e; // Propagar o erro para poder ser tratado no controller
        }
    }

    /**
     * Sincroniza artistas com o vinyl
     *
     * @param VinylMaster $vinylMaster Registro do vinyl
     * @param array $artists Lista de artistas
     * @return void
     */
    public function syncArtists(VinylMaster $vinylMaster, array $artists): void
    {
        if (empty($artists)) {
            return;
        }

        try {
            $artistIds = collect($artists)->map(function ($artistData) {
                if (empty($artistData['name']) || empty($artistData['id'])) {
                    return null;
                }

                $artistName = trim($artistData['name']);
                $artistSlug = Str::slug($artistName);
                $artistDiscogsId = $artistData['id'];

                $artist = Artist::updateOrCreate(
                    ['slug' => $artistSlug],
                    [
                        'name' => $artistName,
                        'discogs_id' => $artistDiscogsId,
                        'discogs_url' => str_replace('api.discogs.com', 'www.discogs.com', $artistData['resource_url'] ?? null),
                    ]
                );

                if ($artist->wasRecentlyCreated || empty($artist->profile) || empty($artist->images)) {
                    $details = $this->discogsService->getArtistDetails($artistDiscogsId);
                    if ($details) {
                        $updateData = [];
                        if (!empty($details['profile'])) {
                            $updateData['profile'] = $details['profile'];
                        }
                        if (!empty($details['images'][0]['uri'])) {
                            $imageContent = $this->discogsService->fetchImage($details['images'][0]['uri']);
                            if ($imageContent) {
                                $imagePath = $this->imageService->saveImageFromContents(
                                    $imageContent,
                                    $artistDiscogsId,
                                    'jpg',
                                    null,
                                    'artist'
                                );
                                $updateData['images'] = [['url' => $imagePath, 'type' => 'primary']];
                            }
                        }
                        if (!empty($updateData)) {
                            $artist->update($updateData);
                        }
                    }
                }

                return $artist->id;
            })->filter()->unique();

            $vinylMaster->artists()->sync($artistIds);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar artistas: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Método removido: Sincronização de gêneros
     *
     * Este projeto não utiliza a entidade Genre, por isso o método foi removido.
     */
    // O método syncGenres foi removido pois não usamos a entidade Genre neste projeto

    /**
     * Sincroniza estilos com o vinyl
     *
     * @param VinylMaster $vinylMaster Registro do vinyl
     * @param array $styles Lista de estilos
     * @return void
     */
    public function syncStyles(VinylMaster $vinylMaster, array $styles): void
    {
        if (empty($styles)) {
            return;
        }

        try {
            $styleIds = collect($styles)->map(function ($styleName) {
                // Garantir que temos um valor válido
                if (empty($styleName) || !is_string($styleName)) {
                    return null;
                }

                // Normalizar o nome do estilo
                $name = trim($styleName);

                $style = Style::updateOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name)]
                );
                return $style->id;
            })->filter(); // Remover valores null

            // Sincronizar apenas se houver estilos válidos
            if ($styleIds->isNotEmpty()) {
                $vinylMaster->styles()->sync($styleIds);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar estilos: ' . $e->getMessage());
            // Não propagamos o erro para não interromper o fluxo principal
        }
    }

    /**
     * Associa uma gravadora ao vinyl
     *
     * @param VinylMaster $vinylMaster Registro do vinyl
     * @param array|null $labelData Dados da gravadora
     * @return void
     */
    public function associateRecordLabel(VinylMaster $vinylMaster, ?array $labelData): void
    {
        if (empty($labelData) || empty($labelData['name'])) {
            $vinylMaster->record_label_id = null;
            $vinylMaster->save();
            return;
        }

        try {
            $labelName = trim($labelData['name']);
            $labelSlug = Str::slug($labelName);
            $labelDiscogsId = $labelData['id'] ?? null;

            $recordLabel = RecordLabel::updateOrCreate(
                ['slug' => $labelSlug],
                [
                    'name' => $labelName,
                    'discogs_id' => $labelDiscogsId,
                ]
            );

            if ($recordLabel->wasRecentlyCreated || empty($recordLabel->images)) {
                if ($labelDiscogsId) {
                    $details = $this->discogsService->getLabelDetails($labelDiscogsId);
                    if ($details && !empty($details['images'][0]['uri'])) {
                         $imageContent = $this->discogsService->fetchImage($details['images'][0]['uri']);
                         if ($imageContent) {
                            $imagePath = $this->imageService->saveImageFromContents(
                                $imageContent,
                                $labelDiscogsId,
                                'jpg',
                                'label-' . $labelDiscogsId,
                                'label'
                            );
                            $recordLabel->update(['images' => [['url' => $imagePath, 'type' => 'primary']]]);
                         }
                    }
                }
            }

            $vinylMaster->record_label_id = $recordLabel->id;
            $vinylMaster->save();

        } catch (\Exception $e) {
            Log::error('Erro ao associar gravadora: ' . $e->getMessage());
        }
    }

    /**
     * Cria ou atualiza faixas do vinyl
     *
     * @param VinylMaster $vinylMaster Registro do vinyl
     * @param array $tracklist Lista de faixas
     * @return void
     */
    public function createOrUpdateTracks(VinylMaster $vinylMaster, array $tracklist): void
    {
        if (empty($tracklist) || !is_array($tracklist)) {
            return;
        }

        try {
            // Remover faixas existentes para evitar duplicação
            $vinylMaster->tracks()->delete();

            // Processar cada faixa na lista
            foreach ($tracklist as $position => $trackData) {
                // Verificar se há dados válidos para a faixa
                if (empty($trackData) || empty($trackData['title'])) {
                    continue;
                }

                // Normalizar o título da faixa
                $title = trim($trackData['title']);
                $duration = !empty($trackData['duration']) ? trim($trackData['duration']) : null;
                $position = $position + 1; // Posição iniciando em 1

                // Extrair informações adicionais, se disponíveis
                $extraInfo = [];
                if (!empty($trackData['extraartists'])) {
                    $extraArtists = collect($trackData['extraartists'])->map(function ($artist) {
                        return $artist['name'] . ' (' . $artist['role'] . ')';
                    })->implode(', ');

                    $extraInfo['extra_artists'] = $extraArtists;
                }

                if (!empty($trackData['type_'])) {
                    $extraInfo['type'] = $trackData['type_'];
                }

                // Salvar informações extras como JSON, se houver
                $extraInfoJson = !empty($extraInfo) ? json_encode($extraInfo, JSON_UNESCAPED_UNICODE) : null;

                // Converter duração do formato MM:SS para segundos, se disponível
                $durationSeconds = null;
                if ($duration) {
                    // Padrão típico: "MM:SS"
                    if (preg_match('/^(\d+):(\d{2})$/', $duration, $matches)) {
                        $minutes = (int) $matches[1];
                        $seconds = (int) $matches[2];
                        $durationSeconds = ($minutes * 60) + $seconds;
                    }
                }

                // Criar a faixa com campos adicionais
                Track::updateOrCreate(
                    [
                        'vinyl_master_id' => $vinylMaster->id,
                        'position' => $position, // Já incrementamos position acima
                    ],
                    [
                        'name' => $title,
                        'duration' => $duration,
                        'duration_seconds' => $durationSeconds,
                        'youtube_url' => null, // Preparado para futuras atualizações
                        'extra_info' => $extraInfoJson,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar/atualizar faixas: ' . $e->getMessage());
            // Não propagamos o erro para não interromper o fluxo principal
        }
    }

    /**
     * Cria ou atualiza o produto associado ao vinyl
     *
     * @param VinylMaster $vinylMaster Registro do vinyl
     * @param array $releaseData Dados do lançamento
     * @return Product Produto criado ou atualizado
     */
    public function createOrUpdateProduct(VinylMaster $vinylMaster, array $releaseData): Product
    {
        try {
            $productType = ProductType::where('slug', 'vinyl')->firstOrFail();

            // Extrair informações necessárias
            $title = $releaseData['title'] ?? $vinylMaster->title;
            $discogsId = $releaseData['id'] ?? $vinylMaster->discogs_id;
            $description = $releaseData['notes'] ?? $vinylMaster->description;

            // Verificar se já existe um produto para este vinyl
            $existingProduct = Product::where('productable_id', $vinylMaster->id)
                ->where('productable_type', 'App\\Models\\VinylMaster')
                ->first();

            if ($existingProduct) {
                // Se já existe, atualizar sem modificar o slug
                $existingProduct->update([
                    'name' => $title,
                    'description' => $description,
                    'product_type_id' => $productType->id,
                ]);

                return $existingProduct;
            } else {
                // Para novos produtos, gerar um slug único
                $baseSlug = Str::slug($title);

                // Adicionar timestamp e parte do ID do Discogs para garantir unicidade
                $uniqueSlug = $baseSlug . '-' . time() . '-' . substr($discogsId, -4);

                // Criar o produto com o slug único
                $product = new Product([
                    'productable_id' => $vinylMaster->id,
                    'productable_type' => 'App\\Models\\VinylMaster',
                    'name' => $title,
                    'slug' => $uniqueSlug,
                    'description' => $description,
                    'product_type_id' => $productType->id,
                ]);

                $product->save();
                return $product;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar/atualizar produto: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém contagem de itens na lista de desejos para um vinyl
     *
     * @param VinylMaster $vinyl Registro do vinyl
     * @return int Contagem de itens
     */
    public function getIncompleteCartsCount(VinylMaster $vinyl): int
    {
        return DB::table('carts')
            ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->where('cart_items.product_id', $vinyl->id)
            ->whereRaw('carts.updated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)')
            ->distinct('carts.id')
            ->count();
    }

    /**
     * Atualiza um campo específico da tabela VinylSec
     *
     * @param int $vinylId ID do vinyl
     * @param string $field Nome do campo a ser atualizado
     * @param mixed $value Valor a ser atribuído
     * @return bool Resultado da operação
     * @throws \Exception
     */
    public function updateField(int $vinylId, string $field, $value): bool
    {
        $vinyl = VinylMaster::findOrFail($vinylId);

        if (!$vinyl->vinylSec) {
            throw new \Exception('Vinyl não possui dados secundários cadastrados.');
        }

        // Lista de campos permitidos para atualização
        $allowedFields = [
            'weight_id', 'dimension_id', 'quantity', 'price',
            'buy_price', 'promotional_price', 'is_promotional', 'in_stock',
            'cover_status', 'midia_status', 'format', 'num_discs',
            'speed', 'edition', 'notes', 'is_new'
        ];

        if (!in_array($field, $allowedFields)) {
            throw new \Exception('Campo não permitido para atualização.');
        }

        return $vinyl->vinylSec->update([$field => $value]);
    }

    /**
     * Gera um slug único para o disco combinando título, ano e gravadora
     *
     * @param string $title Título do disco
     * @param string $year Ano do lançamento
     * @param string $label Nome da gravadora
     * @param string $discogsId ID do Discogs
     * @return string Slug único
     */
    private function generateUniqueSlug(string $title, string $year, string $label, string $discogsId): string
    {
        // Normalizar o título removendo caracteres especiais e tornando minúsculo
        $normalizedTitle = trim($title);

        // Criar slug base apenas com o título
        $baseSlug = Str::slug($normalizedTitle);
        if (empty($baseSlug)) {
            // Fallback se o slug ficar vazio
            $baseSlug = 'vinyl-' . substr(md5($title), 0, 8);
        }

        // Adicionar ano se disponível
        if (!empty($year)) {
            $baseSlug .= '-' . $year;
        }

        // Adicionar gravadora se disponível
        if (!empty($label)) {
            $labelSlug = Str::slug($label);
            if (!empty($labelSlug)) {
                $baseSlug .= '-' . $labelSlug;
            }
        }

        // Adicionar parte do discogsId para garantir maior unicidade
        // Mesmo sem verificar duplicidade, já incluir parte do ID
        $baseSlug .= '-' . substr($discogsId, -4);

        // Verificar se já existe um disco com este slug (qualquer disco, não apenas com discogs_id diferente)
        $slug = $baseSlug;
        $count = 1;

        while (VinylMaster::where('slug', $slug)->exists()) {
            $count++;
            $slug = $baseSlug . '-' . $count;
        }

        return $slug;
    }
}

