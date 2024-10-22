<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cliente;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RoleSeeder::class);

        // usuario default
        $user = User::create([
            'document'  => '1067953510',
            'name'      => 'Armando',
            'last_name' => 'Mariaga',
            'user_name' => '1067953510',
            'password'  => bcrypt( '1067953510' ),
            'email'     => 'j.mariaga20@gmail.com',
            'phone'     => '3045613903',
            'picture'   => '',
            'status'    => 1,
        ])->assignRole('SuperAdmin');;

        // cliente default
        Cliente::create([
            'nombre' => 'Cliente general'
        ]);
    }
}
