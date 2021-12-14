<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Default USER
        User::create([
            'name'              => 'Admin',
            'email'             => 'admin@gmail.com',
            'password'          => bcrypt('rahasia'), // secret
            'remember_token'    => Str::random(10),
        ]);
    }
}
