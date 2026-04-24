<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Service;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_relationships()
    {
        $service = Service::create(['name' => 'Web', 'base_price' => 10, 'description' => 'D']);
        $this->assertInstanceOf(HasMany::class, $service->servicetocustomers());
        $this->assertInstanceOf(BelongsTo::class, $service->customer());
        $this->assertInstanceOf(HasManyThrough::class, $service->payments());
    }

    public function test_tag_relationships()
    {
        $tag = Tag::create(['name' => 'Urgent']);
        $this->assertInstanceOf(MorphToMany::class, $tag->customers());
        $this->assertInstanceOf(MorphToMany::class, $tag->deals());
    }

    public function test_customer_relationships()
    {
        $customer = new Customer;
        $this->assertInstanceOf(HasMany::class, $customer->services());
        $this->assertInstanceOf(HasMany::class, $customer->payments());
        $this->assertInstanceOf(HasMany::class, $customer->servicetocustomer());
        $this->assertInstanceOf(MorphToMany::class, $customer->tags());
        $this->assertInstanceOf(MorphMany::class, $customer->notes());
        $this->assertInstanceOf(HasMany::class, $customer->deals());
        $this->assertInstanceOf(MorphMany::class, $customer->tasks());
    }

    public function test_deal_relationships()
    {
        $deal = new Deal;
        $this->assertInstanceOf(BelongsTo::class, $deal->customer());
        $this->assertInstanceOf(BelongsTo::class, $deal->stage());
        $this->assertInstanceOf(BelongsTo::class, $deal->assignee());
        $this->assertInstanceOf(MorphToMany::class, $deal->tags());
        $this->assertInstanceOf(MorphMany::class, $deal->notes());
        $this->assertInstanceOf(MorphMany::class, $deal->tasks());
    }

    public function test_task_relationships()
    {
        $task = new Task;
        $this->assertInstanceOf(MorphTo::class, $task->taskable());
        $this->assertInstanceOf(BelongsTo::class, $task->assignee());
    }
}
