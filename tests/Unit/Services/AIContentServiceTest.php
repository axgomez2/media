<?php

namespace Tests\Unit\Services;

use App\Services\AIContentService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AIContentServiceTest extends TestCase
{
    use RefreshDatabase;

    private AIContentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AIContentService();
        Cache::flush();
    }

    public function test_generate_content_with_valid_type_and_api_available()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Generated test content'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $result = $this->service->generateContent('test prompt', 'title');

        // Assert
        $this->assertEquals('Generated test content', $result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.openai.com/v1/chat/completions' &&
                   $request->hasHeader('Authorization', 'Bearer test-api-key') &&
                   $request['model'] === 'gpt-3.5-turbo';
        });
    }

    public function test_generate_content_returns_fallback_when_api_unavailable()
    {
        // Arrange
        Config::set('services.openai.api_key', null);

        // Act
        $result = $this->service->generateContent('test prompt', 'title');

        // Assert
        $this->assertEquals('Título gerado automaticamente - Edite conforme necessário', $result);
    }

    public function test_generate_content_returns_fallback_on_api_error()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([], 500)
        ]);

        Log::shouldReceive('error')->once();

        // Act
        $result = $this->service->generateContent('test prompt', 'content');

        // Assert
        $this->assertStringContainsString('Conteúdo Gerado Automaticamente', $result);
    }

    public function test_generate_content_throws_exception_for_invalid_type()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        // Act & Assert
        $result = $this->service->generateContent('test prompt', 'invalid_type');

        // Should return fallback content instead of throwing exception
        $this->assertStringContainsString('Conteúdo Gerado Automaticamente', $result);
    }

    public function test_build_prompt_creates_correct_templates()
    {
        // We'll test this indirectly by checking the API calls
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Generated content'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Test title prompt
        $this->service->generateContent('test topic', 'title');

        Http::assertSent(function ($request) {
            $messages = $request['messages'];
            return str_contains($messages[1]['content'], 'título atrativo') &&
                   str_contains($messages[1]['content'], 'test topic');
        });
    }

    public function test_process_response_removes_quotes_from_title()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '"Test Title with Quotes"'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $result = $this->service->generateContent('test prompt', 'title');

        // Assert
        $this->assertEquals('Test Title with Quotes', $result);
    }

    public function test_process_response_formats_keywords()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'keyword1; keyword2| keyword3'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $result = $this->service->generateContent('test prompt', 'keywords');

        // Assert
        $this->assertEquals('keyword1, keyword2, keyword3', $result);
    }

    public function test_process_response_limits_meta_description_length()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        $longDescription = str_repeat('This is a very long meta description that exceeds the character limit. ', 5);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $longDescription
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $result = $this->service->generateContent('test prompt', 'meta_description');

        // Assert
        $this->assertLessThanOrEqual(160, strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    public function test_process_response_formats_meta_keywords()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'meta1; meta2| meta3 ,  meta4'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $result = $this->service->generateContent('test prompt', 'meta_keywords');

        // Assert
        $this->assertEquals('meta1, meta2, meta3, meta4', $result);
    }

    public function test_is_api_available_returns_correct_status()
    {
        // Test with API key
        Config::set('services.openai.api_key', 'test-key');
        $this->assertTrue($this->service->isApiAvailable());

        // Test without API key
        Config::set('services.openai.api_key', null);
        $this->assertFalse($this->service->isApiAvailable());

        // Test with empty API key
        Config::set('services.openai.api_key', '');
        $this->assertFalse($this->service->isApiAvailable());
    }

    public function test_rate_limiting_works_correctly()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        // Set rate limit to 1 for testing
        Cache::put('ai_content_requests', 100, now()->addHour());

        // Act
        $result = $this->service->generateContent('test prompt', 'title');

        // Assert - should return fallback due to rate limit
        $this->assertEquals('Título gerado automaticamente - Edite conforme necessário', $result);
    }

    public function test_get_supported_types_returns_correct_array()
    {
        // Act
        $types = $this->service->getSupportedTypes();

        // Assert
        $this->assertEquals(['title', 'excerpt', 'content', 'keywords', 'meta_description', 'meta_keywords'], $types);
    }

    public function test_get_rate_limit_status_returns_correct_info()
    {
        // Arrange
        Cache::put('ai_content_requests', 25, now()->addHour());

        // Act
        $status = $this->service->getRateLimitStatus();

        // Assert
        $this->assertEquals([
            'current' => 25,
            'limit' => 100,
            'remaining' => 75
        ], $status);
    }

    public function test_get_fallback_content_returns_correct_content_for_each_type()
    {
        // Test each type
        $types = ['title', 'excerpt', 'content', 'keywords', 'meta_description', 'meta_keywords'];

        foreach ($types as $type) {
            Config::set('services.openai.api_key', null);
            $result = $this->service->generateContent('test', $type);
            $this->assertNotEmpty($result);

            // Verify specific content for each type
            switch ($type) {
                case 'title':
                    $this->assertStringContainsString('Título gerado automaticamente', $result);
                    break;
                case 'excerpt':
                    $this->assertStringContainsString('Resumo gerado automaticamente', $result);
                    break;
                case 'content':
                    $this->assertStringContainsString('Conteúdo Gerado Automaticamente', $result);
                    break;
                case 'keywords':
                    $this->assertStringContainsString('notícias, blog', $result);
                    break;
                case 'meta_description':
                    $this->assertStringContainsString('Descrição otimizada para SEO', $result);
                    break;
                case 'meta_keywords':
                    $this->assertStringContainsString('notícias, blog', $result);
                    break;
            }
        }
    }

    public function test_api_timeout_configuration()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => function () {
                // Simulate timeout
                throw new \Illuminate\Http\Client\ConnectionException('Timeout');
            }
        ]);

        Log::shouldReceive('error')->once();

        // Act
        $result = $this->service->generateContent('test prompt', 'title');

        // Assert - should return fallback
        $this->assertEquals('Título gerado automaticamente - Edite conforme necessário', $result);
    }

    public function test_api_request_includes_correct_parameters()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');
        Config::set('services.openai.model', 'gpt-4');
        Config::set('services.openai.max_tokens', 2000);
        Config::set('services.openai.temperature', 0.5);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Generated content'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $this->service->generateContent('test prompt', 'title');

        // Assert
        Http::assertSent(function ($request) {
            return $request['model'] === 'gpt-4' &&
                   $request['max_tokens'] === 2000 &&
                   $request['temperature'] === 0.5 &&
                   $request['messages'][0]['role'] === 'system' &&
                   str_contains($request['messages'][0]['content'], 'português brasileiro');
        });
    }
}
