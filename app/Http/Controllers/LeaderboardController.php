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
     * Compute and return the leaderboard data array.
     * Used by AdminController::dashboard() when tab=leaderboard.
     */
    public static function getLeaderboardData(): \Illuminate\Support\Collection
    {
        $candidates = Candidate::all();

        $leaderboard = $candidates->map(function (Candidate $candidate) {
            // Get literacy score (sum of 10 tasks, each 0-2 = max 20)
            $literacyRecord = LiteracyScore::where('candidate_id', $candidate->id)->first();
            $literacyScore = $literacyRecord ? (float) $literacyRecord->total_score : 0;
            $assessmentDate = $literacyRecord ? $literacyRecord->assessment_date : null;

            // Get panel scores: average each criterion across all panelists, then sum
            $panelScores = PanelScore::where('candidate_id', $candidate->id)->get();

            $interviewScore = 0;

            if ($panelScores->isNotEmpty()) {
                $avgMotivation      = $panelScores->avg('crit1_motivation');
                $avgAvailability    = $panelScores->avg('crit2_availability');
                $avgResilience      = $panelScores->avg('crit3_resilience');
                $avgCommunication   = $panelScores->avg('crit4_communication');

                // Each criterion avg is 1-5, sum = max 20
                $interviewScore = round($avgMotivation + $avgAvailability + $avgResilience + $avgCommunication, 2);
            }

            $grandTotal = round($literacyScore + $interviewScore, 2);

            return [
                'id'               => $candidate->id,
                'candidate_name'   => $candidate->name,
                'literacy_score'   => $literacyScore,
                'interview_score'  => $interviewScore,
                'grand_total'      => $grandTotal,
                'panelists_scored' => $panelScores->count(),
                'assessment_date'  => $assessmentDate,
            ];
        });

        // Sort properly using multi-level sort
        $leaderboard = $leaderboard->sort(function ($a, $b) {
            if ($a['grand_total'] !== $b['grand_total']) {
                return $b['grand_total'] <=> $a['grand_total'];
            }
            if ($a['literacy_score'] !== $b['literacy_score']) {
                return $b['literacy_score'] <=> $a['literacy_score'];
            }
            return $a['candidate_name'] <=> $b['candidate_name'];
        })->values();

        // Add rank and status
        $leaderboard = $leaderboard->map(function (array $entry, int $index) {
            $entry['rank'] = $index + 1;
            $entry['status'] = $entry['rank'] <= 10 ? 'ACCEPTED' : 'WAITLIST';
            return $entry;
        });

        return $leaderboard;
    }

    /**
     * Display the leaderboard page (redirects to dashboard with tab).
     */
    public function index(Request $request): \Illuminate\Http\RedirectResponse|StreamedResponse
    {
        $leaderboard = self::getLeaderboardData();

        // CSV export if requested
        if ($request->query('format') === 'csv') {
            return $this->exportCsv($leaderboard);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'leaderboard']);
    }

    /**
     * Export leaderboard as CSV download.
     *
     * @param  \Illuminate\Support\Collection  $leaderboard
     */
    private function exportCsv(Collection $leaderboard): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leaderboard.csv"',
        ];

        return response()->stream(function () use ($leaderboard) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, ['Rank', 'Name', 'Literacy Score (/20)', 'Interview Score (/20)', 'Grand Total (/40)', 'Status']);

            // Data rows
            foreach ($leaderboard as $entry) {
                fputcsv($handle, [
                    $entry['rank'],
                    $entry['name'],
                    $entry['literacy_score'],
                    $entry['interview_score'],
                    $entry['grand_total'],
                    $entry['status'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
