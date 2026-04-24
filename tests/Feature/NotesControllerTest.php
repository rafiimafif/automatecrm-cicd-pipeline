<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::factory()->create();
    }

    public function test_store_creates_note_for_customer()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();

        $data = [
            'notable_type' => 'App\\Models\\Customer',
            'notable_id' => $customer->id,
            'type' => 'call',
            'content' => 'Discussed new requirements',
        ];

        $response = $this->actingAs($user)->post('/notes', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('notes', [
            'notable_type' => 'App\\Models\\Customer',
            'notable_id' => $customer->id,
            'type' => 'call',
            'content' => 'Discussed new requirements',
            'user_id' => $user->id,
        ]);
    }

    public function test_destroy_deletes_note()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();

        $note = Note::create([
            'notable_type' => 'App\\Models\\Customer',
            'notable_id' => $customer->id,
            'type' => 'meeting',
            'content' => 'Delete me',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete('/notes/'.$note->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }
}
