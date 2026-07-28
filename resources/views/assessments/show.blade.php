@extends('layouts.app')

@section('title', $assessment->title . ' — Assessment Hub')

@section('content')

@php
    $currentUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id'));
    $isSuper     = $currentUser && $currentUser->isSuper();
    $activeTab   = request()->query('tab', 'results');

    $typeBadgeClass = match($assessment->type) {
        'interview' => 'badge-blue',
        'survey'    => 'badge-purple',
        default     => 'badge-teal',
    };
    $statusBadgeClass = match($assessment->status) {
        'active'    => 'badge-green',
        'draft'     => 'badge-gray',
        'completed' => 'badge-orange',
        default     => 'badge-gray',
    };
    $typeIcon = match($assessment->type) {
        'interview' => '🎤',
        'survey'    => '📋',
        default     => '📝',
    };
@endphp

{{-- ========== ASSESSMENT HUB HEADER ========== --}}
<div class="hub-header">
    <div class="hub-header-left">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
            <span style="font-size:2rem;">{{ $typeIcon }}</span>
            <h1>{{ $assessment->title }}</h1>
        </div>
        @if($assessment->description)
            <p>{{ $assessment->description }}</p>
        @endif
        <div class="hub-header-badges" style="margin-top:10px;">
            <span class="badge {{ $statusBadgeClass }}">{{ $assessment->status }}</span>
            <span class="badge {{ $typeBadgeClass }}">{{ $assessment->type }}</span>
            @if($assessment->access_key)
                <span class="badge badge-gray" style="font-family:monospace; letter-spacing:1px;">
                    🔑 {{ $assessment->access_key }}
                </span>
            @endif
            @if($assessment->rule?->passing_threshold !== null)
                <span class="badge badge-teal">
                    Pass: {{ number_format($assessment->rule->passing_threshold, 1) }}
                </span>
            @endif
            @if($assessment->rule?->score_cap !== null)
                <span class="badge badge-orange">
                    Cap: {{ number_format($assessment->rule->score_cap, 1) }}
                </span>
            @endif
        </div>
    </div>

    <div class="hub-header-actions">
        <a href="{{ route('assessments.evaluate', $assessment->id) }}"
           class="btn btn-sm" style="background:#0f766e; color:white;">
            ✏️ Grade / Evaluate
        </a>
        @if($isSuper)
            <a href="{{ route('assessments.edit', $assessment->id) }}"
               class="btn btn-outline btn-sm">⚙️ Edit</a>
        @endif
        <a href="{{ route('assessments.index') }}"
           class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- ========== METRIC OVERVIEW ========== --}}
<div class="metric-cards" style="margin-bottom:24px;">
    <div class="metric-card">
        <div class="value">{{ $results['total_candidates'] }}</div>
        <div class="label">Candidates</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:#2563eb;">{{ $results['total_panelists'] }}</div>
        <div class="label">Evaluators</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:#7c3aed;">{{ $results['total_questions'] }}</div>
        <div class="label">Questions</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:var(--green-primary);">
            {{ collect($results['candidate_results'])->where('passed', true)->count() }}
        </div>
        <div class="label">Passed</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:#dc2626;">
            {{ collect($results['candidate_results'])->where('passed', false)->count() }}
        </div>
        <div class="label">Failed</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:#c2410c;">
            {{ collect($results['candidate_results'])->where('passed', null)->count() }}
        </div>
        <div class="label">Ungraded</div>
    </div>
</div>

{{-- ========== PER-ASSESSMENT HUB TABS ========== --}}
<div class="hub-tabs">
    <a href="{{ route('assessments.show', [$assessment->id, 'tab' => 'results']) }}"
       class="hub-tab {{ $activeTab === 'results' ? 'active' : '' }}">
        🏆 Results & Leaderboard
    </a>
    <a href="{{ route('assessments.show', [$assessment->id, 'tab' => 'questions']) }}"
       class="hub-tab {{ $activeTab === 'questions' ? 'active' : '' }}">
        ❓ Questions & Criteria
    </a>
    @if($isSuper)
        <a href="{{ route('assessments.show', [$assessment->id, 'tab' => 'panelists']) }}"
           class="hub-tab {{ $activeTab === 'panelists' ? 'active' : '' }}">
            👥 Panelists
        </a>
        <a href="{{ route('assessments.show', [$assessment->id, 'tab' => 'candidates']) }}"
           class="hub-tab {{ $activeTab === 'candidates' ? 'active' : '' }}">
            🧑‍🎓 Candidates
        </a>
        <a href="{{ route('assessments.show', [$assessment->id, 'tab' => 'rules']) }}"
           class="hub-tab {{ $activeTab === 'rules' ? 'active' : '' }}">
            📐 Rules & Config
        </a>
    @endif
</div>

