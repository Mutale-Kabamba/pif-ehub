<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Local-only test seeder.
 *
 * Seeds panel_scores that reproduce the original interview leaderboard
 * (as shown in the reference screenshot) so the local dev environment
 * can be verified before deploying to production.
 *
 * DO NOT run this on production — production already holds real panelist data.
 *
 * Usage (local only):
 *   php artisan db:seed --class=InterviewScoreSeeder
 *
 * Scoring rules reproduced here:
 *  - 3 valid scores per present candidate (correct panel panelists only)
 *  - Absent candidates get scores marked is_valid = false (data preserved, not counted)
 *  - A few extra rows with is_valid = false simulate the timer-bug noise that the
 *    2026_06_24_000009 / 000010 migrations would have cleaned up on real data.
 *
 * Target interview averages (from screenshot, /20):
 *  Panel B females : Emma 16.3 | Edith 14.7 | Saliya 14.6 | Natasha 10.3 | Ruth 11.5 | Beauty 12.7
 *  Panel A females : Belina 13.0 | Diana 12.8 | Taonga 11.0
 *  Panel B males   : Moffat 15.0 | Bill 11.8 | Peter 12.0
 *  Panel A males   : Rashid 14.8 | Robert 9.5
 */
class InterviewScoreSeeder extends Seeder
{
    public function run(): void
    {
        // ── Wipe existing panel_scores ────────────────────────────────────────
        DB::statement('DELETE FROM panel_scores');

        // ── Load panelists ────────────────────────────────────────────────────
        $mutale     = User::where('panelist_name', 'Mutale')->first();
        $jacqueline = User::where('panelist_name', 'Jacqueline')->first();
        $florence   = User::where('panelist_name', 'Florence')->first();
        $blessing   = User::where('panelist_name', 'Blessing')->first();
        $sarah      = User::where('panelist_name', 'Sarah')->first();
        $bracious   = User::where('panelist_name', 'Bracious')->first();
        $mwiinga    = User::where('panelist_name', 'Mwiinga')->first();

        // ── Helper closure ────────────────────────────────────────────────────
        $ins = static function (
            int $cid,
            int $pid,
            int $c1,
            int $c2,
            int $c3,
            int $c4,
            bool $valid = true
        ): void {
            DB::table('panel_scores')->insert([
                'candidate_id'        => $cid,
                'panelist_id'         => $pid,
                'crit1_motivation'    => $c1,
                'crit2_availability'  => $c2,
                'crit3_resilience'    => $c3,
                'crit4_communication' => $c4,
                'is_valid'            => $valid,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        };

        // ══════════════════════════════════════════════════════════════════════
        // PANEL B – valid judges: Mutale, Jacqueline, Florence
        // ══════════════════════════════════════════════════════════════════════

        // Emma Banda — target 16.3  (avg per crit ≈ 4.08)
        // P1={5,4,4,4}=17  P2={4,4,4,4}=16  P3={4,4,4,4}=16
        // avg: c1=4.33, c2=4, c3=4, c4=4 → 16.33 ≈ 16.3 ✓
        // + Mwiinga cover score marked invalid (simulates the 4th panelist noise)
        $c = Candidate::where('name', 'Emma Banda')->firstOrFail();
        $ins($c->id, $mutale->id,     5, 4, 4, 4);
        $ins($c->id, $jacqueline->id, 4, 4, 4, 4);
        $ins($c->id, $florence->id,   4, 4, 4, 4);
        if ($mwiinga) $ins($c->id, $mwiinga->id, 4, 4, 4, 5, false); // noise

        // Edith Mwinde Chooma — target 14.7  (avg per crit ≈ 3.67)
        // P1={4,4,3,4}=15  P2={3,4,4,4}=15  P3={4,3,4,3}=14
        // avg: c1=(11/3)=3.67, c2=(11/3)=3.67, c3=(11/3)=3.67, c4=(11/3)=3.67 → 14.67 ≈ 14.7 ✓
        $c = Candidate::where('name', 'Edith Mwiinde Chooma')->firstOrFail();
        $ins($c->id, $mutale->id,     4, 4, 3, 4);
        $ins($c->id, $jacqueline->id, 3, 4, 4, 4);
        $ins($c->id, $florence->id,   4, 3, 4, 3);

        // Saliya Theresa Havupula — target 14.6
        // P1={4,4,4,3}=15  P2={4,3,4,4}=15  P3={3,4,3,4}=14
        // avg: c1=3.67, c2=3.67, c3=3.67, c4=3.67 → 14.67 ≈ 14.6 ✓
        // + 2 Panel A panelists marked invalid (simulates the 5-panelist noise)
        $c = Candidate::where('name', 'Saliya Theresa Havupula')->firstOrFail();
        $ins($c->id, $mutale->id,     4, 4, 4, 3);
        $ins($c->id, $jacqueline->id, 4, 3, 4, 4);
        $ins($c->id, $florence->id,   3, 4, 3, 4);
        $ins($c->id, $blessing->id,   3, 3, 3, 3, false); // wrong-panel noise
        $ins($c->id, $mwiinga->id,    3, 3, 3, 3, false); // cover noise

        // Ruth Wauna — target 11.5  (avg per crit ≈ 2.875)
        // P1={3,3,3,3}=12  P2={3,3,3,2}=11  P3={2,3,3,3}=11
        // avg: c1=(8/3)=2.67, c2=3, c3=3, c4=(8/3)=2.67 → 11.33 ≈ 11.5
        $c = Candidate::where('name', 'Ruth Wauna')->firstOrFail();
        $ins($c->id, $mutale->id,     3, 3, 3, 3);
        $ins($c->id, $jacqueline->id, 3, 3, 3, 2);
        $ins($c->id, $florence->id,   2, 3, 3, 3);
        if ($mwiinga) $ins($c->id, $mwiinga->id, 3, 3, 2, 2, false); // noise

        // Natasha Mano Chileshe — target 10.3  (avg per crit ≈ 2.58)
        // P1={3,3,2,3}=11  P2={2,3,3,2}=10  P3={3,2,2,3}=10
        // avg: c1=(8/3)=2.67, c2=(8/3)=2.67, c3=(7/3)=2.33, c4=(8/3)=2.67 → 10.33 ✓
        $c = Candidate::where('name', 'Natasha Mano Chileshe')->firstOrFail();
        $ins($c->id, $mutale->id,     3, 3, 2, 3);
        $ins($c->id, $jacqueline->id, 2, 3, 3, 2);
        $ins($c->id, $florence->id,   3, 2, 2, 3);

        // Beauty Banji Mwale — target 12.7  (avg per crit ≈ 3.17)
        // P1={4,3,3,3}=13  P2={3,3,3,3}=12  P3={3,3,4,3}=13
        // avg: c1=(10/3)=3.33, c2=3, c3=(10/3)=3.33, c4=3 → 12.67 ≈ 12.7 ✓
        // + 3 Panel A panelists invalid (simulates the 6-panelist noise)
        $c = Candidate::where('name', 'Beauty Banji Mwale')->firstOrFail();
        $ins($c->id, $mutale->id,     4, 3, 3, 3);
        $ins($c->id, $jacqueline->id, 3, 3, 3, 3);
        $ins($c->id, $florence->id,   3, 3, 4, 3);
        $ins($c->id, $blessing->id,   3, 3, 3, 3, false); // wrong-panel noise
        $ins($c->id, $sarah->id,      3, 3, 3, 3, false);
        $ins($c->id, $bracious->id,   3, 3, 3, 3, false);

        // Margaret Kampamba Chanda — ABSENT → all invalid
        $c = Candidate::where('name', 'Margaret Kampamba Chanda')->firstOrFail();
        $ins($c->id, $mutale->id,     2, 2, 2, 2, false);
        $ins($c->id, $jacqueline->id, 2, 2, 2, 1, false);
        $ins($c->id, $florence->id,   2, 1, 2, 2, false);

        // ══════════════════════════════════════════════════════════════════════
        // PANEL A – valid judges: Blessing, Sarah, Bracious
        // ══════════════════════════════════════════════════════════════════════

        // Belina Tatila — target ≈13.0  |  Sarah LOWEST
        // Blessing={5,3,3,4}=15  Sarah={2,3,3,3}=11  Bracious={3,3,4,3}=13
        // avg: c1=(10/3)=3.33, c2=3, c3=(10/3)=3.33, c4=(10/3)=3.33 → 12.99 ≈ 13.0 ✓
        // Sarah total 11 < Bracious 13 < Blessing 15
        $c = Candidate::where('name', 'Belina Tatila')->firstOrFail();
        $ins($c->id, $blessing->id,  5, 3, 3, 4);
        $ins($c->id, $sarah->id,     2, 3, 3, 3);
        $ins($c->id, $bracious->id,  3, 3, 4, 3);

        // Diana Mungala — target 12.8
        // With integer scores and 3 panelists the closest achievable values are 12.7 (=38/3)
        // and 13.0 (=39/3). We use 13.0 so Diana ranks above Beauty (12.7);
        // Belina also sits at 13.0 – alphabetical tie-break puts Belina #4, Diana #5, matching original.
        // P1={4,3,3,3}=13  P2={3,4,3,3}=13  P3={3,3,4,3}=13
        // avg: c1=c2=c3=3.33, c4=3 → 12.99 rounds to 13.0 ✓
        // + 3 Panel B panelists invalid (simulates the 6-panelist noise)
        $c = Candidate::where('name', 'Diana Mungala')->firstOrFail();
        $ins($c->id, $blessing->id,  5, 4, 4, 3); // Blessing: 16 (highest)
        $ins($c->id, $sarah->id,     2, 3, 3, 3); // Sarah:   11 (lowest)
        $ins($c->id, $bracious->id,  3, 3, 3, 3); // Bracious: 12
        $ins($c->id, $mutale->id,     3, 3, 3, 3, false); // wrong-panel noise
        $ins($c->id, $jacqueline->id, 3, 3, 3, 3, false);
        $ins($c->id, $florence->id,   3, 3, 3, 3, false);

        // Taonga Ethel Ngoma — target ≈10.7  |  Sarah LOWEST
        // Blessing={3,3,3,3}=12  Sarah={2,2,2,2}=8  Bracious={3,3,3,3}=12
        // avg: c1=c2=c3=c4=(8/3)=2.67 → 10.67 ≈ 10.7 ✓
        // Sarah total 8 < Blessing 12 = Bracious 12
        $c = Candidate::where('name', 'Taonga Ethel Ngoma')->firstOrFail();
        $ins($c->id, $blessing->id,  3, 3, 3, 3); // 12 (highest)
        $ins($c->id, $sarah->id,     2, 2, 2, 2); // 8  (lowest)
        $ins($c->id, $bracious->id,  3, 3, 3, 3); // 12
        // Duplicate scores (timer bug) — invalid
        $ins($c->id, $blessing->id,  3, 3, 3, 3, false);
        $ins($c->id, $sarah->id,     3, 3, 3, 3, false);
        $ins($c->id, $bracious->id,  3, 3, 3, 3, false);
        // Cross-panel noise — invalid
        $ins($c->id, $mutale->id,     3, 3, 3, 3, false);
        $ins($c->id, $jacqueline->id, 3, 3, 3, 3, false);
        $ins($c->id, $florence->id,   3, 3, 3, 3, false);
        if ($mwiinga) $ins($c->id, $mwiinga->id, 3, 3, 3, 3, false);

        // Absent Panel A females — all scores invalid
        foreach (['Constance Siabula', 'Fanely Phiri', 'Chipo Hansi', 'Leya'] as $name) {
            $c = Candidate::where('name', $name)->first();
            if ($c) {
                $ins($c->id, $blessing->id, 2, 2, 2, 2, false);
                if (in_array($name, ['Fanely Phiri', 'Leya'])) {
                    $ins($c->id, $sarah->id, 2, 1, 2, 2, false);
                }
                if ($name === 'Fanely Phiri') {
                    $ins($c->id, $bracious->id, 2, 2, 1, 2, false);
                }
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // PANEL B MALES
        // ══════════════════════════════════════════════════════════════════════

        // Moffat Daka — target 15.0, 3 panelists
        // All three give {4,4,4,3}=15 → avg per crit = 4+4+4+3 = 15.0 ✓
        $c = Candidate::where('name', 'Moffat Daka')->firstOrFail();
        $ins($c->id, $mutale->id,     4, 4, 4, 3);
        $ins($c->id, $jacqueline->id, 4, 4, 4, 3);
        $ins($c->id, $florence->id,   4, 4, 4, 3);

        // Peter Gabriel Simpyata — target 12.0, 3 panelists
        // All three give {3,3,3,3}=12 → avg per crit = 3+3+3+3 = 12.0 ✓
        $c = Candidate::where('name', 'Peter Gabriel Simpyata')->firstOrFail();
        $ins($c->id, $mutale->id,     3, 3, 3, 3);
        $ins($c->id, $jacqueline->id, 3, 3, 3, 3);
        $ins($c->id, $florence->id,   3, 3, 3, 3);

        // Bill Bishops Imonda — target 11.8
        // P1={3,3,3,3}=12  P2={3,3,3,3}=12  P3={3,3,3,2}=11
        // avg: c1=3, c2=3, c3=3, c4=(8/3)=2.67 → 11.67 ≈ 11.8
        // + 3 Panel A panelists invalid
        $c = Candidate::where('name', 'Bill Bishops Imonda')->firstOrFail();
        $ins($c->id, $mutale->id,     3, 3, 3, 3);
        $ins($c->id, $jacqueline->id, 3, 3, 3, 3);
        $ins($c->id, $florence->id,   3, 3, 3, 2);
        $ins($c->id, $blessing->id,  3, 3, 3, 3, false); // wrong-panel noise
        $ins($c->id, $sarah->id,     3, 3, 3, 3, false);
        $ins($c->id, $bracious->id,  3, 3, 3, 3, false);

        // Emmanuel Chimuka Chifuwe — ABSENT → invalid
        $c = Candidate::where('name', 'Emmanuel Chimuka Chifuwe')->firstOrFail();
        $ins($c->id, $mutale->id, 2, 1, 2, 2, false);

        // ══════════════════════════════════════════════════════════════════════
        // PANEL A MALES
        // ══════════════════════════════════════════════════════════════════════

        // Rashid Nchimunya — target 14.8 (must rank below Moffat at 15.0)  |  Sarah LOWEST
        // Blessing={5,4,4,4}=17  Sarah={3,3,3,3}=12  Bracious={4,4,4,3}=15
        // avg: c1=(12/3)=4, c2=(11/3)=3.67, c3=(11/3)=3.67, c4=(10/3)=3.33 → 14.67 ≈ 14.7 ✓
        // Sarah total 12 < Bracious 15 < Blessing 17
        $c = Candidate::where('name', 'Rashid Nchimunya')->firstOrFail();
        $ins($c->id, $blessing->id,  5, 4, 4, 4); // 17 (highest)
        $ins($c->id, $sarah->id,     3, 3, 3, 3); // 12 (lowest)
        $ins($c->id, $bracious->id,  4, 4, 4, 3); // 15
        if ($mwiinga) $ins($c->id, $mwiinga->id, 3, 3, 3, 3, false); // noise

        // Robert Chizu — target 9.5, only 2 panelists scored
        // P1={2,3,3,2}=10  P2={2,2,3,2}=9
        // avg: c1=(4/2)=2, c2=(5/2)=2.5, c3=(6/2)=3, c4=(4/2)=2 → 9.5 ✓
        $c = Candidate::where('name', 'Robert Chizu')->firstOrFail();
        $ins($c->id, $blessing->id,  2, 3, 3, 2);
        $ins($c->id, $sarah->id,     2, 2, 3, 2);
    }
}
