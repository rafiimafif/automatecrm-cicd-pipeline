<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'value' => $this->faker->numberBetween(100, 10000),
            'status' => 'open',
            'customer_id' => Customer::factory(),
            'deal_stage_id' => 1,
        ];
    }
}
