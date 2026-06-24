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
}