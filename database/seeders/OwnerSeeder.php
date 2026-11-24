<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            [
                'email' => env('OWNER_EMAIL', 'owner@coffeeshop.test'),
            ],
            [
                'name' => 'Coffee Shop Owner',
                'password' => Hash::make('12345678'),
            ]
        );

        $owner->assignRole('owner');
    }
}
