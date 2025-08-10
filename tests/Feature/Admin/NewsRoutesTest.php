<?php

use App\Models\User;
use App\Models\News;
use App\Models\NewsTopic;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
});

test('admin can access news index', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.news.index'));
    $response->assertStatus(200);
});

test('admin can access news create form', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.news.create'));
    $response->assertStatus(200);
});

test('admin can access news topics index', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.news-topics.index'));
    $response->assertStatus(200);
});

test('admin can access news topics create form', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.news-topics.create'));
    $response->assertStatus(200);
});

test('guests cannot access news routes', function () {
    $response = $this->get(route('admin.news.index'));
    $response->assertRedirect('/login');
});

test('guests cannot access news topics routes', function () {
    $response = $this->get(route('admin.news-topics.index'));
    $response->assertRedirect('/login');
});
