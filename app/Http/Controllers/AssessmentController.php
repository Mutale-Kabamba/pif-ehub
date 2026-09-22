<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentRule;
use App\Models\Candidate;
use App\Models\EvaluationScore;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\EvaluationService;
use App\Services\ExcelImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentController extends Controller
{
    protected EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Display a listing of assessments.
     * Admins see all assessments; Panelists see only assigned assessments.
     */
    public function index(Request $request): View
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));

        $query = Assessment::withCount(['questions', 'panelists', 'candidates']);

        // Non-admin panelists only see assigned assessments
        if ($currentUser && ! $currentUser->isSuper()) {
            $query->whereHas('assignments', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $assessments = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('assessments.index', compact('assessments'));
    }

    /**
     * Show the form for creating a new assessment.
     */
    public function create(): View
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only administrators can create new assessments.');
        }

        $panelists = User::where('role', 'panelist')
            ->orWhere('role', 'super')
            ->orderBy('name')
            ->get();

        $candidates = Candidate::orderBy('name')->get();
        $suggestedKey = 'KEY-' . Str::upper(Str::random(6));

        return view('assessments.create', compact('panelists', 'candidates', 'suggestedKey'));
    }

    /**
     * Store a newly created assessment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only administrators can create assessments.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:interview,survey,assessment',
            'status' => 'required|in:draft,active,completed',
            'access_key' => 'nullable|string|max:50|unique:assessments,access_key',
            'number_of_panels' => 'nullable|integer|min:1',
            'max_panelists' => 'nullable|integer|min:1',
            'score_cap' => 'nullable|numeric|min:0',
            'passing_threshold' => 'nullable|numeric|min:0',
            'rules_json' => 'nullable|string',
            'panelists' => 'nullable|array',
            'new_panelists' => 'nullable|array',
            'new_panelists.*.name' => 'required_with:new_panelists|string|max:255',
            'new_panelists.*.email' => 'required_with:new_panelists|email|unique:users,email',
            'new_panelists.*.password' => 'required_with:new_panelists|string|min:6',
            'new_panelists.*.panel_name' => 'nullable|string|max:50',
            'candidates' => 'nullable|array',
            'new_candidates' => 'nullable|array',
            'new_candidates.*.name' => 'required_with:new_candidates|string|max:255',
            'new_candidates.*.gender' => 'nullable|in:Male,Female',
            'new_candidates.*.panel_name' => 'nullable|string|max:50',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:scale,text,multiple_choice,boolean',
            'questions.*.weight' => 'nullable|numeric|min:0',
            'questions.*.order' => 'nullable|integer',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.option_label' => 'required|string',
            'questions.*.options.*.option_value' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Generate access key if empty
            $accessKey = !empty($validated['access_key']) ? Str::upper($validated['access_key']) : 'KEY-' . Str::upper(Str::random(6));

            // 1. Create Assessment
            $assessment = Assessment::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'status' => $validated['status'],
                'access_key' => $accessKey,
            ]);

            // 2. Create Assessment Rule
            $rulesData = [];
            if (!empty($validated['rules_json'])) {
                $decoded = json_decode($validated['rules_json'], true);
                if (is_array($decoded)) {
                    $rulesData = $decoded;
                }
            }
            if (!empty($validated['number_of_panels'])) {
                $rulesData['number_of_panels'] = (int) $validated['number_of_panels'];
            }

            AssessmentRule::create([
                'assessment_id' => $assessment->id,
                'max_panelists' => $validated['max_panelists'] ?? null,
                'score_cap' => $validated['score_cap'] ?? null,
                'passing_threshold' => $validated['passing_threshold'] ?? null,
                'rules_json' => $rulesData,
            ]);

            // 3. Create Questions & Options
            foreach ($validated['questions'] as $idx => $qData) {
                $question = Question::create([
                    'assessment_id' => $assessment->id,
                    'question_text' => $qData['question_text'],
                    'type' => $qData['type'],
                    'weight' => $qData['weight'] ?? 1.00,
                    'order' => $qData['order'] ?? ($idx + 1),
                ]);

                if (!empty($qData['options']) && in_array($qData['type'], ['multiple_choice', 'boolean'])) {
                    foreach ($qData['options'] as $optData) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_label' => $optData['option_label'],
                            'option_value' => $optData['option_value'] ?? 0.00,
                        ]);
                    }
                }
            }

            // 4. Assign Existing Panelists (supports both flat IDs and object maps)
            $panelistInputs = $request->input('panelists', []);
            if (!empty($panelistInputs)) {
                foreach ($panelistInputs as $pData) {
                    $userId = is_array($pData) ? ($pData['user_id'] ?? null) : $pData;
                    if ($userId && User::where('id', $userId)->exists()) {
                        $user = User::find($userId);
                        $panelName = is_array($pData) ? ($pData['panel_name'] ?? $user?->panel) : $user?->panel;
                        AssessmentAssignment::create([
                            'assessment_id' => $assessment->id,
                            'user_id' => $userId,
                            'role' => 'panelist',
                            'panel_name' => $panelName ?: 'A',
                        ]);
                    }
                }
            }

            // 5. Register & Assign New Panelists
            if (!empty($validated['new_panelists'])) {
                foreach ($validated['new_panelists'] as $npData) {
                    $newUser = User::create([
                        'name' => $npData['name'],
                        'email' => $npData['email'],
                        'password' => Hash::make($npData['password']),
                        'role' => 'panelist',
                        'panelist_name' => $npData['name'],
                        'panel' => $npData['panel_name'] ?? 'A',
                    ]);

                    AssessmentAssignment::create([
                        'assessment_id' => $assessment->id,
                        'user_id' => $newUser->id,
                        'role' => 'panelist',
                        'panel_name' => $npData['panel_name'] ?? 'A',
                    ]);
                }
            }

            // 6. Assign Existing Candidates (supports both flat IDs and object maps)
            $candidateInputs = $request->input('candidates', []);
            if (!empty($candidateInputs)) {
                foreach ($candidateInputs as $cData) {
                    $candidateId = is_array($cData) ? ($cData['candidate_id'] ?? null) : $cData;
                    if ($candidateId && Candidate::where('id', $candidateId)->exists()) {
                        $candidate = Candidate::find($candidateId);
                        $panelName = is_array($cData) ? ($cData['panel_name'] ?? $candidate?->panel) : $candidate?->panel;
                        AssessmentAssignment::create([
                            'assessment_id' => $assessment->id,
                            'candidate_id' => $candidateId,
                            'role' => 'candidate',
                            'panel_name' => $panelName ?: 'A',
                        ]);
                    }
                }
            }

            // 7. Register & Assign New Candidates
            if (!empty($validated['new_candidates'])) {
                foreach ($validated['new_candidates'] as $ncData) {
                    $newCandidate = Candidate::create([
                        'name' => $ncData['name'],
                        'gender' => $ncData['gender'] ?? 'Male',
                        'panel' => $ncData['panel_name'] ?? 'A',
                    ]);

                    AssessmentAssignment::create([
                        'assessment_id' => $assessment->id,
                        'candidate_id' => $newCandidate->id,
                        'role' => 'candidate',
                        'panel_name' => $ncData['panel_name'] ?? 'A',
                    ]);
                }
            }
        });

        return redirect()->route('assessments.index')
            ->with('success', 'Assessment created successfully!');
    }

    /**
     * Display the specified assessment details and evaluation results.
     */
    public function show(Assessment $assessment): View
    {
        $results = $this->evaluationService->compileAssessmentResults($assessment);
        $allCandidates = Candidate::orderBy('name')->get();

        return view('assessments.show', compact('assessment', 'results', 'allCandidates'));
    }

    /**
     * Show the form for editing the specified assessment.
     */
    public function edit(Assessment $assessment): View
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only administrators can edit assessments.');
        }

        $assessment->load(['questions.options', 'rule', 'panelists', 'candidates', 'assignments']);

        $panelists = User::where('role', 'panelist')
            ->orWhere('role', 'super')
            ->orderBy('name')
            ->get();

        $candidates = Candidate::orderBy('name')->get();

        return view('assessments.edit', compact('assessment', 'panelists', 'candidates'));
    }

    /**
     * Update the specified assessment in storage.
     */
    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only administrators can update assessments.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:interview,survey,assessment',
            'status' => 'required|in:draft,active,completed',
            'access_key' => 'nullable|string|max:50|unique:assessments,access_key,' . $assessment->id,
            'number_of_panels' => 'nullable|integer|min:1',
            'max_panelists' => 'nullable|integer|min:1',
            'score_cap' => 'nullable|numeric|min:0',
            'passing_threshold' => 'nullable|numeric|min:0',
            'rules_json' => 'nullable|string',
            'panelists' => 'nullable|array',
            'new_panelists' => 'nullable|array',
            'new_panelists.*.name' => 'required_with:new_panelists|string|max:255',
            'new_panelists.*.email' => 'required_with:new_panelists|email|unique:users,email',
            'new_panelists.*.password' => 'required_with:new_panelists|string|min:6',
            'new_panelists.*.panel_name' => 'nullable|string|max:50',
            'candidates' => 'nullable|array',
            'new_candidates' => 'nullable|array',
            'new_candidates.*.name' => 'required_with:new_candidates|string|max:255',
            'new_candidates.*.gender' => 'nullable|in:Male,Female',
            'new_candidates.*.panel_name' => 'nullable|string|max:50',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:scale,text,multiple_choice,boolean',
            'questions.*.weight' => 'nullable|numeric|min:0',
            'questions.*.order' => 'nullable|integer',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.option_label' => 'required|string',
            'questions.*.options.*.option_value' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated, $assessment, $request) {
            // 1. Update Assessment
            $accessKey = !empty($validated['access_key']) ? Str::upper($validated['access_key']) : ($assessment->access_key ?: 'KEY-' . Str::upper(Str::random(6)));

            $assessment->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'status' => $validated['status'],
                'access_key' => $accessKey,
            ]);

            // 2. Update/Create Rule
            $rulesData = $assessment->rule?->rules_json ?? [];
            if (!empty($validated['rules_json'])) {
                $decoded = json_decode($validated['rules_json'], true);
                if (is_array($decoded)) {
                    $rulesData = array_merge($rulesData, $decoded);
                }
            }
            if (!empty($validated['number_of_panels'])) {
                $rulesData['number_of_panels'] = (int) $validated['number_of_panels'];
            }

            $assessment->rule()->updateOrCreate(
                ['assessment_id' => $assessment->id],
                [
                    'max_panelists' => $validated['max_panelists'] ?? null,
                    'score_cap' => $validated['score_cap'] ?? null,
                    'passing_threshold' => $validated['passing_threshold'] ?? null,
                    'rules_json' => $rulesData,
                ]
            );

            // 3. Update Questions & Options
            $assessment->questions()->delete();

            foreach ($validated['questions'] as $idx => $qData) {
                $question = Question::create([
                    'assessment_id' => $assessment->id,
                    'question_text' => $qData['question_text'],
                    'type' => $qData['type'],
                    'weight' => $qData['weight'] ?? 1.00,
                    'order' => $qData['order'] ?? ($idx + 1),
                ]);

                if (!empty($qData['options']) && in_array($qData['type'], ['multiple_choice', 'boolean'])) {
                    foreach ($qData['options'] as $optData) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_label' => $optData['option_label'],
                            'option_value' => $optData['option_value'] ?? 0.00,
                        ]);
                    }
                }
            }

            // 4. Sync Assignments (delete old and re-attach)
            $assessment->assignments()->delete();

            // Existing Panelists
            $panelistInputs = $request->input('panelists', []);
            if (!empty($panelistInputs)) {
                foreach ($panelistInputs as $pData) {
                    $userId = is_array($pData) ? ($pData['user_id'] ?? null) : $pData;
                    if ($userId && User::where('id', $userId)->exists()) {
                        $user = User::find($userId);
                        $panelName = is_array($pData) ? ($pData['panel_name'] ?? $user?->panel) : $user?->panel;
                        AssessmentAssignment::create([
                            'assessment_id' => $assessment->id,
                            'user_id' => $userId,
                            'role' => 'panelist',
                            'panel_name' => $panelName ?: 'A',
                        ]);
                    }
                }
            }

            // New Panelists
            if (!empty($validated['new_panelists'])) {
                foreach ($validated['new_panelists'] as $npData) {
                    $newUser = User::create([
                        'name' => $npData['name'],
                        'email' => $npData['email'],
                        'password' => Hash::make($npData['password']),
                        'role' => 'panelist',
                        'panelist_name' => $npData['name'],
                        'panel' => $npData['panel_name'] ?? 'A',
                    ]);

                    AssessmentAssignment::create([
                        'assessment_id' => $assessment->id,
                        'user_id' => $newUser->id,
                        'role' => 'panelist',
                        'panel_name' => $npData['panel_name'] ?? 'A',
                    ]);
                }
            }

            // Existing Candidates
            $candidateInputs = $request->input('candidates', []);
            if (!empty($candidateInputs)) {
                foreach ($candidateInputs as $cData) {
                    $candidateId = is_array($cData) ? ($cData['candidate_id'] ?? null) : $cData;
                    if ($candidateId && Candidate::where('id', $candidateId)->exists()) {
                        $candidate = Candidate::find($candidateId);
                        $panelName = is_array($cData) ? ($cData['panel_name'] ?? $candidate?->panel) : $candidate?->panel;
                        AssessmentAssignment::create([
                            'assessment_id' => $assessment->id,
                            'candidate_id' => $candidateId,
                            'role' => 'candidate',
                            'panel_name' => $panelName ?: 'A',
                        ]);
                    }
                }
            }

            // New Candidates
            if (!empty($validated['new_candidates'])) {
                foreach ($validated['new_candidates'] as $ncData) {
                    $newCandidate = Candidate::create([
                        'name' => $ncData['name'],
                        'gender' => $ncData['gender'] ?? 'Male',
                        'panel' => $ncData['panel_name'] ?? 'A',
                    ]);

                    AssessmentAssignment::create([
                        'assessment_id' => $assessment->id,
                        'candidate_id' => $newCandidate->id,
                        'role' => 'candidate',
                        'panel_name' => $ncData['panel_name'] ?? 'A',
                    ]);
                }
            }
        });

        return redirect()->route('assessments.show', $assessment->id)
            ->with('success', 'Assessment updated successfully!');
    }

    /**
     * Remove the specified assessment from storage.
     */
    public function destroy(Assessment $assessment): RedirectResponse
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only administrators can delete assessments.');
        }

        $assessment->delete();

        return redirect()->route('assessments.index')
            ->with('success', 'Assessment deleted successfully.');
    }

    /**
     * Render evaluation grading sheet for assigned candidates.
     * Filters candidate list based on logged-in evaluator's assigned panel!
     */
    public function evaluateForm(Request $request, Assessment $assessment): View
    {
        $assessment->load(['questions.options', 'candidates', 'panelists', 'rule', 'assignments']);

        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));

        // Determine evaluator's assigned panel for this assessment
        $assignedPanel = null;
        if ($currentUser && ! $currentUser->isSuper()) {
            $userAssignment = $assessment->assignments
                ->where('user_id', $currentUser->id)
                ->where('role', 'panelist')
                ->first();

            $assignedPanel = $userAssignment?->panel_name ?: $currentUser->panel;
        }

        // Filter candidates assigned to this assessment based on evaluator's panel
        $assignedCandidates = $assessment->candidates;
        if ($assignedPanel && $assignedPanel !== 'cover') {
            // Filter by assignment panel_name or candidate default panel
            $assignedCandidates = $assignedCandidates->filter(function ($cand) use ($assessment, $assignedPanel) {
                $candAssignment = $assessment->assignments
                    ->where('candidate_id', $cand->id)
                    ->where('role', 'candidate')
                    ->first();

                $candPanel = $candAssignment?->panel_name ?: $cand->panel;
                return $candPanel === $assignedPanel;
            });
        }

        $candidateId = $request->query('candidate_id');
        $selectedCandidate = null;

        if ($candidateId) {
            $selectedCandidate = $assignedCandidates->firstWhere('id', $candidateId) ?: Candidate::find($candidateId);
        } elseif ($assignedCandidates->isNotEmpty()) {
            $selectedCandidate = $assignedCandidates->first();
        }

        // Fetch existing evaluation scores
        $evaluatorId = $currentUser?->id ?: session('admin_user_id');
        $existingScores = collect();
        if ($evaluatorId && $selectedCandidate) {
            $existingScores = EvaluationScore::where('assessment_id', $assessment->id)
                ->where('candidate_id', $selectedCandidate->id)
                ->where('evaluator_id', $evaluatorId)
                ->get()
                ->keyBy('question_id');
        }

        return view('assessments.evaluate', compact(
            'assessment',
            'assignedCandidates',
            'selectedCandidate',
            'existingScores',
            'assignedPanel'
        ));
    }

    /**
     * Store evaluation scores for an assessment.
     */
    public function submitEvaluation(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'candidate_id' => 'nullable|exists:candidates,id',
            'scores' => 'required|array',
            'scores.*.question_id' => 'required|exists:questions,id',
            'scores.*.score' => 'nullable|numeric',
            'scores.*.text_response' => 'nullable|string',
        ]);

        $evaluatorId = auth()->id() ?: session('admin_user_id');
        $candidateId = $validated['candidate_id'] ?? null;

        DB::transaction(function () use ($validated, $assessment, $evaluatorId, $candidateId) {
            foreach ($validated['scores'] as $qScore) {
                $questionId = $qScore['question_id'];
                $scoreVal = isset($qScore['score']) ? (float) $qScore['score'] : null;
                $textVal = $qScore['text_response'] ?? null;

                EvaluationScore::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'candidate_id' => $candidateId,
                        'evaluator_id' => $evaluatorId,
                        'question_id' => $questionId,
                    ],
                    [
                        'score' => $scoreVal,
                        'text_response' => $textVal,
                    ]
                );
            }
        });

        return redirect()->route('assessments.show', $assessment->id)
            ->with('success', 'Evaluation submitted successfully!');
    }

    /**
     * Download custom assessment spreadsheet template.
     */
    public function downloadTemplate(Request $request, Assessment $assessment, ExcelImportExportService $excelService): StreamedResponse
    {
        $format = $request->query('format', 'xlsx');
        return $excelService->downloadAssessmentTemplate($assessment, $format);
    }

    /**
     * Import results from Excel or CSV for this specific assessment.
     */
    public function importResults(Request $request, Assessment $assessment, ExcelImportExportService $excelService): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $result = $excelService->importAssessmentResults($assessment, $request->file('file'));

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', implode(' ', $result['errors'] ?? ['Failed to import results.']));
        }

        return redirect()->route('assessments.show', [$assessment->id, 'tab' => 'results'])
            ->with('success', $result['message']);
    }

    /**
     * Add existing or new candidates to this assessment.
     */
    public function addCandidates(Request $request, Assessment $assessment): RedirectResponse
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only administrators can assign candidates.');
        }

        $validated = $request->validate([
            'candidate_ids' => 'nullable|array',
            'candidate_ids.*' => 'exists:candidates,id',
            'panel_name' => 'nullable|string|max:50',
            'new_candidate_name' => 'nullable|string|max:255',
            'new_candidate_gender' => 'nullable|in:Male,Female',
            'new_candidate_panel' => 'nullable|string|max:50',
        ]);

        $addedCount = 0;

        DB::transaction(function () use ($assessment, $validated, &$addedCount) {
            // 1. Assign selected existing candidates
            if (!empty($validated['candidate_ids'])) {
                foreach ($validated['candidate_ids'] as $candidateId) {
                    $existing = AssessmentAssignment::where('assessment_id', $assessment->id)
                        ->where('candidate_id', $candidateId)
                        ->first();

                    if (!$existing) {
                        $cand = Candidate::find($candidateId);
                        AssessmentAssignment::create([
                            'assessment_id' => $assessment->id,
                            'candidate_id' => $candidateId,
                            'role' => 'candidate',
                            'panel_name' => $validated['panel_name'] ?: ($cand?->panel ?: 'A'),
                        ]);
                        $addedCount++;
                    }
                }
            }

            // 2. Register & assign new candidate if provided
            if (!empty($validated['new_candidate_name'])) {
                $newCand = Candidate::create([
                    'name' => trim($validated['new_candidate_name']),
                    'gender' => $validated['new_candidate_gender'] ?? 'Female',
                    'panel' => $validated['new_candidate_panel'] ?? 'A',
                ]);

                AssessmentAssignment::create([
                    'assessment_id' => $assessment->id,
                    'candidate_id' => $newCand->id,
                    'role' => 'candidate',
                    'panel_name' => $validated['new_candidate_panel'] ?? 'A',
                ]);
                $addedCount++;
            }
        });

        return redirect()->route('assessments.show', [$assessment->id, 'tab' => 'candidates'])
            ->with('success', "{$addedCount} candidate(s) successfully assigned to this assessment.");
    }

    /**
     * Remove candidate from this assessment.
     */
    public function removeCandidate(Assessment $assessment, Candidate $candidate): RedirectResponse
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only administrators can remove candidates.');
        }

        DB::transaction(function () use ($assessment, $candidate) {
            // Remove assignment
            AssessmentAssignment::where('assessment_id', $assessment->id)
                ->where('candidate_id', $candidate->id)
                ->delete();

            // Clean up evaluation scores for this assessment and candidate
            EvaluationScore::where('assessment_id', $assessment->id)
                ->where('candidate_id', $candidate->id)
                ->delete();
        });

        return redirect()->route('assessments.show', [$assessment->id, 'tab' => 'candidates'])
            ->with('success', "Candidate '{$candidate->name}' removed from this assessment.");
    }
}
