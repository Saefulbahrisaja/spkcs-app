<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('users')->insert([
            [
                'id'         => 1,
                'nama'       => 'Admin Sistem',
                'email'      => 'admin@example.com',
                'username'   => 'admin',
                'password'   => Hash::make('password'),
                'role'       => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'nama'       => 'Petugas Dinas',
                'email'      => 'dinas@example.com',
                'username'   => 'dinas',
                'password'   => Hash::make('password'),
                'role'       => 'dinas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'nama'       => 'Penyuluh Lapangan',
                'email'      => 'penyuluh@example.com',
                'username'   => 'penyuluh',
                'password'   => Hash::make('password'),
                'role'       => 'penyuluh',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