{{-- ======================================================
     TAB: RESULTS & LEADERBOARD
====================================================== --}}
@if($activeTab === 'results')
    <div class="card">
        <div class="card-header">
            <span class="card-title">🏆 Candidate Evaluation Leaderboard</span>
            <div class="info-box info" style="margin:0; padding:8px 14px; font-size:0.8rem; border-radius:100px;">
                <strong>Score Formula:</strong>
                Avg<sub>panelists</sub>(Σ question_score × weight), capped at score limit
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;">Rank</th>
                        <th>Candidate</th>
                        <th>Panel</th>
                        <th style="text-align:center;">Evaluations</th>
                        <th style="text-align:right;">Weighted Total</th>
                        <th style="text-align:right;">Final Score</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results['candidate_results'] as $idx => $res)
                        <tr class="{{ $res['passed'] === true ? 'accepted' : '' }}">
                            <td style="font-weight:700; color:var(--text-muted);">
                                #{{ $idx + 1 }}
                            </td>
                            <td>
                                <strong>{{ $res['candidate']->name }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-gray">Panel {{ $res['candidate']->panel }}</span>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-weight:600;">{{ $res['evaluator_count'] }}</span>
                            </td>
                            <td style="text-align:right; font-weight:600; color:var(--text-secondary);">
                                {{ number_format($res['weighted_total'], 2) }}
                            </td>
                            <td style="text-align:right; font-weight:800; font-size:1.1rem; color:var(--green-primary);">
                                {{ number_format($res['final_score'], 2) }}
                            </td>
                            <td style="text-align:center;">
                                @if($res['passed'] === true)
                                    <span class="badge badge-green">PASSED</span>
                                @elseif($res['passed'] === false)
                                    <span class="badge badge-red">FAILED</span>
                                @else
                                    <span class="badge badge-gray">UNGRADED</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('assessments.evaluate', [$assessment->id, 'candidate_id' => $res['candidate']->id]) }}"
                                   class="btn btn-primary btn-sm">Grade</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">
                                No candidates assigned or evaluated yet.
                                @if($isSuper)
                                    <a href="{{ route('assessments.edit', $assessment->id) }}" style="color:var(--green-primary);">Add candidates →</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ======================================================
     TAB: QUESTIONS & CRITERIA
====================================================== --}}
@if($activeTab === 'questions')
    <div style="display:grid; gap:16px;">
        @forelse($assessment->questions as $q)
            @php
                $qTypeClass = match($q->type) {
                    'scale'           => 'badge-blue',
                    'multiple_choice' => 'badge-purple',
                    'boolean'         => 'badge-teal',
                    default           => 'badge-gray',
                };
            @endphp
            <div class="question-builder-item">
                <div class="q-handle">{{ $q->order }}</div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:10px;">
                    <strong style="font-size:0.95rem; line-height:1.4; color:var(--text-primary); flex:1;">
                        {{ $q->question_text }}
                    </strong>
                    <div style="display:flex; gap:6px; flex-shrink:0; align-items:center;">
                        <span class="badge {{ $qTypeClass }}">{{ $q->type }}</span>
                        <span class="badge badge-green">{{ number_format($q->weight, 1) }}× weight</span>
                    </div>
                </div>

                @if($q->type === 'scale')
                    <div style="font-size:0.82rem; color:var(--text-secondary);">
                        Scale rating (1–10 or configured range). Evaluator enters a numeric score.
                    </div>
                @elseif($q->type === 'text')
                    <div style="font-size:0.82rem; color:var(--text-secondary);">
                        Open-ended text response. No numeric score calculated.
                    </div>
                @endif

                @if($q->options->isNotEmpty())
                    <div style="margin-top:10px; font-size:0.82rem; color:var(--text-secondary);">
                        <strong>Options:</strong>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:6px;">
                            @foreach($q->options as $opt)
                                <span class="badge badge-gray" style="font-size:0.78rem;">
                                    {{ $opt->option_label }}
                                    <span style="color:var(--green-primary); font-weight:700; margin-left:4px;">
                                        +{{ number_format($opt->option_value, 1) }}
                                    </span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align:center; padding:40px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text-muted);">
                No questions configured.
                @if($isSuper)
                    <a href="{{ route('assessments.edit', $assessment->id) }}" style="color:var(--green-primary);">Add questions →</a>
                @endif
            </div>
        @endforelse
    </div>
@endif

