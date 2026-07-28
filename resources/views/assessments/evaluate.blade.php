@extends('layouts.app')

@section('title', 'Evaluation Sheet — ' . $assessment->title)

@section('content')

@php
    $currentUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id'));
    $isSuper     = $currentUser && $currentUser->isSuper();
@endphp

{{-- Page Header --}}
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
    <div>
        <h1 style="margin-bottom:4px;">✏️ Evaluation Sheet</h1>
        <p style="color:var(--text-secondary); font-size:0.9rem;">
            <strong>{{ $assessment->title }}</strong>
            @if($assignedPanel)
                ·
                <span class="badge badge-blue" style="vertical-align:middle;">Panel {{ $assignedPanel }}</span>
            @endif
            @if($selectedCandidate)
                · Grading: <strong style="color:var(--green-primary);">{{ $selectedCandidate->name }}</strong>
            @endif
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('assessments.show', $assessment->id) }}"
           class="btn btn-ghost btn-sm">← Back to Hub</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <strong>Please correct the following errors:</strong>
        <ul style="margin-top:6px; padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Candidate Selector --}}
@if($assignedCandidates->isNotEmpty())
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <span class="card-title">
                🧑‍🎓 Select Candidate
                <span style="font-weight:400; color:var(--text-muted); font-size:0.82rem; margin-left:6px;">
                    (Panel {{ $assignedPanel ?: 'All' }} roster)
                </span>
            </span>
        </div>
        <div class="card-body" style="display:flex; gap:8px; flex-wrap:wrap;">
            @foreach($assignedCandidates as $cand)
                @php $isSelected = $selectedCandidate && $selectedCandidate->id === $cand->id; @endphp
                <a href="{{ route('assessments.evaluate', [$assessment->id, 'candidate_id' => $cand->id]) }}"
                   class="btn btn-sm {{ $isSelected ? 'btn-primary' : 'btn-outline' }}">
                    {{ $cand->name }}
                    <span style="font-weight:400; opacity:0.75; font-size:0.75rem;">({{ $cand->gender ?: 'N/A' }})</span>
                </a>
            @endforeach
        </div>
    </div>
@elseif($assessment->candidates->isNotEmpty())
    <div class="info-box warning" style="margin-bottom:24px;">
        No candidates assigned to <strong>Panel {{ $assignedPanel }}</strong> for this assessment.
        @if($isSuper)
            <a href="{{ route('assessments.edit', $assessment->id) }}" style="color:var(--green-dark);">Manage assignments →</a>
        @endif
    </div>
@endif

