<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomersControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::factory()->create();
    }

    public function test_index_displays_customers()
    {
        $user = $this->createUser();

        Customer::factory()->create([
            'fname' => 'John',
            'lname' => 'Doe',
        ]);

        $response = $this->actingAs($user)->get('/customers');

        $response->assertStatus(200);
        $response->assertViewIs('customers.index');
        $response->assertSee('John');
        $response->assertSee('Doe');
    }

    public function test_index_filters_customers_by_search_and_tag()
    {
        $user = $this->createUser();

        $customer1 = Customer::factory()->create([
            'fname' => 'Alice',
        ]);

        $customer2 = Customer::factory()->create([
            'fname' => 'Bob',
        ]);

        $tag = Tag::create(['name' => 'VIP', 'slug' => 'vip', 'color' => '#f00']);
        $customer1->tags()->attach($tag->id);

        // Search by name
        $response = $this->actingAs($user)->get('/customers?search=Alice');
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');

        // Filter by tag
        $response = $this->actingAs($user)->get('/customers?tag='.$tag->id);
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');
    }

    public function test_store_creates_customer()
    {
        $user = $this->createUser();

        $data = [
            'fname' => 'Jane',
            'lname' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '123456789',
            'company' => 'Tech Corp',
            'address' => '123 Tech Ave',
        ];

        $response = $this->actingAs($user)->post('/customer_add', $data);

        $response->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', [
            'fname' => 'Jane',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_edit_displays_customer_details()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->get('/customer_edit/'.$customer->id);

        $response->assertStatus(200);
        $response->assertViewIs('customers.single');
        $response->assertSee($customer->fname);
    }

    public function test_update_modifies_customer()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create([
            'fname' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $data = [
            'fname' => 'New Name',
            'lname' => 'Lastname',
            'email' => 'new@example.com',
        ];

        $response = $this->actingAs($user)->put('/customer_update/'.$customer->id, $data);

        $response->assertRedirect(route('customer_edit', ['id' => $customer->id]));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'fname' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_destroy_deletes_customer()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->delete('/customer_delete/'.$customer->id);

        $response->assertRedirect('/customers');
        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }
}
