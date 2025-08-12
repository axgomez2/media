<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIContentGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_ai_content_generation_endpoint_requires_authentication()
    {
        // Act
        $response = $this->postJson('/admin/news/generate-content', [
            'prompt' => 'Test prompt',
            'type' => 'title'
        ]);

        // Assert
        $response->assertStatus(401);
    }

    public function test_ai_content_generation_with_valid_data()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Generated AI Title'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/news/generate-content', [
                'prompt' => 'Create a title about technology',
                'type' => 'title'
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'content' => 'Generated AI Title'
            ]);
    }

    public function test_ai_content_generation_with_invalid_type()
    {
        // Act
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/news/generate-content', [
                'prompt' => 'Test prompt',
                'type' => 'invalid_type'
            ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_ai_content_generation_without_prompt()
    {
        // Act
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/news/generate-content', [
                'type' => 'title'
            ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);
    }

    public function test_ai_content_generation_returns_fallback_when_api_unavailable()
    {
        // Arrange
        Config::set('services.openai.api_key', null);

        // Act
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/news/generate-content', [
                'prompt' => 'Test prompt',
                'type' => 'title'
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'content' => 'Título gerado automaticamente - Edite conforme necessário'
            ]);
    }

    public function test_ai_content_generation_handles_api_errors_gracefully()
    {
        // Arrange
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([], 500)
        ]);

        // Act
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/news/generate-content', [
                'prompt' => 'Test prompt',
                'type' => 'title'
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'content' => 'Título gerado automaticamente - Edite conforme necessário'
            ]);
    }

    public function test_ai_content_generation_supports_all_content_types()
    {
        // Arrange
        Config::set('services.openai.api_key', null); // Use fallback for predictable results

        $types = ['title', 'excerpt', 'content', 'keywords', 'meta_description', 'meta_keywords'];

        foreach ($types as $type) {
            // Act
            $response = $this->actingAs($this->adminUser)
                ->postJson('/admin/news/generate-content', [
                    'prompt' => 'Test prompt',
                    'type' => $type
                ]);

            // Assert
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'content'
                ]);

            $this->assertNotEmpty($response->json('content'));
        }
    }

    public function test_ai_content_generation_with_meta_description_limits_length()
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
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/news/generate-content', [
                'prompt' => 'Test prompt',
                'type' => 'meta_description'
            ]);

        // Assert
        $response->assertStatus(200);
        $content = $response->json('content');
        $this->assertLessThanOrEqual(160, strlen($content));
    }

    public function test_ai_content_generation_formats_keywords_correctly()
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
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/news/generate-content', [
                'prompt' => 'Test prompt',
                'type' => 'keywords'
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'content' => 'keyword1, keyword2, keyword3'
            ]);
    }
}
