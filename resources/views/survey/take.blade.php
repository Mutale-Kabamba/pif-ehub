@extends('layouts.app')

@section('title', 'Survey: ' . $assessment->title)

@section('content')
@php
    $rules = $assessment->rules ? (is_array($assessment->rules->rules_json) ? $assessment->rules->rules_json : json_decode($assessment->rules->rules_json, true)) : [];
    $surveyStage = $rules['survey_stage'] ?? 'general';
    $isAnonymous = !empty($rules['is_anonymous']);
    $stageLabels = [
        'baseline' => ['name' => 'Baseline Survey', 'desc' => 'Initial assessment of knowledge, expectations, and skills before training.', 'badge' => 'badge-blue', 'icon' => '🌱'],
        'midline' => ['name' => 'Midline Survey', 'desc' => 'Progress checkpoint to evaluate learning velocity, challenges, and mid-course adaptation.', 'badge' => 'badge-teal', 'icon' => '⚖️'],
        'endline' => ['name' => 'Endline Survey', 'desc' => 'Culminating impact evaluation measuring skill acquisition, confidence, and career readiness.', 'badge' => 'badge-purple', 'icon' => '🎓'],
        'general' => ['name' => 'Feedback Survey', 'desc' => 'Monitoring and evaluation feedback questionnaire.', 'badge' => 'badge-gray', 'icon' => '📋'],
    ];
    $stageInfo = $stageLabels[$surveyStage] ?? $stageLabels['general'];
@endphp

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span class="badge badge-purple" style="font-size: 0.85rem; padding: 4px 10px;">
                🔒 Strictly Anonymous Survey
            </span>
            <span class="badge badge-teal" style="font-size: 0.85rem; padding: 4px 10px;">
                📋 M&amp;E Feedback Instrument
            </span>
        </div>
        <h1 style="margin: 0 0 6px 0;">{{ $assessment->title }}</h1>
        <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem;">
            {{ $assessment->description ?: 'Please provide your honest feedback across the questions below.' }}
        </p>
    </div>
    <div>
        <a href="{{ route('surveys.index') }}" class="btn btn-ghost" style="border: 1px solid var(--border);">
            &larr; Back to Gallery
        </a>
    </div>
</div>

