<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsShowViewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_admin_can_view_news_show_page()
    {
        // Create a topic
        $topic = NewsTopic::factory()->create([
            'name' => 'Technology',
            'color' => '#3B82F6',
            'is_active' => true
        ]);

        // Create a news article
        $news = News::factory()->create([
            'title' => 'Test News Article',
            'slug' => 'test-news-article',
            'excerpt' => 'This is a test excerpt for the news article.',
            'content' => 'This is the full content of the test news article. It contains detailed information about the topic.',
            'status' => 'published',
            'featured' => true,
            'author_id' => $this->admin->id,
            'topics' => [$topic->id],
            'meta_description' => 'This is a meta description for SEO purposes.',
            'meta_keywords' => 'test, news, article, technology',
            'published_at' => now(),
        ]);

        // Create related news
        News::factory()->count(2)->create([
            'status' => 'published',
            'author_id' => $this->admin->id,
            'topics' => [$topic->id],
            'published_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee($news->title);
        $response->assertSee($news->excerpt);
        $response->assertSee($news->content);
        $response->assertSee($news->status_formatted);
        $response->assertSee('Destaque'); // Featured badge
        $response->assertSee($topic->name);
        $response->assertSee('Notícias Relacionadas');
        $response->assertSee('Informações SEO');
        $response->assertSee($news->meta_description);
        $response->assertSee($news->meta_keywords);
    }

    public function test_show_view_displays_breadcrumbs()
    {
        $news = News::factory()->create([
            'title' => 'Test News Article',
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Notícias');
        $response->assertSee(route('admin.dashboard'));
        $response->assertSee(route('admin.news.index'));
    }

    public function test_show_view_displays_action_buttons()
    {
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee('Editar');
        $response->assertSee('Excluir');
        $response->assertSee(route('admin.news.edit', $news));
    }

    public function test_show_view_handles_news_without_featured_image()
    {
        $news = News::factory()->create([
            'title' => 'News Without Image',
            'featured_image' => null,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee($news->title);
    }

    public function test_show_view_handles_news_without_topics()
    {
        $news = News::factory()->create([
            'title' => 'News Without Topics',
            'topics' => [],
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee($news->title);
    }

    public function test_show_view_displays_related_news_when_available()
    {
        $topic = NewsTopic::factory()->create(['is_active' => true]);

        $news = News::factory()->create([
            'title' => 'Main News Article',
            'author_id' => $this->admin->id,
            'topics' => [$topic->id],
            'status' => 'published',
        ]);

        $relatedNews = News::factory()->create([
            'title' => 'Related News Article',
            'author_id' => $this->admin->id,
            'topics' => [$topic->id],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee('Notícias Relacionadas');
        $response->assertSee($relatedNews->title);
    }
}
