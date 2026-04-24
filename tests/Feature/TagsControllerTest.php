<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::factory()->create();
    }

    public function test_index_displays_tags()
    {
        $user = $this->createUser();

        Tag::create([
            'name' => 'VIP',
            'slug' => 'vip',
            'color' => '#ff0000',
        ]);

        $response = $this->actingAs($user)->get('/tags');

        $response->assertStatus(200);
        $response->assertViewIs('tags.index');
        $response->assertSee('VIP');
    }

    public function test_store_creates_tag()
    {
        $user = $this->createUser();

        $data = [
            'name' => 'Important',
            'color' => '#00ff00',
        ];

        $response = $this->actingAs($user)->post('/tags', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'name' => 'Important',
            'slug' => 'important',
            'color' => '#00ff00',
        ]);
    }

    public function test_destroy_deletes_tag()
    {
        $user = $this->createUser();

        $tag = Tag::create([
            'name' => 'Delete Me',
            'slug' => 'delete-me',
            'color' => '#0000ff',
        ]);

        $response = $this->actingAs($user)->delete('/tags/'.$tag->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_attach_adds_tag_to_model()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();

        $tag = Tag::create([
            'name' => 'VIP',
            'slug' => 'vip',
            'color' => '#ff0000',
        ]);

        $response = $this->actingAs($user)->post('/tags/attach', [
            'tag_id' => $tag->id,
            'taggable_type' => 'App\\Models\\Customer',
            'taggable_id' => $customer->id,
        ]);

        $response->assertRedirect();
        $this->assertTrue($customer->tags->contains($tag->id));
    }

    public function test_detach_removes_tag_from_model()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();

        $tag = Tag::create([
            'name' => 'VIP',
            'slug' => 'vip',
            'color' => '#ff0000',
        ]);

        $customer->tags()->attach($tag->id);
        $this->assertTrue($customer->fresh()->tags->contains($tag->id));

        $response = $this->actingAs($user)->post('/tags/detach', [
            'tag_id' => $tag->id,
            'taggable_type' => 'App\\Models\\Customer',
            'taggable_id' => $customer->id,
        ]);

        $response->assertRedirect();
        $this->assertFalse($customer->fresh()->tags->contains($tag->id));
    }
}
