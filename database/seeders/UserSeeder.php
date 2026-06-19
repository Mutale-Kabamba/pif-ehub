<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super User
        // Note: Hash::make() omitted because your User model has 'password' => 'hashed' in $casts
        User::updateOrCreate(
            ['email' => 'super@pif.zm'],
            [
                'name' => 'Mutale',
                'password' => 'PIF_Admin_2026', 
                'role' => 'super',
                'panelist_name' => 'Mutale',
            ]
        );

        // 2. Panelists Array
        $panelists = [
            [
                'name' => 'Mwiinga',
                'email' => 'mwiinga@pif.zm',
                'password' => 'PIF_Mwi_2026',
                'panelist_name' => 'Mwiinga',
            ],
            [
                'name' => 'Sarah',
                'email' => 'sarah@pif.zm',
                'password' => 'PIF_Sar_2026',
                'panelist_name' => 'Sarah',
            ],
            [
                'name' => 'Bracious',
                'email' => 'bracious@pif.zm',
                'password' => 'PIF_Bra_2026',
                'panelist_name' => 'Bracious',
            ],
            [
                'name' => 'Blessing',
                'email' => 'blessing@pif.zm',
                'password' => 'PIF_Ble_2026',
                'panelist_name' => 'Blessing',
            ],
            [
                'name' => 'Jacqueline',
                'email' => 'jacqueline@pif.zm',
                'password' => 'PIF_Jac_2026',
                'panelist_name' => 'Jacqueline',
            ],
            [
                'name' => 'Florence',
                'email' => 'florence@pif.zm',
                'password' => 'PIF_Flo_2026',
                'panelist_name' => 'Florence',
            ],
        ];

        // 3. Loop and use updateOrCreate to prevent unique constraint crashes
        foreach ($panelists as $panelist) {
            User::updateOrCreate(
                ['email' => $panelist['email']], // Check if this email exists
                [
                    'name' => $panelist['name'],
                    'password' => $panelist['password'],
                    'role' => 'panelist',
                    'panelist_name' => $panelist['panelist_name'],
                ]
            );
        }
    }
}