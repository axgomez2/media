<?php

namespace Tests\Feature\Admin;

use App\Models\NewsTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTopicManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user and authenticate
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);
    }

    /** @test */
    public function it_can_display_topics_index_page()
    {
        $topic = NewsTopic::factory()->create([
            'name' => 'Test Topic',
            'color' => '#FF0000',
            'is_active' => true
        ]);

        $response = $this->get(route('admin.news-topics.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Topic');
        $response->assertSee('Tópicos de Notícias');
    }

    /** @test */
    public function it_can_display_create_topic_form()
    {
        $response = $this->get(route('admin.news-topics.create'));

        $response->assertStatus(200);
        $response->assertSee('Novo Tópico');
        $response->assertSee('Nome do Tópico');
    }

    /** @test */
    public function it_can_create_a_new_topic()
    {
        $topicData = [
            'name' => 'New Test Topic',
            'description' => 'Test description',
            'color' => '#00FF00',
            'is_active' => true
        ];

        $response = $this->post(route('admin.news-topics.store'), $topicData);

        $response->assertRedirect(route('admin.news-topics.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('news_topics', [
            'name' => 'New Test Topic',
            'color' => '#00FF00',
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_can_display_edit_topic_form()
    {
        $topic = NewsTopic::factory()->create(['name' => 'Edit Test Topic']);

        $response = $this->get(route('admin.news-topics.edit', $topic));

        $response->assertStatus(200);
        $response->assertSee('Editar Tópico');
        $response->assertSee('Edit Test Topic');
    }

    /** @test */
    public function it_can_update_a_topic()
    {
        $topic = NewsTopic::factory()->create([
            'name' => 'Original Name',
            'color' => '#FF0000'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'color' => '#0000FF',
            'is_active' => '0'  // Send as string like form would
        ];

        $response = $this->put(route('admin.news-topics.update', $topic), $updateData);



        $response->assertRedirect(route('admin.news-topics.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('news_topics', [
            'id' => $topic->id,
            'name' => 'Updated Name',
            'color' => '#0000FF',
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_delete_a_topic()
    {
        $topic = NewsTopic::factory()->create(['name' => 'Delete Test Topic']);

        $response = $this->delete(route('admin.news-topics.destroy', $topic));

        $response->assertRedirect(route('admin.news-topics.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('news_topics', [
            'id' => $topic->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_topic()
    {
        $response = $this->post(route('admin.news-topics.store'), []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_auto_generates_slug_from_name()
    {
        $topicData = [
            'name' => 'Test Topic With Spaces',
            'is_active' => true
        ];

        $this->post(route('admin.news-topics.store'), $topicData);

        $this->assertDatabaseHas('news_topics', [
            'name' => 'Test Topic With Spaces',
            'slug' => 'test-topic-with-spaces'
        ]);
    }
}
