<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /**
     * Quantitative survey questions (key => label).
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
        'q11_career_efficacy'    => "I feel confident that the digital and administrative skills I am gaining in this programme will make me competitive for remote online work and local tech-enabled jobs.",
    ];

    /**
     * Qualitative survey questions (key => label).
     */
    private static array $qualQuestions = [
        'qual1_why_join'       => "Why did you decide to join this programme? What do you hope to achieve by the end of it?",
        'qual2_skills_hoped'   => "What skills or knowledge are you hoping to gain from this training? Be as specific as possible.",
        'qual3_success_criteria' => "At the end of this programme, what would make you feel it was successful for you personally?",
        'qual4_challenges'     => "What challenges do you anticipate might make it harder for you to complete this programme? How do you plan to overcome them?",
    ];

    /**
     * Display the survey form.
     */
    public function index(): \Illuminate\View\View
    {
        return view('survey.index', [
            'quantQuestions' => self::$quantQuestions,
            'qualQuestions'  => self::$qualQuestions,
        ]);
    }

    /**
     * Store a newly submitted survey response.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
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

        return redirect()
            ->back()
            ->with('success', 'Thank you! Your survey response has been submitted successfully.');
    }
}
