<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'customer_id',
        'deal_stage_id',
        'value',
        'expected_close_date',
        'assigned_to',
        'status',
        'notes',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'value' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function stage()
    {
        return $this->belongsTo(DealStage::class, 'deal_stage_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }
}
