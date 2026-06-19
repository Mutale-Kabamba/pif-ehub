<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super User
        User::create([
            'name' => 'Super User',
            'email' => 'super@pif.zm',
            'password' => Hash::make('PIF_Admin_2026'),
            'role' => 'super',
            'panelist_name' => null,
        ]);

        // Panelists
        $panelists = [
            [
                'name' => 'Rabecca',
                'email' => 'rabecca@pif.zm',
                'password' => Hash::make('PIF_Rab_2026'),
                'panelist_name' => 'Rabecca',
            ],
            [
                'name' => 'Sarah',
                'email' => 'sarah@pif.zm',
                'password' => Hash::make('PIF_Sar_2026'),
                'panelist_name' => 'Sarah',
            ],
            [
                'name' => 'Bracious',
                'email' => 'bracious@pif.zm',
                'password' => Hash::make('PIF_Bra_2026'),
                'panelist_name' => 'Bracious',
            ],
            [
                'name' => 'Mutale',
                'email' => 'mutale@pif.zm',
                'password' => Hash::make('PIF_Mut_2026'),
                'panelist_name' => 'Mutale',
            ],
            [
                'name' => 'Blessing',
                'email' => 'blessing@pif.zm',
                'password' => Hash::make('PIF_Ble_2026'),
                'panelist_name' => 'Blessing',
            ],
        ];

        foreach ($panelists as $panelist) {
            User::create([
                'name' => $panelist['name'],
                'email' => $panelist['email'],
                'password' => $panelist['password'],
                'role' => 'panelist',
                'panelist_name' => $panelist['panelist_name'],
            ]);
        }
    }
}
