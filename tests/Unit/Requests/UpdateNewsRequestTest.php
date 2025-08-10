<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use App\Models\NewsTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateNewsRequestTest extends TestCase
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
            'title' => 'Updated News Title',
            'excerpt' => 'This is an updated excerpt',
            'content' => 'This is the updated main content of the news article.',
            'status' => 'published',
            'meta_description' => 'Updated meta description',
            'meta_keywords' => 'updated, news, article',
            'topics' => [$topic->id],
            'featured' => true
        ];

        $request = new UpdateNewsRequest();

        // Mock the rules method to avoid route dependency for basic validation
        $rules = $request->rules();

        // Remove slug rule that depends on route
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_fails_validation_when_title_is_missing()
    {
        $data = [
            'content' => 'This is the main content.',
            'status' => 'draft'
        ];

        $request = new UpdateNewsRequest();
        $rules = $request->rules();
        unset($rules['slug']); // Remove slug rule

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_status()
    {
        $data = [
            'title' => 'Test Title',
            'content' => 'This is the main content.',
            'status' => 'invalid_status'
        ];

        $request = new UpdateNewsRequest();
        $rules = $request->rules();
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_remove_featured_image_field()
    {
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
            'status' => 'draft',
            'remove_featured_image' => true
        ];

        $request = new UpdateNewsRequest();
        $rules = $request->rules();
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_remove_gallery_images_field()
    {
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
            'status' => 'draft',
            'remove_gallery_images' => ['image1.jpg', 'image2.jpg']
        ];

        $request = new UpdateNewsRequest();
        $rules = $request->rules();
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_allows_past_published_date_for_updates()
    {
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
            'published_at' => now()->subDay()->format('Y-m-d H:i:s'), // Past date allowed for updates
            'status' => 'published',
            'meta_description' => 'Required for published'
        ];

        $request = new UpdateNewsRequest();
        $rules = $request->rules();
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_has_custom_validation_methods()
    {
        $request = new UpdateNewsRequest();

        // Test that the withValidator method exists
        $this->assertTrue(method_exists($request, 'withValidator'));

        // Test that custom attributes are defined
        $attributes = $request->attributes();
        $this->assertArrayHasKey('title', $attributes);
        $this->assertEquals('título', $attributes['title']);
    }

    /** @test */
    public function it_requires_meta_description_for_published_articles()
    {
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
            'status' => 'published',
            'meta_description' => '' // Empty meta description
        ];

        $request = new UpdateNewsRequest();
        $request->merge($data); // Merge data into request

        $rules = $request->rules();
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);
        $request->withValidator($validator);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('meta_description', $validator->errors()->toArray());
    }

    /** @test */
    public function it_allows_empty_meta_description_for_draft_articles()
    {
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
            'status' => 'draft',
            'meta_description' => '' // Empty meta description is OK for drafts
        ];

        $request = new UpdateNewsRequest();
        $rules = $request->rules();
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);
        $request->withValidator($validator);

        $this->assertTrue($validator->passes());
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

        $request = new UpdateNewsRequest();
        $rules = $request->rules();
        unset($rules['slug']);

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('topics', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_custom_messages_are_in_portuguese()
    {
        $request = new UpdateNewsRequest();
        $messages = $request->messages();

        $this->assertStringContainsString('obrigatório', $messages['title.required']);
        $this->assertStringContainsString('caracteres', $messages['title.min']);
        $this->assertStringContainsString('conteúdo', $messages['content.required']);
    }

    /** @test */
    public function it_prepares_data_correctly_for_validation()
    {
        $request = new UpdateNewsRequest();

        // Mock request data
        $request->merge([
            'featured' => '1',
            'remove_featured_image' => '1',
            'status' => 'published'
        ]);

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertTrue($request->input('featured'));
        $this->assertTrue($request->input('remove_featured_image'));
    }
}
