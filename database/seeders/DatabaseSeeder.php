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
        $user = User::create([
            'name'              => 'Satya Wibawa',
            'email'             => 'digiforest.id@gmail.com',
            'password'          => bcrypt('acs22acs'), // secret
            'remember_token'    => Str::random(10),
        ]);
    }
}
