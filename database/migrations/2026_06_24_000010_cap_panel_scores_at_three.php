<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supplementary cleanup migration.
 *
 *  1. Invalidates any scores submitted by cover-role users (e.g. Mwiinga),
 *     who are observers/coordinators and should never have been scoring.
 *
 *  2. Safety-net cap: after steps 1 and the previous migration, if any
 *     present candidate still has more than 3 valid scores, we keep only
 *     the 3 oldest valid rows and invalidate the rest.
 *
 * This is additive – no rows are deleted, just flagged is_valid = false.
 */
return new class extends Migration
{
    private array $absentCandidates = [
        'Constance Siabula',
        'Leya',
        'Fanely Phiri',
        'Chipo Hansi',
        'Emmanuel Chimuka Chifuwe',
        'Margaret Kampamba Chanda',
    ];

    public function up(): void
    {
        // Skip if is_valid column does not exist (migration 000009 not yet run)
        if (! Schema::hasColumn('panel_scores', 'is_valid')) {
            return;
        }

        // ── Step 1: Invalidate all scores from cover-role users ───────────────
        $coverUserIds = DB::table('users')
            ->where('panel', 'cover')
            ->pluck('id');

        if ($coverUserIds->isNotEmpty()) {
            DB::table('panel_scores')
                ->whereIn('panelist_id', $coverUserIds)
                ->update(['is_valid' => false]);
        }

        // ── Step 2: Hard cap – no present candidate may have more than 3 ─────
        //           valid scores.  Keep only the 3 oldest valid rows.          ─
        $presentCandidateIds = DB::table('candidates')
            ->whereNotIn('name', $this->absentCandidates)
            ->pluck('id');

        foreach ($presentCandidateIds as $candidateId) {
            $validIds = DB::table('panel_scores')
                ->where('candidate_id', $candidateId)
                ->where('is_valid', true)
                ->orderBy('id')          // oldest first
                ->pluck('id');

            if ($validIds->count() > 3) {
                // Invalidate everything beyond the first 3
                $toInvalidate = $validIds->slice(3)->values();
                DB::table('panel_scores')
                    ->whereIn('id', $toInvalidate)
                    ->update(['is_valid' => false]);
            }
        }
    }

    public function down(): void
    {
        // Re-validate cover-panelist scores (restores state before this migration)
        $coverUserIds = DB::table('users')
            ->where('panel', 'cover')
            ->pluck('id');

        if ($coverUserIds->isNotEmpty()) {
            DB::table('panel_scores')
                ->whereIn('panelist_id', $coverUserIds)
                ->update(['is_valid' => true]);
        }

        // The hard cap cannot be automatically reversed because we don't know
        // which rows were capped vs. already invalidated by the previous migration.
        // Rolling back migration 000009 (which drops is_valid entirely) is the
        // correct full-rollback path.
    }
};
