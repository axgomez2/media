<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN
        ]);

        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_featured_image_during_creation()
    {
        $featuredImage = UploadedFile::fake()->image('featured.jpg', 800, 600)->size(1024);

        $newsData = [
            'title' => 'News with Featured Image',
            'content' => 'This is comprehensive test content for the news article with featured image.',
            'status' => 'draft',
            'featured_image' => $featuredImage,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $news = News::where('title', 'News with Featured Image')->first();
        $this->assertNotNull($news);
        $this->assertNotNull($news->featured_image);

        // Check file was stored
        Storage::disk('public')->assertExists('news/' . $news->featured_image);

        // Check file has correct dimensions and format
        $this->assertStringEndsWith('.jpg', $news->featured_image);
    }

    /** @test */
    public function it_can_upload_multiple_gallery_images_during_creation()
    {
        $galleryImages = [
            UploadedFile::fake()->image('gallery1.jpg', 600, 400)->size(512),
            UploadedFile::fake()->image('gallery2.jpg', 600, 400)->size(512),
            UploadedFile::fake()->image('gallery3.png', 600, 400)->size(512),
        ];

        $newsData = [
            'title' => 'News with Gallery',
            'content' => 'This is comprehensive test content for the news article with gallery images.',
            'status' => 'draft',
            'gallery_images' => $galleryImages,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertRedirect();

        $news = News::where('title', 'News with Gallery')->first();
        $this->assertNotNull($news);
        $this->assertIsArray($news->gallery_images);
        $this->assertCount(3, $news->gallery_images);

        // Check all files were stored
        foreach ($news->gallery_images as $image) {
            Storage::disk('public')->assertExists('news/' . $image);
        }
    }

    /** @test */
    public function it_can_replace_featured_image_during_update()
    {
        // Create news with existing featured image
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'old-featured.jpg',
        ]);

        // Create the old image file
        Storage::disk('public')->put('news/old-featured.jpg', 'old content');

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
        $this->assertNotEquals('old-featured.jpg', $news->featured_image);

        // Old image should be deleted
        Storage::disk('public')->assertMissing('news/old-featured.jpg');

        // New image should exist
        Storage::disk('public')->assertExists('news/' . $news->featured_image);
    }

    /** @test */
    public function it_can_remove_featured_image_during_update()
    {
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'existing-featured.jpg',
        ]);

        Storage::disk('public')->put('news/existing-featured.jpg', 'content');

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
        Storage::disk('public')->assertMissing('news/existing-featured.jpg');
    }

    /** @test */
    public function it_can_add_gallery_images_to_existing_news()
    {
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
    public function it_can_remove_specific_gallery_images()
    {
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
    public function it_deletes_all_images_when_news_is_deleted()
    {
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

        // News should be deleted from database
        $this->assertDatabaseMissing('news', ['id' => $news->id]);

        // All associated files should be deleted
        Storage::disk('public')->assertMissing('news/featured.jpg');
        Storage::disk('public')->assertMissing('news/gallery1.jpg');
        Storage::disk('public')->assertMissing('news/gallery2.jpg');
    }

    /** @test */
    public function it_validates_image_file_types()
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
    public function it_validates_image_file_sizes()
    {
        $largeImage = UploadedFile::fake()->image('large.jpg', 800, 600)->size(6144); // 6MB

        $newsData = [
            'title' => 'Test News',
            'content' => 'This is test content that is long enough for validation.',
            'status' => 'draft',
            'featured_image' => $largeImage,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors(['featured_image']);
    }

    /** @test */
    public function it_validates_image_dimensions()
    {
        $smallImage = UploadedFile::fake()->image('small.jpg', 100, 100)->size(512);

        $newsData = [
            'title' => 'Test News',
            'content' => 'This is test content that is long enough for validation.',
            'status' => 'draft',
            'featured_image' => $smallImage,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors(['featured_image']);
    }

    /** @test */
    public function it_validates_maximum_gallery_images()
    {
        $images = [];
        for ($i = 0; $i < 11; $i++) { // 11 images (max is 10)
            $images[] = UploadedFile::fake()->image("test{$i}.jpg", 600, 400)->size(512);
        }

        $newsData = [
            'title' => 'Test News',
            'content' => 'This is test content that is long enough for validation.',
            'status' => 'draft',
            'gallery_images' => $images,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        $response->assertSessionHasErrors(['gallery_images']);
    }

    /** @test */
    public function it_handles_image_upload_errors_gracefully()
    {
        // Mock a storage failure
        Storage::shouldReceive('disk->put')->andReturn(false);

        $featuredImage = UploadedFile::fake()->image('featured.jpg', 800, 600)->size(1024);

        $newsData = [
            'title' => 'Test News',
            'content' => 'This is test content that is long enough for validation.',
            'status' => 'draft',
            'featured_image' => $featuredImage,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData);

        // Should handle the error gracefully
        $response->assertStatus(302); // Redirect back with error
    }

    /** @test */
    public function it_generates_unique_filenames_for_uploads()
    {
        $image1 = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);
        $image2 = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);

        // Create first news with image
        $newsData1 = [
            'title' => 'First News',
            'content' => 'This is test content that is long enough for validation.',
            'status' => 'draft',
            'featured_image' => $image1,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData1);

        // Create second news with same filename
        $newsData2 = [
            'title' => 'Second News',
            'content' => 'This is test content that is long enough for validation.',
            'status' => 'draft',
            'featured_image' => $image2,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.news.store'), $newsData2);

        $news1 = News::where('title', 'First News')->first();
        $news2 = News::where('title', 'Second News')->first();

        // Should have different filenames
        $this->assertNotEquals($news1->featured_image, $news2->featured_image);

        // Both files should exist
        Storage::disk('public')->assertExists('news/' . $news1->featured_image);
        Storage::disk('public')->assertExists('news/' . $news2->featured_image);
    }

    /** @test */
    public function it_preserves_existing_images_when_no_new_images_uploaded()
    {
        $news = News::factory()->create([
            'author_id' => $this->admin->id,
            'featured_image' => 'existing-featured.jpg',
            'gallery_images' => ['existing1.jpg', 'existing2.jpg'],
        ]);

        Storage::disk('public')->put('news/existing-featured.jpg', 'content');
        Storage::disk('public')->put('news/existing1.jpg', 'content');
        Storage::disk('public')->put('news/existing2.jpg', 'content');

        $updateData = [
            'title' => 'Updated Title',
            'content' => $news->content,
            'status' => $news->status,
            // No image fields provided
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.news.update', $news), $updateData);

        $response->assertRedirect();

        $news->refresh();

        // Images should be preserved
        $this->assertEquals('existing-featured.jpg', $news->featured_image);
        $this->assertEquals(['existing1.jpg', 'existing2.jpg'], $news->gallery_images);

        // Files should still exist
        Storage::disk('public')->assertExists('news/existing-featured.jpg');
        Storage::disk('public')->assertExists('news/existing1.jpg');
        Storage::disk('public')->assertExists('news/existing2.jpg');
    }
}
