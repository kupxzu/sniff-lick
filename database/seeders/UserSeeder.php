<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@slvc.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create test user (also admin)
        User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create demo user (client)
        User::create([
            'name' => 'Demo User',
            'username' => 'demo',
            'email' => 'demo@slvc.com',
            'password' => Hash::make('demo123'),
            'email_verified_at' => now(),
            'role' => 'client',
        ]);

        // Create developer user (client)
        User::create([
            'name' => 'Developer',
            'username' => 'dev',
            'email' => 'dev@slvc.com',
            'password' => Hash::make('dev123'),
            'email_verified_at' => now(),
            'role' => 'client',
        ]);

        // Create manager user (client)
        User::create([
            'name' => 'Manager User',
            'username' => 'manager',
            'email' => 'manager@slvc.com',
            'password' => Hash::make('manager123'),
            'email_verified_at' => now(),
            'role' => 'client',
        ]);

        // Create additional random users using factory (all clients)
        User::factory(15)->create([
            'role' => 'client',
        ]);

        $this->command->info('Users seeded successfully!');
        $this->command->info('✅ 2 Admin users created');
        $this->command->info('✅ 18 Client users created');
    }
}
