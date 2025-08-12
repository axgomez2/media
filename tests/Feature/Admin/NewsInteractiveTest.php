<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsInteractiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN
        ]);

        // Create test topics
        $this->topics = NewsTopic::factory()->count(3)->create();

        // Create test news
        $this->news = News::factory()->count(5)->create([
            'author_id' => $this->admin->id
        ]);
    }

    /** @test */
    public function it_loads_news_index_with_interactive_elements()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for search indicator
        $response->assertSee('id="search-indicator"', false);

        // Check for filter indicator
        $response->assertSee('id="filter-indicator"', false);

        // Check for news cards with data attributes
        $response->assertSee('data-news-id=', false);
    }

    /** @test */
    public function it_provides_topics_api_endpoint()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.news-topics.api'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'color',
                'slug',
                'news_count'
            ]
        ]);
    }

    /** @test */
    public function it_handles_ajax_search_requests()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'test']), [
                'X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
        // Should return HTML content for AJAX requests
        $response->assertSee('grid', false);
    }

    /** @test */
    public function it_handles_ajax_filter_requests()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'status' => 'published',
                'topic' => $this->topics->first()->id
            ]), [
                'X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_loads_create_form_with_interactive_elements()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);

        // Check for drop zones
        $response->assertSee('drop-zone', false);
        $response->assertSee('data-type="featured"', false);
        $response->assertSee('data-type="gallery"', false);

        // Check for multi-select topics
        $response->assertSee('id="topics-multiselect"', false);

        // Check for preview containers
        $response->assertSee('id="featured-image-preview"', false);
        $response->assertSee('id="gallery-images-preview"', false);
    }

    /** @test */
    public function it_loads_edit_form_with_interactive_elements()
    {
        $news = $this->news->first();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.edit', $news));

        $response->assertStatus(200);

        // Check for drop zones
        $response->assertSee('drop-zone', false);

        // Check for multi-select topics
        $response->assertSee('id="topics-multiselect"', false);
    }

    /** @test */
    public function it_handles_delete_requests_via_ajax()
    {
        $news = $this->news->first();

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.news.destroy', $news));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }

    /** @test */
    public function it_validates_image_uploads()
    {
        Storage::fake('public');

        $validImage = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024); // 1MB
        $invalidImage = UploadedFile::fake()->create('test.txt', 1024); // Text file
        $largeImage = UploadedFile::fake()->image('large.jpg', 800, 600)->size(6144); // 6MB

        // Test valid image
        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), [
                'title' => 'Test News',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => $validImage,
            ]);

        $response->assertRedirect();

        // Test invalid file type
        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), [
                'title' => 'Test News 2',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => $invalidImage,
            ]);

        $response->assertSessionHasErrors('featured_image');

        // Test file too large
        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), [
                'title' => 'Test News 3',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => $largeImage,
            ]);

        $response->assertSessionHasErrors('featured_image');
    }

    /** @test */
    public function it_handles_multiple_topic_selection()
    {
        $topicIds = $this->topics->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), [
                'title' => 'Test News with Topics',
                'content' => 'Test content',
                'status' => 'draft',
                'topics' => $topicIds,
            ]);

        $response->assertRedirect();

        $news = News::where('title', 'Test News with Topics')->first();
        $this->assertNotNull($news);
        $this->assertEquals($topicIds, $news->topics);
    }

    /** @test */
    public function it_provides_proper_csrf_protection()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertSee('csrf-token', false);
        $response->assertSee('name="csrf-token"', false);
    }

    /** @test */
    public function it_includes_necessary_javascript_modules()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        // Check that the admin layout includes the necessary Vite assets
        $response->assertSee('@vite', false);
    }
}
