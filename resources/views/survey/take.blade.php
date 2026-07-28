@extends('layouts.app')

@section('title', 'Survey: ' . $assessment->title)

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1>{{ $assessment->title }}</h1>
        <h4>{{ $assessment->description ?: 'Please answer all survey questions below honestly.' }}</h4>
    </div>
    <div>
        <a href="{{ route('surveys.index') }}" class="btn" style="background: #e0e0e0; color: #333;">
            &larr; Back to Gallery
        </a>
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

    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
        <div class="form-group" style="margin: 0;">
            <label for="respondent_name">Your Name / Student ID (Optional)</label>
            <input type="text" name="respondent_name" id="respondent_name" class="form-control" placeholder="Enter your full name or student ID if requested...">
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
