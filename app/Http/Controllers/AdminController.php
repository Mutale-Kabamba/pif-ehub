<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\LiteracyScore;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    private array $quantQuestions = [
        'q1_os_filemgmt'         => "I can independently manage digital file directories...",
        'q2_spreadsheets'        => "I feel confident using spreadsheet software...",
        'q3_ux_design'           => "I can translate a product idea into user-friendly...",
        'q4_frontend'            => "I am confident in my ability to write clean...",
        'q5_js_logic'            => "I feel capable of writing custom JavaScript logic...",
        'q6_fullstack'           => "I understand how back-end servers, databases...",
        'q7_resilience'          => "When my design or code fails to work, I view it...",
        'q8_troubleshooting'     => "I can independently find solutions to technical errors...",
        'q9_freelance'           => "I feel equipped to write professional project proposals...",
        'q10_livingstone_tourism' => "I can easily identify real-world business bottlenecks...",
        'q11_career_efficacy'    => "I feel confident that the digital and administrative...",
    ];

    private array $qualQuestions = [
        'qual1_why_join'       => "Why did you decide to join this programme?",
        'qual2_skills_hoped'   => "What skills or knowledge are you hoping to gain...",
        'qual3_success_criteria' => "At the end of this programme, what would make you...",
        'qual4_challenges'     => "What challenges do you anticipate might make it...",
    ];

    private array $literacyTasks = [
        'task1_directory'   => "1. Directory Setup: On Desktop, create a folder named 'PIF_Project_[Your Name]'. Inside, create sub-folder 'Assets'.",
        'task2_wordproc'    => "2. Word Processing: Open MS Word and type a short 3-sentence paragraph describing a mobile app/website concept.",
        'task3_research'    => "3. Web Research: Search definition of 'HTML' or 'User Experience (UX)' and copy/paste it below paragraph.",
        'task4_formatting'  => "4. Formatting: Make the definition Bold and create a bulleted list of 3 features your app/website would have.",
        'task5_saving'      => "5. File Saving: Save this Word document directly into your main project folder, naming the file 'App_Concept'.",
        'task6_spreadsheet' => "6. Spreadsheet / Data: Create 'User Database' table in Excel with headers: ID, Username, Email. Add 2 fake users.",
        'task7_screenshot'  => "7. Visual Asset Capture: Take a desktop screenshot. Save it as an image file directly into the 'Assets' sub-folder.",
        'task8_zip'         => "8. File Compression: Right-click main project folder and compress (Zip) it into a single zipped folder.",
        'task9_sysspecs'    => "9. System Specifications: Navigate to System Settings. State Installed RAM size and OS version accurately.",
        'task10_notepad'    => "10. Plain Text / Code Prep: Open Notepad. Type exactly: &lt;h1&gt;Hello World&lt;/h1&gt;. Save to Desktop as 'index.html'.",
    ];

    private array $interviewCriteria = [
        'crit1_motivation'    => "Passion/Motivation (Genuine interest vs. just wanting...)",
        'crit2_availability'  => "Availability (Clear schedule, no conflicting commitments)",
        'crit3_resilience'    => "Resilience/Problem Solving (Shows grit and willingness...)",
        'crit4_communication' => "Communication Skills (Articulates thoughts clearly)",
    ];

    /**
     * Show the admin login form.
     */
    public function loginForm(): \Illuminate\View\View
    {
        $panelists = User::where('role', 'panelist')
            ->orderBy('panelist_name')
            ->pluck('panelist_name', 'id')
            ->toArray();

        return view('admin.login', [
            'panelists' => $panelists,
        ]);
    }

    /**
     * Process the admin login request.
     */
    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'role'     => 'required|string',
            'password' => 'required|string',
        ]);

        $roleInput = $validated['role'];
        $password  = $validated['password'];

        // Determine the user based on role selection
        if ($roleInput === 'Super User') {
            $user = User::where('role', 'super')->first();
        } else {
            $user = User::where('role', 'panelist')
                ->where('panelist_name', $roleInput)
                ->first();
        }

        if ($user === null || ! Hash::check($password, $user->password)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid credentials. Please try again.');
        }

        $request->session()->put('admin_user_id', $user->id);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Welcome, {$user->name}!");
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->forget('admin_user_id');

        return redirect()
            ->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard(Request $request): \Illuminate\View\View
    {
        /** @var User $user */
        $user = User::find($request->session()->get('admin_user_id'));

        $tab = $request->query('tab', $user->isSuper() ? 'leaderboard' : 'panel');

        // All candidates (used for literacy, leaderboard, etc.)
        $candidates = Candidate::orderBy('name')->get();

        // Candidates filtered to the logged-in user's panel (for panel evaluation tab)
        if ($user->panel && $user->panel !== 'cover') {
            $panelCandidates = Candidate::where('panel', $user->panel)->orderBy('name')->get();
        } else {
            $panelCandidates = $candidates; // cover / no panel → see all
        }

        $viewData = [
            'user'              => $user,
            'tab'               => $tab,
            'candidates'        => $candidates,
            'panelCandidates'   => $panelCandidates,
            'quantQuestions'    => $this->quantQuestions,
            'qualQuestions'     => $this->qualQuestions,
            'literacyTasks'     => $this->literacyTasks,
            'interviewCriteria' => $this->interviewCriteria,
        ];

        // Load leaderboard data when on leaderboard tab
        if ($tab === 'leaderboard' && $user->isSuper()) {
            $viewData['leaderboard'] = LeaderboardController::getLeaderboardData();
        }

        // Load analytics data when on analytics tab
        if ($tab === 'analytics' && $user->isSuper()) {
            $viewData = array_merge($viewData, $this->getAnalyticsData());
        }

        return view('admin.dashboard', $viewData);
    }

    /**
     * Get analytics data as an array.
     */
    private function getAnalyticsData(): array
    {
        $totalResponses = SurveyResponse::count();
        $baselineCount  = SurveyResponse::where('survey_type', 'baseline')->count();
        $endlineCount   = SurveyResponse::where('survey_type', 'endline')->count();

        // Compute averages per question, grouped by survey_type
        $quantKeys = array_keys($this->quantQuestions);
        $avgSelect = [];
        foreach ($quantKeys as $key) {
            $avgSelect[] = "AVG({$key}) as {$key}";
        }

        $rawScores = SurveyResponse::select(
            'survey_type',
            DB::raw(implode(', ', $avgSelect))
        )
            ->groupBy('survey_type')
            ->get()
            ->keyBy('survey_type')
            ->toArray();

        // Reformat: $avgScores[q_key][survey_type] = avg_value
        $avgScores = [];
        foreach ($quantKeys as $key) {
            $avgScores[$key] = [
                'baseline' => isset($rawScores['baseline'][$key]) ? round((float) $rawScores['baseline'][$key], 2) : 0,
                'endline'  => isset($rawScores['endline'][$key])  ? round((float) $rawScores['endline'][$key], 2)  : 0,
            ];
        }

        return [
            'totalResponses' => $totalResponses,
            'baselineCount'  => $baselineCount,
            'endlineCount'   => $endlineCount,
            'avgScores'      => $avgScores,
            'quantQuestions' => $this->quantQuestions,
        ];
    }

    /**
     * Show survey analytics (redirects to dashboard with tab).
     */
    public function analytics(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.dashboard', ['tab' => 'analytics']);
    }

    /**
     * Show the literacy assessment form (redirects to dashboard with tab).
     */
    public function literacyForm(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.dashboard', ['tab' => 'literacy']);
    }

    /**
     * Store or update literacy scores for a candidate.
     */
    public function literacyStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'candidate_id'     => 'required|exists:candidates,id',
            'assessment_date'  => 'required|date',
        ];

        foreach (array_keys($this->literacyTasks) as $key) {
            $rules[$key] = 'required|integer|between:0,2';
        }

        $validated = $request->validate($rules);

        $candidateId = $validated['candidate_id'];
        $taskScores = [];
        $totalScore = 0;

        foreach (array_keys($this->literacyTasks) as $key) {
            $value = (int) $validated[$key];
            $taskScores[$key] = $value;
            $totalScore += $value;
        }

        LiteracyScore::updateOrCreate(
            ['candidate_id' => $candidateId],
            array_merge($taskScores, [
                'assessment_date' => $validated['assessment_date'],
                'total_score'     => $totalScore,
            ])
        );

        return redirect()
            ->back()
            ->with('success', 'Literacy assessment saved successfully.');
    }
}
