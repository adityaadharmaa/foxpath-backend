<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'roles_id' => 1,
                'username' => "dits",
                'email' => "gedeagusadityadharma@gmail.com",
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'roles_id' => 2,
                'username' => "adityaadharma",
                'email' => "agusadityadharma65@gmail.com",
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
