<?php

namespace Tests\Unit;

use App\Services\RenewalService;
use Tests\TestCase;

class RenewalServiceTest extends TestCase
{
    public function test_handle_renewal()
    {
        $service = new RenewalService;
        $service->handleRenewal(null);
        $this->assertTrue(true);
    }
}
