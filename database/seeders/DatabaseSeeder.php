<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin User manually since UserFactory might not be complete
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // Seed some basic services if they don't exist
        $services = ['Website Hosting', 'Email Hosting', 'Domain', 'Web Development'];
        foreach ($services as $serviceName) {
            Service::firstOrCreate(['name' => $serviceName]);
        }

        echo "[seeder] Seeded admin user and default services.\n";

        $this->call(DealStageSeeder::class);
    }
}
