<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rtId = '019dd189-5042-7141-acf0-de0571dc59f6';

        // Insert RT
        DB::table('rt')->updateOrInsert(
            ['id_rt' => $rtId],
            [
                'nama_rt' => 'RT 01 RW 02',
                'alamat' => 'Jl. Merdeka No. 1',
                'created_at' => now(),
            ]
        );

        $users = [
            [
                'id_rt' => $rtId,
                'nama' => 'Pak Ketua',
                'NIK' => '3578010101010001',
                'role' => 'KETUA',
                'phone' => '081111111111',
                'password_hash' => Hash::make('password123'),
            ],
            [
                'id_rt' => $rtId,
                'nama' => 'Pak Bendahara',
                'NIK' => '3578010101010002',
                'role' => 'BENDAHARA',
                'phone' => '082222222222',
                'password_hash' => Hash::make('password123'),
            ],
            [
                'id_rt' => $rtId,
                'nama' => 'Warga Biasa',
                'NIK' => '3578010101010003',
                'role' => 'WARGA',
                'phone' => '083333333333',
                'password_hash' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $user) {
            $exists = DB::table('users')->where('NIK', $user['NIK'])->first();
            if (!$exists) {
                $user['id'] = Str::uuid();
                $user['created_at'] = now();
                DB::table('users')->insert($user);
            }
        }
    }
}
