<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\AssessmentRule;
use App\Models\Candidate;
use App\Models\EvaluationScore;
use App\Models\Question;
use App\Models\User;
use App\Services\EvaluationService;
use App\Services\ExcelImportExportService;
use Illuminate\Support\Facades\DB;

echo "=== PIF M&E Survey Verification ===\n";

DB::beginTransaction();

try {
    $evaluator = User::first() ?: User::create([
        'name' => 'M&E Officer',
        'email' => 'me@pif.zm',
        'password' => bcrypt('secret'),
        'role' => 'super',
    ]);

    // 1. Create a Baseline Anonymous Survey
    $survey = Assessment::create([
        'title' => 'Livingstone Cohort 2026 Baseline M&E Survey',
        'description' => 'Baseline survey measuring pre-training confidence and expectations.',
        'type' => 'survey',
        'status' => 'active',
        'access_key' => 'SURV-BASE-' . rand(1000, 9999),
    ]);

    $rule = AssessmentRule::create([
        'assessment_id' => $survey->id,
        'rules_json' => [
            'survey_stage' => 'baseline',
            'is_anonymous' => true,
            'passing_threshold' => null,
            'score_cap' => null,
        ],
    ]);

    // 2. Add Questions (Scale, Boolean, Multiple Choice, Text)
    $q1 = Question::create([
        'assessment_id' => $survey->id,
        'question_text' => 'I feel confident writing HTML/CSS code.',
        'type' => 'scale',
        'order' => 1,
        'max_score' => 5,
    ]);

    $q2 = Question::create([
        'assessment_id' => $survey->id,
        'question_text' => 'Have you previously built any website?',
        'type' => 'boolean',
        'order' => 2,
        'max_score' => 1,
    ]);

    $q3 = Question::create([
        'assessment_id' => $survey->id,
        'question_text' => 'What is your primary learning goal for this cohort?',
        'type' => 'text',
        'order' => 3,
        'max_score' => null,
    ]);

    // 3. Add Simulated Responses from 5 Participants
    $responses = [
        ['scale' => 2, 'bool' => 0, 'text' => 'Learn web development from scratch and build a career.'],
        ['scale' => 3, 'bool' => 0, 'text' => 'Get skills to work remotely for international clients.'],
        ['scale' => 4, 'bool' => 1, 'text' => 'Upgrade my digital design and programming skills.'],
        ['scale' => 2, 'bool' => 0, 'text' => 'Gain confidence in tech and start freelancing in Livingstone.'],
        ['scale' => 3, 'bool' => 0, 'text' => 'Build web solutions for local tourism businesses.'],
    ];

    foreach ($responses as $i => $resp) {
        $cand = Candidate::create([
            'name' => "Respondent #" . ($i + 1),
            'gender' => ($i % 2 === 0) ? 'Female' : 'Male',
            'panel' => 'A',
        ]);

        EvaluationScore::create([
            'assessment_id' => $survey->id,
            'candidate_id' => $cand->id,
            'evaluator_id' => $evaluator->id,
            'question_id' => $q1->id,
            'score' => $resp['scale'],
            'text_response' => null,
        ]);

        EvaluationScore::create([
            'assessment_id' => $survey->id,
            'candidate_id' => $cand->id,
            'evaluator_id' => $evaluator->id,
            'question_id' => $q2->id,
            'score' => $resp['bool'],
            'text_response' => $resp['bool'] ? 'Yes' : 'No',
        ]);

        EvaluationScore::create([
            'assessment_id' => $survey->id,
            'candidate_id' => $cand->id,
            'evaluator_id' => $evaluator->id,
            'question_id' => $q3->id,
            'score' => null,
            'text_response' => $resp['text'],
        ]);
    }

    // 4. Test EvaluationService Compilation
    $evalService = app(EvaluationService::class);
    $results = $evalService->compileAssessmentResults($survey);

    echo "Survey Stage: " . ($results['survey_stage'] ?? 'N/A') . "\n";
    echo "Is Anonymous: " . ($results['is_anonymous'] ? 'Yes' : 'No') . "\n";
    echo "Is Survey: " . ($results['is_survey'] ? 'Yes' : 'No') . "\n";
    echo "Passed Count: " . $results['passed_count'] . " (Expected: 0)\n";
    echo "Failed Count: " . $results['failed_count'] . " (Expected: 0)\n";
    echo "Total Candidates / Respondents: " . $results['total_candidates'] . " (Expected: 5)\n";
    echo "Overall Avg Rating: " . $results['overall_survey_average'] . "\n";

    echo "\n--- Question Statistics (Likert Distribution) ---\n";
    foreach ($results['survey_question_stats'] as $qStat) {
        echo "Q: " . $qStat['question_text'] . " (Type: " . $qStat['type'] . ", Avg: " . ($qStat['avg_score'] ?? 'N/A') . ")\n";
        if ($qStat['type'] === 'scale') {
            echo "  Distribution (1-5): " . json_encode($qStat['distribution']) . "\n";
        }
    }

    echo "\n--- Qualitative Feedback Quotes ---\n";
    foreach ($results['qualitative_feedback'] as $qFeed) {
        echo "Topic: " . $qFeed['question_text'] . "\n";
        foreach ($qFeed['responses'] as $respText) {
            echo "  - \"{$respText}\"\n";
        }
    }

    // Check assertions
    assert($results['is_survey'] === true, 'is_survey should be true');
    assert($results['survey_stage'] === 'baseline', 'survey_stage should be baseline');
    assert($results['is_anonymous'] === true, 'is_anonymous should be true');
    assert($results['passed_count'] === 0, 'passed_count must be 0 for surveys');
    assert($results['failed_count'] === 0, 'failed_count must be 0 for surveys');
    assert(count($results['qualitative_feedback']) === 1, 'qualitative feedback should have 1 question');
    assert(count(reset($results['qualitative_feedback'])['responses']) === 5, 'qualitative feedback should have 5 quotes');

    // 5. Test ExcelImportExportService Template Generation for Surveys
    $excelService = app(ExcelImportExportService::class);
    $templateResponse = $excelService->downloadAssessmentTemplate($survey, 'xlsx');
    assert($templateResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse, 'Template should return StreamedResponse');

    echo "\n[SUCCESS] All M&E Survey calculations, stage tracking, anonymization, and template tests PASSED!\n";

    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n[FAILED] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
