<?php

namespace Database\Seeders\V1;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('users')->count() === 0) {
            User::create([
                'name' => 'Example User',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
