<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\Candidate;
use App\Models\EvaluationScore;
use App\Models\LiteracyScore;
use App\Models\PanelScore;
use App\Models\Question;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelImportExportService
{
    /**
     * Read an uploaded Excel or CSV file into a list of associative rows.
     */
    public function readSpreadsheetRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        // Find the first non-empty row as header row
        $headerRowIndex = null;
        $headers = [];
        foreach ($rows as $index => $row) {
            $cleaned = array_filter(array_map('trim', $row));
            if (!empty($cleaned)) {
                $headerRowIndex = $index;
                $headers = $row;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return [];
        }

        // Normalize header keys (strip trailing (1-5), /20, parentheses, etc.)
        $normalizedHeaders = [];
        foreach ($headers as $colIdx => $h) {
            $withoutParens = preg_replace('/\s*\([^)]*\)/', '', (string)$h);
            $withoutSlash = preg_replace('/\s*\/[0-9]+/', '', $withoutParens);
            $clean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$withoutSlash));
            if ($clean !== '') {
                $normalizedHeaders[$colIdx] = $clean;
            }
        }

        $dataRows = [];
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Check if entire row is empty
            $nonEmpty = array_filter($row, fn($val) => $val !== null && trim((string)$val) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $mapped = [];
            foreach ($normalizedHeaders as $colIdx => $normKey) {
                $mapped[$normKey] = isset($row[$colIdx]) ? trim((string)$row[$colIdx]) : null;
            }
            $dataRows[] = $mapped;
        }

        return $dataRows;
    }

    /**
     * Bulk Import Candidates from Excel/CSV.
     * Expected columns: Name, Gender (Male/Female), Panel (A/B/Unassigned)
     */
    public function importCandidates(UploadedFile $file): array
    {
        $rows = $this->readSpreadsheetRows($file);
        if (empty($rows)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['The uploaded spreadsheet is empty or has no recognizable data rows.'],
            ];
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $lineNum => $row) {
                $name = $row['name'] ?? $row['candidatename'] ?? $row['fullname'] ?? null;
                if (!$name) {
                    continue; // Skip lines without name
                }

                // Gender normalization
                $rawGender = $row['gender'] ?? $row['sex'] ?? 'Female';
                $gender = (stripos($rawGender, 'm') === 0 && stripos($rawGender, 'fe') !== 0) ? 'Male' : 'Female';

                // Panel normalization
                $rawPanel = strtoupper($row['panel'] ?? $row['panelname'] ?? '');
                $panel = in_array($rawPanel, ['A', 'B', 'COVER']) ? $rawPanel : null;

                $existing = Candidate::where('name', $name)->first();
                if ($existing) {
                    $existing->update([
                        'gender' => $gender,
                        'panel' => $panel ?: $existing->panel,
                    ]);
                    $updated++;
                } else {
                    Candidate::create([
                        'name' => $name,
                        'gender' => $gender,
                        'panel' => $panel ?: 'A',
                    ]);
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['Database error while importing candidates: ' . $e->getMessage()],
            ];
        }

        return [
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "Candidate import complete: {$imported} added, {$updated} updated.",
        ];
    }

    /**
     * Bulk Import Panelists from Excel/CSV.
     * Expected columns: Name/Panelist Name, Email, Panel (A/B/Cover), Password (optional), Role (panelist/super)
     */
    public function importPanelists(UploadedFile $file): array
    {
        $rows = $this->readSpreadsheetRows($file);
        if (empty($rows)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['The uploaded spreadsheet is empty or has no recognizable data rows.'],
            ];
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $lineNum => $row) {
                $name = $row['name'] ?? $row['panelistname'] ?? $row['fullname'] ?? null;
                $email = $row['email'] ?? $row['emailaddress'] ?? null;

                if (!$name && !$email) {
                    continue;
                }

                if (!$email) {
                    $email = Str::slug($name) . '@pif.zm';
                }

                $rawPanel = strtoupper($row['panel'] ?? $row['panelname'] ?? 'A');
                $panel = in_array($rawPanel, ['A', 'B', 'COVER']) ? $rawPanel : 'A';

                $rawRole = strtolower($row['role'] ?? 'panelist');
                $role = in_array($rawRole, ['super', 'admin']) ? 'super' : 'panelist';

                $plainPassword = $row['password'] ?? $row['pass'] ?? 'PIF_' . substr(Str::studly($name), 0, 3) . '_2026';

                $user = User::where('email', $email)->first();
                if ($user) {
                    $updateData = [
                        'name' => $name ?: $user->name,
                        'panelist_name' => $name ?: $user->panelist_name,
                        'panel' => $panel,
                        'role' => $role,
                    ];
                    if (!empty($row['password'])) {
                        $updateData['password'] = Hash::make($plainPassword);
                    }
                    $user->update($updateData);
                    $updated++;
                } else {
                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'role' => $role,
                        'panelist_name' => $name,
                        'panel' => $panel,
                    ]);
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['Database error while importing panelists: ' . $e->getMessage()],
            ];
        }

        return [
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "Panelist import complete: {$imported} added, {$updated} updated.",
        ];
    }

    /**
     * Bulk Import Panel Interview Scores from Excel/CSV.
     * Expected columns:
     * - Panelist (Name or Email)
     * - Candidate (Name)
     * - Motivation (1-5)
     * - Availability (1-5)
     * - Resilience (1-5)
     * - Communication (1-5)
     * - Comments (optional)
     */
    public function importInterviewScores(UploadedFile $file): array
    {
        $rows = $this->readSpreadsheetRows($file);
        if (empty($rows)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['The uploaded spreadsheet is empty or has no recognizable data rows.'],
            ];
        }

        $imported = 0;
        $updated = 0;
        $errors = [];
        $hasIsValid = Schema::hasColumn('panel_scores', 'is_valid');

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $candidateName = $row['candidate'] ?? $row['candidatename'] ?? $row['name'] ?? null;
                $panelistIdentifier = $row['panelist'] ?? $row['panelistname'] ?? $row['evaluator'] ?? $row['interviewer'] ?? null;

                if (!$candidateName) {
                    continue;
                }

                // Lookup Candidate (or create if missing)
                $candidate = Candidate::where('name', $candidateName)
                    ->orWhere('name', 'LIKE', '%' . $candidateName . '%')
                    ->first();

                if (!$candidate) {
                    // Create candidate automatically
                    $candidate = Candidate::create([
                        'name' => $candidateName,
                        'gender' => 'Female',
                        'panel' => 'A',
                    ]);
                }

                // Lookup Panelist User
                $panelist = null;
                if ($panelistIdentifier) {
                    $panelist = User::where('panelist_name', $panelistIdentifier)
                        ->orWhere('name', $panelistIdentifier)
                        ->orWhere('email', $panelistIdentifier)
                        ->first();
                }

                if (!$panelist) {
                    // Fallback to current authenticated admin/user
                    $panelist = auth()->user() ?: User::first();
                }

                // Extract Criteria Scores (1 to 5)
                $c1 = $this->parseScore($row, ['motivation', 'crit1motivation', 'passion', 'c1'], 3);
                $c2 = $this->parseScore($row, ['availability', 'crit2availability', 'schedule', 'c2'], 3);
                $c3 = $this->parseScore($row, ['resilience', 'crit3resilience', 'problemsolving', 'grit', 'c3'], 3);
                $c4 = $this->parseScore($row, ['communication', 'crit4communication', 'clarity', 'c4'], 3);

                $comments = $row['comments'] ?? $row['notes'] ?? $row['observations'] ?? null;

                $data = [
                    'crit1_motivation' => $c1,
                    'crit2_availability' => $c2,
                    'crit3_resilience' => $c3,
                    'crit4_communication' => $c4,
                    'comments' => $comments,
                ];

                if ($hasIsValid) {
                    $data['is_valid'] = true;
                }

                $existing = PanelScore::where('panelist_id', $panelist->id)
                    ->where('candidate_id', $candidate->id)
                    ->first();

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    PanelScore::create(array_merge($data, [
                        'panelist_id' => $panelist->id,
                        'candidate_id' => $candidate->id,
                    ]));
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['Database error while importing interview scores: ' . $e->getMessage()],
            ];
        }

        return [
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "Interview scores imported successfully: {$imported} added, {$updated} updated.",
        ];
    }

    /**
     * Bulk Import Literacy Assessment Scores from Excel/CSV.
     * Expected columns:
     * - Candidate (Name)
     * - Assessment Date (optional)
     * - Total Score (0-20) OR Task 1 to Task 10 scores (0-2 each)
     */
    public function importLiteracyScores(UploadedFile $file): array
    {
        $rows = $this->readSpreadsheetRows($file);
        if (empty($rows)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['The uploaded spreadsheet is empty or has no recognizable data rows.'],
            ];
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $candidateName = $row['candidate'] ?? $row['candidatename'] ?? $row['name'] ?? null;
                if (!$candidateName) {
                    continue;
                }

                // Lookup or create candidate
                $candidate = Candidate::where('name', $candidateName)
                    ->orWhere('name', 'LIKE', '%' . $candidateName . '%')
                    ->first();

                if (!$candidate) {
                    $candidate = Candidate::create([
                        'name' => $candidateName,
                        'gender' => 'Female',
                        'panel' => 'A',
                    ]);
                }

                $assessmentDate = $row['assessmentdate'] ?? $row['date'] ?? now()->toDateString();

                // Check if specific task columns are provided (task1_directory ... task10_notepad)
                $taskScores = [];
                $hasDetailedTasks = false;
                $totalFromTasks = 0;

                for ($t = 1; $t <= 10; $t++) {
                    $val = null;
                    foreach ($row as $k => $v) {
                        if (str_starts_with($k, "task{$t}") || str_starts_with($k, "t{$t}")) {
                            $val = is_numeric($v) ? min(2, max(0, (int)$v)) : null;
                            break;
                        }
                    }
                    if ($val !== null) {
                        $hasDetailedTasks = true;
                        $taskField = match($t) {
                            1 => 'task1_directory',
                            2 => 'task2_wordproc',
                            3 => 'task3_research',
                            4 => 'task4_formatting',
                            5 => 'task5_saving',
                            6 => 'task6_spreadsheet',
                            7 => 'task7_screenshot',
                            8 => 'task8_zip',
                            9 => 'task9_sysspecs',
                            10 => 'task10_notepad',
                        };
                        $taskScores[$taskField] = $val;
                        $totalFromTasks += $val;
                    }
                }

                // Total score handling
                $rawTotal = $row['totalscore'] ?? $row['total'] ?? $row['score'] ?? $row['literacyscore'] ?? null;
                $finalTotal = $hasDetailedTasks ? $totalFromTasks : ($rawTotal !== null ? min(20, max(0, (float)$rawTotal)) : 0);

                $payload = array_merge($taskScores, [
                    'assessment_date' => $assessmentDate,
                    'total_score' => $finalTotal,
                ]);

                $existing = LiteracyScore::where('candidate_id', $candidate->id)->first();
                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    LiteracyScore::create(array_merge($payload, [
                        'candidate_id' => $candidate->id,
                    ]));
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['Database error while importing literacy scores: ' . $e->getMessage()],
            ];
        }

        return [
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "Literacy scores imported successfully: {$imported} added, {$updated} updated.",
        ];
    }

    /**
     * Helper to safely extract integer score (bounded between 1 and 5).
     */
    private function parseScore(array $row, array $potentialKeys, int $default = 3): int
    {
        // 1. Direct key matches
        foreach ($potentialKeys as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                return (int) min(5, max(1, round((float)$row[$key])));
            }
        }

        // 2. Fuzzy prefix matches (e.g. "motivation15" starts with "motivation")
        foreach ($row as $rowKey => $rowVal) {
            if (!is_numeric($rowVal)) {
                continue;
            }
            foreach ($potentialKeys as $key) {
                if (str_starts_with($rowKey, $key) || stripos($rowKey, $key) !== false) {
                    return (int) min(5, max(1, round((float)$rowVal)));
                }
            }
        }

        return $default;
    }

    /**
     * Bulk Import Dynamic Assessment, Interview, or Survey Results.
     * Maps Candidate, Evaluator/Panelist, and individual Question scores or text responses.
     */
    public function importAssessmentResults(Assessment $assessment, UploadedFile $file): array
    {
        $rows = $this->readSpreadsheetRows($file);
        if (empty($rows)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['The uploaded spreadsheet is empty or has no recognizable data rows.'],
            ];
        }

        $assessment->load(['questions.options', 'candidates', 'panelists']);
        $questions = $assessment->questions;

        if ($questions->isEmpty()) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['This assessment has no questions configured. Please add questions first.'],
            ];
        }

        $isSurvey = ($assessment->type === 'survey');
        $defaultSurveyStage = $assessment->rule?->rules_json['survey_stage'] ?? 'baseline';

        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // Determine survey stage if assessment is survey
                $surveyStage = $defaultSurveyStage;
                if ($isSurvey) {
                    foreach (['surveystage', 'surveystagebaselinemidlineendline', 'stage', 'surveytype', 'type', 'phase'] as $stKey) {
                        if (!empty($row[$stKey])) {
                            $rawSt = strtolower(trim((string)$row[$stKey]));
                            if (str_contains($rawSt, 'mid')) {
                                $surveyStage = 'midline';
                                break;
                            } elseif (str_contains($rawSt, 'end')) {
                                $surveyStage = 'endline';
                                break;
                            } elseif (str_contains($rawSt, 'base')) {
                                $surveyStage = 'baseline';
                                break;
                            }
                        }
                    }
                }

                // 1. Resolve Candidate / Respondent
                $candidateName = $row['candidate'] ?? $row['candidatename'] ?? $row['name'] ?? $row['candidatefullname']
                    ?? $row['respondentcodeidentifier'] ?? $row['respondentname'] ?? $row['respondent'] ?? null;
                $candidateId = null;

                if ($candidateName) {
                    $candidate = Candidate::where('name', $candidateName)
                        ->orWhere('name', 'LIKE', '%' . $candidateName . '%')
                        ->first();

                    if (!$candidate) {
                        $gender = (isset($row['gender']) && stripos($row['gender'], 'm') === 0 && stripos($row['gender'], 'fe') !== 0) ? 'Male' : 'Female';
                        $rawPanel = strtoupper($row['panel'] ?? $row['panelname'] ?? 'A');
                        $panel = in_array($rawPanel, ['A', 'B', 'COVER']) ? $rawPanel : 'A';

                        $candidate = Candidate::create([
                            'name' => $candidateName,
                            'gender' => $gender,
                            'panel' => $panel,
                        ]);
                    }

                    $candidateId = $candidate->id;

                    // Ensure candidate is assigned to this assessment
                    $assignmentExists = AssessmentAssignment::where('assessment_id', $assessment->id)
                        ->where('candidate_id', $candidateId)
                        ->exists();

                    if (!$assignmentExists) {
                        AssessmentAssignment::create([
                            'assessment_id' => $assessment->id,
                            'candidate_id' => $candidateId,
                            'role' => 'candidate',
                            'panel_name' => $candidate->panel ?: 'A',
                        ]);
                    }
                }

                // 2. Resolve Evaluator / Panelist
                $evaluatorIdentifier = $row['evaluator'] ?? $row['panelist'] ?? $row['evaluatorname'] ?? $row['panelistname'] ?? $row['interviewer'] ?? null;
                $evaluator = null;

                if ($evaluatorIdentifier) {
                    $evaluator = User::where('panelist_name', $evaluatorIdentifier)
                        ->orWhere('name', $evaluatorIdentifier)
                        ->orWhere('email', $evaluatorIdentifier)
                        ->first();
                }

                if (!$evaluator) {
                    $evaluator = auth()->user() ?: User::find(session('admin_user_id')) ?: User::first();
                }

                $evaluatorId = $evaluator?->id;

                // Ensure evaluator is assigned to this assessment if user exists
                if ($evaluatorId) {
                    $evalAssignmentExists = AssessmentAssignment::where('assessment_id', $assessment->id)
                        ->where('user_id', $evaluatorId)
                        ->exists();

                    if (!$evalAssignmentExists) {
                        AssessmentAssignment::create([
                            'assessment_id' => $assessment->id,
                            'user_id' => $evaluatorId,
                            'role' => 'panelist',
                            'panel_name' => $evaluator->panel ?: 'A',
                        ]);
                    }
                }

                // 3. Match each question and record score / response
                foreach ($questions as $qIdx => $question) {
                    $qNum = $qIdx + 1;
                    $qTextClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $question->question_text));

                    $matchedVal = null;
                    $potentialKeys = [
                        "q{$qNum}",
                        "question{$qNum}",
                        "q{$question->id}",
                        "question{$question->id}",
                        $qTextClean,
                    ];

                    foreach ($potentialKeys as $pk) {
                        if (array_key_exists($pk, $row) && $row[$pk] !== null && $row[$pk] !== '') {
                            $matchedVal = $row[$pk];
                            break;
                        }
                    }

                    if ($matchedVal === null) {
                        foreach ($row as $rKey => $rVal) {
                            if ($rVal === null || $rVal === '') continue;
                            if (str_starts_with($rKey, "q{$qNum}") || str_starts_with($rKey, "question{$qNum}")) {
                                $matchedVal = $rVal;
                                break;
                            }
                            if (strlen($qTextClean) > 6 && (str_starts_with($rKey, substr($qTextClean, 0, 10)) || stripos($rKey, substr($qTextClean, 0, 10)) !== false)) {
                                $matchedVal = $rVal;
                                break;
                            }
                        }
                    }

                    if ($matchedVal !== null) {
                        $score = null;
                        $textResponse = null;

                        if ($question->type === 'text') {
                            $textResponse = (string) $matchedVal;
                        } elseif ($question->type === 'boolean') {
                            $str = strtolower(trim((string)$matchedVal));
                            if (in_array($str, ['yes', 'true', '1', 'y', 'pass'])) {
                                $score = 1.0;
                                $textResponse = 'Yes';
                            } elseif (in_array($str, ['no', 'false', '0', 'n', 'fail'])) {
                                $score = 0.0;
                                $textResponse = 'No';
                            } else {
                                $score = is_numeric($matchedVal) ? (float)$matchedVal : null;
                                $textResponse = (string)$matchedVal;
                            }
                        } elseif ($question->type === 'multiple_choice') {
                            $opt = $question->options->first(function($o) use ($matchedVal) {
                                return strcasecmp(trim($o->option_label), trim((string)$matchedVal)) === 0;
                            });
                            if ($opt) {
                                $score = (float) $opt->option_value;
                                $textResponse = $opt->option_label;
                            } elseif (is_numeric($matchedVal)) {
                                $score = (float) $matchedVal;
                            } else {
                                $textResponse = (string) $matchedVal;
                            }
                        } else { // scale
                            $score = is_numeric($matchedVal) ? (float)$matchedVal : null;
                            $textResponse = is_numeric($matchedVal) ? null : (string) $matchedVal;
                        }

                        $scoreQuery = EvaluationScore::where('assessment_id', $assessment->id)
                            ->where('candidate_id', $candidateId)
                            ->where('evaluator_id', $evaluatorId)
                            ->where('question_id', $question->id);

                        if ($isSurvey) {
                            $scoreQuery->where('survey_stage', $surveyStage);
                        }

                        $scoreRecord = $scoreQuery->first();

                        if ($scoreRecord) {
                            $scoreRecord->update([
                                'score' => $score,
                                'text_response' => $textResponse,
                                'survey_stage' => $isSurvey ? $surveyStage : ($scoreRecord->survey_stage ?: 'baseline'),
                            ]);
                            $updated++;
                        } else {
                            EvaluationScore::create([
                                'assessment_id' => $assessment->id,
                                'candidate_id' => $candidateId,
                                'evaluator_id' => $evaluatorId,
                                'question_id' => $question->id,
                                'score' => $score,
                                'survey_stage' => $isSurvey ? $surveyStage : 'baseline',
                                'text_response' => $textResponse,
                            ]);
                            $imported++;
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['Database error while importing results: ' . $e->getMessage()],
            ];
        }

        return [
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "Results for '{$assessment->title}' imported successfully: {$imported} added, {$updated} updated.",
        ];
    }

    /**
     * Download custom spreadsheet template for a specific Assessment, Interview, or Survey.
     */
    public function downloadAssessmentTemplate(Assessment $assessment, string $format = 'xlsx'): StreamedResponse
    {
        $assessment->load([
            'questions.options',
            'candidates',
            'panelists',
            'evaluationScores.candidate',
            'evaluationScores.evaluator',
            'evaluationScores.question',
            'rule',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $assessment->title), 0, 30) ?: 'Results');

        $rules = $assessment->rules ? (is_array($assessment->rules->rules_json) ? $assessment->rules->rules_json : json_decode($assessment->rules->rules_json, true)) : [];
        $isSurvey = $assessment->type === 'survey';
        $isAnonymous = !empty($rules['is_anonymous']);
        $allScores = $assessment->evaluationScores;

        if ($isSurvey) {
            $headers = [
                $isAnonymous ? 'Respondent Identifier' : 'Respondent Name / Student ID',
                'Survey Stage',
                'Submitted Date/Time',
            ];
        } else {
            $headers = ['Evaluator Name / Email', 'Candidate Name', 'Panel', 'Round', 'Selection Status'];
        }

        foreach ($assessment->questions as $idx => $q) {
            $num = $idx + 1;
            $typeHint = match($q->type) {
                'scale' => '1-5',
                'boolean' => 'Yes/No',
                'multiple_choice' => 'Option Label',
                default => 'Text',
            };
            $headers[] = "Q{$num}: {$q->question_text} ({$typeHint})";
        }

        if (!$isSurvey) {
            $headers[] = 'Total Score';
            $headers[] = 'Benchmark Status';
        }

        $exportData = [];

        if ($isSurvey) {
            // Group real survey responses by submission instance
            $submissions = $allScores->groupBy(function ($item) {
                $ts = $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : 'legacy_' . $item->id;
                return ($item->evaluator_id ?: 'anon') . '_' . ($item->candidate_id ?: 'anon') . '_' . ($item->survey_stage ?: 'baseline') . '_' . $ts;
            });

            if ($submissions->isNotEmpty()) {
                $respIndex = 1;
                foreach ($submissions as $subScores) {
                    $firstScore = $subScores->first();
                    $cand = $firstScore->candidate;
                    $respName = $cand ? $cand->name : "Respondent #{$respIndex}";
                    $stageName = ucfirst($firstScore->survey_stage ?: 'Baseline');
                    $dateStr = $firstScore->created_at ? $firstScore->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

                    $row = [$respName, $stageName, $dateStr];

                    foreach ($assessment->questions as $q) {
                        $qScore = $subScores->firstWhere('question_id', $q->id);
                        if ($qScore) {
                            if ($q->type === 'text') {
                                $row[] = $qScore->text_response ?? '';
                            } elseif ($q->type === 'boolean') {
                                $row[] = $qScore->text_response ?? ($qScore->score == 1.0 ? 'Yes' : 'No');
                            } elseif ($q->type === 'multiple_choice') {
                                $row[] = $qScore->text_response ?? ($qScore->score !== null ? (string)$qScore->score : '');
                            } else {
                                $row[] = $qScore->score !== null ? (float)$qScore->score : '';
                            }
                        } else {
                            $row[] = '';
                        }
                    }

                    $exportData[] = $row;
                    $respIndex++;
                }
            } else {
                // If no responses in system yet, provide 1 blank row template
                $blankRow = [$isAnonymous ? 'RESP-001' : 'Student Name', 'Baseline', date('Y-m-d H:i:s')];
                foreach ($assessment->questions as $q) {
                    $blankRow[] = '';
                }
                $exportData[] = $blankRow;
            }
        } else {
            // Group real candidate evaluations
            $evaluations = $allScores->groupBy(function ($item) {
                return ($item->evaluator_id ?: '0') . '_' . ($item->candidate_id ?: '0');
            });

            if ($evaluations->isNotEmpty()) {
                foreach ($evaluations as $evalScores) {
                    $firstScore = $evalScores->first();
                    $evaluatorName = $firstScore->evaluator?->name ?: $firstScore->evaluator?->email ?: 'Evaluator';
                    $cand = $firstScore->candidate;
                    $candName = $cand?->name ?? 'Candidate';
                    $candPanel = 'Panel ' . ($cand?->panel ?: 'A');
                    $candRound = (int)($cand?->round ?? 1);
                    $candStatus = ucfirst($cand?->selection_status ?? 'pending');

                    $row = [$evaluatorName, $candName, $candPanel, $candRound, $candStatus];
                    $totalScore = 0.0;
                    $hasScore = false;

                    foreach ($assessment->questions as $q) {
                        $qScore = $evalScores->firstWhere('question_id', $q->id);
                        if ($qScore) {
                            if ($q->type === 'text') {
                                $row[] = $qScore->text_response ?? '';
                            } elseif ($q->type === 'boolean') {
                                $row[] = $qScore->text_response ?? ($qScore->score == 1.0 ? 'Yes' : 'No');
                                if ($qScore->score !== null) {
                                    $totalScore += (float)$qScore->score;
                                    $hasScore = true;
                                }
                            } elseif ($q->type === 'multiple_choice') {
                                $row[] = $qScore->text_response ?? ($qScore->score !== null ? (string)$qScore->score : '');
                                if ($qScore->score !== null) {
                                    $totalScore += (float)$qScore->score;
                                    $hasScore = true;
                                }
                            } else {
                                $row[] = $qScore->score !== null ? (float)$qScore->score : '';
                                if ($qScore->score !== null) {
                                    $totalScore += (float)$qScore->score;
                                    $hasScore = true;
                                }
                            }
                        } else {
                            $row[] = '';
                        }
                    }

                    $row[] = $hasScore ? round($totalScore, 2) : '';
                    $passThreshold = $assessment->rule?->passing_threshold;
                    if ($passThreshold !== null && $hasScore) {
                        $row[] = $totalScore >= $passThreshold ? 'Passed' : 'Failed';
                    } else {
                        $row[] = 'N/A';
                    }

                    $exportData[] = $row;
                }
            } elseif ($assessment->candidates->isNotEmpty()) {
                // If not scored yet, list assigned candidates with blank score columns
                $evaluatorName = $assessment->panelists->first()?->name ?: 'Panel Evaluator';
                foreach ($assessment->candidates as $cand) {
                    $row = [
                        $evaluatorName,
                        $cand->name,
                        'Panel ' . ($cand->panel ?: 'A'),
                        $cand->round ?? 1,
                        ucfirst($cand->selection_status ?? 'pending')
                    ];
                    foreach ($assessment->questions as $q) {
                        $row[] = '';
                    }
                    $row[] = '';
                    $row[] = 'N/A';
                    $exportData[] = $row;
                }
            } else {
                // Blank template row
                $blankRow = ['Evaluator Name', 'Candidate Name', 'Panel A', 1, 'Pending'];
                foreach ($assessment->questions as $q) {
                    $blankRow[] = '';
                }
                $blankRow[] = '';
                $blankRow[] = 'N/A';
                $exportData[] = $blankRow;
            }
        }

        // Write headers and real data to spreadsheet
        $sheet->fromArray([$headers], null, 'A1');
        if (!empty($exportData)) {
            $sheet->fromArray($exportData, null, 'A2');
        }

        $highestColumn = $sheet->getHighestColumn();
        if ($format === 'xlsx') {
            $headerRange = "A1:{$highestColumn}1";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1A7F4F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            foreach (range('A', $highestColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $safeTitle = Str::slug($assessment->title) ?: 'assessment';
        $filename = "PIF_{$safeTitle}_Results_Export";
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $contentType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $downloadName = "{$filename}.{$extension}";

        return response()->streamDownload(function () use ($spreadsheet, $format) {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->save('php://output');
            } else {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }
        }, $downloadName, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Bulk Import Survey Responses from Excel/CSV (Baseline / Endline Surveys).
     */
    public function importSurveyResponses(UploadedFile $file): array
    {
        $rows = $this->readSpreadsheetRows($file);
        if (empty($rows)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['The uploaded spreadsheet is empty or has no recognizable data rows.'],
            ];
        }

        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rawType = strtolower($row['surveytype'] ?? $row['type'] ?? 'baseline');
                $surveyType = in_array($rawType, ['baseline', 'midline', 'endline']) ? $rawType : 'baseline';

                $data = [
                    'survey_type' => $surveyType,
                    'q1_os_filemgmt' => $this->parseScore($row, ['q1osfilemgmt', 'q1', 'filemgmt'], 3),
                    'q2_spreadsheets' => $this->parseScore($row, ['q2spreadsheets', 'q2', 'spreadsheets', 'excel'], 3),
                    'q3_ux_design' => $this->parseScore($row, ['q3uxdesign', 'q3', 'ux', 'figma'], 3),
                    'q4_frontend' => $this->parseScore($row, ['q4frontend', 'q4', 'html', 'css'], 3),
                    'q5_js_logic' => $this->parseScore($row, ['q5jslogic', 'q5', 'js', 'javascript'], 3),
                    'q6_fullstack' => $this->parseScore($row, ['q6fullstack', 'q6', 'backend', 'fullstack'], 3),
                    'q7_resilience' => $this->parseScore($row, ['q7resilience', 'q7', 'grit', 'persistence'], 3),
                    'q8_troubleshooting' => $this->parseScore($row, ['q8troubleshooting', 'q8', 'debugging', 'errors'], 3),
                    'q9_freelance' => $this->parseScore($row, ['q9freelance', 'q9', 'proposals', 'client'], 3),
                    'q10_livingstone_tourism' => $this->parseScore($row, ['q10livingstonetourism', 'q10', 'tourism', 'livingstone'], 3),
                    'q11_career_efficacy' => $this->parseScore($row, ['q11careerefficacy', 'q11', 'career', 'confidence'], 3),
                    'qual1_why_join' => $row['qual1whyjoin'] ?? $row['whyjoin'] ?? $row['qual1'] ?? null,
                    'qual2_skills_hoped' => $row['qual2skillshoped'] ?? $row['skillshoped'] ?? $row['qual2'] ?? null,
                    'qual3_success_criteria' => $row['qual3successcriteria'] ?? $row['successcriteria'] ?? $row['qual3'] ?? null,
                    'qual4_challenges' => $row['qual4challenges'] ?? $row['challenges'] ?? $row['qual4'] ?? null,
                ];

                SurveyResponse::create($data);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'errors' => ['Database error while importing survey responses: ' . $e->getMessage()],
            ];
        }

        return [
            'success' => true,
            'imported' => $imported,
            'updated' => 0,
            'errors' => $errors,
            'message' => "Survey responses imported successfully: {$imported} responses added.",
        ];
    }

    /**
     * Generate and stream a downloadable template file (Excel or CSV).
     */
    public function downloadTemplate(string $type, string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');

        $headers = [];
        $sampleData = [];
        $filename = '';

        switch ($type) {
            case 'candidates':
                $filename = 'PIF_Candidates_Import_Template';
                $headers = ['Candidate Name', 'Gender', 'Panel'];
                $sampleData = [
                    ['Chileshe Mwape', 'Female', 'Panel A'],
                    ['Kabwe Tembo', 'Male', 'Panel B'],
                    ['Natasha Banda', 'Female', 'Panel A'],
                    ['Robert Phiri', 'Male', 'Panel B'],
                ];
                break;

            case 'panelists':
                $filename = 'PIF_Panelists_Import_Template';
                $headers = ['Panelist Name', 'Email', 'Panel', 'Role', 'Password'];
                $sampleData = [
                    ['Sarah Banda', 'sarah@pif.zm', 'Panel A', 'panelist', 'PIF_Sar_2026'],
                    ['Jacqueline Phiri', 'jacqueline@pif.zm', 'Panel B', 'panelist', 'PIF_Jac_2026'],
                    ['Mutale Kabamba', 'mutale@pif.zm', 'Cover', 'super', 'PIF_Admin_2026'],
                ];
                break;

            case 'interview-scores':
                $filename = 'PIF_Interview_Scores_Import_Template';
                $headers = [
                    'Panelist Name',
                    'Candidate Name',
                    'Motivation (1-5)',
                    'Availability (1-5)',
                    'Resilience (1-5)',
                    'Communication (1-5)',
                    'Comments',
                ];
                $sampleData = [
                    ['Blessing', 'Diana Mungala', 5, 4, 4, 3, 'Strong motivation and clear goals.'],
                    ['Sarah', 'Diana Mungala', 2, 3, 3, 3, 'Good communication.'],
                    ['Florence', 'Emma Banda', 4, 4, 4, 4, 'Excellent project drive.'],
                    ['Mutale', 'Emma Banda', 5, 4, 4, 4, 'Outstanding interview performance.'],
                ];
                break;

            case 'literacy-scores':
                $filename = 'PIF_Literacy_Scores_Import_Template';
                $headers = [
                    'Candidate Name',
                    'Assessment Date',
                    'Task1 Directory (0-2)',
                    'Task2 WordProc (0-2)',
                    'Task3 Research (0-2)',
                    'Task4 Formatting (0-2)',
                    'Task5 Saving (0-2)',
                    'Task6 Spreadsheet (0-2)',
                    'Task7 Screenshot (0-2)',
                    'Task8 Zip (0-2)',
                    'Task9 SysSpecs (0-2)',
                    'Task10 Notepad (0-2)',
                    'Total Score (0-20)',
                ];
                $sampleData = [
                    ['Diana Mungala', date('Y-m-d'), 2, 2, 2, 2, 2, 1, 2, 2, 1, 2, 18],
                    ['Emma Banda', date('Y-m-d'), 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 20],
                    ['Kabwe Tembo', date('Y-m-d'), 1, 2, 2, 1, 2, 1, 1, 2, 1, 1, 14],
                ];
                break;

            case 'surveys':
            case 'survey-responses':
                $filename = 'PIF_Survey_Responses_Import_Template';
                $headers = [
                    'Survey Type (baseline/midline/endline)',
                    'Q1 File Management (1-5)',
                    'Q2 Spreadsheets (1-5)',
                    'Q3 UX Design (1-5)',
                    'Q4 HTML CSS (1-5)',
                    'Q5 JS Logic (1-5)',
                    'Q6 Fullstack (1-5)',
                    'Q7 Resilience (1-5)',
                    'Q8 Troubleshooting (1-5)',
                    'Q9 Freelance (1-5)',
                    'Q10 Tourism Impact (1-5)',
                    'Q11 Career Efficacy (1-5)',
                    'Why Join Training',
                    'Skills Hoped For',
                    'Success Criteria',
                    'Anticipated Challenges',
                ];
                $sampleData = [
                    ['baseline', 3, 3, 2, 2, 1, 1, 4, 3, 2, 4, 3, 'To gain practical tech skills and launch a career.', 'Fullstack web development and freelance client pitching.', 'Being able to build web apps and earn income.', 'Electricity load-shedding and transport costs.'],
                    ['midline', 4, 4, 3, 3, 3, 2, 4, 4, 3, 4, 4, 'Mid-cohort progress update.', 'Responsive layouts and JavaScript DOM manipulation.', 'Building independent portfolio components.', 'Balancing study schedule with family.'],
                    ['endline', 5, 4, 4, 4, 4, 4, 5, 5, 4, 5, 5, 'Graduated from Livingstone Cohort.', 'Mastered HTML/CSS/JS and database design.', 'Secured first freelance project in tourism sector.', 'Managed challenges through group peer work.'],
                ];
                break;

            default:
                abort(404, 'Unknown template type.');
        }

        // Add headers
        $sheet->fromArray([$headers], null, 'A1');

        // Add sample data
        if (!empty($sampleData)) {
            $sheet->fromArray($sampleData, null, 'A2');
        }

        // Styling for Excel
        if ($format === 'xlsx') {
            $highestColumn = $sheet->getHighestColumn();
            $headerRange = "A1:{$highestColumn}1";

            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1A7F4F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Auto-size columns
            foreach (range('A', $highestColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $contentType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $downloadName = "{$filename}.{$extension}";

        return response()->streamDownload(function () use ($spreadsheet, $format) {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->save('php://output');
            } else {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }
        }, $downloadName, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
