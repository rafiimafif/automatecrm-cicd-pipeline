<?php

namespace Tests\Feature;

use App\Mail\ExpirationReminder;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicetoCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ServiceExpirationReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_sent_for_30_days_expiration()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'test@example.com']);
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->addDays(30)->format('Y-m-d'),
            'reminder' => 1
        ]);

        $this->artisan('auto:ServiceExpirationReminder')->assertExitCode(0);

        Mail::assertSent(ExpirationReminder::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_reminder_sent_for_15_days_expiration()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'test15@example.com']);
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->addDays(15)->format('Y-m-d'),
            'reminder' => 1
        ]);

        $this->artisan('auto:ServiceExpirationReminder')->assertExitCode(0);

        Mail::assertSent(ExpirationReminder::class, function ($mail) {
            return $mail->hasTo('test15@example.com');
        });
    }

    public function test_reminder_sent_for_5_days_expiration()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'test5@example.com']);
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->addDays(5)->format('Y-m-d'),
            'reminder' => 1
        ]);

        $this->artisan('auto:ServiceExpirationReminder')->assertExitCode(0);

        Mail::assertSent(ExpirationReminder::class, function ($mail) {
            return $mail->hasTo('test5@example.com');
        });
    }

    public function test_reminder_sent_for_today_expiration()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'test0@example.com']);
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->format('Y-m-d'),
            'reminder' => 1
        ]);

        $this->artisan('auto:ServiceExpirationReminder')->assertExitCode(0);

        Mail::assertSent(ExpirationReminder::class, function ($mail) {
            return $mail->hasTo('test0@example.com');
        });
    }

    public function test_reminder_not_sent_if_reminder_disabled()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'disabled@example.com']);
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->addDays(30)->format('Y-m-d'),
            'reminder' => 0 // disabled
        ]);

        $this->artisan('auto:ServiceExpirationReminder')->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
