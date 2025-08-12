<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsTopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = NewsTopic::orderBy('name')->paginate(15);

        return view('admin.news.topics.index', compact('topics'));
    }

    /**
     * API endpoint for topics (used by JavaScript)
     */
    public function api()
    {
        $topics = NewsTopic::active()
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'slug'])
            ->map(function ($topic) {
                $topic->news_count = $topic->news()->count();
                return $topic;
            });

        return response()->json($topics);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.news.topics.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:news_topics,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_topics,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:7'],
            'is_active' => ['nullable', 'boolean']
        ]);

        // Gerar slug se não fornecido
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        try {
            NewsTopic::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'],
                'color' => $validated['color'] ?? '#3B82F6',
                'is_active' => isset($validated['is_active']) ? (bool)$validated['is_active'] : true
            ]);

            return redirect()
                ->route('admin.news-topics.index')
                ->with('success', 'Tópico criado com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar tópico: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NewsTopic $topic)
    {
        return view('admin.news.topics.edit', compact('topic'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NewsTopic $topic)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:news_topics,name,' . $topic->id],
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_topics,slug,' . $topic->id],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:7'],
            'is_active' => ['nullable', 'boolean']
        ]);

        // Gerar slug se não fornecido
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        try {
            $topic->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'],
                'color' => $validated['color'] ?? $topic->color,
                'is_active' => isset($validated['is_active']) ? (bool)$validated['is_active'] : false
            ]);

            return redirect()
                ->route('admin.news-topics.index')
                ->with('success', 'Tópico atualizado com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar tópico: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewsTopic $topic)
    {
        try {
            // Verificar se o tópico está sendo usado em notícias
            $newsCount = \App\Models\News::whereJsonContains('topics', (string)$topic->id)->count();

            if ($newsCount > 0) {
                return back()
                    ->with('error', "Não é possível excluir o tópico '{$topic->name}' pois ele está sendo usado em {$newsCount} notícia(s).");
            }

            $topic->delete();

            return redirect()
                ->route('admin.news-topics.index')
                ->with('success', 'Tópico excluído com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erro ao excluir tópico: ' . $e->getMessage());
        }
    }
}
