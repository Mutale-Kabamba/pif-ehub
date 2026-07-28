<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\AssessmentRule;
use App\Models\AssessmentAssignment;
use App\Models\EvaluationScore;
use App\Models\Candidate;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\DB;

echo "=== TESTING ASSESSMENT ENGINE & EVALUATION SERVICE ===\n\n";

DB::beginTransaction();

try {
    // 1. Create test Candidate and User
    $candidate = Candidate::firstOrCreate(
        ['name' => 'Test Candidate Alpha'],
        ['panel' => 'A', 'gender' => 'F']
    );

    $evaluator1 = User::firstOrCreate(
        ['email' => 'judge1@pif.org'],
        ['name' => 'Judge One', 'password' => bcrypt('password'), 'role' => 'panelist', 'panel' => 'A']
    );

    $evaluator2 = User::firstOrCreate(
        ['email' => 'judge2@pif.org'],
        ['name' => 'Judge Two', 'password' => bcrypt('password'), 'role' => 'panelist', 'panel' => 'A']
    );

    echo "1. Candidate & Evaluators initialized.\n";

    // 2. Create Assessment
    $assessment = Assessment::create([
        'title' => 'Automated Test Interview',
        'description' => 'Testing weighted scoring and rule enforcement.',
        'type' => 'interview',
        'status' => 'active',
    ]);

    // 3. Create Rule (Max 2 panelists, Score Cap 90.00, Passing Threshold 60.00)
    $rule = AssessmentRule::create([
        'assessment_id' => $assessment->id,
        'max_panelists' => 2,
        'score_cap' => 90.00,
        'passing_threshold' => 60.00,
    ]);

    // 4. Create Questions with weights
    // Q1: Scale (1-5), Weight 10.0 => Max score = 5 * 10 = 50
    $q1 = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Technical Competency',
        'type' => 'scale',
        'weight' => 10.00,
        'order' => 1,
    ]);

    // Q2: Multiple Choice, Weight 5.0 => Max score = 5 * 5 = 25
    $q2 = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Leadership Potential',
        'type' => 'multiple_choice',
        'weight' => 5.00,
        'order' => 2,
    ]);

    $opt1 = QuestionOption::create([
        'question_id' => $q2->id,
        'option_label' => 'High Potential',
        'option_value' => 5.00,
    ]);

    $opt2 = QuestionOption::create([
        'question_id' => $q2->id,
        'option_label' => 'Moderate Potential',
        'option_value' => 3.00,
    ]);

    // 5. Create Assignments
    AssessmentAssignment::create([
        'assessment_id' => $assessment->id,
        'user_id' => $evaluator1->id,
        'role' => 'panelist',
    ]);
    AssessmentAssignment::create([
        'assessment_id' => $assessment->id,
        'user_id' => $evaluator2->id,
        'role' => 'panelist',
    ]);
    AssessmentAssignment::create([
        'assessment_id' => $assessment->id,
        'candidate_id' => $candidate->id,
        'role' => 'candidate',
    ]);

    echo "2. Assessment, Questions, Options, Rules, and Assignments created successfully.\n";

    // 6. Submit Evaluation Scores
    // Evaluator 1 gives Q1: 4.0 (Weighted: 4 * 10 = 40), Q2: 5.0 (Weighted: 5 * 5 = 25) => Evaluator 1 Total = 65
    EvaluationScore::create([
        'assessment_id' => $assessment->id,
        'candidate_id' => $candidate->id,
        'evaluator_id' => $evaluator1->id,
        'question_id' => $q1->id,
        'score' => 4.00,
    ]);
    EvaluationScore::create([
        'assessment_id' => $assessment->id,
        'candidate_id' => $candidate->id,
        'evaluator_id' => $evaluator1->id,
        'question_id' => $q2->id,
        'score' => 5.00,
    ]);

    // Evaluator 2 gives Q1: 5.0 (Weighted: 5 * 10 = 50), Q2: 5.0 (Weighted: 5 * 5 = 25) => Evaluator 2 Total = 75
    EvaluationScore::create([
        'assessment_id' => $assessment->id,
        'candidate_id' => $candidate->id,
        'evaluator_id' => $evaluator2->id,
        'question_id' => $q1->id,
        'score' => 5.00,
    ]);
    EvaluationScore::create([
        'assessment_id' => $assessment->id,
        'candidate_id' => $candidate->id,
        'evaluator_id' => $evaluator2->id,
        'question_id' => $q2->id,
        'score' => 5.00,
    ]);

    echo "3. Evaluation scores recorded.\n";

    // 7. Calculate candidate score via EvaluationService
    $service = new EvaluationService();
    $result = $service->calculateCandidateScore($assessment, $candidate);

    echo "\n=== EVALUATION SERVICE OUTPUT ===\n";
    echo "Candidate: " . $result['candidate']->name . "\n";
    echo "Evaluators Count: " . $result['evaluator_count'] . "\n";
    echo "Weighted Average Score: " . $result['weighted_total'] . " (Expected: (65 + 75)/2 = 70.00)\n";
    echo "Final Capped Score: " . $result['final_score'] . "\n";
    echo "Passing Threshold: " . $result['passing_threshold'] . "\n";
    echo "Passed Status: " . ($result['passed'] ? "PASSED (TRUE)" : "FAILED (FALSE)") . "\n";

    assert($result['weighted_total'] == 70.00, "Weighted average calculation failed!");
    assert($result['passed'] === true, "Passing threshold check failed!");

    echo "\n=== ALL VERIFICATION CHECKS PASSED PERFECTLY ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
} finally {
    DB::rollBack();
}
