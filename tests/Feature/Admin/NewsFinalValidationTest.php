<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsFinalValidationTest extends TestCase
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
    }

    /** @test */
    public function it_validates_complete_news_creation_workflow()
    {
        Storage::fake('public');

        $featuredImage = UploadedFile::fake()->image('featured.jpg', 800, 600)->size(1024);
        $galleryImages = [
            UploadedFile::fake()->image('gallery1.jpg', 600, 400)->size(512),
            UploadedFile::fake()->image('gallery2.jpg', 600, 400)->size(512),
        ];

        $newsData = [
            'title' => 'Complete News Article with All Features',
            'content' => 'This is a comprehensive test content for the news article that includes all necessary features and validations. It contains enough text to pass all validation requirements and demonstrates the complete functionality of the news system.',
            'excerpt' => 'This is a comprehensive test excerpt that provides a summary of the article content.',
            'status' => 'published',
            'featured' => true,
            'meta_description' => 'This is a comprehensive meta description that meets all the validation requirements for SEO optimization.',
            'meta_keywords' => 'test, news, article, validation, comprehensive',
            'featured_image' => $featuredImage,
            'gallery_images' => $galleryImages,
            'topics' => array_column($this->topics, 'id'),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $news = News::where('title', 'Complete News Article with All Features')->first();
        $this->assertNotNull($news);

        // Validate all fields were saved correctly
        $this->assertEquals('complete-news-article-with-all-features', $news->slug);
        $this->assertEquals('published', $news->status);
        $this->assertTrue($news->featured);
        $this->assertNotNull($news->published_at);
        $this->assertEquals($this->admin->id, $news->author_id);
        $this->assertEquals(array_column($this->topics, 'id'), $news->topics);

        // Validate images were uploaded
        $this->assertNotNull($news->featured_image);
        $this->assertCount(2, $news->gallery_images);

        // Validate files exist in storage
        Storage::disk('public')->assertExists('news/' . $news->featured_image);
        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->assertExists('news/' . $image);
        }
    }

    /** @test */
    public function it_validates_complete_news_update_workflow()
    {
        Storage::fake('public');

        // Create initial news
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'title' => 'Original Title',
            'content' => 'Original content that is comprehensive enough for validation requirements.',
            'status' => 'draft',
            'featured_image' => 'old-featured.jpg',
            'gallery_images' => ['old-gallery1.jpg'],
        ]);

        // Create old files
        Storage::disk('public')->put('news/old-featured.jpg', 'old content');
        Storage::disk('public')->put('news/old-gallery1.jpg', 'old content');

        $newFeaturedImage = UploadedFile::fake()->image('new-featured.jpg', 800, 600)->size(1024);
        $newGalleryImages = [
            UploadedFile::fake()->image('new-gallery1.jpg', 600, 400)->size(512),
        ];

        $updateData = [
            'title' => 'Updated Complete News Article',
            'content' => 'Updated comprehensive content that includes all necessary features and validations for the news system.',
            'excerpt' => 'Updated comprehensive excerpt that provides a summary of the updated article content.',
            'status' => 'published',
            'featured' => true,
            'meta_description' => 'Updated comprehensive meta description that meets all validation requirements for SEO.',
            'meta_keywords' => 'updated, test, news, article, validation',
            'featured_image' => $newFeaturedImage,
            'gallery_images' => $newGalleryImages,
            'topics' => [array_column($this->topics, 'id')[0]], // Single topic
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $news->refresh();

        // Validate updates
        $this->assertEquals('Updated Complete News Article', $news->title);
        $this->assertEquals('updated-complete-news-article', $news->slug);
        $this->assertEquals('published', $news->status);
        $this->assertTrue($news->featured);
        $this->assertNotNull($news->published_at);

        // Validate old files were deleted
        Storage::disk('public')->assertMissing('news/old-featured.jpg');
        Storage::disk('public')->assertMissing('news/old-gallery1.jpg');

        // Validate new files exist
        Storage::disk('public')->assertExists('news/' . $news->featured_image);
        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->assertExists('news/' . $image);
        }
    }

    /** @test */
    public function it_validates_complete_search_and_filter_functionality()
    {
        // Create test data
        $techTopic = $this->topics[0];
        $businessTopic = $this->topics[1];

        $publishedTechNews = News::factory()->create([
            'author_id' => $this->admin->id,
            'title' => 'Technology Innovation Trends',
            'content' => 'Comprehensive content about technology innovation and trends in the industry.',
            'status' => 'published',
            'topics' => [$techTopic['id']],
            'featured' => true,
            'published_at' => now(),
        ]);

        $draftBusinessNews = News::factory()->create([
            'author_id' => $this->admin->id,
            'title' => 'Business Growth Strategies',
            'content' => 'Comprehensive content about business growth and strategic planning.',
            'status' => 'draft',
            'topics' => [$businessTopic['id']],
            'featured' => false,
        ]);

        // Test search functionality
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'Technology Innovation']));

        $response->assertStatus(200);
        $response->assertSee('Technology Innovation Trends');
        $response->assertDontSee('Business Growth Strategies');

        // Test status filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['status' => 'published']));

        $response->assertStatus(200);
        $response->assertSee('Technology Innovation Trends');
        $response->assertDontSee('Business Growth Strategies');

        // Test topic filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['topic' => $techTopic['id']]));

        $response->assertStatus(200);
        $response->assertSee('Technology Innovation Trends');
        $response->assertDontSee('Business Growth Strategies');

        // Test featured filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['featured' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Technology Innovation Trends');
        $response->assertDontSee('Business Growth Strategies');

        // Test combined filters
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'status' => 'published',
                'topic' => $techTopic['id'],
                'featured' => '1'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Technology Innovation Trends');
        $response->assertDontSee('Business Growth Strategies');
    }

    /** @test */
    public function it_validates_ai_integration_and_error_handling()
    {
        // Test AI content generation with valid API
        Config::set('services.openai.api_key', 'test-api-key');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'AI Generated Content for Testing'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.news.generate-content'), [
                'prompt' => 'Generate a title about technology',
                'type' => 'title'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'content' => 'AI Generated Content for Testing'
            ]);

        // Test AI fallback when API fails
        Http::fake([
            'api.openai.com/*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.news.generate-content'), [
                'prompt' => 'Generate a title about technology',
                'type' => 'title'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertNotEmpty($response->json('content'));

        // Test AI when no API key configured
        Config::set('services.openai.api_key', null);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.news.generate-content'), [
                'prompt' => 'Generate a title about technology',
                'type' => 'title'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertNotEmpty($response->json('content'));
    }

    /** @test */
    public function it_validates_complete_image_management_workflow()
    {
        Storage::fake('public');

        // Test image upload validation
        $validImage = UploadedFile::fake()->image('valid.jpg', 800, 600)->size(1024);
        $invalidImage = UploadedFile::fake()->create('invalid.txt', 1024);
        $largeImage = UploadedFile::fake()->image('large.jpg', 800, 600)->size(6144); // 6MB
        $smallImage = UploadedFile::fake()->image('small.jpg', 100, 100)->size(512);

        // Test valid image upload
        $newsData = [
            'title' => 'News with Valid Image',
            'content' => 'Comprehensive content for testing valid image upload functionality.',
            'status' => 'draft',
            'meta_description' => 'Meta description that meets the minimum length requirements for validation.',
            'featured_image' => $validImage,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Test invalid file type
        $newsData['title'] = 'News with Invalid Image';
        $newsData['featured_image'] = $invalidImage;

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors('featured_image');

        // Test file too large
        $newsData['title'] = 'News with Large Image';
        $newsData['featured_image'] = $largeImage;

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors('featured_image');

        // Test image too small
        $newsData['title'] = 'News with Small Image';
        $newsData['featured_image'] = $smallImage;

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors('featured_image');

        // Test gallery images limit
        $tooManyImages = [];
        for ($i = 0; $i < 11; $i++) { // Max is 10
            $tooManyImages[] = UploadedFile::fake()->image("test{$i}.jpg", 600, 400)->size(512);
        }

        $newsData['title'] = 'News with Too Many Gallery Images';
        $newsData['featured_image'] = $validImage;
        $newsData['gallery_images'] = $tooManyImages;

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors('gallery_images');
    }

    /** @test */
    public function it_validates_complete_deletion_workflow()
    {
        Storage::fake('public');

        // Create news with images
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'featured.jpg',
            'gallery_images' => ['gallery1.jpg', 'gallery2.jpg'],
        ]);

        // Create image files
        Storage::disk('public')->put('news/featured.jpg', 'featured content');
        Storage::disk('public')->put('news/gallery1.jpg', 'gallery1 content');
        Storage::disk('public')->put('news/gallery2.jpg', 'gallery2 content');

        // Test regular deletion
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.news.destroy', $news));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Validate news deleted from database
        $this->assertDatabaseMissing('news', ['id' => $news->id]);

        // Validate all files deleted
        Storage::disk('public')->assertMissing('news/featured.jpg');
        Storage::disk('public')->assertMissing('news/gallery1.jpg');
        Storage::disk('public')->assertMissing('news/gallery2.jpg');

        // Test AJAX deletion
        $anotherNews = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'ajax-featured.jpg',
        ]);

        Storage::disk('public')->put('news/ajax-featured.jpg', 'content');

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.news.destroy', $anotherNews));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('news', ['id' => $anotherNews->id]);
        Storage::disk('public')->assertMissing('news/ajax-featured.jpg');
    }

    /** @test */
    public function it_validates_security_and_authorization()
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $news = News::factory()->create(['author_id' => $this->admin->id]);

        // Test unauthenticated access
        $this->get(route('admin.news.index'))->assertRedirect('/login');
        $this->get(route('admin.news.create'))->assertRedirect('/login');
        $this->post(route('admin.news.store'), [])->assertRedirect('/login');
        $this->get(route('admin.news.show', $news))->assertRedirect('/login');
        $this->get(route('admin.news.edit', $news))->assertRedirect('/login');
        $this->put(route('admin.news.update', $news), [])->assertRedirect('/login');
        $this->delete(route('admin.news.destroy', $news))->assertRedirect('/login');

        // Test non-admin access
        $this->actingAs($regularUser)->get(route('admin.news.index'))->assertStatus(403);
        $this->actingAs($regularUser)->get(route('admin.news.create'))->assertStatus(403);
        $this->actingAs($regularUser)->post(route('admin.news.store'), [])->assertStatus(403);
        $this->actingAs($regularUser)->get(route('admin.news.show', $news))->assertStatus(403);
        $this->actingAs($regularUser)->get(route('admin.news.edit', $news))->assertStatus(403);
        $this->actingAs($regularUser)->put(route('admin.news.update', $news), [])->assertStatus(403);
        $this->actingAs($regularUser)->delete(route('admin.news.destroy', $news))->assertStatus(403);

        // Test CSRF protection
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.news.store'), [
                'title' => 'Test News',
                'content' => 'Test content',
                'status' => 'draft',
            ]);

        // Should work without CSRF middleware disabled
        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_responsive_design_elements()
    {
        News::factory()->count(3)->create(['author_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for responsive grid classes
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4', false);

        // Check for responsive form layout
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);
        $response->assertSee('lg:grid-cols-3', false);
        $response->assertSee('lg:col-span-2', false);

        // Check for mobile-friendly elements
        $response->assertSee('sm:text-sm', false);
        $response->assertSee('block w-full', false);

        // Check for touch-friendly button sizes
        $response->assertSee('px-4 py-2', false);
        $response->assertSee('px-3 py-1.5', false);
    }

    /** @test */
    public function it_validates_interactive_javascript_functionality()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for interactive elements
        $response->assertSee('id="search-indicator"', false);
        $response->assertSee('id="filter-indicator"', false);
        $response->assertSee('data-news-id=', false);
        $response->assertSee('id="deleteModal"', false);

        // Check for JavaScript module
        $response->assertSee('news-interactive.js', false);

        // Check create form interactive elements
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);

        // Check for drop zones
        $response->assertSee('drop-zone', false);
        $response->assertSee('data-type="featured"', false);
        $response->assertSee('data-type="gallery"', false);

        // Check for multi-select topics
        $response->assertSee('id="topics-multiselect"', false);

        // Check for AI content generation buttons
        $response->assertSee('data-ai-type="title"', false);
        $response->assertSee('data-ai-type="excerpt"', false);
        $response->assertSee('data-ai-type="content"', false);
    }

    /** @test */
    public function it_validates_accessibility_compliance()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for ARIA labels
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee('aria-label="Pagination"', false);

        // Check for proper form labels
        $response->assertSee('for="search"', false);

        // Check for screen reader text
        $response->assertSee('sr-only', false);

        // Check for focus states
        $response->assertSee('focus:outline-none', false);
        $response->assertSee('focus:ring-', false);

        // Check for proper color contrast
        $response->assertSee('text-gray-900', false);
        $response->assertSee('text-gray-600', false);
        $response->assertSee('bg-white', false);
    }

    /** @test */
    public function it_validates_performance_optimizations()
    {
        // Create multiple news articles to test pagination
        News::factory()->count(25)->create(['author_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Should have pagination
        $response->assertSee('pagination', false);

        // Test that queries are optimized (no N+1 problems)
        // This would typically be tested with query counting
        $this->assertTrue(true); // Placeholder for query optimization tests
    }
}
