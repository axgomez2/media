<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreNewsRequest;
use App\Models\NewsTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreNewsRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        $topic = NewsTopic::factory()->create();

        $data = [
            'title' => 'Test News Title',
            'slug' => 'test-news-title',
            'excerpt' => 'This is a test excerpt',
            'content' => 'This is the main content of the news article.',
            'status' => 'draft',
            'meta_description' => 'Test meta description',
            'meta_keywords' => 'test, news, article',
            'topics' => [$topic->id],
            'featured' => false
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_fails_validation_when_title_is_missing()
    {
        $data = [
            'content' => 'This is the main content.',
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_when_title_is_too_short()
    {
        $data = [
            'title' => 'AB', // Only 2 characters
            'content' => 'This is the main content.',
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_when_content_is_missing()
    {
        $data = [
            'title' => 'Test Title',
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_when_content_is_too_short()
    {
        $data = [
            'title' => 'Test Title',
            'content' => 'Short', // Only 5 characters
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_status()
    {
        $data = [
            'title' => 'Test Title',
            'content' => 'This is the main content.',
            'status' => 'invalid_status'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_slug_format()
    {
        $data = [
            'title' => 'Test Title',
            'slug' => 'Invalid Slug With Spaces',
            'content' => 'This is the main content.',
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_duplicate_slug()
    {
        // Create a news with existing slug
        \App\Models\News::factory()->create(['slug' => 'existing-slug']);

        $data = [
            'title' => 'Test Title',
            'slug' => 'existing-slug',
            'content' => 'This is the main content.',
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_too_long_excerpt()
    {
        $data = [
            'title' => 'Test Title',
            'excerpt' => str_repeat('A', 501), // 501 characters
            'content' => 'This is the main content.',
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('excerpt', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_too_long_meta_description()
    {
        $data = [
            'title' => 'Test Title',
            'content' => 'This is the main content.',
            'meta_description' => str_repeat('A', 161), // 161 characters
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('meta_description', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_too_many_topics()
    {
        $topics = NewsTopic::factory()->count(6)->create();

        $data = [
            'title' => 'Test Title',
            'content' => 'This is the main content.',
            'topics' => $topics->pluck('id')->toArray(), // 6 topics (max is 5)
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('topics', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_non_existent_topics()
    {
        $data = [
            'title' => 'Test Title',
            'content' => 'This is the main content.',
            'topics' => [999], // Non-existent topic ID
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('topics.0', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_past_published_date()
    {
        $data = [
            'title' => 'Test Title',
            'content' => 'This is the main content.',
            'published_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('published_at', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_too_many_gallery_images()
    {
        $images = [];
        for ($i = 0; $i < 11; $i++) { // 11 images (max is 10)
            $images[] = UploadedFile::fake()->image("test{$i}.jpg", 300, 200);
        }

        $data = [
            'title' => 'Test Title',
            'content' => 'This is the main content.',
            'gallery_images' => $images,
            'status' => 'draft'
        ];

        $request = new StoreNewsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('gallery_images', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_custom_messages_are_in_portuguese()
    {
        $request = new StoreNewsRequest();
        $messages = $request->messages();

        $this->assertStringContainsString('obrigatório', $messages['title.required']);
        $this->assertStringContainsString('caracteres', $messages['title.min']);
        $this->assertStringContainsString('conteúdo', $messages['content.required']);
    }

    /** @test */
    public function it_prepares_data_correctly_for_validation()
    {
        $request = new StoreNewsRequest();

        // Mock request data
        $request->merge([
            'featured' => '1', // String from form
            'status' => 'published'
        ]);

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertTrue($request->input('featured'));
        $this->assertNotNull($request->input('published_at'));
    }
}
