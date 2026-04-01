<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sales_date_in' => 'datetime',
        'sales_date_out' => 'datetime',
        'mdr' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'nett_after_mdr' => 'decimal:2',
    ];
}