<div style="background: var(--surface); border: 1px solid var(--border); border-left: 4px solid #8b5cf6; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px;">
    <div style="font-size: 1.5rem;">ℹ️</div>
    <div style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5;">
        <strong style="color: var(--text-primary);">Anonymous Monitoring &amp; Evaluation:</strong>
        Surveys are not tests and have no pass or fail grades. No names or student IDs are collected. Please select whether you are submitting your <strong>Baseline</strong>, <strong>Midline</strong>, or <strong>Endline</strong> response.
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <strong>Please complete all required questions:</strong>
        <ul style="margin-top: 8px; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('surveys.submit', $assessment->id) }}" method="POST">
    @csrf

    {{-- M&E Survey Phase Selection (Baseline, Midline, Endline) --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 22px; margin-bottom: 24px;">
        <label style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 10px; font-size: 0.98rem;">
            Select Survey Phase / Stage <span style="color: #dc2626;">*</span>
        </label>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0; margin-bottom: 14px;">
            Choose the evaluation milestone corresponding to your current cohort training phase:
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            <label class="survey-stage-card" style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border: 2px solid {{ $surveyStage === 'baseline' || !$surveyStage ? 'var(--green-primary)' : 'var(--border)' }}; border-radius: 8px; background: {{ $surveyStage === 'baseline' || !$surveyStage ? 'var(--green-light)' : 'var(--surface-alt)' }}; cursor: pointer; transition: all 0.15s;">
                <input type="radio" name="survey_stage" value="baseline" {{ $surveyStage === 'baseline' || !$surveyStage ? 'checked' : '' }} required style="accent-color: #59B33F; width: 18px; height: 18px; margin-top: 2px;">
                <div>
                    <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">🌱 Baseline Survey</strong>
                    <span style="font-size: 0.78rem; color: var(--text-secondary); display: block; margin-top: 2px;">Start of Training (Day 1 / Entry)</span>
                </div>
            </label>

            <label class="survey-stage-card" style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border: 2px solid {{ $surveyStage === 'midline' ? 'var(--green-primary)' : 'var(--border)' }}; border-radius: 8px; background: {{ $surveyStage === 'midline' ? 'var(--green-light)' : 'var(--surface-alt)' }}; cursor: pointer; transition: all 0.15s;">
                <input type="radio" name="survey_stage" value="midline" {{ $surveyStage === 'midline' ? 'checked' : '' }} required style="accent-color: #59B33F; width: 18px; height: 18px; margin-top: 2px;">
                <div>
                    <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">⚖️ Midline Survey</strong>
                    <span style="font-size: 0.78rem; color: var(--text-secondary); display: block; margin-top: 2px;">Mid-Cohort Checkpoint (Day 78)</span>
                </div>
            </label>

            <label class="survey-stage-card" style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border: 2px solid {{ $surveyStage === 'endline' ? 'var(--green-primary)' : 'var(--border)' }}; border-radius: 8px; background: {{ $surveyStage === 'endline' ? 'var(--green-light)' : 'var(--surface-alt)' }}; cursor: pointer; transition: all 0.15s;">
                <input type="radio" name="survey_stage" value="endline" {{ $surveyStage === 'endline' ? 'checked' : '' }} required style="accent-color: #59B33F; width: 18px; height: 18px; margin-top: 2px;">
                <div>
                    <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">🎓 Endline Survey</strong>
                    <span style="font-size: 0.78rem; color: var(--text-secondary); display: block; margin-top: 2px;">Program Completion / Exit (Day 156)</span>
                </div>
            </label>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        @forelse($assessment->questions as $idx => $question)
            <div class="evaluation-block" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 12px 0; font-size: 1.1rem; color: #1a1a1a;">
                    Question #{{ $idx + 1 }}: {{ $question->question_text }}
                </h4>

                <input type="hidden" name="scores[{{ $idx }}][question_id]" value="{{ $question->id }}">

                @if($question->type === 'scale')
                    <!-- Numeric / 1-5 Scale -->
                    <div style="margin-top: 12px;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: #555; display: block; margin-bottom: 8px;">
                            Select Rating (1 = Strongly Disagree / Poor, 5 = Strongly Agree / Excellent):
                        </label>
                        <div class="radio-group" style="display: flex; gap: 16px; flex-wrap: wrap;">
                            @for($s = 1; $s <= 5; $s++)
                                <label style="display: flex; align-items: center; gap: 6px; padding: 10px 18px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; cursor: pointer;">
                                    <input type="radio" name="scores[{{ $idx }}][score]" value="{{ $s }}" required style="accent-color: #59B33F;">
                                    <strong style="font-size: 1rem;">{{ $s }}</strong>
                                </label>
                            @endfor
                        </div>
                    </div>
                @elseif($question->type === 'multiple_choice' && $question->options->isNotEmpty())
                    <!-- Multiple Choice -->
                    <div style="margin-top: 12px;">
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($question->options as $opt)
                                <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1px solid #ddd; border-radius: 6px; background: #fafafa; cursor: pointer;">
                                    <input type="radio" name="scores[{{ $idx }}][score]" value="{{ $opt->option_value }}" required style="accent-color: #59B33F; width: 18px; height: 18px;">
                                    <span>{{ $opt->option_label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @elseif($question->type === 'boolean')
                    <!-- Yes / No -->
                    <div style="margin-top: 12px;">
                        <div class="radio-group" style="display: flex; gap: 16px;">
                            @php
                                $opts = $question->options->isNotEmpty() ? $question->options : collect([
                                    (object)['option_label' => 'Yes', 'option_value' => 1.0],
                                    (object)['option_label' => 'No', 'option_value' => 0.0],
                                ]);
                            @endphp
                            @foreach($opts as $opt)
                                <label style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; cursor: pointer;">
                                    <input type="radio" name="scores[{{ $idx }}][score]" value="{{ $opt->option_value }}" required style="accent-color: #59B33F;">
                                    <strong>{{ $opt->option_label }}</strong>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @elseif($question->type === 'text')
                    <!-- Written Text Response -->
                    <div class="form-group" style="margin-top: 12px; margin-bottom: 0;">
                        <textarea name="scores[{{ $idx }}][text_response]" class="form-control" rows="3" placeholder="Type your answer or feedback here..." required></textarea>
                    </div>
                @endif
            </div>
        @empty
            <div style="background: #fff; padding: 40px; text-align: center; border-radius: 8px; border: 1px solid #ddd; color: #777;">
                No questions configured for this survey.
            </div>
        @endforelse

        @if($assessment->questions->isNotEmpty())
            <div style="text-align: right; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 36px; font-size: 1.05rem; font-weight: bold;">
                    Submit Survey Response
                </button>
            </div>
        @endif
    </div>
</form>
@endsection

@section('scripts')
<script>
document.querySelectorAll('input[name="survey_stage"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.survey-stage-card').forEach(card => {
            card.style.borderColor = 'var(--border)';
            card.style.background = 'var(--surface-alt)';
        });
        if (this.checked) {
            const parent = this.closest('.survey-stage-card');
            if (parent) {
                parent.style.borderColor = 'var(--green-primary)';
                parent.style.background = 'var(--green-light)';
            }
        }
    });
});
</script>
@endsection
