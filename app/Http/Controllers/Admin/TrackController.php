<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VinylMaster;
use App\Models\Track;
use Illuminate\Support\Facades\{Log, DB};
use Illuminate\Validation\ValidationException;

class TrackController extends Controller
{
    /**
     * Show the form for editing tracks of a specific vinyl
     */
    public function editTracks($id)
    {
        try {
            $vinyl = VinylMaster::with(['tracks' => function($query) {
                $query->orderBy('position');
            }])->findOrFail($id);

            return view('admin.tracks.edit', compact('vinyl'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar página de edição de tracks: ' . $e->getMessage());
            return redirect()->route('admin.vinyls.index')
                ->with('error', 'Não foi possível carregar a página de edição de faixas.');
        }
    }

    /**
     * Validate track data with enhanced validation
     */
    private function validateTrackData(Request $request)
    {
        return $request->validate([
            'tracks' => 'required|array|min:1',
            'tracks.*.id' => 'nullable|exists:tracks,id',
            'tracks.*.name' => 'required|string|max:255',
            'tracks.*.duration' => 'nullable|string|max:20',
            'tracks.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.position' => 'nullable|integer|min:1',
        ], [
            'tracks.required' => 'Pelo menos uma faixa é obrigatória.',
            'tracks.*.name.required' => 'O nome da faixa é obrigatório.',
            'tracks.*.name.max' => 'O nome da faixa não pode ter mais de 255 caracteres.',
            'tracks.*.duration.max' => 'A duração não pode ter mais de 20 caracteres.',
            'tracks.*.youtube_url.url' => 'A URL do YouTube deve ser válida.',
            'tracks.*.youtube_url.max' => 'A URL do YouTube não pode ter mais de 500 caracteres.',
            'tracks.*.position.integer' => 'A posição deve ser um número inteiro.',
            'tracks.*.position.min' => 'A posição deve ser maior que 0.',
        ]);
    }

    /**
     * Update tracks for a specific vinyl with enhanced error handling
     */
    public function updateTracks(Request $request, $id)
    {
        try {
            $vinyl = VinylMaster::findOrFail($id);
            $validatedData = $this->validateTrackData($request);

            DB::beginTransaction();

            $tracks = $validatedData['tracks'];
            $submittedTrackIds = [];

            foreach ($tracks as $index => $trackData) {
                // Auto-assign position if not provided
                if (!isset($trackData['position'])) {
                    $trackData['position'] = $index + 1;
                }

                // Convert duration to seconds if provided
                $durationSeconds = null;
                if (!empty($trackData['duration'])) {
                    $durationSeconds = $this->convertDurationToSeconds($trackData['duration']);
                }

                if (isset($trackData['id']) && !empty($trackData['id'])) {
                    // Update existing track
                    $track = Track::find($trackData['id']);
                    if ($track && $track->vinyl_master_id == $vinyl->id) {
                        $track->update([
                            'name' => $trackData['name'],
                            'duration' => $trackData['duration'] ?? null,
                            'duration_seconds' => $durationSeconds,
                            'youtube_url' => $trackData['youtube_url'] ?? null,
                            'position' => $trackData['position'],
                        ]);
                        $submittedTrackIds[] = $track->id;
                    }
                } else {
                    // Create new track
                    $newTrack = $vinyl->tracks()->create([
                        'name' => $trackData['name'],
                        'duration' => $trackData['duration'] ?? null,
                        'duration_seconds' => $durationSeconds,
                        'youtube_url' => $trackData['youtube_url'] ?? null,
                        'position' => $trackData['position'],
                    ]);
                    $submittedTrackIds[] = $newTrack->id;
                }
            }

            // Delete tracks that are not in the submitted data
            $deletedCount = $vinyl->tracks()
                ->whereNotIn('id', array_filter($submittedTrackIds))
                ->delete();

            DB::commit();

            $message = 'Faixas atualizadas com sucesso!';
            if ($deletedCount > 0) {
                $message .= " ({$deletedCount} faixa(s) removida(s))";
            }

            Log::info('Tracks atualizadas com sucesso', [
                'vinyl_id' => $vinyl->id,
                'tracks_count' => count($tracks),
                'deleted_count' => $deletedCount
            ]);

            return redirect()->route('admin.vinyls.show', $vinyl->id)
                ->with('success', $message);

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Por favor, corrija os erros abaixo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar tracks: ' . $e->getMessage(), [
                'vinyl_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao atualizar as faixas. Por favor, tente novamente.');
        }
    }

    /**
     * Convert duration string to seconds
     */
    private function convertDurationToSeconds($duration)
    {
        if (empty($duration)) {
            return null;
        }

        // Handle formats like "3:45", "1:23:45", "245" (seconds only)
        if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $duration, $matches)) {
            $hours = isset($matches[3]) ? (int)$matches[1] : 0;
            $minutes = isset($matches[3]) ? (int)$matches[2] : (int)$matches[1];
            $seconds = isset($matches[3]) ? (int)$matches[3] : (int)$matches[2];

            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        // If it's just a number, assume it's seconds
        if (is_numeric($duration)) {
            return (int)$duration;
        }

        return null;
    }

