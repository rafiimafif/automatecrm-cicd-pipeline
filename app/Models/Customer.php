<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model // Conventionally, model names are singular
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fname',
        'lname',
        'company',
        'address',
        'phone',
        'email',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($customer) {
            if (! $customer->isForceDeleting()) {
                return;
            }
            $customer->servicetocustomer()->delete();
        });
    }

    public function services()
    {
        return $this->hasMany(Service::class); // Assuming the foreign key is customer_id
    }

    public function payments()
    {
        return $this->hasMany(Payment::class); // Assuming the foreign key is customer_id
    }

    public function servicetocustomer()
    {
        return $this->hasMany(ServicetoCustomer::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function getFullNameAttribute()
    {
        return "{$this->fname} {$this->lname}";
    }
}
