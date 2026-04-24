<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'taskable_id' => Customer::factory(),
            'taskable_type' => Customer::class,
        ];
    }
}
