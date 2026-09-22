<?php

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentRule;
use App\Models\Candidate;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Compiles the Core Interview, Literacy Assessment, and Survey into the Assessment Engine.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // ── 1. COMPILE INTERVIEW ENGINE ─────────────────────────────────
            $interview = Assessment::firstOrCreate(
                ['type' => 'interview', 'title' => 'PIF Trainee Interview Evaluation'],
                [
                    'description' => 'Official panel interview evaluation for PIF cohort candidates across Motivation, Availability, Resilience, and Communication.',
                    'status' => 'active',
                    'access_key' => 'PIF-INTERVIEW-2026',
                ]
            );

            AssessmentRule::updateOrCreate(
                ['assessment_id' => $interview->id],
                [
                    'max_panelists' => 3,
                    'score_cap' => 20.00,
                    'passing_threshold' => 12.00,
                    'rules_json' => ['number_of_panels' => 2],
                ]
            );

            $interviewCriteria = [
                ['question_text' => 'Passion & Motivation (Genuine interest in digital innovation vs. just wanting a stipend)', 'weight' => 1.00, 'order' => 1],
                ['question_text' => 'Availability & Commitment (Clear schedule, no conflicting employment or school commitments)', 'weight' => 1.00, 'order' => 2],
                ['question_text' => 'Resilience & Problem Solving (Shows grit, willingness to overcome obstacles, and persistence)', 'weight' => 1.00, 'order' => 3],
                ['question_text' => 'Communication Skills (Articulates thoughts clearly, listens actively, and expresses ideas well)', 'weight' => 1.00, 'order' => 4],
            ];

            foreach ($interviewCriteria as $crit) {
                Question::firstOrCreate(
                    [
                        'assessment_id' => $interview->id,
                        'question_text' => $crit['question_text'],
                    ],
                    [
                        'type' => 'scale',
                        'weight' => $crit['weight'],
                        'order' => $crit['order'],
                    ]
                );
            }

            // Assign Panelists & Candidates to Interview Assessment
            $panelists = User::whereIn('role', ['panelist', 'super'])->get();
            foreach ($panelists as $panelist) {
                AssessmentAssignment::firstOrCreate([
                    'assessment_id' => $interview->id,
                    'user_id' => $panelist->id,
                    'role' => 'panelist',
                ], [
                    'panel_name' => $panelist->panel ?: 'A',
                ]);
            }

            $candidates = Candidate::all();
            foreach ($candidates as $candidate) {
                AssessmentAssignment::firstOrCreate([
                    'assessment_id' => $interview->id,
                    'candidate_id' => $candidate->id,
                    'role' => 'candidate',
                ], [
                    'panel_name' => $candidate->panel ?: 'A',
                ]);
            }

            // ── 2. COMPILE DIGITAL LITERACY ASSESSMENT ENGINE ──────────────
            $literacy = Assessment::firstOrCreate(
                ['type' => 'assessment', 'title' => 'Digital Literacy Practical Assessment'],
                [
                    'description' => '10-task practical computer literacy assessment terminal testing operating system, file management, word processing, web research, spreadsheets, and basic coding.',
                    'status' => 'active',
                    'access_key' => 'PIF-LITERACY-2026',
                ]
            );

            AssessmentRule::updateOrCreate(
                ['assessment_id' => $literacy->id],
                [
                    'max_panelists' => 1,
                    'score_cap' => 20.00,
                    'passing_threshold' => 10.00,
                    'rules_json' => ['max_task_score' => 2],
                ]
            );

            $literacyTasks = [
                '1. Directory Setup: On Desktop, create a folder named \'PIF_Project_[Your Name]\'. Inside, create sub-folder \'Assets\'.',
                '2. Word Processing: Open MS Word and type a short 3-sentence paragraph describing a mobile app/website concept.',
                '3. Web Research: Search definition of \'HTML\' or \'User Experience (UX)\' and copy/paste it below paragraph.',
                '4. Formatting: Make the definition Bold and create a bulleted list of 3 features your app/website would have.',
                '5. File Saving: Save this Word document directly into your main project folder, naming the file \'App_Concept\'.',
                '6. Spreadsheet / Data: Create \'User Database\' table in Excel with headers: ID, Username, Email. Add 2 fake users.',
                '7. Visual Asset Capture: Take a desktop screenshot. Save it as an image file directly into the \'Assets\' sub-folder.',
                '8. File Compression: Right-click main project folder and compress (Zip) it into a single zipped folder.',
                '9. System Specifications: Navigate to System Settings. State Installed RAM size and OS version accurately.',
                '10. Plain Text / Code Prep: Open Notepad. Type exactly: <h1>Hello World</h1>. Save to Desktop as \'index.html\'.',
            ];

            foreach ($literacyTasks as $idx => $taskText) {
                Question::firstOrCreate(
                    [
                        'assessment_id' => $literacy->id,
                        'question_text' => $taskText,
                    ],
                    [
                        'type' => 'scale',
                        'weight' => 1.00,
                        'order' => $idx + 1,
                    ]
                );
            }

            foreach ($candidates as $candidate) {
                AssessmentAssignment::firstOrCreate([
                    'assessment_id' => $literacy->id,
                    'candidate_id' => $candidate->id,
                    'role' => 'candidate',
                ], [
                    'panel_name' => $candidate->panel ?: 'A',
                ]);
            }

            // ── 3. COMPILE SURVEY ENGINE ────────────────────────────────────
            $survey = Assessment::firstOrCreate(
                ['type' => 'survey', 'title' => 'PIF Trainee Impact Survey'],
                [
                    'description' => 'Comprehensive trainee baseline and endline impact survey measuring digital confidence, technical competence, and career readiness.',
                    'status' => 'active',
                    'access_key' => 'PIF-SURVEY-2026',
                ]
            );

            AssessmentRule::updateOrCreate(
                ['assessment_id' => $survey->id],
                [
                    'score_cap' => 55.00,
                    'passing_threshold' => 30.00,
                    'rules_json' => ['scale_max' => 5],
                ]
            );

            $quantQuestions = [
                'q1' => 'I can independently manage digital file directories, organize project assets, and troubleshoot basic operating system errors.',
                'q2' => 'I feel confident using spreadsheet software (e.g., Excel/Google Sheets) to organize data, use basic formulas, and generate charts.',
                'q3' => 'I can translate a product idea into user-friendly wireframes and visual UI/UX designs using digital tools like Figma or Penpot.',
                'q4' => 'I am confident in my ability to write clean, semantic HTML and modern CSS to build fully responsive web layouts.',
                'q5' => 'I feel capable of writing custom JavaScript logic to create dynamic user interactions, validate forms, and manipulate web page elements.',
                'q6' => 'I understand how back-end servers, databases, and APIs work together to power full-stack web applications.',
                'q7' => 'When my design or code fails to work, I view it as a learning opportunity and persist until I find a solution.',
                'q8' => 'I can independently find solutions to technical errors using documentation, developer communities, and online search.',
                'q9' => 'I feel equipped to write professional project proposals, communicate with remote clients, and price digital freelance work.',
                'q10' => 'I can easily identify real-world business bottlenecks in the Livingstone tourism/hospitality ecosystem and design digital tools to solve them.',
                'q11' => 'I feel confident that the digital and administrative skills I am acquiring will help me secure employment, freelance contracts, or launch a tech venture.',
            ];

            $order = 1;
            foreach ($quantQuestions as $qText) {
                Question::firstOrCreate(
                    [
                        'assessment_id' => $survey->id,
                        'question_text' => $qText,
                    ],
                    [
                        'type' => 'scale',
                        'weight' => 1.00,
                        'order' => $order++,
                    ]
                );
            }

            $qualQuestions = [
                'Why did you decide to join this training programme?',
                'What specific technical, design, or professional skills are you most hoping to gain?',
                'At the end of this training, what milestone or outcome would make you feel successful?',
                'What challenges do you anticipate might make it difficult to complete this programme, and how do you plan to overcome them?',
            ];

            foreach ($qualQuestions as $qualText) {
                Question::firstOrCreate(
                    [
                        'assessment_id' => $survey->id,
                        'question_text' => $qualText,
                    ],
                    [
                        'type' => 'text',
                        'weight' => 0.00,
                        'order' => $order++,
                    ]
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Assessment::whereIn('title', [
            'PIF Trainee Interview Evaluation',
            'Digital Literacy Practical Assessment',
            'PIF Trainee Impact Survey',
        ])->delete();
    }
};
