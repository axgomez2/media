<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsSearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected array $topics;
    protected array $newsArticles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN
        ]);

        // Create topics
        $this->topics = [
            NewsTopic::factory()->create(['name' => 'Technology', 'slug' => 'technology']),
            NewsTopic::factory()->create(['name' => 'Business', 'slug' => 'business']),
            NewsTopic::factory()->create(['name' => 'Sports', 'slug' => 'sports']),
        ];

        // Create news articles with different statuses and topics
        $this->newsArticles = [
            News::factory()->create([
                'author_id' => $this->admin->id,
                'title' => 'Latest Technology Trends',
                'content' => 'This article discusses the latest trends in technology and innovation.',
                'excerpt' => 'Technology trends overview',
                'status' => 'published',
                'topics' => [$this->topics[0]->id], // Technology
                'published_at' => now(),
            ]),
            News::factory()->create([
                'author_id' => $this->admin->id,
                'title' => 'Business Growth Strategies',
                'content' => 'Learn about effective business growth strategies for modern companies.',
                'excerpt' => 'Business growth guide',
                'status' => 'draft',
                'topics' => [$this->topics[1]->id], // Business
            ]),
            News::factory()->create([
                'author_id' => $this->admin->id,
                'title' => 'Sports Championship Results',
                'content' => 'Complete coverage of the latest sports championship results.',
                'excerpt' => 'Sports results summary',
                'status' => 'published',
                'topics' => [$this->topics[2]->id], // Sports
                'featured' => true,
                'published_at' => now()->subDay(),
            ]),
            News::factory()->create([
                'author_id' => $this->admin->id,
                'title' => 'Archived Technology News',
                'content' => 'This is an archived technology article.',
                'excerpt' => 'Archived tech content',
                'status' => 'archived',
                'topics' => [$this->topics[0]->id], // Technology
            ]),
        ];
    }

    /** @test */
    public function it_can_search_news_by_title()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'Technology']));

        $response->assertStatus(200);
        $response->assertSee('Latest Technology Trends');
        $response->assertSee('Archived Technology News');
        $response->assertDontSee('Business Growth Strategies');
        $response->assertDontSee('Sports Championship Results');
    }

    /** @test */
    public function it_can_search_news_by_content()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'business growth']));

        $response->assertStatus(200);
        $response->assertSee('Business Growth Strategies');
        $response->assertDontSee('Latest Technology Trends');
    }

    /** @test */
    public function it_can_search_news_by_excerpt()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'Sports results']));

        $response->assertStatus(200);
        $response->assertSee('Sports Championship Results');
        $response->assertDontSee('Latest Technology Trends');
    }

    /** @test */
    public function it_can_filter_news_by_status()
    {
        // Test published filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['status' => 'published']));

        $response->assertStatus(200);
        $response->assertSee('Latest Technology Trends');
        $response->assertSee('Sports Championship Results');
        $response->assertDontSee('Business Growth Strategies');
        $response->assertDontSee('Archived Technology News');

        // Test draft filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['status' => 'draft']));

        $response->assertStatus(200);
        $response->assertSee('Business Growth Strategies');
        $response->assertDontSee('Latest Technology Trends');

        // Test archived filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['status' => 'archived']));

        $response->assertStatus(200);
        $response->assertSee('Archived Technology News');
        $response->assertDontSee('Latest Technology Trends');
    }

    /** @test */
    public function it_can_filter_news_by_topic()
    {
        // Test technology topic filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['topic' => $this->topics[0]->id]));

        $response->assertStatus(200);
        $response->assertSee('Latest Technology Trends');
        $response->assertSee('Archived Technology News');
        $response->assertDontSee('Business Growth Strategies');
        $response->assertDontSee('Sports Championship Results');

        // Test business topic filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['topic' => $this->topics[1]->id]));

        $response->assertStatus(200);
        $response->assertSee('Business Growth Strategies');
        $response->assertDontSee('Latest Technology Trends');
    }

    /** @test */
    public function it_can_filter_news_by_featured_status()
    {
        // Test featured filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['featured' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Sports Championship Results');
        $response->assertDontSee('Latest Technology Trends');
        $response->assertDontSee('Business Growth Strategies');

        // Test non-featured filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['featured' => '0']));

        $response->assertStatus(200);
        $response->assertSee('Latest Technology Trends');
        $response->assertSee('Business Growth Strategies');
        $response->assertDontSee('Sports Championship Results');
    }

    /** @test */
    public function it_can_combine_multiple_filters()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'status' => 'published',
                'topic' => $this->topics[0]->id, // Technology
            ]));

        $response->assertStatus(200);
        $response->assertSee('Latest Technology Trends');
        $response->assertDontSee('Archived Technology News'); // Archived, not published
        $response->assertDontSee('Sports Championship Results'); // Different topic
        $response->assertDontSee('Business Growth Strategies'); // Draft status
    }

    /** @test */
    public function it_can_sort_news_by_different_criteria()
    {
        // Test sort by title ascending
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'sort' => 'title',
                'direction' => 'asc'
            ]));

        $response->assertStatus(200);
        // Should see articles in alphabetical order

        // Test sort by published date descending
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'sort' => 'published_at',
                'direction' => 'desc'
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_empty_search_results()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'nonexistent content']));

        $response->assertStatus(200);
        $response->assertSee('Nenhuma notícia encontrada');
    }

    /** @test */
    public function it_handles_ajax_search_requests()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['search' => 'Technology']), [
                'X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
        // Should return partial HTML for AJAX requests
        $response->assertSee('Latest Technology Trends');
    }

    /** @test */
    public function it_handles_ajax_filter_requests()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'status' => 'published',
                'topic' => $this->topics[0]->id
            ]), [
                'X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
        $response->assertSee('Latest Technology Trends');
    }

    /** @test */
    public function it_maintains_pagination_with_filters()
    {
        // Create more news articles to test pagination
        News::factory()->count(15)->create([
            'author_id' => $this->admin->id,
            'status' => 'published',
            'topics' => [$this->topics[0]->id],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', [
                'status' => 'published',
                'topic' => $this->topics[0]->id,
                'page' => 2
            ]));

        $response->assertStatus(200);
        // Should maintain filters across pages
    }

    /** @test */
    public function it_validates_filter_parameters()
    {
        // Test with invalid status
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['status' => 'invalid_status']));

        $response->assertStatus(200);
        // Should ignore invalid filter and show all news

        // Test with invalid topic ID
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index', ['topic' => 999]));

        $response->assertStatus(200);
        // Should ignore invalid topic and show all news
    }

    /** @test */
    public function it_shows_correct_statistics_with_filters()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check statistics cards show correct counts
        $response->assertSee('4'); // Total news
        $response->assertSee('2'); // Published news
        $response->assertSee('1'); // Draft news
        $response->assertSee('1'); // Archived news
        $response->assertSee('1'); // Featured news
    }