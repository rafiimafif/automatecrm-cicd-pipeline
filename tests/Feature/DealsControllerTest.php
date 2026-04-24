<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createDealStage($order = 1)
    {
        return DealStage::create([
            'name' => 'Test Stage ' . $order,
            'order' => $order,
            'color' => '#ffffff'
        ]);
    }

    private function createUser()
    {
        return User::factory()->create();
    }

    public function test_index_displays_deals()
    {
        $user = $this->createUser();
        $stage = $this->createDealStage();
        
        Deal::create([
            'title' => 'Test Deal',
            'deal_stage_id' => $stage->id,
            'value' => 1000,
            'status' => 'open',
            'assigned_to' => $user->id
        ]);

        $response = $this->actingAs($user)->get('/deals');

        $response->assertStatus(200);
        $response->assertViewIs('deals.index');
        $response->assertSee('Test Deal');
    }

    public function test_store_creates_new_deal()
    {
        $user = $this->createUser();
        $stage = $this->createDealStage();

        $data = [
            'title' => 'New Deal',
            'deal_stage_id' => $stage->id,
            'value' => 5000,
        ];

        $response = $this->actingAs($user)->post('/deals', $data);

        $response->assertRedirect(route('deals.index'));
        $this->assertDatabaseHas('deals', [
            'title' => 'New Deal',
            'value' => 5000,
            'status' => 'open'
        ]);
    }

    public function test_show_displays_deal_details()
    {
        $user = $this->createUser();
        $stage = $this->createDealStage();
        
        $deal = Deal::create([
            'title' => 'Show Deal',
            'deal_stage_id' => $stage->id,
            'value' => 2000,
            'status' => 'open',
            'assigned_to' => $user->id
        ]);

        $this->withoutExceptionHandling();
        $response = $this->actingAs($user)->get('/deals/' . $deal->id);

        $response->assertStatus(200);
        $response->assertViewIs('deals.show');
        $response->assertSee('Show Deal');
    }

    public function test_update_modifies_deal()
    {
        $user = $this->createUser();
        $stage = $this->createDealStage();
        
        $deal = Deal::create([
            'title' => 'Old Title',
            'deal_stage_id' => $stage->id,
            'value' => 2000,
            'status' => 'open',
            'assigned_to' => $user->id
        ]);

        $response = $this->actingAs($user)->put('/deals/' . $deal->id, [
            'title' => 'New Title',
            'value' => 3000,
            'status' => 'won'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'title' => 'New Title',
            'value' => 3000,
            'status' => 'won'
        ]);
    }

    public function test_update_stage_via_ajax()
    {
        $user = $this->createUser();
        $stage1 = $this->createDealStage(1);
        $stage2 = $this->createDealStage(2);
        
        $deal = Deal::create([
            'title' => 'Ajax Deal',
            'deal_stage_id' => $stage1->id,
            'value' => 2000,
            'status' => 'open',
            'assigned_to' => $user->id
        ]);

        $response = $this->actingAs($user)->patchJson('/deals/' . $deal->id . '/stage', [
            'deal_stage_id' => $stage2->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'deal_stage_id' => $stage2->id
        ]);
    }

    public function test_destroy_deletes_deal()
    {
        $user = $this->createUser();
        $stage = $this->createDealStage();
        
        $deal = Deal::create([
            'title' => 'Delete Deal',
            'deal_stage_id' => $stage->id,
            'value' => 2000,
            'status' => 'open',
            'assigned_to' => $user->id
        ]);

        $response = $this->actingAs($user)->delete('/deals/' . $deal->id);

        $response->assertRedirect(route('deals.index'));
        $this->assertSoftDeleted('deals', [
            'id' => $deal->id
        ]);
    }
}
