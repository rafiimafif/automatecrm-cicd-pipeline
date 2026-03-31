<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServicetoCustomerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
    }
}
