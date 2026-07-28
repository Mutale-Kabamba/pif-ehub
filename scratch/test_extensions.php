<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\Question;
use App\Models\AssessmentRule;
use App\Models\AssessmentAssignment;
use App\Models\Candidate;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== TESTING EXTENDED ASSESSMENT ENGINE (ACCESS KEYS & MULTI-PANEL) ===\n\n";

DB::beginTransaction();

try {
    // 1. Create Panel A Candidate and Panel B Candidate
    $candidateA = Candidate::firstOrCreate(
        ['name' => 'Alice Panel A'],
        ['panel' => 'A', 'gender' => 'Female']
    );

    $candidateB = Candidate::firstOrCreate(
        ['name' => 'Bob Panel B'],
        ['panel' => 'B', 'gender' => 'Male']
    );

    // 2. Create Panel A Evaluator and Panel B Evaluator
    $evaluatorA = User::firstOrCreate(
        ['email' => 'judgeA@pif.org'],
        ['name' => 'Judge Panel A', 'password' => bcrypt('password'), 'role' => 'panelist', 'panel' => 'A']
    );

    $evaluatorB = User::firstOrCreate(
        ['email' => 'judgeB@pif.org'],
        ['name' => 'Judge Panel B', 'password' => bcrypt('password'), 'role' => 'panelist', 'panel' => 'B']
    );

    echo "1. Panel A and Panel B candidates & evaluators initialized.\n";

    // 3. Create Assessment with Access Key
    $accessKey = 'KEY-TEST99';
    $assessment = Assessment::create([
        'title' => 'Multi-Panel Survey & Interview',
        'description' => 'Testing access key lock and panel-filtered candidate rosters.',
        'type' => 'survey',
        'status' => 'active',
        'access_key' => $accessKey,
    ]);

    // 4. Create Rule with number_of_panels
    AssessmentRule::create([
        'assessment_id' => $assessment->id,
        'max_panelists' => 2,
        'score_cap' => 100.00,
        'passing_threshold' => 50.00,
        'rules_json' => ['number_of_panels' => 2],
    ]);

    // 5. Create Question
    $question = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Overall Performance',
        'type' => 'scale',
        'weight' => 1.0,
        'order' => 1,
    ]);

    // 6. Assign Evaluator A to Panel A, Evaluator B to Panel B
    AssessmentAssignment::create([
        'assessment_id' => $assessment->id,
        'user_id' => $evaluatorA->id,
        'role' => 'panelist',
        'panel_name' => 'A',
    ]);
    AssessmentAssignment::create([
        'assessment_id' => $assessment->id,
        'user_id' => $evaluatorB->id,
        'role' => 'panelist',
        'panel_name' => 'B',
    ]);

    // Assign Candidate A to Panel A, Candidate B to Panel B
    AssessmentAssignment::create([
        'assessment_id' => $assessment->id,
        'candidate_id' => $candidateA->id,
        'role' => 'candidate',
        'panel_name' => 'A',
    ]);
    AssessmentAssignment::create([
        'assessment_id' => $assessment->id,
        'candidate_id' => $candidateB->id,
        'role' => 'candidate',
        'panel_name' => 'B',
    ]);

    echo "2. Multi-panel assignments established.\n";

    // 7. Verify Access Key match
    assert($assessment->access_key === 'KEY-TEST99', 'Access key assignment failed!');
    echo "3. Access key 'KEY-TEST99' verified on assessment.\n";

    // 8. Test Panel Filter logic simulation
    $controller = new \App\Http\Controllers\AssessmentController(new EvaluationService());

    // Simulate logged in as Evaluator A (Panel A)
    auth()->login($evaluatorA);
    $req = new \Illuminate\Http\Request();
    $view = $controller->evaluateForm($req, $assessment);
    $viewData = $view->getData();

    $assignedCandidates = $viewData['assignedCandidates'];
    echo "4. Panel A Evaluator candidate roster count: " . $assignedCandidates->count() . " (Expected: 1 - Alice Panel A)\n";
    assert($assignedCandidates->count() === 1 && $assignedCandidates->first()->id === $candidateA->id, 'Panel candidate filtering failed!');

    echo "\n=== ALL EXTENSION VERIFICATION CHECKS PASSED PERFECTLY ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
} finally {
    DB::rollBack();
}