{{-- Evaluation Form --}}
<form action="{{ route('assessments.submit-evaluation', $assessment->id) }}" method="POST">
    @csrf
    @if($selectedCandidate)
        <input type="hidden" name="candidate_id" value="{{ $selectedCandidate->id }}">
    @endif

    @forelse($assessment->questions as $idx => $question)
        @php
            $existingScore = $existingScores->get($question->id);
            $savedScoreVal = $existingScore?->score;
            $savedTextVal  = $existingScore?->text_response;
        @endphp

        <div class="question-block">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px;">
                <div>
                    <div style="font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">
                        Question {{ $idx + 1 }} of {{ $assessment->questions->count() }}
                    </div>
                    <p style="margin:0; font-size:1rem; line-height:1.5; color:var(--text-primary);">
                        {{ $question->question_text }}
                    </p>
                </div>
                <div style="display:flex; gap:6px; flex-shrink:0; align-items:center;">
                    @php
                        $qTypeClass = match($question->type) {
                            'scale'           => 'badge-blue',
                            'multiple_choice' => 'badge-purple',
                            'boolean'         => 'badge-teal',
                            default           => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $qTypeClass }}">{{ $question->type }}</span>
                    <span class="badge badge-green">{{ number_format($question->weight, 1) }}× weight</span>
                </div>
            </div>

            <input type="hidden" name="scores[{{ $idx }}][question_id]" value="{{ $question->id }}">

            @if($question->type === 'scale')
                {{-- Scale: 1–5 radio buttons --}}
                <div>
                    <div style="font-size:0.82rem; color:var(--text-secondary); font-weight:600; margin-bottom:10px;">
                        Rating (1 = Poor, 5 = Excellent):
                    </div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        @for($s = 1; $s <= 5; $s++)
                            @php $isChecked = (string)$savedScoreVal === (string)$s; @endphp
                            <label style="display:flex; align-items:center; gap:8px; padding:10px 18px; border:2px solid {{ $isChecked ? 'var(--green-primary)' : 'var(--border)' }}; border-radius:var(--radius-sm); background:{{ $isChecked ? 'var(--green-light)' : 'var(--surface)' }}; cursor:pointer; transition:all var(--transition); font-weight:700; font-size:1rem;">
                                <input type="radio" name="scores[{{ $idx }}][score]" value="{{ $s }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       required
                                       style="accent-color:var(--green-primary); width:16px; height:16px;">
                                {{ $s }}
                            </label>
                        @endfor
                    </div>
                </div>

            @elseif($question->type === 'multiple_choice' && $question->options->isNotEmpty())
                {{-- Multiple Choice --}}
                <div>
                    <div style="font-size:0.82rem; color:var(--text-secondary); font-weight:600; margin-bottom:10px;">
                        Select one option:
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @foreach($question->options as $opt)
                            @php $isChecked = (string)$savedScoreVal === (string)$opt->option_value; @endphp
                            <label style="display:flex; align-items:center; gap:12px; padding:12px 16px; border:2px solid {{ $isChecked ? 'var(--green-primary)' : 'var(--border)' }}; border-radius:var(--radius-sm); background:{{ $isChecked ? 'var(--green-light)' : 'var(--surface)' }}; cursor:pointer; transition:all var(--transition);">
                                <input type="radio" name="scores[{{ $idx }}][score]" value="{{ $opt->option_value }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       required
                                       style="accent-color:var(--green-primary); width:18px; height:18px; flex-shrink:0;">
                                <span style="flex:1; font-weight:500;">{{ $opt->option_label }}</span>
                                <span class="badge badge-green" style="font-size:0.72rem;">+{{ number_format($opt->option_value, 1) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            @elseif($question->type === 'boolean')
                {{-- Boolean: Yes / No --}}
                <div>
                    <div style="font-size:0.82rem; color:var(--text-secondary); font-weight:600; margin-bottom:10px;">
                        Select response:
                    </div>
                    <div style="display:flex; gap:12px;">
                        @php
                            $opts = $question->options->isNotEmpty() ? $question->options : collect([
                                (object)['option_label' => 'Yes', 'option_value' => 1.0],
                                (object)['option_label' => 'No',  'option_value' => 0.0],
                            ]);
                        @endphp
                        @foreach($opts as $opt)
                            @php
                                $isChecked = (string)$savedScoreVal === (string)$opt->option_value;
                                $isYes     = strtolower($opt->option_label) === 'yes' || $opt->option_value > 0;
                            @endphp
                            <label style="display:flex; align-items:center; gap:10px; padding:12px 24px; border:2px solid {{ $isChecked ? ($isYes ? 'var(--green-primary)' : '#dc2626') : 'var(--border)' }}; border-radius:var(--radius-sm); background:{{ $isChecked ? ($isYes ? 'var(--green-light)' : '#fef2f2') : 'var(--surface)' }}; cursor:pointer; transition:all var(--transition); font-weight:700; font-size:1rem;">
                                <input type="radio" name="scores[{{ $idx }}][score]" value="{{ $opt->option_value }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       required
                                       style="accent-color:var(--green-primary); width:17px; height:17px;">
                                {{ $opt->option_label }}
                            </label>
                        @endforeach
                    </div>
                </div>

            @elseif($question->type === 'text')
                {{-- Open text feedback --}}
                <div class="form-group" style="margin:0;">
                    <label for="text-{{ $question->id }}" style="font-size:0.82rem; color:var(--text-secondary); font-weight:600;">
                        Written Feedback / Comments:
                    </label>
                    <textarea name="scores[{{ $idx }}][text_response]"
                              id="text-{{ $question->id }}"
                              class="form-control"
                              rows="3"
                              placeholder="Enter your observations and comments here...">{{ $savedTextVal }}</textarea>
                </div>
            @endif
        </div>
    @empty
        <div style="text-align:center; padding:60px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text-muted);">
            <div style="font-size:2.5rem; margin-bottom:12px;">❓</div>
            No questions configured for this assessment.
            @if($isSuper)
                <a href="{{ route('assessments.edit', $assessment->id) }}" style="color:var(--green-primary);">Add questions →</a>
            @endif
        </div>
    @endforelse

    @if($assessment->questions->isNotEmpty() && $selectedCandidate)
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; padding-top:20px; border-top:1px solid var(--border);">
            <a href="{{ route('assessments.show', $assessment->id) }}"
               class="btn btn-ghost">← Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg">
                ✅ Submit Evaluation for {{ $selectedCandidate->name }}
            </button>
        </div>
    @elseif($assessment->questions->isNotEmpty() && !$selectedCandidate)
        <div class="info-box warning" style="margin-top:24px;">
            👆 Please select a candidate above before submitting the evaluation.
        </div>
    @endif
</form>

@endsection

@section('scripts')
<script>
    // Highlight radio option cards on selection
    document.querySelectorAll('.question-block input[type="radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const block = this.closest('.question-block');
            if (!block) return;
            block.querySelectorAll('label').forEach(function(lbl) {
                lbl.style.borderColor = 'var(--border)';
                lbl.style.background  = 'var(--surface)';
            });
            const parentLabel = this.closest('label');
            if (parentLabel) {
                parentLabel.style.borderColor = 'var(--green-primary)';
                parentLabel.style.background  = 'var(--green-light)';
            }
        });
    });
</script>
@endsection
