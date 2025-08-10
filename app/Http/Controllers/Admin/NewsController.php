<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsTopic;
use App\Services\AIContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    protected AIContentService $aiService;

    public function __construct(AIContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validate and sanitize input parameters
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:all,draft,published,archived'],
            'topic' => ['nullable', 'string', 'exists:news_topics,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'in:created_at,title,published_at,views_count'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50']
        ]);

        $status = $validated['status'] ?? 'all';
        $topic = $validated['topic'] ?? null;
        $search = $validated['search'] ?? null;
        $featured = $validated['featured'] ?? null;
        $sort = $validated['sort'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'desc';
        $perPage = $validated['per_page'] ?? 12;

        // Build optimized query with eager loading
        $query = News::with(['author:id,name,email'])
            ->select([
                'id', 'title', 'slug', 'excerpt', 'featured_image',
                'status', 'featured', 'published_at', 'created_at',
                'updated_at', 'author_id', 'topics', 'views_count'
            ]);

        // Apply filters with optimized conditions
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($topic) {
            $query->byTopic($topic);
        }

        if ($featured !== null) {
            $query->where('featured', $featured);
        }

        if ($search) {
            $query->search($search);
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Add secondary sort for consistency
        if ($sort !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }

        $news = $query->paginate($perPage)->withQueryString();

        // Get topics with optimized query
        $topics = NewsTopic::active()
            ->select(['id', 'name', 'color'])
            ->orderBy('name')
            ->get();

        // Get cached stats for better performance
        $stats = $this->getCachedNewsStats();

        return view('admin.news.index', compact(
            'news', 'topics', 'stats', 'status', 'topic',
            'search', 'featured', 'sort', 'direction', 'perPage'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $topics = NewsTopic::active()->orderBy('name')->get();
        return view('admin.news.create', compact('topics'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Enhanced validation with custom rules
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:5',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\.\,\!\?\:]+$/u' // Allow common punctuation
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:news,slug',
                'regex:/^[a-z0-9\-]+$/' // Only lowercase, numbers and hyphens
            ],
            'excerpt' => [
                'nullable',
                'string',
                'min:10',
                'max:500'
            ],
            'content' => [
                'required',
                'string',
                'min:50' // Minimum content length
            ],
            'featured_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
                'dimensions:min_width=300,min_height=200'
            ],
            'gallery_images' => ['nullable', 'array', 'max:10'], // Limit gallery size
            'gallery_images.*' => [
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120',
                'dimensions:min_width=200,min_height=150'
            ],
            'topics' => ['nullable', 'array', 'max:5'], // Limit topics
            'topics.*' => ['integer', 'exists:news_topics,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'meta_description' => [
                'nullable',
                'string',
                'min:50',
                'max:160'
            ],
            'meta_keywords' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\,\-]+$/u' // Keywords format
            ],
            'featured' => ['boolean'],
            'published_at' => [
                'nullable',
                'date',
                'after_or_equal:' . now()->subYear()->format('Y-m-d'), // Not too far in past
                'before_or_equal:' . now()->addMonth()->format('Y-m-d') // Not too far in future
            ]
        ], [
            // Custom error messages
            'title.min' => 'O título deve ter pelo menos 5 caracteres.',
            'title.regex' => 'O título contém caracteres não permitidos.',
            'content.min' => 'O conteúdo deve ter pelo menos 50 caracteres.',
            'featured_image.dimensions' => 'A imagem de destaque deve ter pelo menos 300x200 pixels.',
            'gallery_images.max' => 'Você pode adicionar no máximo 10 imagens na galeria.',
            'topics.max' => 'Você pode selecionar no máximo 5 tópicos.',
            'meta_description.min' => 'A meta descrição deve ter pelo menos 50 caracteres.',
            'meta_keywords.regex' => 'As palavras-chave devem conter apenas letras, números, espaços, vírgulas e hífens.',
        ]);

        try {
            DB::beginTransaction();

            // Upload da imagem de destaque
            $featuredImagePath = null;
            if ($request->hasFile('featured_image')) {
                $featuredImagePath = $request->file('featured_image')->store('news/featured', 'public');
            }

            // Upload das imagens da galeria
            $galleryImages = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $galleryImages[] = $image->store('news/gallery', 'public');
                }
            }

            // Gerar slug se não fornecido
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
            }

            // Definir data de publicação
            if ($validated['status'] === 'published' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            $news = News::create([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'excerpt' => $validated['excerpt'],
                'content' => $validated['content'],
                'featured_image' => $featuredImagePath,
                'gallery_images' => $galleryImages,
                'topics' => $validated['topics'] ?? [],
                'status' => $validated['status'],
                'meta_description' => $validated['meta_description'],
                'meta_keywords' => $validated['meta_keywords'],
                'featured' => $validated['featured'] ?? false,
                'published_at' => $validated['published_at'],
                'author_id' => auth()->id()
            ]);

            DB::commit();

            // Clear stats cache
            $this->clearStatsCache();

            return redirect()
                ->route('admin.news.show', $news)
                ->with('success', 'Notícia criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar notícia: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        // Load author relationship with specific fields
        $news->load(['author:id,name,email,created_at']);

        // Get related news with optimized query
        $relatedNews = News::published()
            ->select(['id', 'title', 'slug', 'excerpt', 'featured_image', 'published_at', 'topics'])
            ->with(['author:id,name'])
            ->where('id', '!=', $news->id)
            ->when($news->topics, function ($query) use ($news) {
                // Find news with similar topics
                $query->where(function ($q) use ($news) {
                    foreach ($news->topics as $topic) {
                        $q->orWhereJsonContains('topics', $topic);
                    }
                });
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // If no related news found by topics, get latest published
        if ($relatedNews->isEmpty()) {
            $relatedNews = News::published()
                ->select(['id', 'title', 'slug', 'excerpt', 'featured_image', 'published_at'])
                ->with(['author:id,name'])
                ->where('id', '!=', $news->id)
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get();
        }

        return view('admin.news.show', compact('news', 'relatedNews'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        $topics = NewsTopic::active()->orderBy('name')->get();
        return view('admin.news.edit', compact('news', 'topics'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        // Enhanced validation with custom rules
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:5',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\.\,\!\?\:]+$/u'
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:news,slug,' . $news->id,
                'regex:/^[a-z0-9\-]+$/'
            ],
            'excerpt' => [
                'nullable',
                'string',
                'min:10',
                'max:500'
            ],
            'content' => [
                'required',
                'string',
                'min:50'
            ],
            'featured_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120',
                'dimensions:min_width=300,min_height=200'
            ],
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => [
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120',
                'dimensions:min_width=200,min_height=150'
            ],
            'topics' => ['nullable', 'array', 'max:5'],
            'topics.*' => ['integer', 'exists:news_topics,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'meta_description' => [
                'nullable',
                'string',
                'min:50',
                'max:160'
            ],
            'meta_keywords' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\,\-]+$/u'
            ],
            'featured' => ['boolean'],
            'published_at' => [
                'nullable',
                'date',
                'after_or_equal:' . now()->subYear()->format('Y-m-d'),
                'before_or_equal:' . now()->addMonth()->format('Y-m-d')
            ],
            'remove_featured_image' => ['boolean'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['string'] // Validate image paths
        ], [
            // Custom error messages
            'title.min' => 'O título deve ter pelo menos 5 caracteres.',
            'title.regex' => 'O título contém caracteres não permitidos.',
            'content.min' => 'O conteúdo deve ter pelo menos 50 caracteres.',
            'featured_image.dimensions' => 'A imagem de destaque deve ter pelo menos 300x200 pixels.',
            'gallery_images.max' => 'Você pode adicionar no máximo 10 imagens na galeria.',
            'topics.max' => 'Você pode selecionar no máximo 5 tópicos.',
            'meta_description.min' => 'A meta descrição deve ter pelo menos 50 caracteres.',
            'meta_keywords.regex' => 'As palavras-chave devem conter apenas letras, números, espaços, vírgulas e hífens.',
        ]);

        try {
            DB::beginTransaction();

            // Gerenciar imagem de destaque
            $featuredImagePath = $news->featured_image;

            if ($validated['remove_featured_image'] ?? false) {
                if ($news->featured_image && Storage::disk('public')->exists($news->featured_image)) {
                    Storage::disk('public')->delete($news->featured_image);
                }
                $featuredImagePath = null;
            }

            if ($request->hasFile('featured_image')) {
                if ($news->featured_image && Storage::disk('public')->exists($news->featured_image)) {
                    Storage::disk('public')->delete($news->featured_image);
                }
                $featuredImagePath = $request->file('featured_image')->store('news/featured', 'public');
            }

            // Gerenciar galeria de imagens
            $galleryImages = $news->gallery_images ?? [];

            // Remover imagens selecionadas
            if (!empty($validated['remove_gallery_images'])) {
                foreach ($validated['remove_gallery_images'] as $imageToRemove) {
                    if (Storage::disk('public')->exists($imageToRemove)) {
                        Storage::disk('public')->delete($imageToRemove);
                    }
                    $galleryImages = array_filter($galleryImages, fn($img) => $img !== $imageToRemove);
                }
            }

            // Adicionar novas imagens
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $galleryImages[] = $image->store('news/gallery', 'public');
                }
            }

            // Definir data de publicação
            $publishedAt = $news->published_at;
            if ($validated['status'] === 'published' && !$news->published_at) {
                $publishedAt = $validated['published_at'] ?? now();
            } elseif ($validated['status'] === 'published' && $validated['published_at']) {
                $publishedAt = $validated['published_at'];
            }

            $news->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? Str::slug($validated['title']),
                'excerpt' => $validated['excerpt'],
                'content' => $validated['content'],
                'featured_image' => $featuredImagePath,
                'gallery_images' => array_values($galleryImages),
                'topics' => $validated['topics'] ?? [],
                'status' => $validated['status'],
                'meta_description' => $validated['meta_description'],
                'meta_keywords' => $validated['meta_keywords'],
                'featured' => $validated['featured'] ?? false,
                'published_at' => $publishedAt
            ]);

            DB::commit();

            // Clear stats cache
            $this->clearStatsCache();

            return redirect()
                ->route('admin.news.show', $news)
                ->with('success', 'Notícia atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar notícia: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        try {
            // Remover imagens
            if ($news->featured_image && Storage::disk('public')->exists($news->featured_image)) {
                Storage::disk('public')->delete($news->featured_image);
            }

            if ($news->gallery_images) {
                foreach ($news->gallery_images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            $news->delete();

            // Clear stats cache
            $this->clearStatsCache();

            return redirect()
                ->route('admin.news.index')
                ->with('success', 'Notícia excluída com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erro ao excluir notícia: ' . $e->getMessage());
        }
    }

    /**
     * Gerar conteúdo com IA
     */
    public function generateContent(Request $request)
    {
        // Enhanced validation for AI content generation
        $validated = $request->validate([
            'prompt' => [
                'required',
                'string',
                'min:10',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-\.\,\!\?\:\;]+$/u' // Allow common punctuation
            ],
            'type' => ['required', 'in:title,excerpt,content,keywords'],
            'context' => ['nullable', 'string', 'max:200'] // Additional context
        ], [
            'prompt.min' => 'O prompt deve ter pelo menos 10 caracteres.',
            'prompt.regex' => 'O prompt contém caracteres não permitidos.',
        ]);

        // Check if AI service is available
        if (!$this->aiService->isApiAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Serviço de IA não está disponível no momento.',
                'fallback' => true
            ], 503);
        }

        // Check rate limits
        $rateLimitStatus = $this->aiService->getRateLimitStatus();
        if ($rateLimitStatus['remaining'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Limite de requisições excedido. Tente novamente em uma hora.',
                'rate_limit' => $rateLimitStatus
            ], 429);
        }

        try {
            $prompt = $validated['prompt'];
            $type = $validated['type'];

            // Add context if provided
            if (!empty($validated['context'])) {
                $prompt = $validated['context'] . '. ' . $prompt;
            }

            $content = $this->aiService->generateContent($prompt, $type);

            return response()->json([
                'success' => true,
                'content' => $content,
                'type' => $type,
                'rate_limit' => $this->aiService->getRateLimitStatus()
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('AI Content Generation Error', [
                'prompt' => $validated['prompt'],
                'type' => $validated['type'],
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar conteúdo com IA. Tente novamente ou use o conteúdo sugerido.',
                'fallback_content' => $this->aiService->getFallbackContent($validated['type']),
                'error_code' => 'AI_GENERATION_FAILED'
            ], 500);
        }
    }

    /**
     * Estatísticas das notícias com cache
     */
    private function getCachedNewsStats(): array
    {
        return \Cache::remember('news_stats', now()->addMinutes(15), function () {
            return $this->getNewsStats();
        });
    }

    /**
     * Estatísticas das notícias
     */
    private function getNewsStats(): array
    {
        // Use Laravel's query builder for database compatibility
        $total = News::count();
        $published = News::where('status', 'published')->count();
        $draft = News::where('status', 'draft')->count();
        $archived = News::where('status', 'archived')->count();
        $featured = News::where('featured', true)->count();
        $thisMonth = News::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'archived' => $archived,
            'featured' => $featured,
            'this_month' => $thisMonth,
            'published_percentage' => $total > 0 ? round(($published / $total) * 100, 1) : 0
        ];
    }

    /**
     * Clear cached statistics
     */
    private function clearStatsCache(): void
    {
        Cache::forget('news_stats');
    }
}
