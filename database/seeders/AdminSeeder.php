<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name'      => 'Mohammed Mostafa',
            'email'     => 'admin@email.com',
            'email_verified_at' => now(),
            'password'  => Hash::make('Password5@'),
            'is_admin'  => true,
            'verification_code'  => 'm5m5m5',
            'code_expired_at'  => now()->addMinutes(10),
        ]);
    }
}
