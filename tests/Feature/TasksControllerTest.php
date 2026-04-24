<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasksControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::factory()->create();
    }

    public function test_index_displays_pending_tasks_by_default()
    {
        $user = $this->createUser();

        Task::create([
            'title' => 'Pending Task',
            'status' => 'pending',
            'priority' => 'medium',
            'assigned_to' => $user->id,
        ]);

        Task::create([
            'title' => 'Completed Task',
            'status' => 'completed',
            'priority' => 'medium',
            'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/tasks');

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
        $response->assertSee('Pending Task');
        $response->assertDontSee('Completed Task');
    }

    public function test_index_filters_tasks()
    {
        $user = $this->createUser();

        Task::create([
            'title' => 'Urgent Pending Task',
            'status' => 'pending',
            'priority' => 'urgent',
            'assigned_to' => $user->id,
        ]);

        Task::create([
            'title' => 'Low Pending Task',
            'status' => 'pending',
            'priority' => 'low',
            'assigned_to' => $user->id,
        ]);

        // Filter by priority
        $response = $this->actingAs($user)->get('/tasks?priority=urgent');

        $response->assertStatus(200);
        $response->assertSee('Urgent Pending Task');
        $response->assertDontSee('Low Pending Task');

        // Filter by status completed
        Task::create([
            'title' => 'Completed Task',
            'status' => 'completed',
            'priority' => 'medium',
            'assigned_to' => $user->id,
        ]);
        $response = $this->actingAs($user)->get('/tasks?status=completed');
        $response->assertSee('Completed Task');
        $response->assertDontSee('Urgent Pending Task');

        // Filter by search
        $response = $this->actingAs($user)->get('/tasks?search=Urgent');
        $response->assertSee('Urgent Pending Task');
    }

    public function test_store_creates_task_with_polymorphic_relation()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();

        $data = [
            'title' => 'Call Customer',
            'priority' => 'high',
            'taskable_type' => 'App\\Models\\Customer',
            'taskable_id' => $customer->id,
            'due_date' => now()->addDays(2)->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)->post('/tasks', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Call Customer',
            'priority' => 'high',
            'status' => 'pending',
            'taskable_type' => 'App\\Models\\Customer',
            'taskable_id' => $customer->id,
        ]);
    }

    public function test_update_modifies_task()
    {
        $user = $this->createUser();

        $task = Task::create([
            'title' => 'Old Title',
            'status' => 'pending',
            'priority' => 'medium',
            'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($user)->put('/tasks/'.$task->id, [
            'title' => 'New Title',
            'priority' => 'urgent',
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Title',
            'priority' => 'urgent',
            'status' => 'completed',
        ]);
    }

    public function test_destroy_deletes_task()
    {
        $user = $this->createUser();

        $task = Task::create([
            'title' => 'Delete Me',
            'status' => 'pending',
            'priority' => 'medium',
            'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete('/tasks/'.$task->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
