<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed all 20 interview candidates (Panel A + Panel B) into production.
 *
 * Safe to run on MySQL production:
 *   - Uses INSERT ... ON DUPLICATE KEY UPDATE (via updateOrInsert) — no truncation.
 *   - Idempotent: running twice changes nothing if rows already exist.
 *   - Does NOT touch panel_scores, so no existing scored data is affected.
 */
return new class extends Migration
{
    private array $panelA = [
        ['name' => 'Diana Mungala',           'gender' => 'Female'],
        ['name' => 'Taonga Ethel Ngoma',       'gender' => 'Female'],
        ['name' => 'Leya',                     'gender' => 'Female'],
        ['name' => 'Fanely Phiri',             'gender' => 'Female'],
        ['name' => 'Belina Tatila',            'gender' => 'Female'],
        ['name' => 'Constance Siabula',        'gender' => 'Female'],
        ['name' => 'Chipo Hansi',             'gender' => 'Female'],
        ['name' => 'Rashid Nchimunya',         'gender' => 'Male'],
        ['name' => 'Emmanuel Chimuka Chifuwe', 'gender' => 'Male'],
        ['name' => 'Robert Chizu',             'gender' => 'Male'],
    ];

    private array $panelB = [
        ['name' => 'Margaret Kampamba Chanda', 'gender' => 'Female'],
        ['name' => 'Natasha Mano Chileshe',    'gender' => 'Female'],
        ['name' => 'Ruth Wauna',               'gender' => 'Female'],
        ['name' => 'Edith Mwiinde Chooma',     'gender' => 'Female'],
        ['name' => 'Saliya Theresa Havupula',  'gender' => 'Female'],
        ['name' => 'Beauty Banji Mwale',       'gender' => 'Female'],
        ['name' => 'Emma Banda',               'gender' => 'Female'],
        ['name' => 'Peter Gabriel Simpyata',   'gender' => 'Male'],
        ['name' => 'Bill Bishops Imonda',      'gender' => 'Male'],
        ['name' => 'Moffat Daka',              'gender' => 'Male'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->panelA as $row) {
            DB::table('candidates')->updateOrInsert(
                ['name' => $row['name']],
                ['panel' => 'A', 'gender' => $row['gender'], 'updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach ($this->panelB as $row) {
            DB::table('candidates')->updateOrInsert(
                ['name' => $row['name']],
                ['panel' => 'B', 'gender' => $row['gender'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        // Nothing to reverse — we only add rows, never delete them
    }
};
