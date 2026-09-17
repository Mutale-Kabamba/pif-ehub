<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Import the real interview panel scores from the 2026-06-24 session.
 *
 * Source: PANELIST INTERVIEW SCORE SHEETS generated 2026-06-24 08:37.
 * All totals verified against the printed score sheet.
 *
 * Key observations from the score sheet:
 *  - Robert Chizu (Panel A) was only scored by Blessing and Sarah (Bracious
 *    did not score him) → he will have panelists_scored = 2.
 *  - Absent candidates (Leya, Fanely Phiri, Constance Siabula, Chipo Hansi,
 *    Emmanuel Chimuka Chifuwe, Margaret Kampamba Chanda) have no entries here.
 *  - is_valid = true for every row inserted by this migration.
 *
 * Idempotent: uses updateOrInsert on (panelist_id, candidate_id) so it is
 * safe to re-run and will correct any stale values without duplicating rows.
 */
return new class extends Migration
{
    /**
     * Raw score data keyed by [panelist_name, candidate_name, c1, c2, c3, c4].
     * Criteria order: motivation, availability, resilience, communication.
     */
    private array $scores = [
        // ── Panel A ─────────────────────────────────────────────────────────
        // Panelist: Blessing  (panel_total = 70)
        ['panelist' => 'Blessing', 'candidate' => 'Belina Tatila',     'c1' => 5, 'c2' => 3, 'c3' => 3, 'c4' => 4],
        ['panelist' => 'Blessing', 'candidate' => 'Diana Mungala',      'c1' => 5, 'c2' => 4, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Blessing', 'candidate' => 'Rashid Nchimunya',   'c1' => 5, 'c2' => 4, 'c3' => 4, 'c4' => 4],
        ['panelist' => 'Blessing', 'candidate' => 'Robert Chizu',       'c1' => 2, 'c2' => 3, 'c3' => 3, 'c4' => 2],
        ['panelist' => 'Blessing', 'candidate' => 'Taonga Ethel Ngoma', 'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],

        // Panelist: Bracious  (panel_total = 52 — Robert Chizu not scored)
        ['panelist' => 'Bracious', 'candidate' => 'Belina Tatila',     'c1' => 3, 'c2' => 3, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Bracious', 'candidate' => 'Diana Mungala',      'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Bracious', 'candidate' => 'Rashid Nchimunya',   'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Bracious', 'candidate' => 'Taonga Ethel Ngoma', 'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],

        // Panelist: Sarah  (panel_total = 51)
        ['panelist' => 'Sarah', 'candidate' => 'Belina Tatila',     'c1' => 2, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Sarah', 'candidate' => 'Diana Mungala',      'c1' => 2, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Sarah', 'candidate' => 'Rashid Nchimunya',   'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Sarah', 'candidate' => 'Robert Chizu',       'c1' => 2, 'c2' => 2, 'c3' => 3, 'c4' => 2],
        ['panelist' => 'Sarah', 'candidate' => 'Taonga Ethel Ngoma', 'c1' => 2, 'c2' => 2, 'c3' => 2, 'c4' => 2],

        // ── Panel B ─────────────────────────────────────────────────────────
        // Panelist: Florence  (panel_total = 116)
        ['panelist' => 'Florence', 'candidate' => 'Beauty Banji Mwale',      'c1' => 3, 'c2' => 3, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Florence', 'candidate' => 'Bill Bishops Imonda',     'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 2],
        ['panelist' => 'Florence', 'candidate' => 'Edith Mwiinde Chooma',    'c1' => 4, 'c2' => 3, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Florence', 'candidate' => 'Emma Banda',              'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 4],
        ['panelist' => 'Florence', 'candidate' => 'Moffat Daka',             'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Florence', 'candidate' => 'Natasha Mano Chileshe',   'c1' => 3, 'c2' => 2, 'c3' => 2, 'c4' => 3],
        ['panelist' => 'Florence', 'candidate' => 'Peter Gabriel Simpyata',  'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Florence', 'candidate' => 'Ruth Wauna',              'c1' => 2, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Florence', 'candidate' => 'Saliya Theresa Havupula', 'c1' => 3, 'c2' => 4, 'c3' => 3, 'c4' => 4],

        // Panelist: Jacqueline  (panel_total = 118)
        ['panelist' => 'Jacqueline', 'candidate' => 'Beauty Banji Mwale',      'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Jacqueline', 'candidate' => 'Bill Bishops Imonda',     'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Jacqueline', 'candidate' => 'Edith Mwiinde Chooma',    'c1' => 3, 'c2' => 4, 'c3' => 4, 'c4' => 4],
        ['panelist' => 'Jacqueline', 'candidate' => 'Emma Banda',              'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 4],
        ['panelist' => 'Jacqueline', 'candidate' => 'Moffat Daka',             'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Jacqueline', 'candidate' => 'Natasha Mano Chileshe',   'c1' => 2, 'c2' => 3, 'c3' => 3, 'c4' => 2],
        ['panelist' => 'Jacqueline', 'candidate' => 'Peter Gabriel Simpyata',  'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Jacqueline', 'candidate' => 'Ruth Wauna',              'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 2],
        ['panelist' => 'Jacqueline', 'candidate' => 'Saliya Theresa Havupula', 'c1' => 4, 'c2' => 3, 'c3' => 4, 'c4' => 4],

        // Panelist: Mutale  (panel_total = 122)
        ['panelist' => 'Mutale', 'candidate' => 'Beauty Banji Mwale',      'c1' => 4, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Mutale', 'candidate' => 'Bill Bishops Imonda',     'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Mutale', 'candidate' => 'Edith Mwiinde Chooma',    'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Mutale', 'candidate' => 'Emma Banda',              'c1' => 5, 'c2' => 4, 'c3' => 4, 'c4' => 4],
        ['panelist' => 'Mutale', 'candidate' => 'Moffat Daka',             'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 3],
        ['panelist' => 'Mutale', 'candidate' => 'Natasha Mano Chileshe',   'c1' => 3, 'c2' => 3, 'c3' => 2, 'c4' => 3],
        ['panelist' => 'Mutale', 'candidate' => 'Peter Gabriel Simpyata',  'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Mutale', 'candidate' => 'Ruth Wauna',              'c1' => 3, 'c2' => 3, 'c3' => 3, 'c4' => 3],
        ['panelist' => 'Mutale', 'candidate' => 'Saliya Theresa Havupula', 'c1' => 4, 'c2' => 4, 'c3' => 4, 'c4' => 3],
    ];

    public function up(): void
    {
        $now = now();

        // Build lookup maps so we avoid N+1 queries
        $users = DB::table('users')
            ->whereNotNull('panelist_name')
            ->pluck('id', 'panelist_name');   // ['Blessing' => 3, ...]

        $candidates = DB::table('candidates')
            ->pluck('id', 'name');            // ['Belina Tatila' => 5, ...]

        $hasIsValid = \Illuminate\Support\Facades\Schema::hasColumn('panel_scores', 'is_valid');

        foreach ($this->scores as $row) {
            $panelistId  = $users->get($row['panelist']);
            $candidateId = $candidates->get($row['candidate']);

            if (! $panelistId || ! $candidateId) {
                // Skip if user or candidate not found — prevents crash on partial data
                continue;
            }

            $values = [
                'crit1_motivation'    => $row['c1'],
                'crit2_availability'  => $row['c2'],
                'crit3_resilience'    => $row['c3'],
                'crit4_communication' => $row['c4'],
                'updated_at'          => $now,
                'created_at'          => $now,
            ];

            if ($hasIsValid) {
                $values['is_valid'] = true;
            }

            DB::table('panel_scores')->updateOrInsert(
                ['panelist_id' => $panelistId, 'candidate_id' => $candidateId],
                $values
            );
        }
    }

    public function down(): void
    {
        // Nothing to reverse automatically — manual data should not be auto-deleted
    }
};
