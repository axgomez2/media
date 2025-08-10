<?php

namespace Tests\Unit\Rules;

use App\Rules\NewsImageValidation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImageValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_passes_validation_with_valid_featured_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        $rule = NewsImageValidation::featured();

        $passes = true;
        $rule->validate('featured_image', $file, function ($message) use (&$passes) {
            $passes = false;
        });

        $this->assertTrue($passes);
    }

    /** @test */
    public function it_passes_validation_with_valid_gallery_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 250, 200);
        $rule = NewsImageValidation::gallery();

        $passes = true;
        $rule->validate('gallery_image', $file, function ($message) use (&$passes) {
            $passes = false;
        });

        $this->assertTrue($passes);
    }

    /** @test */
    public function it_fails_validation_with_non_image_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $rule = new NewsImageValidation();

        $passes = true;
        $errorMessage = '';
        $rule->validate('image', $file, function ($message) use (&$passes, &$errorMessage) {
            $passes = false;
            $errorMessage = $message;
        });

        $this->assertFalse($passes);
        $this->assertStringContainsString('imagem', $errorMessage);
    }

    /** @test */
    public function it_fails_validation_with_invalid_extension()
    {
        $file = UploadedFile::fake()->create('test.gif', 100);
        $rule = new NewsImageValidation();

        $passes = true;
        $errorMessage = '';
        $rule->validate('image', $file, function ($message) use (&$passes, &$errorMessage) {
            $passes = false;
            $errorMessage = $message;
        });

        $this->assertFalse($passes);
        $this->assertStringContainsString('tipo', $errorMessage);
    }

    /** @test */
    public function it_fails_validation_with_oversized_file()
    {
        // Create a file larger than 2MB (2048KB)
        $file = UploadedFile::fake()->create('large.jpg', 3000); // 3MB
        $rule = new NewsImageValidation();

        $passes = true;
        $errorMessage = '';
        $rule->validate('image', $file, function ($message) use (&$passes, &$errorMessage) {
            $passes = false;
            $errorMessage = $message;
        });

        $this->assertFalse($passes);
        $this->assertStringContainsString('exceder', $errorMessage);
    }

    /** @test */
    public function it_fails_validation_with_small_featured_image_dimensions()
    {
        $file = UploadedFile::fake()->image('small.jpg', 200, 150); // Too small for featured
        $rule = NewsImageValidation::featured();

        $passes = true;
        $errorMessage = '';
        $rule->validate('featured_image', $file, function ($message) use (&$passes, &$errorMessage) {
            $passes = false;
            $errorMessage = $message;
        });

        $this->assertFalse($passes);
        $this->assertStringContainsString('300x200', $errorMessage);
    }

    /** @test */
    public function it_fails_validation_with_small_gallery_image_dimensions()
    {
        $file = UploadedFile::fake()->image('small.jpg', 150, 100); // Too small for gallery
        $rule = NewsImageValidation::gallery();

        $passes = true;
        $errorMessage = '';
        $rule->validate('gallery_image', $file, function ($message) use (&$passes, &$errorMessage) {
            $passes = false;
            $errorMessage = $message;
        });

        $this->assertFalse($passes);
        $this->assertStringContainsString('200x150', $errorMessage);
    }

    /** @test */
    public function it_fails_validation_with_poor_aspect_ratio_for_featured_image()
    {
        $file = UploadedFile::fake()->image('square.jpg', 300, 300); // Square image (1:1 ratio)
        $rule = NewsImageValidation::featured();

        $passes = true;
        $errorMessage = '';
        $rule->validate('featured_image', $file, function ($message) use (&$passes, &$errorMessage) {
            $passes = false;
            $errorMessage = $message;
        });

        $this->assertFalse($passes);
        $this->assertStringContainsString('paisagem', $errorMessage);
    }

    /** @test */
    public function it_fails_validation_with_dangerous_filename()
    {
        // Create a temporary image file with dangerous name
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        // Create a simple 1x1 pixel JPEG
        $imageData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A');
        file_put_contents($tempPath, $imageData);

        $file = new UploadedFile($tempPath, 'malicious.php.jpg', 'image/jpeg', null, true);
        $rule = new NewsImageValidation();

        $passes = true;
        $errorMessage = '';
        $rule->validate('image', $file, function ($message) use (&$passes, &$errorMessage) {
            $passes = false;
            $errorMessage = $message;
        });

        $this->assertFalse($passes);
        $this->assertStringContainsString('extensão não permitida', $errorMessage);

        fclose($tempFile);
    }

    /** @test */
    public function it_accepts_all_allowed_image_formats()
    {
        $formats = ['jpg', 'jpeg', 'png', 'webp'];
        $rule = new NewsImageValidation();

        foreach ($formats as $format) {
            $file = UploadedFile::fake()->image("test.{$format}", 300, 200);

            $passes = true;
            $rule->validate('image', $file, function ($message) use (&$passes) {
                $passes = false;
            });

            $this->assertTrue($passes, "Format {$format} should be accepted");
        }
    }

    /** @test */
    public function it_creates_featured_instance_correctly()
    {
        $rule = NewsImageValidation::featured();
        $this->assertInstanceOf(NewsImageValidation::class, $rule);
    }

    /** @test */
    public function it_creates_gallery_instance_correctly()
    {
        $rule = NewsImageValidation::gallery();
        $this->assertInstanceOf(NewsImageValidation::class, $rule);
    }
}