{{-- ======================================================
     TAB: PANELISTS (Super Admin only)
====================================================== --}}
@if($activeTab === 'panelists' && $isSuper)
    <div class="card">
        <div class="card-header">
            <span class="card-title">👥 Assigned Panelists / Evaluators</span>
            <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-outline btn-sm">
                Manage Panelists
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Panel</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessment->panelists as $p)
                        @php
                            $assignment = $assessment->assignments
                                ->where('user_id', $p->id)
                                ->where('role', 'panelist')
                                ->first();
                        @endphp
                        <tr>
                            <td><strong>{{ $p->name }}</strong></td>
                            <td style="color:var(--text-secondary); font-size:0.85rem;">{{ $p->email }}</td>
                            <td>
                                <span class="badge badge-gray">
                                    {{ $assignment?->panel_name ?: ($p->panel ?: 'All') }}
                                </span>
                            </td>
                            <td><span class="badge badge-blue">{{ ucfirst($p->role) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:32px; color:var(--text-muted);">
                                No panelists assigned.
                                <a href="{{ route('assessments.edit', $assessment->id) }}" style="color:var(--green-primary);">Assign panelists →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ======================================================
     TAB: CANDIDATES (Super Admin only)
====================================================== --}}
@if($activeTab === 'candidates' && $isSuper)
    <div class="card">
        <div class="card-header">
            <span class="card-title">🧑‍🎓 Assigned Candidates</span>
            <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-outline btn-sm">
                Manage Candidates
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Panel Assignment</th>
                        <th style="text-align:right;">Quick Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessment->candidates as $c)
                        @php
                            $candAssignment = $assessment->assignments
                                ->where('candidate_id', $c->id)
                                ->where('role', 'candidate')
                                ->first();
                        @endphp
                        <tr>
                            <td><strong>{{ $c->name }}</strong></td>
                            <td>
                                <span class="badge {{ $c->gender === 'Female' ? 'badge-purple' : 'badge-blue' }}">
                                    {{ $c->gender ?: 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-gray">
                                    Panel {{ $candAssignment?->panel_name ?: $c->panel }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('assessments.evaluate', [$assessment->id, 'candidate_id' => $c->id]) }}"
                                   class="btn btn-primary btn-sm">Grade</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:32px; color:var(--text-muted);">
                                No candidates assigned.
                                <a href="{{ route('assessments.edit', $assessment->id) }}" style="color:var(--green-primary);">Add candidates →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ======================================================
     TAB: RULES & CONFIG (Super Admin only)
====================================================== --}}
@if($activeTab === 'rules' && $isSuper)
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        {{-- Scoring Rules Card --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">📐 Scoring Rules</span>
                <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-outline btn-sm">Edit</a>
            </div>
            <div class="card-body">
                @if($assessment->rule)
                    <table style="width:100%; font-size:0.9rem; border-collapse:collapse;">
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 0; color:var(--text-secondary);">Max Panelists</td>
                            <td style="padding:10px 0; font-weight:600; text-align:right;">
                                {{ $assessment->rule->max_panelists ?? 'Unlimited' }}
                            </td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 0; color:var(--text-secondary);">Score Cap</td>
                            <td style="padding:10px 0; font-weight:600; text-align:right;">
                                {{ $assessment->rule->score_cap !== null ? number_format($assessment->rule->score_cap, 2) : 'None' }}
                            </td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 0; color:var(--text-secondary);">Passing Threshold</td>
                            <td style="padding:10px 0; font-weight:600; text-align:right;">
                                {{ $assessment->rule->passing_threshold !== null ? number_format($assessment->rule->passing_threshold, 2) : 'N/A' }}
                            </td>
                        </tr>
                        @if(!empty($assessment->rule->rules_json['number_of_panels']))
                            <tr>
                                <td style="padding:10px 0; color:var(--text-secondary);">Number of Panels</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right;">
                                    {{ $assessment->rule->rules_json['number_of_panels'] }}
                                </td>
                            </tr>
                        @endif
                    </table>

                    @if(!empty($assessment->rule->rules_json) && count($assessment->rule->rules_json) > 0)
                        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border);">
                            <div style="font-size:0.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Extra Rules JSON</div>
                            <pre style="background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm); font-size:0.78rem; overflow-x:auto; border:1px solid var(--border);">{{ json_encode($assessment->rule->rules_json, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                @else
                    <div style="color:var(--text-muted); font-size:0.9rem; padding:8px 0;">
                        No scoring rules configured.
                        <a href="{{ route('assessments.edit', $assessment->id) }}" style="color:var(--green-primary);">Add rules →</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Assessment Info Card --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">ℹ️ Assessment Details</span>
            </div>
            <div class="card-body">
                <table style="width:100%; font-size:0.9rem; border-collapse:collapse;">
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Assessment ID</td>
                        <td style="padding:10px 0; font-weight:600; text-align:right; font-family:monospace;">#{{ $assessment->id }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Type</td>
                        <td style="padding:10px 0; text-align:right;">
                            <span class="badge {{ $typeBadgeClass }}">{{ $assessment->type }}</span>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Status</td>
                        <td style="padding:10px 0; text-align:right;">
                            <span class="badge {{ $statusBadgeClass }}">{{ $assessment->status }}</span>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Survey Access Key</td>
                        <td style="padding:10px 0; font-weight:700; text-align:right; font-family:monospace; color:var(--green-primary);">
                            {{ $assessment->access_key ?: 'None' }}
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Created</td>
                        <td style="padding:10px 0; text-align:right;">{{ $assessment->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:var(--text-secondary);">Last Updated</td>
                        <td style="padding:10px 0; text-align:right;">{{ $assessment->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>

                <div style="margin-top:20px; display:flex; flex-direction:column; gap:8px;">
                    <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-primary btn-full">
                        ⚙️ Edit Full Configuration
                    </a>
                    <form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST"
                          onsubmit="return confirm('Permanently delete this assessment and all its data?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-full">
                            🗑️ Delete Assessment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
