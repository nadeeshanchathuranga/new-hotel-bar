<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        if (!\App\Models\User::where('email', 'admin@admin.com')->exists()) {
    \App\Models\User::factory()->create([
        'name' => 'admin',
        'email' => 'admin@admin.com',
        'role_type' => 'Admin',
        'password' => Hash::make('Tukadmin@2025'),
    ]);
}

if (!\App\Models\User::where('email', 'manager@manager.com')->exists()) {
    \App\Models\User::factory()->create([
        'name' => 'manager',
        'email' => 'manager@manager.com',
        'role_type' => 'Manager',
        'password' => Hash::make('Tukmanager@2025'),
    ]);
}

if (!\App\Models\User::where('email', 't1@cashier.com')->exists()) {
    \App\Models\User::factory()->create([
        'name' => 'cashier',
        'email' => 't1@cashier.com',
        'role_type' => 'Cashier',
        'password' => Hash::make('Tukcashier@2025'),
    ]);
}

    }
}
