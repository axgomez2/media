<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIDescriptionService
{
    private $ollamaUrl;
    private $model;

    public function __construct()
    {
        $this->ollamaUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->model = config('services.ollama.model', 'llama3.2:3b');
    }

    /**
     * Gera uma descrição de produto baseada nas informações do vinil
     *
     * @param array $vinylData Dados do vinil
     * @return string|null Descrição gerada ou null em caso de erro
     */
    public function generateDescription(array $vinylData): ?string
    {
        // Primeiro tentar Ollama (IA local)
        $description = $this->generateWithOllama($vinylData);
        
        // Se Ollama não funcionar, usar fallback com template
        if (!$description) {
            $description = $this->generateWithTemplate($vinylData);
        }
        
        return $description;
    }

    /**
     * Gera descrição usando Ollama
     */
    private function generateWithOllama(array $vinylData): ?string
    {
        try {
            $prompt = $this->buildPrompt($vinylData);
            
            $response = Http::timeout(30)->post("{$this->ollamaUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->cleanDescription($data['response'] ?? '');
            }

            Log::warning('Ollama API response not successful', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Erro ao gerar descrição com Ollama: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gera descrição usando template quando IA não está disponível
     */
    private function generateWithTemplate(array $vinylData): string
    {
        $artists = $vinylData['artists'] ?? 'Artista';
        $title = $vinylData['title'] ?? 'Álbum';
        $year = $vinylData['year'] ?? '';
        $country = $vinylData['country'] ?? '';
        $label = $vinylData['label'] ?? '';
        $styles = $vinylData['styles'] ?? [];
        $tracks = $vinylData['tracks'] ?? [];

        $stylesText = is_array($styles) ? implode(', ', $styles) : $styles;
        
        $description = "🎵 **{$artists} - {$title}**\n\n";
        
        if ($year) {
            $description .= "Lançado em {$year}";
            if ($country) {
                $description .= " ({$country})";
            }
            $description .= ", ";
        }
        
        $description .= "este é um disco de vinil que representa ";
        
        if ($stylesText) {
            $description .= "o melhor do {$stylesText}. ";
        } else {
            $description .= "uma obra musical de qualidade. ";
        }
        
        if ($label) {
            $description .= "Produzido pela gravadora {$label}, ";
        }
        
        $description .= "este álbum é uma adição valiosa para qualquer coleção de vinis.\n\n";
        
        if (is_array($tracks) && count($tracks) > 0) {
            $description .= "**Principais faixas:**\n";
            foreach (array_slice($tracks, 0, 5) as $track) {
                $trackName = is_array($track) ? ($track['name'] ?? $track['title'] ?? '') : $track;
                if ($trackName) {
                    $description .= "• {$trackName}\n";
                }
            }
            $description .= "\n";
        }
        
        $description .= "**Características:**\n";
        $description .= "• Formato: Vinil LP\n";
        $description .= "• Condição: Conforme especificado\n";
        $description .= "• Ideal para colecionadores e amantes da música\n";
        $description .= "• Qualidade de áudio superior do formato analógico\n\n";
        
        $description .= "Adicione este clássico à sua coleção e desfrute da experiência única que apenas o vinil pode proporcionar!";
        
        return $description;
    }

    /**
     * Traduz uma descrição para português
     *
     * @param string $text Texto a ser traduzido
     * @return string|null Texto traduzido ou null em caso de erro
     */
    public function translateToPortuguese(string $text): ?string
    {
        // Primeiro tentar Ollama
        $translation = $this->translateWithOllama($text);
        
        // Se não funcionar, usar fallback simples
        if (!$translation) {
            $translation = $this->translateWithFallback($text);
        }
        
        return $translation;
    }

    /**
     * Traduz usando Ollama
     */
    private function translateWithOllama(string $text): ?string
    {
        try {
            $prompt = "Traduza o seguinte texto para português brasileiro de forma natural e fluida, mantendo o contexto musical e comercial:\n\n{$text}";
            
            $response = Http::timeout(30)->post("{$this->ollamaUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.3,
                    'max_tokens' => 600,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->cleanDescription($data['response'] ?? '');
            }

            Log::warning('Ollama translation API response not successful', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Erro ao traduzir com Ollama: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tradução simples quando IA não está disponível
     */
    private function translateWithFallback(string $text): string
    {
        // Dicionário básico de traduções comuns em descrições de vinis
        $translations = [
            'Album' => 'Álbum',
            'Artist' => 'Artista',
            'Released' => 'Lançado',
            'Record Label' => 'Gravadora',
            'Tracks' => 'Faixas',
            'Main tracks' => 'Principais faixas',
            'Features' => 'Características',
            'Vinyl LP' => 'Vinil LP',
            'Condition' => 'Condição',
            'As specified' => 'Conforme especificado',
            'Ideal for collectors' => 'Ideal para colecionadores',
            'Superior audio quality' => 'Qualidade de áudio superior',
            'analog format' => 'formato analógico',
            'Add this classic' => 'Adicione este clássico',
            'collection' => 'coleção',
            'experience' => 'experiência',
            'unique' => 'única',
            'only vinyl can provide' => 'apenas o vinil pode proporcionar',
        ];

        $translatedText = $text;
        foreach ($translations as $english => $portuguese) {
            $translatedText = str_ireplace($english, $portuguese, $translatedText);
        }

        return $translatedText;
    }

    /**
     * Constrói o prompt para geração de descrição
     *
     * @param array $vinylData Dados do vinil
     * @return string Prompt formatado
     */
    private function buildPrompt(array $vinylData): string
    {
        $artists = $vinylData['artists'] ?? 'Artista desconhecido';
        $title = $vinylData['title'] ?? 'Título desconhecido';
        $year = $vinylData['year'] ?? '';
        $country = $vinylData['country'] ?? '';
        $label = $vinylData['label'] ?? '';
        $styles = $vinylData['styles'] ?? [];
        $tracks = $vinylData['tracks'] ?? [];
        $format = $vinylData['format'] ?? 'Vinil';

        $stylesText = is_array($styles) ? implode(', ', $styles) : $styles;
        $tracksText = '';
        
        if (is_array($tracks) && count($tracks) > 0) {
            $tracksText = "\nFaixas principais:\n";
            foreach (array_slice($tracks, 0, 5) as $track) {
                $trackName = is_array($track) ? ($track['name'] ?? $track['title'] ?? '') : $track;
                if ($trackName) {
                    $tracksText .= "- {$trackName}\n";
                }
            }
        }

        return "Você é um especialista em música e vendas de discos de vinil. Crie uma descrição comercial atrativa e informativa para este produto:

Artista(s): {$artists}
Álbum: {$title}
Ano: {$year}
País: {$country}
Gravadora: {$label}
Formato: {$format}
Estilos: {$stylesText}
{$tracksText}

Instruções:
- Escreva em português brasileiro
- Use entre 150-300 palavras
- Seja profissional mas envolvente
- Destaque a importância musical do álbum
- Mencione características do vinil quando relevante
- Use linguagem comercial adequada para uma loja online
- Não invente informações que não foram fornecidas
- Foque na qualidade musical e valor colecionável

Descrição:";
    }

    /**
     * Limpa e formata a descrição gerada
     *
     * @param string $description Descrição bruta
     * @return string Descrição limpa
     */
    private function cleanDescription(string $description): string
    {
        // Remove quebras de linha excessivas
        $description = preg_replace('/\n{3,}/', "\n\n", $description);
        
        // Remove espaços em branco excessivos
        $description = preg_replace('/[ \t]+/', ' ', $description);
        
        // Trim geral
        $description = trim($description);
        
        return $description;
    }

    /**
     * Verifica se o serviço Ollama está disponível
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->ollamaUrl}/api/tags");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
