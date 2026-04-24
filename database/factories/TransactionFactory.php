<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'sales_number' => 'SALE-'.$this->faker->unique()->numberBetween(1000, 9999),
            'brand' => $this->faker->company,
            'payment_amount' => $this->faker->randomFloat(2, 10, 1000),
            'sales_date_in' => now(),
            'sales_date_out' => now(),
            'mdr' => 0.00,
            'nett_after_mdr' => 0.00,
            'status' => 'completed',
        ];
    }
}
