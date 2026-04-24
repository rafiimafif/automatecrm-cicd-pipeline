<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_view()
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/password/confirm');

        $response->assertStatus(200);
    }

    public function test_confirm_password_logic()
    {
        $this->withoutVite();
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->post('/password/confirm', [
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_confirm_password_fails_with_wrong_password()
    {
        $this->withoutVite();
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->post('/password/confirm', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
