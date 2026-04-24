<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_relationships()
    {
        $service = Service::create(['name' => 'Web', 'base_price' => 10, 'description' => 'D']);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $service->servicetocustomers());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $service->customer());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $service->payments());
    }

    public function test_tag_relationships()
    {
        $tag = Tag::create(['name' => 'Urgent']);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $tag->customers());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $tag->deals());
    }
}
