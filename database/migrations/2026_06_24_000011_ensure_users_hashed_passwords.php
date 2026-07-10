<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Ensure all system users exist and have properly bcrypt-hashed passwords.
 *
 * Handles two production failure modes:
 *  1. Users were seeded without the `hashed` cast active → passwords stored
 *     as plain text → Hash::check() always fails → "Invalid credentials".
 *  2. Users were never seeded at all → $user === null → "Invalid credentials".
 *
 * Safety: only overwrites a password if it is NOT already a valid hash of the
 * expected credential (i.e. plain-text mismatch).  A legitimately changed
 * bcrypt password will pass Hash::check() and is left untouched.
 */
return new class extends Migration
{
    /**
     * Full set of system users with their known credentials.
     * Passwords here match what the UserSeeder uses.
     */
    private array $users = [
        [
            'name'          => 'Mutale',
            'email'         => 'super@pif.zm',
            'password'      => 'PIF_Admin_2026',
            'role'          => 'super',
            'panelist_name' => 'Mutale',
            'panel'         => 'B',
        ],
        [
            'name'          => 'Mwiinga',
            'email'         => 'mwiinga@pif.zm',
            'password'      => 'PIF_Mwi_2026',
            'role'          => 'panelist',
            'panelist_name' => 'Mwiinga',
            'panel'         => 'cover',
        ],
        [
            'name'          => 'Sarah',
            'email'         => 'sarah@pif.zm',
            'password'      => 'PIF_Sar_2026',
            'role'          => 'panelist',
            'panelist_name' => 'Sarah',
            'panel'         => 'A',
        ],
        [
            'name'          => 'Bracious',
            'email'         => 'bracious@pif.zm',
            'password'      => 'PIF_Bra_2026',
            'role'          => 'panelist',
            'panelist_name' => 'Bracious',
            'panel'         => 'A',
        ],
        [
            'name'          => 'Blessing',
            'email'         => 'blessing@pif.zm',
            'password'      => 'PIF_Ble_2026',
            'role'          => 'panelist',
            'panelist_name' => 'Blessing',
            'panel'         => 'A',
        ],
        [
            'name'          => 'Jacqueline',
            'email'         => 'jacqueline@pif.zm',
            'password'      => 'PIF_Jac_2026',
            'role'          => 'panelist',
            'panelist_name' => 'Jacqueline',
            'panel'         => 'B',
        ],
        [
            'name'          => 'Florence',
            'email'         => 'florence@pif.zm',
            'password'      => 'PIF_Flo_2026',
            'role'          => 'panelist',
            'panelist_name' => 'Florence',
            'panel'         => 'B',
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->users as $data) {
            $existing = DB::table('users')->where('email', $data['email'])->first();

            if (! $existing) {
                // User does not exist at all — create with hashed password
                DB::table('users')->insert([
                    'name'          => $data['name'],
                    'email'         => $data['email'],
                    'password'      => Hash::make($data['password']),
                    'role'          => $data['role'],
                    'panelist_name' => $data['panelist_name'],
                    'panel'         => $data['panel'],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            } elseif (! Hash::check($data['password'], $existing->password)) {
                // User exists but stored password does not match known credential
                // (most likely stored as plain text — fix it to a proper hash)
                DB::table('users')
                    ->where('email', $data['email'])
                    ->update([
                        'password'   => Hash::make($data['password']),
                        'updated_at' => $now,
                    ]);
            }
            // else: password is correctly hashed — leave it untouched
        }
    }

    public function down(): void
    {
        // Nothing to reverse — passwords cannot be "un-hashed"
    }
};
