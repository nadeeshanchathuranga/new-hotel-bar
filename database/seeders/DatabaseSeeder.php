<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!\App\Models\User::where('email', 'admin@admin.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'role_type' => 'Admin',   // ⬅️ lowercase
                'password' => Hash::make('Tukadmin@2025'),
            ]);
        }

        if (!\App\Models\User::where('email', 'manager@manager.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'manager',
                'email' => 'manager@manager.com',
                'role_type' => 'Manager', // ⬅️ lowercase
                'password' => Hash::make('Tukmanager@2025'),
            ]);
        }

        if (!\App\Models\User::where('email', 't1@cashier.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'cashier',
                'email' => 't1@cashier.com',
                'role_type' => 'Cashier', // ⬅️ lowercase
                'password' => Hash::make('Tukcashier@2025'),
            ]);
        }

        if (!\App\Models\User::where('email', 'owner1@manager.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'Ayodhya',
                'email' => 'owner1@manager.com',
                'role_type' => 'Owner',   // ⬅️ lowercase
                'password' => Hash::make('ayodhya@tuk2025'),
            ]);
        }

        if (!\App\Models\User::where('email', 'owner2@manager.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'Kasun',
                'email' => 'owner2@manager.com',
                'role_type' => 'Owner',   // ⬅️ lowercase
                'password' => Hash::make('Kasun@tuk2025'),
            ]);
        }

        if (!\App\Models\User::where('email', 'owner3@manager.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'Milinda ',
                'email' => 'owner3@manager.com',
                'role_type' => 'Owner',   // ⬅️ lowercase
                'password' => Hash::make('Milinda@tuk2025'),
            ]);
        }

        if (!\App\Models\User::where('email', 'owner4@manager.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'Buddika',
                'email' => 'owner4@manager.com',
                'role_type' => 'Owner',   // ⬅️ lowercase
                'password' => Hash::make('Buddika@tuk2025'),
            ]);
        }
    }
}
