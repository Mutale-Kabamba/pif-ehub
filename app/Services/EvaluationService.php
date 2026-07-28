<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Candidate;
use App\Models\EvaluationScore;

class EvaluationService
{
    /**
     * Calculate evaluation results for a single candidate under a specific assessment.
     *
     * @param Assessment $assessment
     * @param Candidate $candidate
     * @return array
     */
    public function calculateCandidateScore(Assessment $assessment, Candidate $candidate): array
    {
        $rule = $assessment->rule;
        $questions = $assessment->questions;

        // Fetch evaluation scores for this candidate and assessment
        $scoresQuery = EvaluationScore::where('assessment_id', $assessment->id)
            ->where('candidate_id', $candidate->id)
            ->with(['question', 'evaluator']);

        $allScores = $scoresQuery->get();
        $groupedByEvaluator = $allScores->groupBy('evaluator_id');

        // Apply max_panelists limit if configured
        if ($rule && $rule->max_panelists && $rule->max_panelists > 0) {
            $groupedByEvaluator = $groupedByEvaluator->take($rule->max_panelists);
        }

        $evaluatorCount = $groupedByEvaluator->count();
        $evaluatorBreakdown = [];
        $totalEvaluatorScores = 0.0;
        $questionSum = [];

        foreach ($questions as $q) {
            $questionSum[$q->id] = [
                'question_text' => $q->question_text,
                'weight' => (float) $q->weight,
                'type' => $q->type,
                'total_score' => 0.0,
                'count' => 0,
            ];
        }

        foreach ($groupedByEvaluator as $evaluatorId => $evaluatorScores) {
            $evaluatorWeightedTotal = 0.0;
            $evaluatorRawTotal = 0.0;
            $evaluatorDetails = [];

            foreach ($questions as $question) {
                $scoreRow = $evaluatorScores->firstWhere('question_id', $question->id);
                $rawScore = $scoreRow ? (float) $scoreRow->score : 0.0;
                $weight = (float) $question->weight;
                $weightedScore = $rawScore * $weight;

                $evaluatorRawTotal += $rawScore;
                $evaluatorWeightedTotal += $weightedScore;

                if (isset($questionSum[$question->id])) {
                    $questionSum[$question->id]['total_score'] += $rawScore;
                    $questionSum[$question->id]['count'] += ($scoreRow ? 1 : 0);
                }

                $evaluatorDetails[] = [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'raw_score' => $rawScore,
                    'weight' => $weight,
                    'weighted_score' => $weightedScore,
                    'text_response' => $scoreRow ? $scoreRow->text_response : null,
                ];
            }

            $totalEvaluatorScores += $evaluatorWeightedTotal;

            $evaluatorUser = $evaluatorScores->first()?->evaluator;
            $evaluatorBreakdown[] = [
                'evaluator_id' => $evaluatorId,
                'evaluator_name' => $evaluatorUser ? ($evaluatorUser->panelist_name ?: $evaluatorUser->name) : 'Anonymous',
                'raw_total' => $evaluatorRawTotal,
                'weighted_total' => $evaluatorWeightedTotal,
                'details' => $evaluatorDetails,
            ];
        }

        // Calculate average weighted total score across panelists (or 0 if no evaluations)
        $averageWeightedScore = $evaluatorCount > 0 ? ($totalEvaluatorScores / $evaluatorCount) : 0.0;

        // Apply score cap if defined
        $scoreCap = $rule && $rule->score_cap !== null ? (float) $rule->score_cap : null;
        $finalCappedScore = $scoreCap !== null ? min($averageWeightedScore, $scoreCap) : $averageWeightedScore;

        // Determine passing status
        $passingThreshold = $rule && $rule->passing_threshold !== null ? (float) $rule->passing_threshold : null;
        $isPassed = null;
        if ($passingThreshold !== null) {
            $isPassed = ($finalCappedScore >= $passingThreshold);
        }

        // Compute question-level averages across evaluators
        $questionBreakdown = [];
        foreach ($questionSum as $qId => $data) {
            $avgRaw = $data['count'] > 0 ? ($data['total_score'] / $data['count']) : 0.0;
            $questionBreakdown[] = [
                'question_id' => $qId,
                'question_text' => $data['question_text'],
                'weight' => $data['weight'],
                'avg_raw_score' => round($avgRaw, 2),
                'avg_weighted_score' => round($avgRaw * $data['weight'], 2),
                'response_count' => $data['count'],
            ];
        }

        return [
            'candidate' => $candidate,
            'evaluator_count' => $evaluatorCount,
            'weighted_total' => round($averageWeightedScore, 2),
            'final_score' => round($finalCappedScore, 2),
            'score_cap' => $scoreCap,
            'passing_threshold' => $passingThreshold,
            'passed' => $isPassed,
            'evaluator_breakdown' => $evaluatorBreakdown,
            'question_breakdown' => $questionBreakdown,
        ];
    }

    /**
     * Compile overall assessment results for all assigned candidates or participants.
     *
     * @param Assessment $assessment
     * @return array
     */
    public function compileAssessmentResults(Assessment $assessment): array
    {
        $assessment->load(['questions.options', 'rule', 'candidates', 'panelists', 'evaluationScores']);

        $candidates = $assessment->candidates;
        $candidateResults = [];

        foreach ($candidates as $candidate) {
            $candidateResults[] = $this->calculateCandidateScore($assessment, $candidate);
        }

        // Sort candidates by final score descending
        usort($candidateResults, function ($a, $b) {
            return $b['final_score'] <=> $a['final_score'];
        });

        // Compute survey / overall metrics
        $totalEvaluations = $assessment->evaluationScores()->distinct('evaluator_id')->count('evaluator_id');
        $passedCount = count(array_filter($candidateResults, fn($r) => $r['passed'] === true));
        $failedCount = count(array_filter($candidateResults, fn($r) => $r['passed'] === false));

        return [
            'assessment' => $assessment,
            'total_candidates' => $candidates->count(),
            'total_panelists' => $assessment->panelists->count(),
            'total_questions' => $assessment->questions->count(),
            'total_evaluations_submitted' => $totalEvaluations,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'candidate_results' => $candidateResults,
        ];
    }
}
