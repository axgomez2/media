<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected array $topics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN
        ]);

        $this->topics = NewsTopic::factory()->count(3)->create()->toArray();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_complete_full_news_creation_workflow()
    {
        // Step 1: Access create form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);
        $response->assertSee('Nova Notícia');

        // Step 2: Create news with all features
        $featuredImage = UploadedFile::fake()->image('featured.jpg', 800, 600)->size(1024);
        $galleryImages = [
            UploadedFile::fake()->image('gallery1.jpg', 600, 400)->size(512),
            UploadedFile::fake()->image('gallery2.jpg', 600, 400)->size(512),
        ];

        $newsData = [
            'title' => 'Complete Integration Test News',
            'slug' => 'complete-integration-test-news',
            'excerpt' => 'This is a comprehensive test of the complete news system.',
            'content' => 'This is the full content of the integration test news article. It contains detailed information and is long enough to pass validation.',
            'status' => 'published',
            'featured' => true,
            'meta_description' => 'Integration test meta description for SEO',
            'meta_keywords' => 'integration, test, news, system',
            'topics' => array_column($this->topics, 'id'),
            'featured_image' => $featuredImage,
            'gallery_images' => $galleryImages,
            'published_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Step 3: Verify news was created correctly
        $news = News::where('slug', 'complete-integration-test-news')->first();
        $this->assertNotNull($news);
        $this->assertEquals('Complete Integration Test News', $news->title);
        $this->assertEquals('published', $news->status);
        $this->assertTrue($news->featured);
        $this->assertNotNull($news->published_at);
        $this->assertEquals(array_column($this->topics, 'id'), $news->topics);

        // Step 4: Verify files were uploaded
        $this->assertNotNull($news->featured_image);
        $this->assertCount(2, $news->gallery_images);
        Storage::disk('public')->assertExists('news/' . $news->featured_image);
        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->assertExists('news/' . $image);
        }

        // Step 5: View the created news
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee('Complete Integration Test News');
        $response->assertSee('This is a comprehensive test');

        // Step 6: Edit the news
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.edit', $news));

        $response->assertStatus(200);
        $response->assertSee('Complete Integration Test News');

        // Step 7: Update the news
        $updateData = [
            'title' => 'Updated Integration Test News',
            'content' => $news->content,
            'status' => 'draft',
            'featured' => false,
            'remove_gallery_images' => [$news->gallery_images[0]], // Remove first gallery image
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();

        // Step 8: Verify updates
        $news->refresh();
        $this->assertEquals('Updated Integration Test News', $news->title);
        $this->assertEquals('draft', $news->status);
        $this->assertFalse($news->featured);
        $this->assertCount(1, $news->gallery_images); // One image removed

        // Step 9: Test search and filters
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'Integration']));

        $response->assertStatus(200);
        $response->assertSee('Updated Integration Test News');

        // Step 10: Delete the news
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.news.destroy', $news));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Step 11: Verify deletion
        $this->assertDatabaseMissing('news', ['id' => $news->id]);
        Storage::disk('public')->assertMissing('news/' . $news->featured_image);
        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->assertMissing('news/' . $image);
        }
    }

    /** @test */
    public function it_can_complete_ai_content_generation_workflow()
    {
        // Mock AI API response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'AI Generated Title'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Step 1: Access create form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);

        // Step 2: Generate AI content
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.news.generate-content'), [
                'prompt' => 'Create a title about technology trends',
                'type' => 'title'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'content' => 'AI Generated Title'
        ]);

        // Step 3: Create news with AI-generated content
        $newsData = [
            'title' => 'AI Generated Title',
            'content' => 'This content was created using AI assistance for testing purposes.',
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        // Step 4: Verify news was created
        $this->assertDatabaseHas('news', [
            'title' => 'AI Generated Title',
            'status' => 'draft'
        ]);
    }

    /** @test */
    public function it_can_complete_topic_management_workflow()
    {
        // Step 1: Access topics index
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news-topics.index'));

        $response->assertStatus(200);
        $response->assertSee('Tópicos de Notícias');

        // Step 2: Create new topic
        $topicData = [
            'name' => 'Integration Test Topic',
            'description' => 'Topic created during integration testing',
            'color' => '#FF5733',
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news-topics.store'), $topicData);

        $response->assertRedirect();

        // Step 3: Verify topic creation
        $topic = NewsTopic::where('name', 'Integration Test Topic')->first();
        $this->assertNotNull($topic);
        $this->assertEquals('integration-test-topic', $topic->slug);

        // Step 4: Create news with the new topic
        $newsData = [
            'title' => 'News with New Topic',
            'content' => 'This news article uses the newly created topic.',
            'status' => 'published',
            'topics' => [$topic->id],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        // Step 5: Verify news-topic relationship
        $news = News::where('title', 'News with New Topic')->first();
        $this->assertEquals([$topic->id], $news->topics);

        // Step 6: Filter news by topic
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['topic' => $topic->id]));

        $response->assertStatus(200);
        $response->assertSee('News with New Topic');

        // Step 7: Update topic
        $updateData = [
            'name' => 'Updated Integration Topic',
            'color' => '#33FF57',
            'is_active' => false
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news-topics.update', $topic), $updateData);

        $response->assertRedirect();

        // Step 8: Verify topic update
        $topic->refresh();
        $this->assertEquals('Updated Integration Topic', $topic->name);
        $this->assertFalse($topic->is_active);

        // Step 9: Delete topic
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.news-topics.destroy', $topic));

        $response->assertRedirect();

        // Step 10: Verify topic deletion
        $this->assertDatabaseMissing('news_topics', ['id' => $topic->id]);
    }

    /** @test */
    public function it_handles_complex_filtering_and_pagination_workflow()
    {
        // Create diverse news articles
        $techTopic = $this->topics[0];
        $businessTopic = $this->topics[1];

        $newsArticles = [
            News::factory()->create([
                'author_id' => $this->admin->id,
                'title' => 'Published Tech News',
                'status' => 'published',
                'topics' => [$techTopic['id']],
                'featured' => true,
                'published_at' => now(),
            ]),
            News::factory()->create([
                'author_id' => $this->admin->id,
                'title' => 'Draft Business News',
                'status' => 'draft',
                'topics' => [$businessTopic['id']],
                'featured' => false,
            ]),
            News::factory()->create([
                'author_id' => $this->admin->id,
                'title' => 'Archived Tech News',
                'status' => 'archived',
                'topics' => [$techTopic['id']],
                'featured' => false,
            ]),
        ];

        // Create additional news for pagination testing
        News::factory()->count(15)->create([
            'author_id' => $this->admin->id,
            'status' => 'published',
            'topics' => [$techTopic['id']],
        ]);

        // Step 1: Test basic filtering
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['status' => 'published']));

        $response->assertStatus(200);
        $response->assertSee('Published Tech News');
        $response->assertDontSee('Draft Business News');

        // Step 2: Test topic filtering
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['topic' => $techTopic['id']]));

        $response->assertStatus(200);
        $response->assertSee('Published Tech News');
        $response->assertSee('Archived Tech News');
        $response->assertDontSee('Draft Business News');

        // Step 3: Test combined filtering
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'status' => 'published',
                'topic' => $techTopic['id'],
                'featured' => '1'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Published Tech News');
        $response->assertDontSee('Archived Tech News');

        // Step 4: Test search functionality
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'Business']));

        $response->assertStatus(200);
        $response->assertSee('Draft Business News');
        $response->assertDontSee('Published Tech News');

        // Step 5: Test pagination with filters
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'status' => 'published',
                'page' => 2
            ]));

        $response->assertStatus(200);
        // Should maintain filters across pages

        // Step 6: Test AJAX requests
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'Tech']), [
                'X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
        $response->assertSee('Published Tech News');
    }

    /** @test */
    public function it_handles_error_scenarios_gracefully()
    {
        // Test 1: Invalid news ID
        $response = $this->actingAs($this->admin)
            ->get('/admin/news/999');

        $response->assertStatus(404);

        // Test 2: Unauthorized access
        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.news.index'));

        $response->assertStatus(403);

        // Test 3: Invalid form data
        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), [
                'title' => 'A', // Too short
                'content' => 'Short', // Too short
                'status' => 'invalid_status'
            ]);

        $response->assertSessionHasErrors(['title', 'content', 'status']);

        // Test 4: File upload errors
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), [
                'title' => 'Valid Title',
                'content' => 'Valid content that is long enough for validation.',
                'status' => 'draft',
                'featured_image' => $invalidFile
            ]);

        $response->assertSessionHasErrors(['featured_image']);

        // Test 5: AI API failure
        Http::fake([
            'api.openai.com/*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.news.generate-content'), [
                'prompt' => 'Test prompt',
                'type' => 'title'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]); // Should fallback gracefully
    }

    /** @test */
    public function it_maintains_data_integrity_throughout_workflow()
    {
        // Create news with relationships
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'title' => 'Data Integrity Test',
            'topics' => array_column($this->topics, 'id'),
            'featured_image' => 'test-image.jpg',
            'gallery_images' => ['gallery1.jpg', 'gallery2.jpg'],
        ]);

        // Create the image files
        Storage::disk('public')->put('news/test-image.jpg', 'content');
        Storage::disk('public')->put('news/gallery1.jpg', 'content');
        Storage::disk('public')->put('news/gallery2.jpg', 'content');

        // Verify initial state
        $this->assertEquals(array_column($this->topics, 'id'), $news->topics);
        $this->assertEquals('test-image.jpg', $news->featured_image);
        $this->assertCount(2, $news->gallery_images);

        // Update news
        $updateData = [
            'title' => 'Updated Data Integrity Test',
            'content' => $news->content,
            'status' => $news->status,
            'topics' => [$this->topics[0]['id']], // Reduce topics
            'remove_gallery_images' => ['gallery1.jpg'], // Remove one image
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();

        // Verify data integrity after update
        $news->refresh();
        $this->assertEquals('Updated Data Integrity Test', $news->title);
        $this->assertEquals([$this->topics[0]['id']], $news->topics);
        $this->assertEquals(['gallery2.jpg'], $news->gallery_images);

        // Verify file system integrity
        Storage::disk('public')->assertExists('news/test-image.jpg');
        Storage::disk('public')->assertMissing('news/gallery1.jpg');
        Storage::disk('public')->assertExists('news/gallery2.jpg');

        // Delete news and verify cleanup
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.news.destroy', $news));

        $response->assertRedirect();

        // Verify complete cleanup
        $this->assertDatabaseMissing('news', ['id' => $news->id]);
        Storage::disk('public')->assertMissing('news/test-image.jpg');
        Storage::disk('public')->assertMissing('news/gallery2.jpg');
    }
}
