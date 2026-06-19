<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $candidates = [
            'Emma Banda',
            'Edith Mwiinde Chooma',
            'Ruth Wauna',
            'Saliya Theresa Havupula',
            'Beauty Banji Mwale',
            'Belina Tatila',
            'Margaret Kampamba Chanda',
            'Taonga Ethel Ngoma',
            'Thelma Mwansa',
            'Constance Siabula',
            'Angela Kalimbwe',
            'Beauty Mweenda',
            'Gift Luyando',
            'Namungala Alice',
            'Moffat Daka',
            'Lawrence Pumulo',
            'Charles Zulu',
            'Bill Bishops Imonda',
            'Peter Gabriel Simpyata',
            'Rashid Nchimunya',
        ];

        foreach ($candidates as $name) {
            Candidate::create(['name' => $name]);
        }
    }
}
