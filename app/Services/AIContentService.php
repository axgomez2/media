<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIContentService
{
    private const SUPPORTED_TYPES = ['title', 'excerpt', 'content', 'keywords', 'meta_description', 'meta_keywords'];
    private const API_TIMEOUT = 30;
    private const RATE_LIMIT_KEY = 'ai_content_requests';
    private const RATE_LIMIT_MAX = 100; // requests per hour

    /**
     * Generate content using AI
     */
    public function generateContent(string $prompt, string $type): string
    {
        try {
            $this->validateType($type);
            $this->checkRateLimit();

            if (!$this->isApiAvailable()) {
                return $this->getFallbackContent($type);
            }

            $fullPrompt = $this->buildPrompt($prompt, $type);
            $response = $this->callAiApi($fullPrompt);

            $this->incrementRateLimit();

            return $this->processResponse($response, $type);

        } catch (Exception $e) {
            $this->handleApiError($e);
            return $this->getFallbackContent($type);
        }
    }

    /**
     * Check if AI API is available
     */
    public function isApiAvailable(): bool
    {
        $apiKey = config('services.openai.api_key');
        return !empty($apiKey);
    }

    /**
     * Build prompt based on type and user input
     */
    private function buildPrompt(string $userPrompt, string $type): string
    {
        $templates = [
            'title' => "Crie um título atrativo e otimizado para SEO para um artigo sobre: {$userPrompt}. O título deve ter entre 50-60 caracteres e ser envolvente para o leitor.",

            'excerpt' => "Escreva um resumo/excerpt de 150-160 caracteres para um artigo sobre: {$userPrompt}. O resumo deve ser informativo e despertar curiosidade no leitor.",

            'content' => "Escreva um artigo completo e bem estruturado sobre: {$userPrompt}. O artigo deve ter introdução, desenvolvimento com subtítulos e conclusão. Use linguagem clara e profissional, com parágrafos bem organizados.",

            'keywords' => "Liste 8-10 palavras-chave relevantes para SEO sobre o tema: {$userPrompt}. Separe as palavras-chave por vírgulas. Inclua tanto palavras-chave principais quanto de cauda longa.",

            'meta_description' => "Escreva uma meta descrição otimizada para SEO sobre: {$userPrompt}. A descrição deve ter entre 150-160 caracteres, ser atrativa para cliques e incluir palavras-chave relevantes.",

            'meta_keywords' => "Liste 5-8 palavras-chave específicas para meta keywords sobre: {$userPrompt}. Separe por vírgulas. Foque em termos que as pessoas realmente pesquisam."
        ];

        return $templates[$type] ?? $templates['content'];
    }

    /**
     * Call the AI API
     */
    private function callAiApi(string $prompt): array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            throw new Exception('OpenAI API key not configured');
        }

        $response = Http::timeout(self::API_TIMEOUT)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um assistente especializado em criação de conteúdo para blogs e notícias. Responda sempre em português brasileiro.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => config('services.openai.max_tokens', 1000),
                'temperature' => config('services.openai.temperature', 0.7),
            ]);

        if (!$response->successful()) {
            throw new Exception('AI API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Process API response
     */
    private function processResponse(array $response, string $type): string
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new Exception('Invalid API response format');
        }

        $content = trim($response['choices'][0]['message']['content']);

        // Post-process based on type
        switch ($type) {
            case 'title':
                // Remove quotes if present
                $content = trim($content, '"\'');
                break;

            case 'keywords':
            case 'meta_keywords':
                // Ensure comma-separated format
                $content = str_replace([';', '|'], ',', $content);
                // Remove extra spaces around commas
                $content = preg_replace('/\s*,\s*/', ', ', $content);
                break;

            case 'meta_description':
                // Ensure it's within character limit
                if (strlen($content) > 160) {
                    $content = substr($content, 0, 157) . '...';
                }
                break;
        }

        return $content;
    }

    /**
     * Validate content type
     */
    private function validateType(string $type): void
    {
        if (!in_array($type, self::SUPPORTED_TYPES)) {
            throw new Exception("Unsupported content type: {$type}");
        }
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(): void
    {
        $requests = Cache::get(self::RATE_LIMIT_KEY, 0);

        if ($requests >= self::RATE_LIMIT_MAX) {
            throw new Exception('Rate limit exceeded. Please try again later.');
        }
    }

    /**
     * Increment rate limit counter
     */
    private function incrementRateLimit(): void
    {
        $key = self::RATE_LIMIT_KEY;
        $requests = Cache::get($key, 0);
        Cache::put($key, $requests + 1, now()->addHour());
    }

    /**
     * Handle API errors
     */
    private function handleApiError(Exception $e): void
    {
        Log::error('AI Content Service Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Get fallback content when AI is unavailable
     */
    public function getFallbackContent(string $type): string
    {
        $fallbacks = [
            'title' => 'Título gerado automaticamente - Edite conforme necessário',
            'excerpt' => 'Resumo gerado automaticamente - Edite este texto para descrever melhor o conteúdo do seu artigo.',
            'content' => "# Conteúdo Gerado Automaticamente\n\nEste é um conteúdo de exemplo. Edite este texto para criar seu artigo.\n\n## Introdução\n\nAdicione sua introdução aqui.\n\n## Desenvolvimento\n\nDescreva o conteúdo principal do seu artigo.\n\n## Conclusão\n\nFinalize com suas considerações finais.",
            'keywords' => 'notícias, blog, artigo, conteúdo, informação',
            'meta_description' => 'Descrição otimizada para SEO - Edite este texto para descrever o conteúdo da página.',
            'meta_keywords' => 'notícias, blog, artigo, seo, conteúdo'
        ];

        return $fallbacks[$type] ?? $fallbacks['content'];
    }

    /**
     * Get supported content types
     */
    public function getSupportedTypes(): array
    {
        return self::SUPPORTED_TYPES;
    }

    /**
     * Get current rate limit status
     */
    public function getRateLimitStatus(): array
    {
        $requests = Cache::get(self::RATE_LIMIT_KEY, 0);

        return [
            'current' => $requests,
            'limit' => self::RATE_LIMIT_MAX,
            'remaining' => max(0, self::RATE_LIMIT_MAX - $requests)
        ];
    }
}
