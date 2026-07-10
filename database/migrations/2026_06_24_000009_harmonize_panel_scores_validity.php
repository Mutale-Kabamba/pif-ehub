<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Harmonise panel scores after interview session issues:
 *
 *  - Six candidates were absent but received erroneous scores due to a
 *    20-minute timer bug that re-graded the previous candidate when a new
 *    one was selected.  Their scores are marked invalid (not deleted).
 *
 *  - Cross-panel contamination (a panelist from Panel A scoring a Panel B
 *    candidate, or vice-versa) is marked invalid.
 *
 *  - Duplicate rows where the same panelist scored the same candidate more
 *    than once – only the earliest row (lowest id) is kept valid.
 *
 *  The `is_valid` column lets the application filter scores without any
 *  permanent data loss.  Rolling back simply drops the column and all
 *  original rows remain intact.
 */
return new class extends Migration
{
    /**
     * Candidates who were absent on interview day.
     * Any panel_scores rows for these names will be marked is_valid = false.
     */
    private array $absentCandidates = [
        'Constance Siabula',
        'Leya',
        'Fanely Phiri',
        'Chipo Hansi',
        'Emmanuel Chimuka Chifuwe',
        'Margaret Kampamba Chanda',
    ];

    // -------------------------------------------------------------------------

    public function up(): void
    {
        // ── Step 1: Add the is_valid flag (guard makes this idempotent) ───────
        if (! Schema::hasColumn('panel_scores', 'is_valid')) {
            Schema::table('panel_scores', function (Blueprint $table) {
                $table->boolean('is_valid')->default(true)->after('comments');
            });
        }

        // ── Step 2: Invalidate scores for absent candidates ──────────────────
        $absentIds = DB::table('candidates')
            ->whereIn('name', $this->absentCandidates)
            ->pluck('id');

        if ($absentIds->isNotEmpty()) {
            DB::table('panel_scores')
                ->whereIn('candidate_id', $absentIds)
                ->update(['is_valid' => false]);
        }

        // ── Step 3: Invalidate cross-panel scores ─────────────────────────────
        // Panel A panelists (panel = 'A') should only have scored Panel A candidates.
        // Panel B panelists (panel = 'B') should only have scored Panel B candidates.

        $panelAUserIds       = DB::table('users')->where('panel', 'A')->pluck('id');
        $panelBUserIds       = DB::table('users')->where('panel', 'B')->pluck('id');
        $panelACandidateIds  = DB::table('candidates')->where('panel', 'A')->pluck('id');
        $panelBCandidateIds  = DB::table('candidates')->where('panel', 'B')->pluck('id');

        // Panel A panelist → Panel B candidate  (wrong panel)
        if ($panelAUserIds->isNotEmpty() && $panelBCandidateIds->isNotEmpty()) {
            DB::table('panel_scores')
                ->whereIn('panelist_id', $panelAUserIds)
                ->whereIn('candidate_id', $panelBCandidateIds)
                ->update(['is_valid' => false]);
        }

        // Panel B panelist → Panel A candidate  (wrong panel)
        if ($panelBUserIds->isNotEmpty() && $panelACandidateIds->isNotEmpty()) {
            DB::table('panel_scores')
                ->whereIn('panelist_id', $panelBUserIds)
                ->whereIn('candidate_id', $panelACandidateIds)
                ->update(['is_valid' => false]);
        }

        // ── Step 4: Deduplicate – keep only the first score per panelist per ──
        //           candidate (timer bug could create multiple rows for same  ──
        //           panelist+candidate combination).                           ──
        //
        // We iterate over every (candidate_id, panelist_id) pair that has more
        // than one still-valid row, then invalidate every row except the oldest.

        $presentCandidateIds = DB::table('candidates')
            ->whereNotIn('name', $this->absentCandidates)
            ->pluck('id');

        foreach ($presentCandidateIds as $candidateId) {
            // All panelists who have at least one valid score for this candidate
            $panelistIds = DB::table('panel_scores')
                ->where('candidate_id', $candidateId)
                ->where('is_valid', true)
                ->distinct()
                ->pluck('panelist_id');

            foreach ($panelistIds as $panelistId) {
                $rows = DB::table('panel_scores')
                    ->where('candidate_id', $candidateId)
                    ->where('panelist_id', $panelistId)
                    ->where('is_valid', true)
                    ->orderBy('id')        // oldest first → we keep id[0]
                    ->pluck('id');

                if ($rows->count() > 1) {
                    // Invalidate everything except the first (lowest) id
                    DB::table('panel_scores')
                        ->whereIn('id', $rows->slice(1)->values())
                        ->update(['is_valid' => false]);
                }
            }
        }
    }

    // -------------------------------------------------------------------------

    public function down(): void
    {
        // Dropping the column restores the original state completely.
        Schema::table('panel_scores', function (Blueprint $table) {
            $table->dropColumn('is_valid');
        });
    }
};
