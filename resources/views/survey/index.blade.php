@extends('layouts.app')

@section('title', 'Student Survey Portal')

@section('content')
<div class="page-header">
    <h1>Play It Forward (PIF) E-Hub</h1>
    <h4><strong>Professional Cohort (PIZ-C4-26)</strong> | Trainee Digital Self-Efficacy &amp; Mindset Survey</h4>
</div>

<div class="info-box">
    <p style="margin-bottom: 0;">
        Welcome to the Play It Forward (PIF) Professional Cohort E-Hub. This anonymous survey captures your
        Digital Self-Efficacy and Mindset at the <strong>start (Day 1/Baseline)</strong> and
        <strong>completion (Day 156/Endline)</strong> of your 6-month skills programme.
        Your honest responses help us measure the programme's impact and improve future cohorts.
        Please answer all questions truthfully — there are no right or wrong answers.
    </p>
</div>

<button id="tts-btn" class="btn btn-primary" style="margin-bottom: 20px;"
        data-text="Welcome to the Play It Forward (PIF) Professional Cohort E-Hub. This anonymous survey captures your Digital Self-Efficacy and Mindset at the start (Day 1, Baseline) and completion (Day 156, Endline) of your 6-month skills programme. Your honest responses help us measure the programme's impact and improve future cohorts. Please answer all questions truthfully — there are no right or wrong answers.">
    &#128266; Listen to Introduction (Read Aloud)
</button>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('survey.store') }}" method="POST" id="survey-form">
    @csrf

    <div class="form-group">
        <label for="survey_type">Survey Type</label>
        <select name="survey_type" id="survey_type" class="form-control" required>
            <option value="">-- Select Survey Timing --</option>
            <option value="baseline">Baseline (Day 1)</option>
            <option value="endline">Endline (Day 156)</option>
        </select>
    </div>

    <hr class="section-divider">

    <h3>Part 1: Technical &amp; Mindset Self-Assessment</h3>

    <div class="legend-box">
        <strong>How to Answer:</strong> Rate your scale of agreement:
        <strong>1:</strong> Strongly Disagree |
        <strong>2:</strong> Disagree |
        <strong>3:</strong> Neutral |
        <strong>4:</strong> Agree |
        <strong>5:</strong> Strongly Agree
    </div>

    @php
        $quantQuestions = [
            'q1_os_filemgmt' => "I can independently manage digital file directories, organize files, and troubleshoot basic computer system errors.",
            'q2_spreadsheets' => "I feel confident using spreadsheet software (like MS Excel) to structure data, write formulas, and build visual charts to analyze business metrics.",
            'q3_ux_design' => "I can translate a product idea into user-friendly digital wireframes and visual designs using modern design tools like Figma.",
            'q4_frontend' => "I am confident in my ability to write clean, semantic HTML and CSS code to build a responsive, mobile-friendly webpage.",
            'q5_js_logic' => "I feel capable of writing custom JavaScript logic (using loops, arrays, and functions) to make a website dynamic and interactive.",
            'q6_fullstack' => "I understand how back-end servers, databases, and APIs connect to safely handle secure user logins and persistent data storage.",
            'q7_resilience' => "When my design or code fails to work, I view it as an expected part of the learning process.",
            'q8_troubleshooting' => "I can independently find solutions to technical errors by searching online, reading developer documentation, and experimenting with my code.",
            'q9_freelance' => "I feel equipped to write professional project proposals, estimate developmental hourly rates, and pitch my services on freelance platforms.",
            'q10_livingstone_tourism' => "I can easily identify real-world business bottlenecks within Livingstone's tourism, craft, or commercial sectors that can be solved using digital products.",
            'q11_career_efficacy' => "I feel confident that the digital and administrative skills I am learning will enable me to secure a technical job, freelance work, or launch an enterprise."
        ];
    @endphp

    @foreach($quantQuestions as $key => $question)
        <div class="question-block">
            <p>{{ $loop->iteration }}. {{ $question }}</p>
            <div class="radio-group">
                @for($i = 1; $i <= 5; $i++)
                    <label>
                        <input type="radio" name="{{ $key }}" value="{{ $i }}" {{ $i == 3 ? 'checked' : '' }} required>
                        {{ $i }}
                    </label>
                @endfor
            </div>
        </div>
    @endforeach

    <hr class="section-divider">

    <h3>Part 2: Qualitative Insight Responses</h3>

    @php
        $qualQuestions = [
            'qual1_why_join' => "Why did you decide to join this programme?",
            'qual2_skills_hoped' => "What skills or knowledge are you hoping to gain through this programme?",
            'qual3_success_criteria' => "At the end of this programme, what would make you say that your participation was successful?",
            'qual4_challenges' => "What challenges do you anticipate might make it difficult for you to complete this programme successfully?"
        ];
    @endphp

    @foreach($qualQuestions as $key => $question)
        <div class="form-group question-block">
            <p>{{ $question }}</p>
            <textarea name="{{ $key }}" class="form-control" rows="4"
                      placeholder="Share your honest thoughts..." required></textarea>
        </div>
    @endforeach

    <div class="form-group" style="margin-top: 32px;">
        <button type="submit" class="btn btn-primary btn-full" style="font-size: 1.1rem; padding: 14px 24px;">
            Submit Survey Safely
        </button>
    </div>
</form>
@endsection
