<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsCRUDTest extends TestCase
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
    public function it_can_create_news_with_basic_data()
    {
        $newsData = [
            'title' => 'Test News Article',
            'content' => 'This is a comprehensive test content for the news article. It contains enough text to pass validation requirements.',
            'excerpt' => 'This is a test excerpt',
            'status' => 'draft',
            'meta_description' => 'This is a comprehensive test meta description that meets the minimum length requirements for validation.',
            'meta_keywords' => 'test, news, article',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('news', [
            'title' => 'Test News Article',
            'slug' => 'test-news-article',
            'status' => 'draft',
            'author_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function it_can_create_news_with_featured_image()
    {
        Storage::fake('public');

        $featuredImage = UploadedFile::fake()->image('featured.jpg', 800, 600)->size(1024);

        $newsData = [
            'title' => 'News with Featured Image',
            'content' => 'This is a comprehensive test content for the news article with featured image.',
            'status' => 'draft',
            'featured_image' => $featuredImage,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        $news = News::where('title', 'News with Featured Image')->first();
        $this->assertNotNull($news);
        $this->assertNotNull($news->featured_image);

        Storage::disk('public')->assertExists('news/' . $news->featured_image);
    }

    /** @test */
    public function it_can_create_news_with_gallery_images()
    {
        Storage::fake('public');

        $galleryImages = [
            UploadedFile::fake()->image('gallery1.jpg', 600, 400)->size(512),
            UploadedFile::fake()->image('gallery2.jpg', 600, 400)->size(512),
        ];

        $newsData = [
            'title' => 'News with Gallery',
            'content' => 'This is a comprehensive test content for the news article with gallery images.',
            'status' => 'draft',
            'gallery_images' => $galleryImages,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        $news = News::where('title', 'News with Gallery')->first();
        $this->assertNotNull($news);
        $this->assertIsArray($news->gallery_images);
        $this->assertCount(2, $news->gallery_images);

        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->assertExists('news/' . $image);
        }
    }

    /** @test */
    public function it_can_create_news_with_topics()
    {
        $topicIds = array_column($this->topics, 'id');

        $newsData = [
            'title' => 'News with Topics',
            'content' => 'This is a comprehensive test content for the news article with topics.',
            'status' => 'draft',
            'topics' => $topicIds,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        $news = News::where('title', 'News with Topics')->first();
        $this->assertNotNull($news);
        $this->assertEquals($topicIds, $news->topics);
    }

    /** @test */
    public function it_can_update_existing_news()
    {
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'title' => 'Original Title',
            'content' => 'Original content that is long enough for validation.',
            'status' => 'draft',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content that is also long enough for validation requirements.',
            'excerpt' => 'Updated excerpt',
            'status' => 'published',
            'meta_description' => 'Updated comprehensive meta description that meets validation requirements.',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $news->refresh();
        $this->assertEquals('Updated Title', $news->title);
        $this->assertEquals('updated-title', $news->slug);
        $this->assertEquals('published', $news->status);
        $this->assertNotNull($news->published_at);
    }

    /** @test */
    public function it_can_update_news_featured_image()
    {
        Storage::fake('public');

        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'old-image.jpg',
        ]);

        // Create the old image file
        Storage::disk('public')->put('news/old-image.jpg', 'old content');

        $newFeaturedImage = UploadedFile::fake()->image('new-featured.jpg', 800, 600)->size(1024);

        $updateData = [
            'title' => $news->title,
            'content' => $news->content,
            'status' => $news->status,
            'featured_image' => $newFeaturedImage,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();

        $news->refresh();
        $this->assertNotEquals('old-image.jpg', $news->featured_image);

        // Old image should be deleted
        Storage::disk('public')->assertMissing('news/old-image.jpg');

        // New image should exist
        Storage::disk('public')->assertExists('news/' . $news->featured_image);
    }

    /** @test */
    public function it_can_remove_featured_image_during_update()
    {
        Storage::fake('public');

        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'existing-image.jpg',
        ]);

        Storage::disk('public')->put('news/existing-image.jpg', 'content');

        $updateData = [
            'title' => $news->title,
            'content' => $news->content,
            'status' => $news->status,
            'remove_featured_image' => '1',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();

        $news->refresh();
        $this->assertNull($news->featured_image);
        Storage::disk('public')->assertMissing('news/existing-image.jpg');
    }

    /** @test */
    public function it_can_add_gallery_images_during_update()
    {
        Storage::fake('public');

        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'gallery_images' => ['existing1.jpg'],
        ]);

        Storage::disk('public')->put('news/existing1.jpg', 'content');

        $newGalleryImages = [
            UploadedFile::fake()->image('new1.jpg', 600, 400)->size(512),
            UploadedFile::fake()->image('new2.jpg', 600, 400)->size(512),
        ];

        $updateData = [
            'title' => $news->title,
            'content' => $news->content,
            'status' => $news->status,
            'gallery_images' => $newGalleryImages,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();

        $news->refresh();
        $this->assertCount(3, $news->gallery_images); // 1 existing + 2 new

        // All images should exist
        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->assertExists('news/' . $image);
        }
    }

    /** @test */
    public function it_can_remove_specific_gallery_images_during_update()
    {
        Storage::fake('public');

        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'gallery_images' => ['image1.jpg', 'image2.jpg', 'image3.jpg'],
        ]);

        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->put('news/' . $image, 'content');
        }

        $updateData = [
            'title' => $news->title,
            'content' => $news->content,
            'status' => $news->status,
            'remove_gallery_images' => ['image1.jpg', 'image3.jpg'],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();

        $news->refresh();
        $this->assertEquals(['image2.jpg'], $news->gallery_images);

        // Removed images should not exist
        Storage::disk('public')->assertMissing('news/image1.jpg');
        Storage::disk('public')->assertMissing('news/image3.jpg');

        // Remaining image should exist
        Storage::disk('public')->assertExists('news/image2.jpg');
    }

    /** @test */
    public function it_can_view_news_details()
    {
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'title' => 'Test News for Viewing',
            'content' => 'This is the content for viewing test.',
            'status' => 'published',
            'topics' => array_column($this->topics, 'id'),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);
        $response->assertSee('Test News for Viewing');
        $response->assertSee('This is the content for viewing test.');

        // Should see topic names
        foreach ($this->topics as $topic) {
            $response->assertSee($topic['name']);
        }
    }

    /** @test */
    public function it_can_delete_news_and_associated_files()
    {
        Storage::fake('public');

        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'featured.jpg',
            'gallery_images' => ['gallery1.jpg', 'gallery2.jpg'],
        ]);

        // Create the image files
        Storage::disk('public')->put('news/featured.jpg', 'featured content');
        Storage::disk('public')->put('news/gallery1.jpg', 'gallery1 content');
        Storage::disk('public')->put('news/gallery2.jpg', 'gallery2 content');

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.news.destroy', $news));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // News should be deleted from database
        $this->assertDatabaseMissing('news', ['id' => $news->id]);

        // All associated files should be deleted
        Storage::disk('public')->assertMissing('news/featured.jpg');
        Storage::disk('public')->assertMissing('news/gallery1.jpg');
        Storage::disk('public')->assertMissing('news/gallery2.jpg');
    }

    /** @test */
    public function it_can_delete_news_via_ajax()
    {
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.news.destroy', $news));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }

    /** @test */
    public function it_validates_required_fields_during_creation()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), []);

        $response->assertSessionHasErrors(['title', 'content', 'status']);
    }

    /** @test */
    public function it_validates_image_uploads()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1024);

        $newsData = [
            'title' => 'Test News',
            'content' => 'This is test content that is long enough for validation.',
            'status' => 'draft',
            'featured_image' => $invalidFile,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors(['featured_image']);
    }

    /** @test */
    public function it_auto_generates_slug_from_title()
    {
        $newsData = [
            'title' => 'This is a Test Title with Special Characters!',
            'content' => 'This is comprehensive test content for slug generation.',
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        $this->assertDatabaseHas('news', [
            'title' => 'This is a Test Title with Special Characters!',
            'slug' => 'this-is-a-test-title-with-special-characters',
        ]);
    }

    /** @test */
    public function it_sets_published_at_when_status_is_published()
    {
        $newsData = [
            'title' => 'Published News',
            'content' => 'This is comprehensive test content for published news.',
            'status' => 'published',
            'meta_description' => 'This is a comprehensive meta description required for published articles that meets validation requirements.',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        $news = News::where('title', 'Published News')->first();
        $this->assertNotNull($news->published_at);
    }

    /** @test */
    public function it_requires_authentication_for_all_operations()
    {
        $news = News::factory()->create();

        // Test all routes require authentication
        $this->get(route('admin.news.index'))->assertRedirect('/login');
        $this->get(route('admin.news.create'))->assertRedirect('/login');
        $this->post(route('admin.news.store'), [])->assertRedirect('/login');
        $this->get(route('admin.news.show', $news))->assertRedirect('/login');
        $this->get(route('admin.news.edit', $news))->assertRedirect('/login');
        $this->put(route('admin.news.update', $news), [])->assertRedirect('/login');
        $this->delete(route('admin.news.destroy', $news))->assertRedirect('/login');
    }

    /** @test */
    public function it_requires_admin_role_for_all_operations()
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $news = News::factory()->create();

        // Test all routes require admin role
        $this->actingAs($regularUser)->get(route('admin.news.index'))->assertStatus(403);
        $this->actingAs($regularUser)->get(route('admin.news.create'))->assertStatus(403);
        $this->actingAs($regularUser)->post(route('admin.news.store'), [])->assertStatus(403);
        $this->actingAs($regularUser)->get(route('admin.news.show', $news))->assertStatus(403);
        $this->actingAs($regularUser)->get(route('admin.news.edit', $news))->assertStatus(403);
        $this->actingAs($regularUser)->put(route('admin.news.update', $news), [])->assertStatus(403);
        $this->actingAs($regularUser)->delete(route('admin.news.destroy', $news))->assertStatus(403);
    }
}
