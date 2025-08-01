<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{VinylMaster, Artist, Genre, Style, Product, ProductType, RecordLabel, Track, Weight, Dimension, VinylSec, Media, CatStyleShop, Cart, Wantlist, Wishlist};
use App\Services\{VinylService, DiscogsService, ImageService};
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, DB, Storage, Log};
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class VinylController extends Controller
{


    /**
     * @var VinylService
     */
    protected $vinylService;

    /**
     * @var DiscogsService
     */
    protected $discogsService;

    /**
     * @var ImageService
     */
    protected $imageService;

    /**
     * @param VinylService $vinylService
     * @param DiscogsService $discogsService
     * @param ImageService $imageService
     */
    public function __construct(VinylService $vinylService, DiscogsService $discogsService, ImageService $imageService)
    {
        $this->vinylService = $vinylService;
        $this->discogsService = $discogsService;
        $this->imageService = $imageService;
    }

    // Index and listing methods
    public function index(Request $request)
    {
        $query = VinylMaster::with(['artists', 'vinylSec', 'media', 'recordLabel']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('artists', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('recordLabel', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('cat_style_shop.id', $categoryId);
            });
        }

        // Filtro de disponibilidade de estoque
        if ($request->filled('stock_status')) {
            $stockStatus = $request->input('stock_status');

            switch ($stockStatus) {
                case 'available':
                    // Discos disponíveis: in_stock = 1 E stock > 0
                    $query->whereHas('vinylSec', function ($q) {
                        $q->where('in_stock', 1)
                          ->where('stock', '>', 0);
                    });
                    break;

                case 'unavailable':
                    // Discos indisponíveis: in_stock = 0 OU stock = 0 OU sem vinylSec
                    $query->where(function ($q) {
                        $q->whereDoesntHave('vinylSec')
                          ->orWhereHas('vinylSec', function ($subQ) {
                              $subQ->where('in_stock', 0)
                                   ->orWhere('stock', 0);
                          });
                    });
                    break;

                case 'low_stock':
                    // Estoque baixo: in_stock = 1 E stock <= 5 E stock > 0
                    $query->whereHas('vinylSec', function ($q) {
                        $q->where('in_stock', 1)
                          ->where('stock', '>', 0)
                          ->where('stock', '<=', 5);
                    });
                    break;
            }
        }

        $vinyls = $query->latest()->paginate(50)->withQueryString();

        $categories = CatStyleShop::has('vinylMasters')->orderBy('name')->get();

        return view('admin.vinyls.index', compact('vinyls', 'categories'));
    }

    public function show($id)
    {
        $vinyl = VinylMaster::with(['artists', 'media', 'vinylSec', 'tracks', 'recordLabel', 'styles', 'categories'])->findOrFail($id);
        $incompleteCartsCount = $this->getIncompleteCartsCount($vinyl);

        return view('admin.vinyls.show', compact('vinyl', 'incompleteCartsCount'));
    }

    // Create and store methods
    public function create(Request $request)
    {
        try {
            $searchResults = [];
            $query = $request->input('query');
            $selectedRelease = null;

            if ($query) {
                $searchResults = $this->searchDiscogs($query);
            }

            $releaseId = $request->input('release_id');
            if ($releaseId) {
                $selectedRelease = $this->discogsService->getRelease($releaseId);
            }

            return view('admin.vinyls.create', compact('searchResults', 'query', 'selectedRelease'));
        } catch (\Exception $e) {
            Log::error('Erro ao criar vinyl: ' . $e->getMessage());
            return response()->view('errors.500', [], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Verificar se a solicitação é AJAX ou submissão de formulário
            $isAjax = $request->ajax() || $request->wantsJson();
            $releaseId = (string) $request->input('release_id');

            // Verificar se temos um ID válido
            if (empty($releaseId)) {
                if ($isAjax) {
                    return response()->json(['status' => 'error', 'message' => 'ID do lançamento não fornecido.'], 400);
                }
                return redirect()->back()->with('error', 'ID do lançamento não fornecido.');
            }

            // Obter dados do Discogs
            $releaseData = $this->discogsService->getRelease($releaseId);

            if (!$releaseData) {
                if ($isAjax) {
                    return response()->json(['status' => 'error', 'message' => 'Falha ao buscar dados da API do Discogs.'], 400);
                }
                return redirect()->back()->with('error', 'Falha ao buscar dados da API do Discogs.');
            }

            // Verificar se o disco já existe
            $existingVinyl = VinylMaster::where('discogs_id', $releaseId)->first();
            if ($existingVinyl) {
                if ($isAjax) {
                    return response()->json([
                        'status' => 'exists',
                        'message' => 'Este disco já está cadastrado no sistema.',
                        'vinyl_id' => $existingVinyl->id
                    ]);
                }
                return redirect()->back()->with('warning', 'Este disco já está cadastrado no sistema.');
            }

            // Verificar se já existe algum disco com o mesmo título
            $title = $releaseData['title'] ?? '';
            $baseSlug = Str::slug($title);

            // Cria um slug totalmente único e garantido usando timestamp
            // Isso evita QUALQUER possibilidade de duplicação
            $uniqueSlug = $baseSlug . '-' . time() . '-' . substr($releaseId, -4);

            // Injetamos o slug diretamente nos dados
            $releaseData['_unique_slug'] = $uniqueSlug;

            DB::beginTransaction();

            // Capturar o índice da imagem selecionada (padrão: 0 se não for especificado)
            $selectedCoverIndex = $request->input('selected_cover_index', 0);

            // Criar o registro principal do vinyl - agora com o slug único e a imagem selecionada
            $vinylMaster = $this->vinylService->createOrUpdateVinylMaster($releaseData, $selectedCoverIndex);

            // Sincronizar relacionamentos
            $this->vinylService->syncArtists($vinylMaster, $releaseData['artists'] ?? []);
            // Removido a sincronização de gêneros (não necessário)
            $this->vinylService->syncStyles($vinylMaster, $releaseData['styles'] ?? []);

            // Verificar se há dados de gravadora antes de sincronizar
            if (!empty($releaseData['labels']) && !empty($releaseData['labels'][0])) {
                $this->vinylService->associateRecordLabel($vinylMaster, $releaseData['labels'][0]);
            }

            // Criar faixas e produto
            if (!empty($releaseData['tracklist'])) {
                $this->vinylService->createOrUpdateTracks($vinylMaster, $releaseData['tracklist']);
            }
            $this->vinylService->createOrUpdateProduct($vinylMaster, $releaseData);

            DB::commit();

            // Carregar relacionamentos para incluir na resposta
            $vinylMaster->load(['artists', 'recordLabel', 'styles', 'tracks']);

            // Verificar formato da resposta (JSON ou redirecionamento)
            if ($isAjax) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Disco salvo com sucesso!',
                    'vinyl_id' => $vinylMaster->id,
                    'vinyl_data' => [
                        'id' => $vinylMaster->id,
                        'title' => $vinylMaster->title,
                        'slug' => $vinylMaster->slug,
                        'image_url' => $vinylMaster->cover_image
                    ]
                ]);
            }

            // Para submissão de formulário normal, redirecionar para a página de completar cadastro
            return redirect()->route('admin.vinyls.complete', $vinylMaster->id)
                ->with('success', 'Disco salvo com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao salvar vinyl: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ocorreu um erro ao salvar o disco: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o disco: ' . $e->getMessage());
        }
    }

    // Edit and update methods
    public function edit($id)
    {
        $vinyl = VinylMaster::with('vinylSec', 'categories')->findOrFail($id);
        $weights = Weight::all();
        $dimensions = Dimension::all();
        $categories = CatStyleShop::all();

        return view('admin.vinyls.edit', compact('vinyl', 'weights', 'dimensions', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $vinyl = VinylMaster::findOrFail($id);

        $validatedData = $request->validate([
            'description'         => 'nullable|string',
            'weight_id'           => 'required|exists:weights,id',
            'dimension_id'        => 'required|exists:dimensions,id',
            'stock'               => 'required|integer|min:0',
            'price'               => 'required|numeric|min:0',

            'promotional_price'   => 'nullable|numeric|min:0',
            'is_promotional'      => 'boolean',
            'in_stock'            => 'boolean',
            'category_ids'        => 'required|array',
            'category_ids.*'      => 'exists:cat_style_shop,id',
        ]);

        DB::beginTransaction();

        try {
            $vinyl->update(['description' => $validatedData['description']]);

            $vinyl->vinylSec()->updateOrCreate(
                ['vinyl_master_id' => $vinyl->id],
                [
                    'weight_id'           => $validatedData['weight_id'],
                    'dimension_id'        => $validatedData['dimension_id'],
                    'stock'               => $validatedData['stock'],
                    'price'               => $validatedData['price'],

                    'promotional_price'   => $validatedData['promotional_price'],
                    'is_promotional'      => $validatedData['is_promotional'] ?? false,
                    'in_stock'            => $validatedData['in_stock'] ?? false,
                ]
            );

            $vinyl->categories()->sync($validatedData['category_ids']);

            DB::commit();
            return redirect()->route('admin.vinyls.index')->with('success', 'Disco atualizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating vinyl: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->withInput()->with('error', 'Ocorreu um erro ao atualizar o disco. Por favor, tente novamente.');
        }
    }

    // Delete method
    public function destroy($id)
    {
        $vinyl = VinylMaster::findOrFail($id);

        try {
            DB::beginTransaction();

            $vinyl->artists()->detach();

            $vinyl->styles()->detach();
            $vinyl->tracks()->delete();

            if ($vinyl->vinylSec) {
                $vinyl->vinylSec->delete();
            }

            if ($vinyl->product) {
                $vinyl->product->delete();
            }

            if ($vinyl->cover_image) {
                Storage::disk('media')->delete($vinyl->cover_image);
            }

            foreach ($vinyl->media as $media) {
                Storage::disk('media')->delete($media->file_path);
                $media->delete();
            }

            $vinyl->delete();

            DB::commit();
            return redirect()->route('admin.vinyls.index')->with('success', 'Disco excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting vinyl: ' . $e->getMessage());
            return redirect()->route('admin.vinyls.index')->with('error', 'Ocorreu um erro ao excluir o disco. Por favor, tente novamente.');
        }
    }

    // Image handling methods
    public function uploadImage(Request $request, $id)
    {
        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048']);

        $vinyl = VinylMaster::findOrFail($id);

        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $coverImageName = 'vinyl_covers/' . $vinyl->id . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

                Storage::disk('media')->put($coverImageName, file_get_contents($image));

                if ($vinyl->cover_image) {
                    Storage::disk('media')->delete($vinyl->cover_image);
                }

                $vinyl->cover_image = $coverImageName;
                $vinyl->save();

                DB::commit();
                return back()->with('success', 'Imagem carregada com sucesso.');
            }

            DB::rollBack();
            return back()->with('error', 'Falha ao carregar a imagem.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao fazer upload da imagem: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Ocorreu um erro ao salvar a imagem. Por favor, tente novamente.');
        }
    }

    public function fetchDiscogsImage($id)
    {
        $vinyl = VinylMaster::findOrFail($id);

        if (!$vinyl->discogs_id) {
            return back()->with('error', 'ID do Discogs não encontrado.');
        }

        DB::beginTransaction();

        try {
            $release = $this->discogsService->getRelease($vinyl->discogs_id);

            if ($release && isset($release['images'][0]['uri'])) {
                $coverImageUrl = $release['images'][0]['uri'];
                $coverImageContents = Http::get($coverImageUrl)->body();
                $coverImageName = 'vinyl_covers/' . $vinyl->id . '_' . Str::random(10) . '.jpg';

                Storage::disk('media')->put($coverImageName, $coverImageContents);

                if ($vinyl->cover_image) {
                    Storage::disk('media')->delete($vinyl->cover_image);
                }

                $vinyl->cover_image = $coverImageName;
                $vinyl->save();

                DB::commit();
                return back()->with('success', 'Imagem do Discogs importada com sucesso.');
            }

            DB::rollBack();
            return back()->with('warning', 'Nenhuma imagem encontrada no Discogs. Por favor, faça o upload manual de uma imagem.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao buscar imagem do Discogs: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Erro ao buscar imagem do Discogs. Por favor, tente fazer o upload manual de uma imagem.');
        }
    }

    public function removeImage($id)
    {
        $vinyl = VinylMaster::findOrFail($id);

        DB::beginTransaction();

        try {
            if ($vinyl->cover_image) {
                Storage::disk('media')->delete($vinyl->cover_image);
                $vinyl->cover_image = null;
                $vinyl->save();

                DB::commit();
                return back()->with('success', 'Imagem removida com sucesso.');
            }

            DB::rollBack();
            return back()->with('error', 'Nenhuma imagem para remover.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao remover imagem: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Ocorreu um erro ao remover a imagem. Por favor, tente novamente.');
        }
    }

    // Helper methods
    private function searchDiscogs($query)
    {
        try {
            return $this->discogsService->search($query);
        } catch (\Exception $e) {
            Log::error('Error searching Discogs: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createOrUpdateVinylMaster($releaseData)
    {
        $coverImagePath = null;
        if (!empty($releaseData['images'][0]['uri'])) {
            $coverImageUrl = $releaseData['images'][0]['uri'];
            $coverImageContents = Http::get($coverImageUrl)->body();
            $coverImageName = 'vinyl_covers/' . $releaseData['id'] . '_' . Str::random(10) . '.jpg';
            Storage::disk('media')->put($coverImageName, $coverImageContents);
            $coverImagePath = $coverImageName;
        }

        return VinylMaster::updateOrCreate(
            ['discogs_id' => $releaseData['id']],
            [
                'title' => $releaseData['title'],
                'release_year' => $releaseData['year'],
                'country' => $releaseData['country'],
                'description' => $releaseData['notes'] ?? null,
                'cover_image' => $coverImagePath,
                'discogs_url' => $releaseData['uri'] ?? null,
            ]
        );
    }

    private function syncArtists($vinylMaster, $artists)
    {
        $artistIds = collect($artists)->map(function ($artistData) {
            $artist = Artist::updateOrCreate(
                ['name' => $artistData['name']],
                ['slug' => Str::slug($artistData['name'])]
            );
            return $artist->id;
        });

        $vinylMaster->artists()->sync($artistIds);
    }

    // Método syncGenres removido - não utilizamos gêneros neste projeto

    private function syncStyles($vinylMaster, $styles)
    {
        $styleIds = collect($styles)->map(function ($styleName) {
            $style = Style::updateOrCreate(
                ['name' => $styleName],
                ['slug' => Str::slug($styleName)]
            );
            return $style->id;
        });

        $vinylMaster->styles()->sync($styleIds);
    }

    private function associateRecordLabel($vinylMaster, $labelData)
    {
        if ($labelData) {
            $label = RecordLabel::updateOrCreate(
                ['name' => $labelData['name']],
                ['slug' => Str::slug($labelData['name'])]
            );
            $vinylMaster->recordLabel()->associate($label);
            $vinylMaster->save();
        }
    }

    private function createOrUpdateTracks($vinylMaster, $tracklist)
    {
        foreach ($tracklist as $trackData) {
            if (!empty($trackData['title'])) {
                Track::updateOrCreate(
                    [
                        'vinyl_master_id' => $vinylMaster->id,
                        'name' => $trackData['title'],
                    ],
                    [
                        'duration' => $trackData['duration'] ?? null,
                    ]
                );
            }
        }
    }

    private function createOrUpdateProduct($vinylMaster, $releaseData)
    {
        $productType = ProductType::where('slug', 'vinyl')->firstOrFail();

        $product = Product::updateOrCreate(
            [
                'productable_id' => $vinylMaster->id,
                'productable_type' => 'App\\Models\\VinylMaster',
            ],
            [
                'name' => $releaseData['title'],
                'slug' => Str::slug($releaseData['title']),
                'description' => $releaseData['notes'] ?? null,
                'product_type_id' => $productType->id,
            ]
        );

        return $product;
    }

    private function getWantListCount($vinyl)
    {
        if (!$vinyl->vinylSec || !$vinyl->vinylSec->in_stock) {
            return DB::table('wantlists')
                ->where('productable_id', $vinyl->id)
                ->where('productable_type', \App\Models\VinylMaster::class)
                ->count();
        }
        return 0;
    }

    private function getWishlistCount($vinyl)
    {
        return DB::table('wishlists')
            ->where('productable_id', $vinyl->id)
            ->where('productable_type', \App\Models\VinylMaster::class)
            ->count();
    }

    private function getIncompleteCartsCount($vinyl)
    {
        return DB::table('carts')
            ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->where('cart_items.product_id', $vinyl->id)
            ->whereRaw('carts.updated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)')
            ->distinct('carts.id')
            ->count();
    }

    public function complete($id)
    {
        try {
            $vinylMaster = VinylMaster::with('vinylSec.categories', 'tracks')->findOrFail($id);

            // Carregando dados das tabelas relacionadas
            $weights = \App\Models\Weight::all();
            $dimensions = \App\Models\Dimension::all();
            $categories = \App\Models\CatStyleShop::all();
            $midiaStatuses = \App\Models\MidiaStatus::orderBy('title')->get();
            $coverStatuses = \App\Models\CoverStatus::orderBy('title')->get();
            $suppliers = \App\Models\Supplier::orderBy('name')->get();

            // Categorias selecionadas (se existirem)
            $selectedCategories = $vinylMaster->vinylSec ? $vinylMaster->vinylSec->categories->pluck('id')->toArray() : [];
            $tracks = $vinylMaster->tracks;

            return view('admin.vinyls.complete', compact(
                'vinylMaster',
                'weights',
                'dimensions',
                'categories',
                'selectedCategories',
                'tracks',
                'midiaStatuses',
                'coverStatuses',
                'suppliers'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading vinyl completion form: ' . $e->getMessage());
            return redirect()->route('admin.vinyls.index')->with('error', 'Não foi possível carregar o formulário de finalização do vinyl. Por favor, tente novamente.');
        }
    }
    public function storeComplete(Request $request, $id)
    {
        $vinylMaster = VinylMaster::with('tracks')->findOrFail($id);

        $validatedData = $request->validate([
            'catalog_number'       => 'nullable|string',
            'barcode'              => 'nullable|string',
            'internal_code'        => 'nullable|string',
            'weight_id'            => 'nullable|exists:weights,id',
            'dimension_id'         => 'nullable|exists:dimensions,id',
            'midia_status_id'      => 'nullable|exists:midia_status,id',
            'cover_status_id'      => 'nullable|exists:cover_status,id',
            'supplier_id'          => 'nullable|exists:suppliers,id',
            'stock'                => 'required|integer|min:0',
            'price'                => 'required|numeric|min:0',
            'format'               => 'nullable|string',
            'num_discs'            => 'required|integer|min:1',
            'speed'                => 'nullable|string',
            'edition'              => 'nullable|string',
            'notes'                => 'nullable|string',
            'is_new'               => 'required|boolean',
            'buy_price'            => 'nullable|numeric|min:0',
            'promotional_price'    => 'nullable|numeric|min:0',
            'is_promotional'       => 'required|boolean',
            'is_presale'           => 'boolean',
            'presale_arrival_date' => 'nullable|date',
            'tracks.*.youtube_url' => 'nullable|url',
            'category_ids'         => 'nullable|array',
            'category_ids.*'       => 'exists:cat_style_shop,id',
        ]);

        DB::beginTransaction();
        try {
            // Preparar os dados para o VinylSec
            $vinylSecData = [
                'vinyl_master_id' => $vinylMaster->id,
                'catalog_number' => $validatedData['catalog_number'] ?? null,
                'barcode' => $validatedData['barcode'] ?? null,
                'internal_code' => $validatedData['internal_code'] ?? null,
                'weight_id' => $validatedData['weight_id'] ?? null,
                'dimension_id' => $validatedData['dimension_id'] ?? null,
                'midia_status_id' => $validatedData['midia_status_id'] ?? null,
                'cover_status_id' => $validatedData['cover_status_id'] ?? null,
                'supplier_id' => $validatedData['supplier_id'] ?? null,
                'stock' => $validatedData['stock'] ?? 0,
                'price' => $validatedData['price'],
                'format' => $validatedData['format'] ?? null,
                'num_discs' => $validatedData['num_discs'] ?? 1,
                'speed' => $validatedData['speed'] ?? null,
                'edition' => $validatedData['edition'] ?? null,
                'notes' => $validatedData['notes'] ?? null,
                'is_new' => $validatedData['is_new'] ?? true,
                'buy_price' => $validatedData['buy_price'] ?? null,
                'promotional_price' => $validatedData['promotional_price'] ?? null,
                'is_promotional' => $validatedData['is_promotional'] ?? false,
                'is_presale' => $validatedData['is_presale'] ?? false,
                'presale_arrival_date' => $validatedData['presale_arrival_date'] ?? null,
                'in_stock' => $validatedData['in_stock'] ?? true
            ];

            // Verifica se já existe um VinylSec para este VinylMaster
            $vinylSec = VinylSec::where('vinyl_master_id', $vinylMaster->id)->first();

            if ($vinylSec) {
                // Se já existe, atualiza
                // Certifique-se que os campos promocionais são mantidos se não forem fornecidos
                if (!isset($vinylSecData['promo_starts_at']) && $vinylSec->promo_starts_at) {
                    $vinylSecData['promo_starts_at'] = $vinylSec->promo_starts_at;
                }

                if (!isset($vinylSecData['promo_ends_at']) && $vinylSec->promo_ends_at) {
                    $vinylSecData['promo_ends_at'] = $vinylSec->promo_ends_at;
                }

                $vinylSec->update($vinylSecData);
            } else {
                // Se não existe, cria um novo
                // Se não está em promoção, garantimos que as datas sejam nulas
                if (!isset($vinylSecData['is_promotional']) || !$vinylSecData['is_promotional']) {
                    $vinylSecData['promo_starts_at'] = null;
                    $vinylSecData['promo_ends_at'] = null;
                }

                $vinylSec = VinylSec::create($vinylSecData);
            }

            // Sincroniza as categorias
            if ($request->has('category_ids')) {
                $vinylMaster->categories()->sync($request->input('category_ids'));
            }

            // Atualiza as faixas existentes
            if ($request->has('tracks')) {
                foreach ($request->input('tracks') as $trackId => $trackData) {
                    $track = $vinylMaster->tracks->find($trackId);
                    if ($track) {
                        $track->update([
                            'youtube_url' => $trackData['youtube_url']
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.vinyls.index')
                             ->with('success', 'Vinyl finalizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log mais detalhado do erro
            Log::error('Error completing vinyl record: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Verifica se estamos em ambiente de desenvolvimento
            if (config('app.debug')) {
                return redirect()->back()->withInput()
                                 ->withErrors([
                                    'error' => 'Erro ao salvar o vinyl: ' . $e->getMessage(),
                                    'file' => $e->getFile(),
                                    'line' => $e->getLine()
                                 ]);
            }

            return redirect()->back()->withInput()
                             ->withErrors(['error' => 'Ocorreu um erro ao salvar o vinyl. Por favor, tente novamente.']);
        }
    }

    /**
     * Update a specific field for a vinyl product (toggle switches)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateField(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|numeric|exists:vinyl_masters,id',
                'field' => 'required|string|in:is_promotional,in_stock',
                'value' => 'required|boolean'
            ]);

            $vinyl = VinylMaster::findOrFail($request->id);

            if (!$vinyl->vinylSec) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este disco não possui informações secundárias configuradas.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Este disco não possui informações secundárias configuradas.');
            }

            $vinyl->vinylSec->update([
                $request->field => $request->value
            ]);

            // Construir mensagem de sucesso com base no campo atualizado
            $fieldName = $request->field === 'is_promotional' ? 'promoção' : 'estoque';
            $fieldStatus = $request->value ? 'ativado' : 'desativado';
            $successMessage = "Status de {$fieldName} {$fieldStatus} com sucesso";

            // Responder baseado no tipo de requisição
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => [
                        'id' => $vinyl->id,
                        'field' => $request->field,
                        'value' => (bool)$request->value
                    ]
                ]);
            }

            // Para submissões de formulário normais, redirecionar de volta com mensagem de sucesso
            return redirect()->back()->with('success', $successMessage);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }
}

