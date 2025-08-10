<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlaylistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');

        $playlists = Playlist::query()
            ->when($type !== 'all', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('admin.playlists.index-simple', compact('playlists', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'dj');
        $products = Product::with(['productable'])
            ->orderBy('name')
            ->get();

        return view('admin.playlists.create-simple', compact('type', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:dj,chart'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'chart_date' => ['nullable', 'date', 'required_if:type,chart'],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'url'],
            'social_links.soundcloud' => ['nullable', 'url'],
            'social_links.spotify' => ['nullable', 'url'],
            'dj_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
            'tracks' => ['required', 'array', 'max:10'],
            'tracks.*' => ['nullable', 'exists:products,id'],
            'is_active' => ['boolean']
        ]);

        // Validar que pelo menos uma faixa foi selecionada
        $nonEmptyTracks = array_filter($validated['tracks'], function($track) {
            return !empty($track);
        });

        if (empty($nonEmptyTracks)) {
            return back()->withInput()->withErrors(['tracks' => 'Selecione pelo menos uma faixa.']);
        }

        try {
            DB::beginTransaction();

            // Upload da foto do DJ se fornecida
            $djPhotoPath = null;
            if ($validated['type'] === 'dj' && $request->hasFile('dj_photo')) {
                $djPhotoPath = $request->file('dj_photo')->store('playlists/dj-photos', 'public');
            }

            // Criar a playlist
            $playlist = Playlist::create([
                'type' => $validated['type'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'chart_date' => $validated['type'] === 'chart' ? $validated['chart_date'] : null,
                'social_links' => $validated['type'] === 'dj' ? ($validated['social_links'] ?? null) : null,
                'dj_photo' => $djPhotoPath,
                'is_active' => $validated['is_active'] ?? true,
                'position' => Playlist::where('type', $validated['type'])->max('position') + 1
            ]);

            // Adicionar as faixas (filtrar valores vazios)
            $tracks = array_filter($validated['tracks'], function($productId) {
                return !empty($productId);
            });

            foreach ($tracks as $position => $productId) {
                $playlist->tracks()->create([
                    'product_id' => $productId,
                    'position' => $position + 1
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.playlists.show', $playlist)
                ->with('success', 'Playlist criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar playlist: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Playlist $playlist)
    {
        $playlist->load(['tracks.product.productable']);

        return view('admin.playlists.show', compact('playlist'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Playlist $playlist)
    {
        $playlist->load(['tracks.product']);
        $products = Product::with(['productable'])
            ->orderBy('name')
            ->get();

        return view('admin.playlists.edit-simple', compact('playlist', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Playlist $playlist)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'chart_date' => ['nullable', 'date', Rule::requiredIf($playlist->type === 'chart')],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'url'],
            'social_links.soundcloud' => ['nullable', 'url'],
            'social_links.spotify' => ['nullable', 'url'],
            'dj_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'tracks' => ['required', 'array', 'max:10'],
            'tracks.*' => ['nullable', 'exists:products,id'],
            'is_active' => ['boolean']
        ]);

        // Validar que pelo menos uma faixa foi selecionada
        $nonEmptyTracks = array_filter($validated['tracks'], function($track) {
            return !empty($track);
        });

        if (empty($nonEmptyTracks)) {
            return back()->withInput()->withErrors(['tracks' => 'Selecione pelo menos uma faixa.']);
        }

        try {
            DB::beginTransaction();

            // Gerenciar foto do DJ
            $djPhotoPath = $playlist->dj_photo;

            // Remover foto se solicitado
            if ($validated['remove_photo'] ?? false) {
                if ($playlist->dj_photo && \Storage::disk('public')->exists($playlist->dj_photo)) {
                    \Storage::disk('public')->delete($playlist->dj_photo);
                }
                $djPhotoPath = null;
            }

            // Upload nova foto se fornecida
            if ($playlist->type === 'dj' && $request->hasFile('dj_photo')) {
                // Remover foto antiga se existir
                if ($playlist->dj_photo && \Storage::disk('public')->exists($playlist->dj_photo)) {
                    \Storage::disk('public')->delete($playlist->dj_photo);
                }
                $djPhotoPath = $request->file('dj_photo')->store('playlists/dj-photos', 'public');
            }

            // Atualizar a playlist
            $playlist->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'chart_date' => $playlist->type === 'chart' ? $validated['chart_date'] : null,
                'social_links' => $playlist->type === 'dj' ? ($validated['social_links'] ?? null) : null,
                'dj_photo' => $djPhotoPath,
                'is_active' => $validated['is_active'] ?? true
            ]);

            // Remover faixas antigas
            $playlist->tracks()->delete();

            // Adicionar novas faixas
            foreach ($validated['tracks'] as $position => $productId) {
                $playlist->tracks()->create([
                    'product_id' => $productId,
                    'position' => $position + 1
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.playlists.show', $playlist)
                ->with('success', 'Playlist atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar playlist: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Playlist $playlist)
    {
        try {
            // Remover foto do DJ se existir
            if ($playlist->dj_photo && \Storage::disk('public')->exists($playlist->dj_photo)) {
                \Storage::disk('public')->delete($playlist->dj_photo);
            }

            $playlist->delete();

            return redirect()
                ->route('admin.playlists.index')
                ->with('success', 'Playlist excluída com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erro ao excluir playlist: ' . $e->getMessage());
        }
    }

    /**
     * Toggle playlist status
     */
    public function toggleStatus(Playlist $playlist)
    {
        $playlist->update(['is_active' => !$playlist->is_active]);

        $status = $playlist->is_active ? 'ativada' : 'desativada';

        return back()->with('success', "Playlist {$status} com sucesso!");
    }
}
