<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\EvaluationScore;
use App\Models\Question;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SurveyController extends Controller
{
    /**
     * Quantitative survey questions (baseline static questions).
     */
    private static array $quantQuestions = [
        'q1_os_filemgmt'         => "I can independently manage digital file directories (create folders, move files, copy/paste, delete, and rename files) on a Windows computer without supervision.",
        'q2_spreadsheets'        => "I feel confident using spreadsheet software (like Microsoft Excel or Google Sheets) to enter data, format cells, sort and filter information, and create simple tables for business record-keeping.",
        'q3_ux_design'           => "I can translate a product idea into user-friendly interface designs using tools like Figma, Canva, or similar design platforms to create website and mobile app prototypes.",
        'q4_frontend'            => "I am confident in my ability to write clean, well-structured HTML and CSS code to build responsive web pages that look good on both desktop and mobile devices.",
        'q5_js_logic'            => "I feel capable of writing custom JavaScript logic to handle interactive web features like form validations, dynamic content updates, and basic web animations.",
        'q6_fullstack'           => "I understand how back-end servers, databases (like MySQL), and front-end web technologies connect to deliver complete full-stack web applications.",
        'q7_resilience'          => "When my design or code fails to work, I view it as a learning opportunity and persist through debugging until I find a solution.",
        'q8_troubleshooting'     => "I can independently find solutions to technical errors by reading documentation, watching tutorials, testing different approaches, and knowing when to ask for help online.",
        'q9_freelance'           => "I feel equipped to write professional project proposals, estimate timelines, manage client expectations, and deliver web development or design work as a freelancer.",
        'q10_livingstone_tourism' => "I can easily identify real-world business bottlenecks in Livingstone's tourism and digital service sectors and propose practical, tech-driven solutions to improve efficiency.",
        'q11_career_efficacy'    => "I feel confident that the digital and administrative skills I am gaining in this training will make me competitive for remote online work and local tech-enabled jobs.",
    ];

    /**
     * Qualitative survey questions.
     */
    private static array $qualQuestions = [
        'qual1_why_join'       => "Why did you decide to join this training? What do you hope to achieve by the end of it?",
        'qual2_skills_hoped'   => "What skills or knowledge are you hoping to gain from this training? Be as specific as possible.",
        'qual3_success_criteria' => "At the end of this training, what would make you feel it was successful for you personally?",
        'qual4_challenges'     => "What challenges do you anticipate might make it harder for you to complete this training? How do you plan to overcome them?",
    ];

    /**
     * Render the Landing Page (Dual section: Auth Login & Survey Portal CTA).
     */
    public function landing(): View
    {
        return view('landing');
    }

    /**
     * Display Public Survey Gallery (Card listing of active surveys & assessments).
     */
    public function index(): View
    {
        $surveys = Assessment::where('status', 'active')
            ->whereIn('type', ['survey', 'assessment', 'interview'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('survey.gallery', [
            'surveys' => $surveys,
            'quantQuestions' => self::$quantQuestions,
            'qualQuestions' => self::$qualQuestions,
        ]);
    }

    /**
     * Verify Survey Access Key submitted by user via modal or form input.
     */
    public function verifyKey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_key' => 'required|string',
            'assessment_id' => 'required|exists:assessments,id',
        ]);

        $assessment = Assessment::where('id', $validated['assessment_id'])
            ->where('status', 'active')
            ->firstOrFail();

        $inputKey = strtoupper(trim($validated['access_key']));
        $targetKey = strtoupper(trim($assessment->access_key));

        if ($inputKey !== $targetKey) {
            return redirect()->back()
                ->with('key_error', 'Invalid Access Key for ' . $assessment->title . '. Please try again.')
                ->with('target_assessment_id', $assessment->id);
        }

        // Store unlocked assessment ID in session
        $unlocked = session()->get('unlocked_assessments', []);
        $unlocked[] = $assessment->id;
        session()->put('unlocked_assessments', array_unique($unlocked));

        return redirect()->route('surveys.take', $assessment->id)
            ->with('success', 'Access granted! Please complete the survey below.');
    }

    /**
     * Render dynamic survey questionnaire for an unlocked assessment.
     */
    public function take(Assessment $assessment): View|RedirectResponse
    {
        if ($assessment->status !== 'active') {
            return redirect()->route('surveys.index')
                ->with('error', 'This assessment is currently inactive.');
        }

        // Check if access key is unlocked in session
        $unlocked = session()->get('unlocked_assessments', []);
        if (! in_array($assessment->id, $unlocked) && $assessment->access_key) {
            return redirect()->route('surveys.index')
                ->with('key_error', 'Please enter the access key to open ' . $assessment->title . '.')
                ->with('target_assessment_id', $assessment->id);
        }

        $assessment->load(['questions.options']);

        return view('survey.take', compact('assessment'));
    }

    /**
     * Process dynamic survey response submission.
     */
    public function submit(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'respondent_name' => 'nullable|string|max:255',
            'scores' => 'required|array',
            'scores.*.question_id' => 'required|exists:questions,id',
            'scores.*.score' => 'nullable|numeric',
            'scores.*.text_response' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $assessment) {
            foreach ($validated['scores'] as $qScore) {
                EvaluationScore::create([
                    'assessment_id' => $assessment->id,
                    'evaluator_id' => auth()->id() ?: session('admin_user_id'),
                    'question_id' => $qScore['question_id'],
                    'score' => isset($qScore['score']) ? (float) $qScore['score'] : null,
                    'text_response' => $qScore['text_response'] ?? null,
                ]);
            }
        });

        return redirect()->route('surveys.index')
            ->with('success', 'Thank you! Your survey responses for "' . $assessment->title . '" have been submitted successfully.');
    }

    /**
     * Store a newly submitted legacy baseline/endline survey response.
     */
    public function store(Request $request): RedirectResponse
    {
        $quantRules = [];
        foreach (array_keys(self::$quantQuestions) as $key) {
            $quantRules[$key] = 'required|integer|between:1,5';
        }

        $qualRules = [];
        foreach (array_keys(self::$qualQuestions) as $key) {
            $qualRules[$key] = 'nullable|string';
        }

        $validated = $request->validate(array_merge($quantRules, $qualRules, [
            'survey_type' => 'required|in:baseline,endline',
        ]));

        SurveyResponse::create($validated);

        return redirect()->back()
            ->with('success', 'Thank you! Your survey response has been submitted successfully.');
    }
}
