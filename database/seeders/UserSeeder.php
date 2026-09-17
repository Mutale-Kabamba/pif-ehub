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
        // Remove stale users from old seeders (keep only the ones we define below)
        User::whereNotIn('email', [
            'super@pif.zm', 'mwiinga@pif.zm', 'sarah@pif.zm', 'bracious@pif.zm',
            'blessing@pif.zm', 'jacqueline@pif.zm', 'florence@pif.zm',
        ])->delete();

        // 1. Super User — Mutale is also a Panel B judge
        User::updateOrCreate(
            ['email' => 'super@pif.zm'],
            [
                'name'          => 'Mutale',
                'password'      => 'PIF_Admin_2026',
                'role'          => 'super',
                'panelist_name' => 'Mutale',
                'panel'         => 'B',
            ]
        );

        // 2. Panelists Array
        // panel A: Blessing, Sara, Bracious
        // panel B: Jacqueline, Florence  (Mutale covered above)
        // cover:   Mwiinga (observer/coordinator)
        $panelists = [
            [
                'name'          => 'Mwiinga',
                'email'         => 'mwiinga@pif.zm',
                'password'      => 'PIF_Mwi_2026',
                'panelist_name' => 'Mwiinga',
                'panel'         => 'cover',
            ],
            [
                'name'          => 'Sarah',
                'email'         => 'sarah@pif.zm',
                'password'      => 'PIF_Sar_2026',
                'panelist_name' => 'Sarah',
                'panel'         => 'A',
            ],
            [
                'name'          => 'Bracious',
                'email'         => 'bracious@pif.zm',
                'password'      => 'PIF_Bra_2026',
                'panelist_name' => 'Bracious',
                'panel'         => 'A',
            ],
            [
                'name'          => 'Blessing',
                'email'         => 'blessing@pif.zm',
                'password'      => 'PIF_Ble_2026',
                'panelist_name' => 'Blessing',
                'panel'         => 'A',
            ],
            [
                'name'          => 'Jacqueline',
                'email'         => 'jacqueline@pif.zm',
                'password'      => 'PIF_Jac_2026',
                'panelist_name' => 'Jacqueline',
                'panel'         => 'B',
            ],
            [
                'name'          => 'Florence',
                'email'         => 'florence@pif.zm',
                'password'      => 'PIF_Flo_2026',
                'panelist_name' => 'Florence',
                'panel'         => 'B',
            ],
        ];

        // 3. Loop and use updateOrCreate to prevent unique constraint crashes
        foreach ($panelists as $panelist) {
            User::updateOrCreate(
                ['email' => $panelist['email']],
                [
                    'name'          => $panelist['name'],
                    'password'      => $panelist['password'],
                    'role'          => 'panelist',
                    'panelist_name' => $panelist['panelist_name'],
                    'panel'         => $panelist['panel'],
                ]
            );
        }
    }
}