    /**
     * Remove the specified track from storage.
     */
    public function destroy(Track $track)
    {
        try {
            $vinylId = $track->vinyl_master_id;
            $trackName = $track->name;

            $track->delete();

            Log::info('Track excluída com sucesso', [
                'track_id' => $track->id,
                'track_name' => $trackName,
                'vinyl_id' => $vinylId,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Faixa "' . $trackName . '" excluída com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir faixa: ' . $e->getMessage(), [
                'track_id' => $track->id ?? null,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir a faixa.');
        }
    }

    /**
     * Reorder tracks positions
     */
    public function reorder(Request $request, $vinylId)
    {
        try {
            $vinyl = VinylMaster::findOrFail($vinylId);

            $request->validate([
                'track_ids' => 'required|array',
                'track_ids.*' => 'exists:tracks,id'
            ]);

            DB::beginTransaction();

            $trackIds = $request->input('track_ids');

            foreach ($trackIds as $position => $trackId) {
                Track::where('id', $trackId)
                    ->where('vinyl_master_id', $vinyl->id)
                    ->update(['position' => $position + 1]);
            }

            DB::commit();

            Log::info('Tracks reordenadas com sucesso', [
                'vinyl_id' => $vinyl->id,
                'track_count' => count($trackIds),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordem das faixas atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reordenar tracks: ' . $e->getMessage(), [
                'vinyl_id' => $vinylId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao reordenar as faixas.'
            ], 500);
        }
    }

    /**
     * Add a single track to a vinyl
     */
    public function store(Request $request, $vinylId)
    {
        try {
            $vinyl = VinylMaster::findOrFail($vinylId);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'duration' => 'nullable|string|max:20',
                'youtube_url' => 'nullable|url|max:500',
                'position' => 'nullable|integer|min:1',
            ], [
                'name.required' => 'O nome da faixa é obrigatório.',
                'name.max' => 'O nome da faixa não pode ter mais de 255 caracteres.',
                'duration.max' => 'A duração não pode ter mais de 20 caracteres.',
                'youtube_url.url' => 'A URL do YouTube deve ser válida.',
                'youtube_url.max' => 'A URL do YouTube não pode ter mais de 500 caracteres.',
            ]);

            // If no position provided, add to the end
            if (!isset($validatedData['position'])) {
                $maxPosition = $vinyl->tracks()->max('position') ?? 0;
                $validatedData['position'] = $maxPosition + 1;
            }

            // Convert duration to seconds
            $durationSeconds = null;
            if (!empty($validatedData['duration'])) {
                $durationSeconds = $this->convertDurationToSeconds($validatedData['duration']);
            }

            $track = $vinyl->tracks()->create([
                'name' => $validatedData['name'],
                'duration' => $validatedData['duration'] ?? null,
                'duration_seconds' => $durationSeconds,
                'youtube_url' => $validatedData['youtube_url'] ?? null,
                'position' => $validatedData['position'],
            ]);

            Log::info('Nova track adicionada com sucesso', [
                'track_id' => $track->id,
                'vinyl_id' => $vinyl->id,
                'user_id' => auth()->id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Faixa adicionada com sucesso!',
                    'track' => $track
                ]);
            }

            return redirect()->back()->with('success', 'Faixa "' . $track->name . '" adicionada com sucesso!');

        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar track: ' . $e->getMessage(), [
                'vinyl_id' => $vinylId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao adicionar a faixa.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Ocorreu um erro ao adicionar a faixa.');
        }
    }

    /**
     * Update a single track
     */
    public function update(Request $request, Track $track)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'duration' => 'nullable|string|max:20',
                'youtube_url' => 'nullable|url|max:500',
                'position' => 'nullable|integer|min:1',
            ]);

            // Convert duration to seconds
            $durationSeconds = null;
            if (!empty($validatedData['duration'])) {
                $durationSeconds = $this->convertDurationToSeconds($validatedData['duration']);
            }

            $track->update([
                'name' => $validatedData['name'],
                'duration' => $validatedData['duration'] ?? null,
                'duration_seconds' => $durationSeconds,
                'youtube_url' => $validatedData['youtube_url'] ?? null,
                'position' => $validatedData['position'] ?? $track->position,
            ]);

            Log::info('Track atualizada com sucesso', [
                'track_id' => $track->id,
                'vinyl_id' => $track->vinyl_master_id,
                'user_id' => auth()->id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Faixa atualizada com sucesso!',
                    'track' => $track->fresh()
                ]);
            }

            return redirect()->back()->with('success', 'Faixa "' . $track->name . '" atualizada com sucesso!');

        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar track: ' . $e->getMessage(), [
                'track_id' => $track->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar a faixa.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar a faixa.');
        }
    }
}

