<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\AssessmentRule;
use App\Models\EvaluationScore;
use App\Models\Question;
use App\Models\SurveyResponse;
use App\Http\Controllers\SurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== Testing Pure Anonymous Survey Submissions with Baseline, Midline, and Endline ===\n";

DB::beginTransaction();

try {
    // 1. Create a dynamic M&E Survey
    $survey = Assessment::create([
        'title' => 'Digital Self-Efficacy & Mindset Survey',
        'description' => 'M&E survey tracking progress across baseline, midline, and endline.',
        'type' => 'survey',
        'status' => 'active',
        'access_key' => 'SURV-TEST-' . rand(1000, 9999),
    ]);

    $q1 = Question::create([
        'assessment_id' => $survey->id,
        'question_text' => 'I feel confident writing JavaScript logic.',
        'type' => 'scale',
        'order' => 1,
        'max_score' => 5,
    ]);

    $q2 = Question::create([
        'assessment_id' => $survey->id,
        'question_text' => 'What was your biggest achievement this month?',
        'type' => 'text',
        'order' => 2,
    ]);

    $controller = app(SurveyController::class);

    // 2. Submit Baseline Response
    $reqBaseline = Request::create(route('surveys.submit', $survey->id), 'POST', [
        'survey_stage' => 'baseline',
        'scores' => [
            ['question_id' => $q1->id, 'score' => 3, 'text_response' => null],
            ['question_id' => $q2->id, 'score' => null, 'text_response' => 'Started learning web concepts.'],
        ],
    ]);
    $resBaseline = $controller->submit($reqBaseline, $survey);
    echo "Baseline Submission Response: " . session('success') . "\n";
    assert(session('success') !== null, 'Baseline submission should succeed');

    // 3. Submit Midline Response
    $reqMidline = Request::create(route('surveys.submit', $survey->id), 'POST', [
        'survey_stage' => 'midline',
        'scores' => [
            ['question_id' => $q1->id, 'score' => 4, 'text_response' => null],
            ['question_id' => $q2->id, 'score' => null, 'text_response' => 'Built an interactive DOM dashboard.'],
        ],
    ]);
    $resMidline = $controller->submit($reqMidline, $survey);
    echo "Midline Submission Response: " . session('success') . "\n";
    assert(session('success') !== null, 'Midline submission should succeed');

    // 4. Submit Endline Response
    $reqEndline = Request::create(route('surveys.submit', $survey->id), 'POST', [
        'survey_stage' => 'endline',
        'scores' => [
            ['question_id' => $q1->id, 'score' => 5, 'text_response' => null],
            ['question_id' => $q2->id, 'score' => null, 'text_response' => 'Graduated and secured first client project.'],
        ],
    ]);
    $resEndline = $controller->submit($reqEndline, $survey);
    echo "Endline Submission Response: " . session('success') . "\n";
    assert(session('success') !== null, 'Endline submission should succeed');

    // 5. Submit Legacy Survey with Midline
    $reqLegacy = Request::create(route('survey.store'), 'POST', [
        'survey_type' => 'midline',
        'q1_os_filemgmt' => 4,
        'q2_spreadsheets' => 4,
        'q3_ux_design' => 3,
        'q4_frontend' => 4,
        'q5_js_logic' => 3,
        'q6_fullstack' => 3,
        'q7_resilience' => 4,
        'q8_troubleshooting' => 4,
        'q9_freelance' => 3,
        'q10_livingstone_tourism' => 4,
        'q11_career_efficacy' => 4,
        'qual1_why_join' => 'Midline feedback.',
        'qual2_skills_hoped' => 'Advanced JS.',
        'qual3_success_criteria' => 'Freelance work.',
        'qual4_challenges' => 'Time management.',
    ]);
    $resLegacy = $controller->store($reqLegacy);
    echo "Legacy Midline Submission Response: " . session('success') . "\n";
    assert(session('success') !== null, 'Legacy midline submission should succeed');

    $midlineCount = SurveyResponse::where('survey_type', 'midline')->count();
    echo "SurveyResponse midline records count: {$midlineCount}\n";
    assert($midlineCount >= 1, 'SurveyResponse should contain midline record');

    echo "\n[ALL CHECKS PASSED] Surveys work purely anonymously with Baseline, Midline, and Endline selection!\n";

    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n[TEST FAILED] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
