<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AIDescriptionService;
use App\Models\VinylMaster;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIController extends Controller
{
    private $aiService;

    public function __construct(AIDescriptionService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Gera descrição usando IA baseada nos dados do vinil
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateDescription(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'vinyl_id' => 'required|exists:vinyl_masters,id'
            ]);

            $vinylId = $request->input('vinyl_id');
            $vinyl = VinylMaster::with(['artists', 'recordLabel', 'styles', 'tracks'])->findOrFail($vinylId);

            // Preparar dados para a IA
            $vinylData = [
                'artists' => $vinyl->artists->pluck('name')->join(', '),
                'title' => $vinyl->title,
                'year' => $vinyl->release_year,
                'country' => $vinyl->country,
                'label' => $vinyl->recordLabel?->name ?? '',
                'styles' => $vinyl->styles->pluck('name')->toArray(),
                'tracks' => $vinyl->tracks->map(function ($track) {
                    return ['name' => $track->name, 'title' => $track->name];
                })->toArray(),
                'format' => 'Vinil LP'
            ];

            $description = $this->aiService->generateDescription($vinylData);

            if ($description) {
                return response()->json([
                    'success' => true,
                    'description' => $description
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível gerar a descrição. Tente novamente.'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traduz texto para português
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function translateDescription(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'text' => 'required|string|max:2000'
            ]);

            $text = $request->input('text');

            $translatedText = $this->aiService->translateToPortuguese($text);

            if ($translatedText) {
                return response()->json([
                    'success' => true,
                    'translated_text' => $translatedText
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível traduzir o texto. Tente novamente.'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica status do serviço de IA
     *
     * @return JsonResponse
     */
    public function checkStatus(): JsonResponse
    {
        $isAvailable = $this->aiService->isAvailable();
        
        return response()->json([
            'available' => $isAvailable,
            'message' => $isAvailable 
                ? 'Serviço de IA disponível' 
                : 'Serviço de IA indisponível. Verifique se o Ollama está rodando.'
        ]);
    }
}
