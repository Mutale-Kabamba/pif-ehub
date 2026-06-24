<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\LiteracyScore;
use App\Models\PanelScore;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaderboardController extends Controller
{
    /**
     * Compute and return the leaderboard data split by gender track.
     * Used by AdminController::dashboard() when tab=leaderboard.
     */
    public static function getLeaderboardData(): \Illuminate\Support\Collection
    {
        $candidates = Candidate::all();

        // 1. Map evaluation arrays with metrics
        $mappedCandidates = $candidates->map(function (Candidate $candidate) {
            $literacyRecord = LiteracyScore::where('candidate_id', $candidate->id)->first();
            $literacyScore = $literacyRecord ? (float) $literacyRecord->total_score : 0;
            $assessmentDate = $literacyRecord ? $literacyRecord->assessment_date : null;

            $panelScores = PanelScore::where('candidate_id', $candidate->id)
                ->where('is_valid', true)
                ->get();
            $interviewScore = 0;

            if ($panelScores->isNotEmpty()) {
                $avgMotivation      = $panelScores->avg('crit1_motivation');
                $avgAvailability    = $panelScores->avg('crit2_availability');
                $avgResilience      = $panelScores->avg('crit3_resilience');
                $avgCommunication   = $panelScores->avg('crit4_communication');

                $interviewScore = round($avgMotivation + $avgAvailability + $avgResilience + $avgCommunication, 2);
            }

            $grandTotal = round($literacyScore + $interviewScore, 2);

            // Use gender stored in database (set during seeding)
            $gender = $candidate->gender ?? 'Female';

            return [
                'id'               => $candidate->id,
                'candidate_name'   => $candidate->name,
                'gender'           => $gender,
                'literacy_score'   => $literacyScore,
                'interview_score'  => $interviewScore,
                'grand_total'      => $grandTotal,
                'panelists_scored' => $panelScores->count(),
                'assessment_date'  => $assessmentDate,
            ];
        });

        // 3. Multi-level Tie-Breaker Ordering
        $sortFunction = function ($a, $b) {
            if ($a['grand_total'] !== $b['grand_total']) {
                return $b['grand_total'] <=> $a['grand_total'];
            }
            if ($a['literacy_score'] !== $b['literacy_score']) {
                return $b['literacy_score'] <=> $a['literacy_score'];
            }
            return $a['candidate_name'] <=> $b['candidate_name'];
        };

        // 4. Filter and Rank Females (Top 7 Advance)
        $females = $mappedCandidates->whereIn('gender', ['Female', 'female'])->sort($sortFunction)->values();
        $females = $females->map(function (array $entry, int $index) {
            $entry['rank'] = $index + 1;
            $entry['status'] = $entry['rank'] <= 7 ? 'ACCEPTED' : 'WAITLIST';
            return $entry;
        });

        // 5. Filter and Rank Males (Top 3 Advance)
        $males = $mappedCandidates->where('gender', 'Male')->sort($sortFunction)->values();
        $males = $males->map(function (array $entry, int $index) {
            $entry['rank'] = $index + 1;
            $entry['status'] = $entry['rank'] <= 3 ? 'ACCEPTED' : 'WAITLIST';
            return $entry;
        });

        return collect([
            'females' => $females,
            'males'   => $males,
        ]);
    }

    /**
     * Display the leaderboard page (redirects to dashboard with tab).
     */
    public function index(Request $request): \Illuminate\Http\RedirectResponse|StreamedResponse
    {
        $leaderboard = self::getLeaderboardData();

        if ($request->query('format') === 'csv') {
            return $this->exportCsv($leaderboard);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'leaderboard']);
    }

    /**
     * Export segmented leaderboard tracks as CSV download.
     */
    private function exportCsv(Collection $leaderboard): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="gender_separated_leaderboard.csv"',
        ];

        return response()->stream(function () use ($leaderboard) {
            $handle = fopen('php://output', 'w');

            // --- FEMALE SELECTION TRACK ---
            fputcsv($handle, ['FEMALE LEADERBOARD (Top 7 Advance)']);
            fputcsv($handle, ['Rank', 'Name', 'Gender', 'Literacy Score (/20)', 'Interview Score (/20)', 'Grand Total (/40)', 'Status']);
            foreach ($leaderboard['females'] as $entry) {
                fputcsv($handle, [
                    $entry['rank'],
                    $entry['candidate_name'],
                    $entry['gender'],
                    $entry['literacy_score'],
                    $entry['interview_score'],
                    $entry['grand_total'],
                    $entry['status'],
                ]);
            }

            fputcsv($handle, []); // Blank separator rows
            fputcsv($handle, []);

            // --- MALE SELECTION TRACK ---
            fputcsv($handle, ['MALE LEADERBOARD (Top 3 Advance)']);
            fputcsv($handle, ['Rank', 'Name', 'Gender', 'Literacy Score (/20)', 'Interview Score (/20)', 'Grand Total (/40)', 'Status']);
            foreach ($leaderboard['males'] as $entry) {
                fputcsv($handle, [
                    $entry['rank'],
                    $entry['candidate_name'],
                    $entry['gender'],
                    $entry['literacy_score'],
                    $entry['interview_score'],
                    $entry['grand_total'],
                    $entry['status'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export individual panelist score sheets as a CSV download.
     *
     * Columns per panelist section:
     *   Rank | Candidate | Motivation /5 | Availability /5 | Resilience /5 | Communication /5 | Total /20 | Comments
     *
     * Panelists are grouped by panel (A then B) and sorted by name within each panel.
     * Within each candidate row, panelist scores are shown individually (not averaged).
     */
    public function scoresheetCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="panelist_score_sheets.csv"',
        ];

        // Pre-build leaderboard rank maps so we can show candidate rank per gender track
        $leaderboard = self::getLeaderboardData();
        $rankMap = [];
        foreach ($leaderboard['females'] as $entry) {
            $rankMap[$entry['id']] = '#' . $entry['rank'] . ' (' . $entry['status'] . ')';
        }
        foreach ($leaderboard['males'] as $entry) {
            $rankMap[$entry['id']] = '#' . $entry['rank'] . ' (' . $entry['status'] . ')';
        }

        // Load all panelists that belong to a real panel (A or B), ordered by panel then name
        $panelists = \App\Models\User::whereIn('panel', ['A', 'B'])
            ->orderBy('panel')
            ->orderBy('panelist_name')
            ->get();

        return response()->stream(function () use ($panelists, $rankMap) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['PANELIST INTERVIEW SCORE SHEETS']);
            fputcsv($handle, ['Generated: ' . now()->format('Y-m-d H:i')]);
            fputcsv($handle, []);

            $currentPanel = null;

            foreach ($panelists as $panelist) {
                // Print panel header when panel changes
                if ($panelist->panel !== $currentPanel) {
                    $currentPanel = $panelist->panel;
                    fputcsv($handle, []);
                    fputcsv($handle, ['══ PANEL ' . $currentPanel . ' ══']);
                    fputcsv($handle, []);
                }

                fputcsv($handle, ['Panelist: ' . $panelist->panelist_name . '  (Panel ' . $panelist->panel . ')']);
                fputcsv($handle, [
                    'Leaderboard Rank',
                    'Candidate Name',
                    'Motivation (/5)',
                    'Availability (/5)',
                    'Resilience (/5)',
                    'Communication (/5)',
                    'Interview Total (/20)',
                    'Comments',
                ]);

                // All candidates assigned to this panelist's panel, sorted by name
                $candidates = \App\Models\Candidate::where('panel', $panelist->panel)
                    ->orderBy('name')
                    ->get();

                // Index this panelist's valid scores by candidate_id for fast lookup
                $validScores = \App\Models\PanelScore::where('panelist_id', $panelist->id)
                    ->where('is_valid', true)
                    ->get()
                    ->keyBy('candidate_id');

                $grandTotal = 0;

                foreach ($candidates as $candidate) {
                    $score = $validScores->get($candidate->id);

                    if ($score) {
                        // Candidate was present and scored
                        $total       = $score->crit1_motivation
                                     + $score->crit2_availability
                                     + $score->crit3_resilience
                                     + $score->crit4_communication;
                        $grandTotal += $total;

                        fputcsv($handle, [
                            $rankMap[$candidate->id] ?? 'N/A',
                            $candidate->name,
                            $score->crit1_motivation,
                            $score->crit2_availability,
                            $score->crit3_resilience,
                            $score->crit4_communication,
                            $total,
                            $score->comments ?? '',
                        ]);
                    } else {
                        // Candidate did not attend — scores were invalidated
                        fputcsv($handle, [
                            $rankMap[$candidate->id] ?? 'N/A',
                            $candidate->name,
                            '—',
                            '—',
                            '—',
                            '—',
                            'DID NOT ATTEND',
                            '',
                        ]);
                    }
                }

                // Totals row (only counts attended candidates)
                fputcsv($handle, [
                    '', 'PANELIST TOTAL (attended candidates only)', '', '', '', '', $grandTotal, '',
                ]);

                fputcsv($handle, []);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
