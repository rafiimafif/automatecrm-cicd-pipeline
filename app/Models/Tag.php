<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'slug'];

    protected static function booted()
    {
        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function customers()
    {
        return $this->morphedByMany(Customer::class, 'taggable');
    }

    public function deals()
    {
        return $this->morphedByMany(Deal::class, 'taggable');
    }
}
