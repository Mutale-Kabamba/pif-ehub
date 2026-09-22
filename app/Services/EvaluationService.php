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

        // Determine passing status (Only for interviews and graded assessments, NOT surveys)
        $isSurvey = ($assessment->type === 'survey');
        $passingThreshold = (!$isSurvey && $rule && $rule->passing_threshold !== null) ? (float) $rule->passing_threshold : null;
        $isPassed = null;
        if (!$isSurvey && $passingThreshold !== null) {
            $isPassed = ($finalCappedScore >= $passingThreshold);
        }

        // Compute question-level averages across evaluators
        $questionBreakdown = [];
        foreach ($questionSum as $qId => $data) {
            $avgRaw = $data['count'] > 0 ? ($data['total_score'] / $data['count']) : 0.0;
            $questionBreakdown[] = [
                'question_id' => $qId,
                'question_text' => $data['question_text'],
                'type' => $data['type'],
                'weight' => $data['weight'],
                'avg_raw_score' => round($avgRaw, 2),
                'avg_weighted_score' => round($avgRaw * $data['weight'], 2),
                'response_count' => $data['count'],
            ];
        }

        return [
            'candidate' => $candidate,
            'round' => (int) ($candidate->pivot->round ?? 1),
            'selection_status' => $candidate->pivot->selection_status ?? 'pending',
            'selection_notes' => $candidate->pivot->selection_notes ?? null,
            'panel_name' => $candidate->pivot->panel_name ?? $candidate->panel ?? 'A',
            'evaluator_count' => $evaluatorCount,
            'weighted_total' => round($averageWeightedScore, 2),
            'final_score' => round($finalCappedScore, 2),
            'score_cap' => $isSurvey ? null : $scoreCap,
            'passing_threshold' => $passingThreshold,
            'passed' => $isPassed,
            'is_survey' => $isSurvey,
            'evaluator_breakdown' => $evaluatorBreakdown,
            'question_breakdown' => $questionBreakdown,
        ];
    }

    /**
     * Compile overall assessment results for all assigned candidates or survey participants.
     *
     * @param Assessment $assessment
     * @return array
     */
    public function compileAssessmentResults(Assessment $assessment): array
    {
        $assessment->load(['questions.options', 'rule', 'candidates', 'panelists', 'evaluationScores.question', 'evaluationScores.candidate', 'evaluationScores.evaluator']);

        $isSurvey = ($assessment->type === 'survey');
        $candidates = $assessment->candidates;
        $candidateResults = [];

        foreach ($candidates as $candidate) {
            $candidateResults[] = $this->calculateCandidateScore($assessment, $candidate);
        }

        // Sort candidates by final score descending if graded assessment
        if (!$isSurvey) {
            usort($candidateResults, function ($a, $b) {
                return $b['final_score'] <=> $a['final_score'];
            });
        }

        $allScores = $assessment->evaluationScores;
        $totalEvaluations = $allScores->groupBy(function($item) {
            return ($item->evaluator_id ?: 'anon') . '_' . ($item->candidate_id ?: 'anon') . '_' . ($item->created_at ? $item->created_at->format('Y-m-d_H:i') : $item->id);
        })->count();

        // Survey specific aggregations
        $surveyQuestionStats = [];
        $qualitativeFeedback = [];
        $totalLikertSum = 0.0;
        $totalLikertCount = 0;

        foreach ($assessment->questions as $question) {
            $qScores = $allScores->where('question_id', $question->id);
            $numericScores = $qScores->whereNotNull('score')->pluck('score');
            $avgScore = $numericScores->isNotEmpty() ? round($numericScores->avg(), 2) : null;
            $textResponses = $qScores->whereNotNull('text_response')
                ->where('text_response', '!=', '')
                ->map(function ($s) {
                    return [
                        'text' => $s->text_response,
                        'stage' => $s->survey_stage ?: 'baseline',
                        'created_at' => $s->created_at ? $s->created_at->format('M d, Y') : null,
                    ];
                })
                ->values()
                ->toArray();

            $baseScores = $qScores->where('survey_stage', 'baseline')->whereNotNull('score')->pluck('score');
            $midScores = $qScores->where('survey_stage', 'midline')->whereNotNull('score')->pluck('score');
            $endScores = $qScores->where('survey_stage', 'endline')->whereNotNull('score')->pluck('score');

            $baseAvg = $baseScores->isNotEmpty() ? round($baseScores->avg(), 2) : 0.0;
            $midAvg = $midScores->isNotEmpty() ? round($midScores->avg(), 2) : 0.0;
            $endAvg = $endScores->isNotEmpty() ? round($endScores->avg(), 2) : 0.0;

            if ($numericScores->isNotEmpty()) {
                $totalLikertSum += $numericScores->sum();
                $totalLikertCount += $numericScores->count();
            }

            if ($question->type === 'text' && !empty($textResponses)) {
                $qualitativeFeedback[$question->id] = [
                    'question_text' => $question->question_text,
                    'responses' => $textResponses,
                ];
            }

            $surveyQuestionStats[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'response_count' => $qScores->count(),
                'avg_score' => $avgScore,
                'baseline_avg' => $baseAvg,
                'midline_avg' => $midAvg,
                'endline_avg' => $endAvg,
                'baseline_count' => $baseScores->count(),
                'midline_count' => $midScores->count(),
                'endline_count' => $endScores->count(),
                'distribution' => [
                    1 => $numericScores->filter(fn($s) => round($s) == 1)->count(),
                    2 => $numericScores->filter(fn($s) => round($s) == 2)->count(),
                    3 => $numericScores->filter(fn($s) => round($s) == 3)->count(),
                    4 => $numericScores->filter(fn($s) => round($s) == 4)->count(),
                    5 => $numericScores->filter(fn($s) => round($s) == 5)->count(),
                ],
                'text_responses_count' => count($textResponses),
            ];
        }

        $overallSurveyAverage = $totalLikertCount > 0 ? round($totalLikertSum / $totalLikertCount, 2) : 0.0;
        $rulesData = $assessment->rule?->rules_json ?? [];
        $isAnonymous = $rulesData['is_anonymous'] ?? true;

        // Detect stages present in scores
        $presentStages = $allScores->pluck('survey_stage')->filter()->unique()->values()->toArray();
        if (count($presentStages) === 1) {
            $surveyStage = $presentStages[0];
        } elseif (count($presentStages) > 1) {
            $surveyStage = implode(' / ', array_map('ucfirst', $presentStages));
        } else {
            $surveyStage = $rulesData['survey_stage'] ?? 'baseline';
        }

        $stageCounts = [
            'baseline' => $allScores->where('survey_stage', 'baseline')->groupBy(function($item) {
                return ($item->evaluator_id ?: 'anon') . '_' . ($item->candidate_id ?: 'anon') . '_' . ($item->created_at ? $item->created_at->format('Y-m-d_H:i') : $item->id);
            })->count(),
            'midline' => $allScores->where('survey_stage', 'midline')->groupBy(function($item) {
                return ($item->evaluator_id ?: 'anon') . '_' . ($item->candidate_id ?: 'anon') . '_' . ($item->created_at ? $item->created_at->format('Y-m-d_H:i') : $item->id);
            })->count(),
            'endline' => $allScores->where('survey_stage', 'endline')->groupBy(function($item) {
                return ($item->evaluator_id ?: 'anon') . '_' . ($item->candidate_id ?: 'anon') . '_' . ($item->created_at ? $item->created_at->format('Y-m-d_H:i') : $item->id);
            })->count(),
        ];

        $baseAllScores = $allScores->where('survey_stage', 'baseline')->whereNotNull('score')->pluck('score');
        $midAllScores = $allScores->where('survey_stage', 'midline')->whereNotNull('score')->pluck('score');
        $endAllScores = $allScores->where('survey_stage', 'endline')->whereNotNull('score')->pluck('score');

        $stageOverallAverages = [
            'baseline' => $baseAllScores->isNotEmpty() ? round($baseAllScores->avg(), 2) : 0.0,
            'midline' => $midAllScores->isNotEmpty() ? round($midAllScores->avg(), 2) : 0.0,
            'endline' => $endAllScores->isNotEmpty() ? round($endAllScores->avg(), 2) : 0.0,
        ];

        $passedCount = $isSurvey ? 0 : count(array_filter($candidateResults, fn($r) => $r['passed'] === true));
        $failedCount = $isSurvey ? 0 : count(array_filter($candidateResults, fn($r) => $r['passed'] === false));

        $distinctSurveyRespondents = $allScores->pluck('candidate_id')->filter()->unique()->count() ?: $totalEvaluations;

        // Selection overview metrics across rounds
        $selectedCount = count(array_filter($candidateResults, fn($r) => ($r['selection_status'] ?? '') === 'selected'));
        $reserveCount = count(array_filter($candidateResults, fn($r) => ($r['selection_status'] ?? '') === 'reserve'));
        $pulledOutCount = count(array_filter($candidateResults, fn($r) => ($r['selection_status'] ?? '') === 'pulled_out'));
        $roundsList = array_values(array_unique(array_map(fn($r) => (int)($r['round'] ?? 1), $candidateResults)));
        sort($roundsList);
        if (empty($roundsList)) {
            $roundsList = [1];
        }

        return [
            'assessment' => $assessment,
            'is_survey' => $isSurvey,
            'survey_stage' => $surveyStage,
            'survey_stages_present' => $presentStages,
            'stage_counts' => $stageCounts,
            'stage_overall_averages' => $stageOverallAverages,
            'is_anonymous' => $isAnonymous,
            'total_candidates' => $isSurvey ? ($distinctSurveyRespondents ?: $candidates->count()) : $candidates->count(),
            'total_panelists' => $assessment->panelists->count(),
            'total_questions' => $assessment->questions->count(),
            'total_evaluations_submitted' => $totalEvaluations ?: $allScores->count(),
            'overall_survey_average' => $overallSurveyAverage,
            'survey_question_stats' => $surveyQuestionStats,
            'qualitative_feedback' => $qualitativeFeedback,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'selected_count' => $selectedCount,
            'reserve_count' => $reserveCount,
            'pulled_out_count' => $pulledOutCount,
            'rounds_list' => $roundsList,
            'candidate_results' => $candidateResults,
        ];
    }
}
