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

    public function test_customer_relationships()
    {
        $customer = new Customer();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->services());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->payments());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->servicetocustomer());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $customer->tags());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $customer->notes());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->deals());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $customer->tasks());
    }

    public function test_deal_relationships()
    {
        $deal = new Deal();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $deal->customer());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $deal->stage());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $deal->assignee());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $deal->tags());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $deal->notes());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $deal->tasks());
    }

    public function test_task_relationships()
    {
        $task = new Task();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $task->taskable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $task->assignee());
    }
}
