<?php

namespace Tests\Feature;

use App\Providers\BroadcastServiceProvider;
use Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_broadcast_service_provider_boots()
    {
        $provider = new BroadcastServiceProvider($this->app);
        $provider->boot();

        // If no exception is thrown, we consider it a success for basic coverage
        $this->assertTrue(true);
    }
}
