<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsResponsivenessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN
        ]);
    }

    /** @test */
    public function it_includes_responsive_css_classes_in_index_view()
    {
        News::factory()->count(3)->create(['author_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for responsive grid classes
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4', false);

        // Check for responsive statistics cards
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6', false);

        // Check for responsive filter bar
        $response->assertSee('space-y-4 md:space-y-0 md:flex', false);
        $response->assertSee('w-full md:w-auto', false);

        // Check for responsive pagination
        $response->assertSee('sm:hidden', false);
        $response->assertSee('hidden sm:flex-1', false);
    }

    /** @test */
    public function it_includes_responsive_css_classes_in_create_view()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);

        // Check for responsive form layout
        $response->assertSee('lg:grid-cols-3', false);
        $response->assertSee('lg:col-span-2', false);

        // Check for responsive form elements
        $response->assertSee('sm:text-sm', false);
        $response->assertSee('block w-full', false);
    }

    /** @test */
    public function it_includes_responsive_css_classes_in_edit_view()
    {
        $news = News::factory()->create(['author_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.edit', $news));

        $response->assertStatus(200);

        // Check for responsive form layout
        $response->assertSee('lg:grid-cols-3', false);
        $response->assertSee('lg:col-span-2', false);
    }

    /** @test */
    public function it_includes_responsive_css_classes_in_show_view()
    {
        $news = News::factory()->create(['author_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.show', $news));

        $response->assertStatus(200);

        // Check for responsive layout
        $response->assertSee('lg:grid-cols-3', false);
        $response->assertSee('lg:col-span-2', false);

        // Check for responsive action buttons
        $response->assertSee('sm:flex-row', false);
        $response->assertSee('sm:space-x-3', false);
    }

    /** @test */
    public function it_includes_mobile_friendly_navigation()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for mobile menu elements
        $response->assertSee('ml-64', false); // Desktop sidebar margin
        $response->assertSee('pt-16', false); // Top padding for fixed header
    }

    /** @test */
    public function it_includes_interactive_javascript_elements()
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

        // Check for delete modal
        $response->assertSee('id="deleteModal"', false);

        // Check for JavaScript module import
        $response->assertSee('news-interactive.js', false);
    }

    /** @test */
    public function it_includes_interactive_elements_in_create_form()
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

        // Check for AI content generation buttons
        $response->assertSee('data-ai-type="title"', false);
        $response->assertSee('data-ai-type="excerpt"', false);
        $response->assertSee('data-ai-type="content"', false);
    }

    /** @test */
    public function it_includes_interactive_elements_in_edit_form()
    {
        $news = News::factory()->create(['author_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.edit', $news));

        $response->assertStatus(200);

        // Check for drop zones
        $response->assertSee('drop-zone', false);

        // Check for multi-select topics
        $response->assertSee('id="topics-multiselect"', false);

        // Check for AI content generation buttons
        $response->assertSee('data-ai-type="title"', false);
    }

    /** @test */
    public function it_provides_proper_csrf_protection()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);

        // Check for CSRF token
        $response->assertSee('name="_token"', false);
        $response->assertSee('csrf-token', false);
    }

    /** @test */
    public function it_includes_accessibility_attributes()
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
    }

    /** @test */
    public function it_includes_loading_states_for_interactive_elements()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for loading indicators
        $response->assertSee('animate-spin', false);
        $response->assertSee('style="display: none;"', false);
    }

    /** @test */
    public function it_handles_touch_friendly_interactions()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for touch-friendly button sizes
        $response->assertSee('px-4 py-2', false);
        $response->assertSee('px-3 py-1.5', false);

        // Check for proper spacing
        $response->assertSee('space-x-2', false);
        $response->assertSee('space-y-2', false);
    }

    /** @test */
    public function it_includes_proper_meta_viewport_for_mobile()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // The viewport meta tag should be in the layout
        // This would typically be checked in the layout test
        $response->assertSee('viewport', false);
    }

    /** @test */
    public function it_provides_keyboard_navigation_support()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for focus states
        $response->assertSee('focus:outline-none', false);
        $response->assertSee('focus:ring-', false);
        $response->assertSee('focus:border-', false);

        // Check for tabindex attributes where needed
        $response->assertSee('tabindex=', false);
    }

    /** @test */
    public function it_includes_proper_color_contrast_classes()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for proper text contrast classes
        $response->assertSee('text-gray-900', false);
        $response->assertSee('text-gray-600', false);
        $response->assertSee('text-white', false);

        // Check for proper background contrast
        $response->assertSee('bg-white', false);
        $response->assertSee('bg-blue-600', false);
        $response->assertSee('bg-red-600', false);
    }

    /** @test */
    public function it_handles_different_screen_sizes_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);

        // Check for breakpoint-specific classes
        $response->assertSee('sm:', false);
        $response->assertSee('md:', false);
        $response->assertSee('lg:', false);
        $response->assertSee('xl:', false);

        // Check for responsive utilities
        $response->assertSee('hidden sm:block', false);
        $response->assertSee('block sm:hidden', false);
    }

    /** @test */
    public function it_includes_proper_form_validation_feedback()
    {
        // Test with validation errors
        $response = $this->actingAs($this->admin)
            ->post(route('admin.news.store'), []);

        $response->assertSessionHasErrors();

        // Check that error display is responsive
        $response = $this->actingAs($this->admin)
            ->get(route('admin.news.create'));

        $response->assertStatus(200);

        // Check for error styling classes
        $response->assertSee('text-red-600', false);
        $response->assertSee('border-red-300', false);
    }
}
