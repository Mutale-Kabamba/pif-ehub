<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Candidate;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable FK checks, truncate, then re-enable so we start clean
        DB::statement('PRAGMA foreign_keys = OFF');
        Candidate::truncate();
        DB::statement('PRAGMA foreign_keys = ON');

        // Panel A candidates (Judges: Blessing, Sara, Bracious)
        $panelA = [
            ['name' => 'Diana Mungala',           'gender' => 'Female'],
            ['name' => 'Taonga Ethel Ngoma',       'gender' => 'Female'],
            ['name' => 'Leya',                     'gender' => 'Female'],
            ['name' => 'Fanely Phiri',             'gender' => 'Female'],
            ['name' => 'Belina Tatila',              'gender' => 'Female'],
            ['name' => 'Constance Siabula',        'gender' => 'Female'],
            ['name' => 'Chipo Hansi',              'gender' => 'Female'],
            ['name' => 'Rashid Nchimunya',         'gender' => 'Male'],
            ['name' => 'Emmanuel Chimuka Chifuwe', 'gender' => 'Male'],
            ['name' => 'Robert Chizu',             'gender' => 'Male'],
        ];

        // Panel B candidates (Judges: Mutale, Jacqueline, Florence)
        $panelB = [
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

        foreach ($panelA as $data) {
            Candidate::updateOrCreate(
                ['name' => $data['name']],
                ['panel' => 'A', 'gender' => $data['gender']]
            );
        }

        foreach ($panelB as $data) {
            Candidate::updateOrCreate(
                ['name' => $data['name']],
                ['panel' => 'B', 'gender' => $data['gender']]
            );
        }
    }
}
