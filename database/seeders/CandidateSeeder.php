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
            'Mwansa Kapwepwe',
            'Chipo Musonda',
            'Miyanda Mudenda',
            'Nalukui Siyoto',
            'Kondwani Banda',
            'Taonga Zulu',
            'Sipho Ncube',
            'Mutinta Himanje',
            'Thabo Nyirenda',
            'Bupe Chilufya',
            'Musonda Tembo',
            'Sepo Kabika',
            'Luyando Munkombwe',
            'Mwaka Chileshe',
            'Zeko Mwanza',
            'Mapalo Kaunda',
            'Kambole Sikazwe',
            'Mubanga Chewe',
            'Namukolo Mukelabai',
            'Chilufya Chanda',
        ];

        foreach ($candidates as $name) {
            Candidate::create(['name' => $name]);
        }
    }
}
