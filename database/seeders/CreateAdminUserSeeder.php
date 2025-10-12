<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing admins
        \App\Models\User::where('is_admin', true)->delete();

        // Create the requested admin
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'cjnr598@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Admin123!'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
