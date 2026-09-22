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
            @if($results['is_survey'])
                <span class="badge badge-purple">
                    📌 {{ ucfirst($results['survey_stage']) }} Survey
                </span>
                <span class="badge {{ $results['is_anonymous'] ? 'badge-blue' : 'badge-teal' }}">
                    {{ $results['is_anonymous'] ? '🔒 Anonymous Responses' : '🧑‍🎓 Identified Participants' }}
                </span>
            @endif
            @if($assessment->access_key)
                <span class="badge badge-gray" style="font-family:monospace; letter-spacing:1px;">
                    🔑 {{ $assessment->access_key }}
                </span>
            @endif
            @if(!$results['is_survey'] && $assessment->rule?->passing_threshold !== null)
                <span class="badge badge-teal">
                    Pass: {{ number_format($assessment->rule->passing_threshold, 1) }}
                </span>
            @endif
            @if(!$results['is_survey'] && $assessment->rule?->score_cap !== null)
                <span class="badge badge-orange">
                    Cap: {{ number_format($assessment->rule->score_cap, 1) }}
                </span>
            @endif
        </div>
    </div>

    <div class="hub-header-actions" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <button type="button" class="btn btn-sm" style="background:#2563eb; color:white;" onclick="openModal('importResultsModal')">
            📥 Import Results
        </button>
        @if($results['is_survey'])
            <a href="{{ route('surveys.take', $assessment->id) }}"
               class="btn btn-sm" style="background:#7c3aed; color:white;" target="_blank">
                📋 Take / Open Survey ↗
            </a>
        @else
            <a href="{{ route('assessments.evaluate', $assessment->id) }}"
               class="btn btn-sm" style="background:#0f766e; color:white;">
                ✏️ Grade / Evaluate
            </a>
        @endif
        @if($isSuper)
            <a href="{{ route('assessments.edit', $assessment->id) }}"
               class="btn btn-outline btn-sm">⚙️ Edit</a>
            <form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this {{ $assessment->type }} and all associated evaluation scores and candidate assignments?');"
                  style="display:inline; margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="padding:6px 10px;" title="Delete this {{ $assessment->type }}">
                    🗑️ Delete
                </button>
            </form>
        @endif
        <a href="{{ route('assessments.index') }}"
           class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- ========== METRIC OVERVIEW ========== --}}
@if($results['is_survey'])
    {{-- Survey M&E Metric Cards --}}
    <div class="metric-cards" style="margin-bottom:24px;">
        <div class="metric-card">
            <div class="value" style="color:#2563eb;">{{ $results['total_evaluations_submitted'] }}</div>
            <div class="label">Total Responses</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#7c3aed;">{{ ucfirst($results['survey_stage']) }}</div>
            <div class="label">M&amp;E Stage</div>
        </div>
        <div class="metric-card">
            <div class="value">{{ $results['total_questions'] }}</div>
            <div class="label">Questions</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:var(--green-primary);">
                {{ $results['overall_survey_average'] > 0 ? number_format($results['overall_survey_average'], 2) : '—' }}
            </div>
            <div class="label">Avg Rating (1-5)</div>
        </div>
        @if(!empty($results['stage_counts']))
            <div class="metric-card">
                <div class="value" style="font-size:1.05rem; display:flex; gap:6px; justify-content:center; align-items:center;">
                    <span style="color:#059669; font-weight:700;" title="Baseline">🌱 {{ $results['stage_counts']['baseline'] ?? 0 }}</span>
                    <span style="color:#7c3aed; font-weight:700;" title="Midline">⚖️ {{ $results['stage_counts']['midline'] ?? 0 }}</span>
                    <span style="color:#0284c7; font-weight:700;" title="Endline">🎓 {{ $results['stage_counts']['endline'] ?? 0 }}</span>
                </div>
                <div class="label">Base / Mid / End</div>
            </div>
        @endif
        <div class="metric-card">
            <div class="value" style="color:#64748b; font-size:1.1rem;">
                {{ $results['is_anonymous'] ? 'Anonymous' : 'Identified' }}
            </div>
            <div class="label">Response Mode</div>
        </div>
    </div>
@else
    {{-- Graded Assessment & Multi-Round Selection Metric Cards --}}
    <div class="metric-cards" style="margin-bottom:24px;">
        <div class="metric-card">
            <div class="value">{{ $results['total_candidates'] }}</div>
            <div class="label">Total Candidates</div>
            @if($results['rounds_list'] && count($results['rounds_list']) > 1)
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                    Across {{ count($results['rounds_list']) }} Selection Rounds
                </div>
            @endif
        </div>
        <div class="metric-card">
            <div class="value" style="color:var(--green-primary);">{{ $results['selected_count'] }}</div>
            <div class="label">🟢 Selected for Cohort</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#f59e0b;">{{ $results['reserve_count'] }}</div>
            <div class="label">🟡 Reserve Pool</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#ef4444;">{{ $results['pulled_out_count'] }}</div>
            <div class="label">⚪ Pulled Out / Declined</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:var(--teal-primary);">{{ $results['total_panelists'] }}</div>
            <div class="label">Panelists</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#64748b;">{{ $results['total_evaluations_submitted'] }}</div>
            <div class="label">Scores Logged</div>
        </div>
    </div>
@endif

{{-- ======================================================
     TAB NAVIGATION
====================================================== --}}
<div class="hub-tabs">
    <a href="?tab=results" class="hub-tab {{ $activeTab === 'results' ? 'active' : '' }}">
        {{ $results['is_survey'] ? '📊 Survey Analysis & Insights' : '🏆 Selection Leaderboard' }}
    </a>
    <a href="?tab=candidates" class="hub-tab {{ $activeTab === 'candidates' ? 'active' : '' }}">
        {{ $results['is_survey'] ? '👥 Respondents & Participation' : '👥 Candidates & Rounds (' . $results['total_candidates'] . ')' }}
    </a>
    <a href="?tab=panelists" class="hub-tab {{ $activeTab === 'panelists' ? 'active' : '' }}">
        👨‍🏫 Panelists ({{ $results['total_panelists'] }})
    </a>
    <a href="?tab=questions" class="hub-tab {{ $activeTab === 'questions' ? 'active' : '' }}">
        📋 Rubric &amp; Questions ({{ $results['total_questions'] }})
    </a>
    @if($isSuper)
        <a href="?tab=settings" class="hub-tab {{ $activeTab === 'settings' ? 'active' : '' }}">
            ⚙️ Configuration
        </a>
    @endif
</div>

{{-- ======================================================
     TAB: RESULTS & LEADERBOARD (OR SURVEY ANALYSIS)
====================================================== --}}
@if($activeTab === 'results')
    @if($results['is_survey'])
        {{-- ======================================================
             SURVEY SPECIFIC ANALYSIS VIEW (NO PASS/FAIL)
        ====================================================== --}}
        <div style="display:grid; gap:20px;">
            {{-- Survey Bar Chart Overview --}}
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <span class="card-title">📊 Survey Competency Rating Bar Chart</span>
                        <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:2px;">
                            Hover over any bar to view the full question text · <strong>{{ ucfirst($results['survey_stage']) }} (/5.00)</strong>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        <a href="{{ route('assessments.template', ['assessment' => $assessment->id, 'format' => 'xlsx']) }}"
                           class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                            📥 Export (.xlsx)
                        </a>
                        <a href="{{ route('assessments.template', ['assessment' => $assessment->id, 'format' => 'csv']) }}"
                           class="btn btn-ghost btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                            .csv
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('importResultsModal')" style="display:inline-flex; align-items:center; gap:6px;">
                            📤 Import Results
                        </button>
                    </div>
                </div>

                {{-- Interactive Stage Filter Bar --}}
                <div style="padding:10px 20px; background:var(--surface-alt); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <span style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-right:4px;">Filter Stage:</span>
                        <button type="button" class="assessment-stage-btn active" onclick="filterAssessmentSurveyStage('all', this)"
                                style="padding:4px 10px; font-size:0.78rem; border-radius:100px; border:1px solid var(--green-primary); background:var(--green-primary); color:white; font-weight:700; cursor:pointer;">
                            📊 All Stages Comparison
                        </button>
                        <button type="button" class="assessment-stage-btn" onclick="filterAssessmentSurveyStage('baseline', this)"
                                style="padding:4px 10px; font-size:0.78rem; border-radius:100px; border:1px solid var(--border); background:#fff; color:var(--text-primary); font-weight:600; cursor:pointer;">
                            🟢 Baseline ({{ $results['stage_counts']['baseline'] ?? 0 }})
                        </button>
                        <button type="button" class="assessment-stage-btn" onclick="filterAssessmentSurveyStage('midline', this)"
                                style="padding:4px 10px; font-size:0.78rem; border-radius:100px; border:1px solid var(--border); background:#fff; color:var(--text-primary); font-weight:600; cursor:pointer;">
                            🟣 Midline ({{ $results['stage_counts']['midline'] ?? 0 }})
                        </button>
                        <button type="button" class="assessment-stage-btn" onclick="filterAssessmentSurveyStage('endline', this)"
                                style="padding:4px 10px; font-size:0.78rem; border-radius:100px; border:1px solid var(--border); background:#fff; color:var(--text-primary); font-weight:600; cursor:pointer;">
                            🔵 Endline ({{ $results['stage_counts']['endline'] ?? 0 }})
                        </button>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">
                        Scale: 1 (Lowest) to 5 (Highest)
                    </div>
                </div>

                <div style="padding:16px 20px;">
                    <div style="height: 320px; position: relative;">
                        <canvas id="assessmentSurveyChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Qualitative Feedback Feed (Collapsible & Stage Filtered) --}}
            @if(!empty($results['qualitative_feedback']))
                @php
                    $totalQualCount = 0;
                    foreach ($results['qualitative_feedback'] as $qFeed) {
                        $totalQualCount += count($qFeed['responses']);
                    }
                @endphp
                <div class="card" id="qualitativeFeedbackCard">
                    <div class="card-header" onclick="toggleQualitativeCollapse()" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="card-title" style="margin: 0;">💬 Qualitative Participant Feedback</span>
                            <span class="badge badge-gray" id="qualCountBadge" style="font-size: 0.75rem; font-weight: 700;">
                                {{ $totalQualCount }} responses
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-size: 0.82rem;">
                            <span style="font-size: 0.78rem;">Click to toggle</span>
                            <span id="qualToggleIcon" style="font-size: 0.95rem; font-weight: bold; transition: transform 0.2s;">▼</span>
                        </div>
                    </div>
                    <div id="qualitativeFeedbackBody" style="padding: 20px; display: grid; gap: 16px;">
                        <div id="qualEmptyState" style="display: none; padding: 24px; text-align: center; color: var(--text-secondary); background: var(--surface-alt); border-radius: var(--radius-sm); border: 1px dashed var(--border);">
                            💬 No qualitative text responses submitted for this survey stage.
                        </div>

                        @foreach($results['qualitative_feedback'] as $qFeed)
                            <div class="qual-topic-block" style="background: var(--surface-alt); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px;">
                                <div style="font-weight: 700; color: var(--text-primary); font-size: 0.9rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                    <span>❓</span>
                                    <span>{{ $qFeed['question_text'] }}</span>
                                </div>
                                <div class="qual-responses-list" style="display: grid; gap: 8px;">
                                    @foreach($qFeed['responses'] as $resp)
                                        @php
                                            $respText = is_array($resp) ? ($resp['text'] ?? '') : $resp;
                                            $respStage = is_array($resp) ? strtolower($resp['stage'] ?? 'baseline') : 'baseline';
                                            $stageBadgeClass = match($respStage) {
                                                'midline' => 'badge-purple',
                                                'endline' => 'badge-blue',
                                                default => 'badge-teal',
                                            };
                                            $stageBorderColor = match($respStage) {
                                                'midline' => '#8b5cf6',
                                                'endline' => '#0ea5e9',
                                                default => '#10b981',
                                            };
                                            $stageIcon = match($respStage) {
                                                'midline' => '⚖️',
                                                'endline' => '🎓',
                                                default => '🌱',
                                            };
                                        @endphp
                                        <div class="qual-response-item" data-stage="{{ $respStage }}"
                                             style="background: #fff; border-left: 3px solid {{ $stageBorderColor }}; padding: 10px 14px; border-radius: 4px; font-size: 0.85rem; color: var(--text-primary); line-height: 1.4; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                                            <div style="flex: 1;">
                                                "{{ $respText }}"
                                            </div>
                                            <span class="badge {{ $stageBadgeClass }}" style="font-size: 0.7rem; padding: 2px 8px; flex-shrink: 0; text-transform: capitalize;">
                                                {{ $stageIcon }} {{ $respStage }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- ======================================================
             GRADED CANDIDATE EVALUATION & SELECTION LEADERBOARD
        ====================================================== --}}
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <span class="card-title">🏆 Candidate Selection Leaderboard</span>
                    <div class="info-box info" style="margin:4px 0 0; padding:4px 12px; font-size:0.75rem; border-radius:100px; display:inline-block;">
                        <strong>Selection &amp; Replacement:</strong> Mark candidates as Selected, Reserve Pool, or Pulled Out to manage multi-round cohort admissions.
                    </div>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="{{ route('assessments.template', ['assessment' => $assessment->id, 'format' => 'xlsx']) }}"
                       class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                        📥 Template (.xlsx)
                    </a>
                    <a href="{{ route('assessments.template', ['assessment' => $assessment->id, 'format' => 'csv']) }}"
                       class="btn btn-ghost btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                        .csv
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('importResultsModal')" style="display:inline-flex; align-items:center; gap:6px;">
                        📤 Import Results
                    </button>
                </div>
            </div>

            {{-- Multi-Round Filter Tabs --}}
            <div style="padding:12px 20px; background:var(--surface-alt); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-right:4px;">Round Filter:</span>
                    <button type="button" class="round-filter-btn active" onclick="filterByRound('all', this)"
                            style="padding:4px 10px; font-size:0.78rem; border-radius:100px; border:1px solid var(--border); background:var(--green-primary); color:white; font-weight:600; cursor:pointer;">
                        All Rounds ({{ count($results['candidate_results']) }})
                    </button>
                    @foreach($results['rounds_list'] as $rNum)
                        @php
                            $rCount = count(array_filter($results['candidate_results'], fn($item) => (int)($item['round'] ?? 1) === $rNum));
                            $rLabel = match($rNum) {
                                1 => 'Round 1 (Initial)',
                                2 => 'Round 2 (Replacement)',
                                3 => 'Round 3 (Reserve / Extension)',
                                default => "Round {$rNum}",
                            };
                        @endphp
                        <button type="button" class="round-filter-btn" onclick="filterByRound({{ $rNum }}, this)"
                                style="padding:4px 10px; font-size:0.78rem; border-radius:100px; border:1px solid var(--border); background:#fff; color:var(--text-primary); font-weight:600; cursor:pointer;">
                            {{ $rLabel }} ({{ $rCount }})
                        </button>
                    @endforeach
                </div>

                @if($isSuper)
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline" onclick="openModal('addCandidatesModal')">
                            + Add Round 2 / Replacement Candidates
                        </button>
                    </div>
                @endif
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table" id="leaderboardTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">Rank</th>
                            <th>Candidate</th>
                            <th style="text-align:center;">Round</th>
                            <th>Panel</th>
                            <th style="text-align:center;">Evaluations</th>
                            <th style="text-align:right;">Final Score</th>
                            <th style="text-align:center;">Benchmark</th>
                            <th style="text-align:center;">Selection Status</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results['candidate_results'] as $idx => $res)
                            @php
                                $cRound = (int) ($res['round'] ?? 1);
                                $cStatus = $res['selection_status'] ?? 'pending';

                                $statusBadge = match($cStatus) {
                                    'selected'   => ['class' => 'badge-green', 'icon' => '🟢', 'label' => 'Selected'],
                                    'reserve'    => ['class' => 'badge-orange', 'icon' => '🟡', 'label' => 'Reserve Pool'],
                                    'pulled_out' => ['class' => 'badge-red', 'icon' => '🔴', 'label' => 'Pulled Out'],
                                    'rejected'   => ['class' => 'badge-red', 'icon' => '✕', 'label' => 'Not Selected'],
                                    default      => ['class' => 'badge-gray', 'icon' => '⚪', 'label' => 'Pending'],
                                };

                                $rowHighlight = match($cStatus) {
                                    'selected' => 'background:rgba(16, 185, 129, 0.04);',
                                    'pulled_out' => 'background:rgba(239, 68, 68, 0.04); opacity:0.85;',
                                    default => '',
                                };
                            @endphp
                            <tr class="cand-row {{ $res['passed'] === true ? 'accepted' : '' }}" data-round="{{ $cRound }}" style="{{ $rowHighlight }}">
                                <td style="font-weight:700; color:var(--text-muted);">
                                    #{{ $idx + 1 }}
                                </td>
                                <td>
                                    <strong>{{ $res['candidate']->name }}</strong>
                                    @if($res['candidate']->gender)
                                        <span style="font-size:0.75rem; color:var(--text-muted); margin-left:4px;">({{ $res['candidate']->gender }})</span>
                                    @endif
                                    @if($res['selection_notes'])
                                        <div style="font-size:0.75rem; color:#6b7280; margin-top:2px; font-style:italic;">
                                            📝 Note: {{ $res['selection_notes'] }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge {{ $cRound > 1 ? 'badge-purple' : 'badge-gray' }}" style="font-size:0.75rem;">
                                        R{{ $cRound }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-gray">Panel {{ $res['panel_name'] ?: 'A' }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:600;">{{ $res['evaluator_count'] }}</span>
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:1.05rem; color:var(--green-primary);">
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
                                <td style="text-align:center;">
                                    <span class="badge {{ $statusBadge['class'] }}" style="display:inline-flex; align-items:center; gap:4px; font-size:0.78rem;">
                                        {{ $statusBadge['icon'] }} {{ $statusBadge['label'] }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:4px; align-items:center;">
                                        <a href="{{ route('assessments.evaluate', [$assessment->id, 'candidate_id' => $res['candidate']->id]) }}"
                                           class="btn btn-primary btn-sm" style="padding:3px 8px; font-size:0.78rem;">
                                            Grade
                                        </a>
                                        @if($isSuper)
                                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px; font-size:0.78rem;"
                                                    onclick="openStatusModal({{ $res['candidate']->id }}, '{{ addslashes($res['candidate']->name) }}', {{ $cRound }}, '{{ $cStatus }}', '{{ addslashes($res['selection_notes'] ?? '') }}')">
                                                Status ▾
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center; padding:40px; color:var(--text-muted);">
                                    No candidates assigned or evaluated yet.
                                    @if($isSuper)
                                        <div style="margin-top:8px;">
                                            <a href="javascript:void(0)" onclick="openModal('addCandidatesModal')" style="color:var(--green-primary); font-weight:600;">+ Add candidates now</a>
                                            or
                                            <a href="javascript:void(0)" onclick="openModal('importResultsModal')" style="color:#2563eb; font-weight:600;">📥 Import Results from Excel</a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
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
     TAB: CANDIDATES & MULTI-ROUND SELECTION (Super Admin only)
====================================================== --}}
@if($activeTab === 'candidates' && $isSuper)
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <span class="card-title">🧑‍🎓 Candidates &amp; Selection Rounds ({{ $assessment->candidates->count() }})</span>
                <p style="margin:2px 0 0; color:var(--text-secondary); font-size:0.82rem;">
                    Manage candidate rosters across rounds (e.g. Round 1 initial, Round 2 replacement pool) and track selection lifecycle.
                </p>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCandidatesModal')">
                    + Add Candidates / Replacement Round
                </button>
                <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-outline btn-sm">
                    ⚙️ Configure Roster
                </a>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Candidate Name</th>
                        <th style="text-align:center;">Round</th>
                        <th style="text-align:center;">Selection Status</th>
                        <th>Gender Track</th>
                        <th>Assigned Panel</th>
                        <th style="text-align:center;">Evaluated</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessment->candidates as $c)
                        @php
                            $candAssignment = $assessment->assignments
                                ->where('candidate_id', $c->id)
                                ->where('role', 'candidate')
                                ->first();

                            $cRound = (int) ($candAssignment?->round ?? 1);
                            $cStatus = $candAssignment?->selection_status ?? 'pending';
                            $cNotes = $candAssignment?->selection_notes ?? '';

                            $hasScores = \App\Models\EvaluationScore::where('assessment_id', $assessment->id)
                                ->where('candidate_id', $c->id)
                                ->exists();

                            $statusBadge = match($cStatus) {
                                'selected'   => ['class' => 'badge-green', 'icon' => '🟢', 'label' => 'Selected'],
                                'reserve'    => ['class' => 'badge-orange', 'icon' => '🟡', 'label' => 'Reserve Pool'],
                                'pulled_out' => ['class' => 'badge-red', 'icon' => '🔴', 'label' => 'Pulled Out'],
                                'rejected'   => ['class' => 'badge-red', 'icon' => '✕', 'label' => 'Not Selected'],
                                default      => ['class' => 'badge-gray', 'icon' => '⚪', 'label' => 'Pending'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $c->name }}</strong>
                                @if($cNotes)
                                    <div style="font-size:0.75rem; color:#6b7280; margin-top:2px; font-style:italic;">
                                        📝 {{ $cNotes }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $cRound > 1 ? 'badge-purple' : 'badge-gray' }}" style="font-size:0.75rem;">
                                    Round {{ $cRound }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $statusBadge['class'] }}" style="font-size:0.75rem;">
                                    {{ $statusBadge['icon'] }} {{ $statusBadge['label'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $c->gender === 'Female' ? 'badge-purple' : 'badge-blue' }}">
                                    {{ $c->gender ?: 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-gray">
                                    Panel {{ $candAssignment?->panel_name ?: ($c->panel ?: 'A') }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @if($hasScores)
                                    <span class="badge badge-green">Scored</span>
                                @else
                                    <span class="badge badge-gray">Pending</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px; align-items:center;">
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:0.78rem;"
                                            onclick="openStatusModal({{ $c->id }}, '{{ addslashes($c->name) }}', {{ $cRound }}, '{{ $cStatus }}', '{{ addslashes($cNotes) }}')">
                                        Status
                                    </button>
                                    <a href="{{ route('assessments.evaluate', [$assessment->id, 'candidate_id' => $c->id]) }}"
                                       class="btn btn-primary btn-sm" style="padding:4px 8px; font-size:0.78rem;">
                                        Grade
                                    </a>
                                    <form action="{{ route('assessments.candidates.remove', ['assessment' => $assessment->id, 'candidate' => $c->id]) }}" method="POST"
                                          onsubmit="return confirm('Remove candidate {{ addslashes($c->name) }} from this assessment? Associated assessment evaluation scores will be detached.');"
                                          style="display:inline; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:0.85rem; color:#dc2626;" title="Remove from this assessment">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:36px; color:var(--text-muted);">
                                No candidates assigned to this assessment yet.
                                <div style="margin-top:10px;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCandidatesModal')">
                                        + Add Candidates Now
                                    </button>
                                </div>
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
        {{-- Scoring / M&E Rules Card --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">{{ $results['is_survey'] ? '📋 M&E Survey Configuration' : '📐 Scoring Rules' }}</span>
                <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-outline btn-sm">Edit</a>
            </div>
            <div class="card-body">
                @if($assessment->rule)
                    <table style="width:100%; font-size:0.9rem; border-collapse:collapse;">
                        @if($results['is_survey'])
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">M&amp;E Survey Stage</td>
                                <td style="padding:10px 0; font-weight:700; text-align:right;">
                                    <span class="badge badge-purple">{{ ucfirst($results['survey_stage']) }} Survey</span>
                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">Response Mode</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right;">
                                    {{ $results['is_anonymous'] ? '🔒 Anonymous (No names recorded)' : '🧑‍🎓 Identified Responses' }}
                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">Pass / Fail Scoring</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right; color:var(--text-muted);">
                                    Disabled (M&amp;E Analytics Only)
                                </td>
                            </tr>
                        @else
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
                        @endif
                    </table>

                    @if(!empty($assessment->rule->rules_json) && count($assessment->rule->rules_json) > 0)
                        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border);">
                            <div style="font-size:0.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Rules &amp; Parameters JSON</div>
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

{{-- ======================================================
     MODAL 1: IMPORT RESULTS SPREADSHEET
====================================================== --}}
<div id="importResultsModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('importResultsModal')"></div>
    <div class="pif-modal-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <h3 style="margin:0; font-size:1.2rem; display:flex; align-items:center; gap:8px;">
                📥 Import Results: {{ $assessment->title }}
            </h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('importResultsModal')">✕</button>
        </div>

        <div style="margin-bottom:16px; font-size:0.85rem; color:var(--text-secondary); line-height:1.4;">
            Upload an Excel (.xlsx, .xls) or CSV spreadsheet containing evaluated candidate scores. Any new candidates will be created and assigned automatically.
        </div>

        <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:12px; margin-bottom:16px;">
            <div style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">Step 1: Download Customized Template</div>
            <div style="display:flex; gap:8px; align-items:center;">
                <a href="{{ route('assessments.template', ['assessment' => $assessment->id, 'format' => 'xlsx']) }}" class="btn btn-outline btn-sm" style="font-size:0.8rem;">
                    📥 Template (.xlsx)
                </a>
                <a href="{{ route('assessments.template', ['assessment' => $assessment->id, 'format' => 'csv']) }}" class="btn btn-ghost btn-sm" style="font-size:0.8rem;">
                    📥 Template (.csv)
                </a>
            </div>
        </div>

        <div style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">Step 2: Upload Completed Score Sheet</div>
        <form action="{{ route('assessments.import-results', $assessment->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="margin-bottom:16px;">
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required style="padding:8px; font-size:0.85rem;">
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Max file size: 10MB.</small>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('importResultsModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#2563eb;">Upload &amp; Import</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================================================
     MODAL 2: ADD CANDIDATES / REPLACEMENT ROUND
====================================================== --}}
<div id="addCandidatesModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('addCandidatesModal')"></div>
    <div class="pif-modal-card" style="max-width:580px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <h3 style="margin:0; font-size:1.2rem; display:flex; align-items:center; gap:8px;">
                🧑‍🎓 Add Candidates &amp; Selection Rounds
            </h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('addCandidatesModal')">✕</button>
        </div>

        @php
            $assignedCandIds = $assessment->candidates->pluck('id')->toArray();
        @endphp

        <form action="{{ route('assessments.candidates.add', $assessment->id) }}" method="POST">
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                <div>
                    <label for="modal_target_round" style="font-weight:700; font-size:0.82rem; color:var(--text-primary); display:block; margin-bottom:4px;">
                        🎯 Selection Round:
                    </label>
                    <select name="round" id="modal_target_round" class="form-control" style="font-size:0.82rem; height:36px;">
                        <option value="1">Round 1 (Initial / Main)</option>
                        <option value="2">Round 2 (Replacement Pool)</option>
                        <option value="3">Round 3 (Reserve / Standby)</option>
                        <option value="4">Round 4 (Special Cohort)</option>
                    </select>
                </div>
                <div>
                    <label for="assign_panel_name" style="font-weight:700; font-size:0.82rem; color:var(--text-primary); display:block; margin-bottom:4px;">
                        👥 Assigned Panel:
                    </label>
                    <select name="panel_name" id="assign_panel_name" class="form-control" style="font-size:0.82rem; height:36px;">
                        <option value="">Default Candidate Panel</option>
                        <option value="A">Panel A</option>
                        <option value="B">Panel B</option>
                        <option value="cover">Cover / Observer</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label style="font-weight:700; font-size:0.85rem; color:var(--text-primary); margin:0;">
                        1. Select From Existing Roster:
                    </label>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="selectAllModalCandidates(true)" class="btn btn-ghost btn-sm" style="font-size:0.75rem; padding:2px 6px;">Select All</button>
                        <button type="button" onclick="selectAllModalCandidates(false)" class="btn btn-ghost btn-sm" style="font-size:0.75rem; padding:2px 6px;">Clear</button>
                    </div>
                </div>

                <input type="text" id="modalCandidateSearch" onkeyup="filterModalCandidates()" placeholder="Search candidate name..."
                       style="width:100%; padding:6px 10px; font-size:0.82rem; border:1px solid var(--border); border-radius:var(--radius-sm); margin-bottom:8px;">

                <div id="modalCandidatesList" style="max-height:160px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius-sm); padding:8px 12px; background:var(--surface-alt);">
                    @foreach($allCandidates as $cand)
                        @php
                            $isAlreadyAssigned = in_array($cand->id, $assignedCandIds);
                        @endphp
                        <label class="modal-cand-item" style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:4px 0; margin:0; font-weight:normal; cursor:{{ $isAlreadyAssigned ? 'default' : 'pointer' }}; opacity:{{ $isAlreadyAssigned ? '0.6' : '1' }};">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="candidate_ids[]" value="{{ $cand->id }}" class="modal-cand-cb" {{ $isAlreadyAssigned ? 'disabled checked' : '' }} style="accent-color:var(--green-primary);">
                                <span class="cand-name-text">{{ $cand->name }}</span>
                            </div>
                            <span class="badge badge-gray" style="font-size:0.72rem;">
                                {{ $isAlreadyAssigned ? 'Assigned' : 'Panel ' . ($cand->panel ?: 'A') }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="border-top:1px dashed #ddd; padding-top:12px; margin-bottom:16px;">
                <div style="font-weight:700; font-size:0.85rem; color:var(--text-primary); margin-bottom:8px;">
                    2. OR Quick Register a New Candidate:
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:8px;">
                    <input type="text" name="new_candidate_name" placeholder="Full name" class="form-control" style="font-size:0.82rem; height:34px;">
                    <select name="new_candidate_gender" class="form-control" style="font-size:0.82rem; height:34px;">
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                    </select>
                    <select name="new_candidate_panel" class="form-control" style="font-size:0.82rem; height:34px;">
                        <option value="A">Panel A</option>
                        <option value="B">Panel B</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCandidatesModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save &amp; Assign Candidates</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================================================
     MODAL 3: UPDATE CANDIDATE SELECTION STATUS & ROUND
====================================================== --}}
<div id="updateStatusModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('updateStatusModal')"></div>
    <div class="pif-modal-card" style="max-width:480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <h3 style="margin:0; font-size:1.15rem; display:flex; align-items:center; gap:8px;">
                🎯 Update Candidate Status
            </h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('updateStatusModal')">✕</button>
        </div>

        <form id="updateStatusForm" method="POST" action="">
            @csrf
            <div style="margin-bottom:12px;">
                <div style="font-size:0.82rem; color:var(--text-secondary);">Candidate:</div>
                <div id="statusModalCandName" style="font-weight:700; font-size:1rem; color:var(--text-primary); margin-top:2px;"></div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="statusSelect" style="font-weight:700; font-size:0.85rem;">Selection Status:</label>
                <select name="selection_status" id="statusSelect" class="form-control" style="font-size:0.88rem;" required>
                    <option value="pending">⚪ Pending Review (Under Evaluation)</option>
                    <option value="selected">🟢 Selected / Confirmed (Finalist Cohort)</option>
                    <option value="reserve">🟡 Reserve / Standby Pool (Next in Line)</option>
                    <option value="pulled_out">🔴 Pulled Out / Declined (Passed &amp; Withdrew)</option>
                    <option value="rejected">✕ Not Selected / Failed Benchmarks</option>
                </select>
                <small style="color:var(--text-muted); display:block; margin-top:4px;">
                    When a candidate is marked "Pulled Out", their score is preserved and replacement candidates can be promoted from the Reserve Pool or added in Round 2.
                </small>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="statusRoundInput" style="font-weight:700; font-size:0.85rem;">Assigned Round:</label>
                <select name="round" id="statusRoundInput" class="form-control" style="font-size:0.88rem;">
                    <option value="1">Round 1 (Initial / Main)</option>
                    <option value="2">Round 2 (Replacement Round)</option>
                    <option value="3">Round 3 (Reserve / Additional)</option>
                    <option value="4">Round 4</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="statusNotesInput" style="font-weight:700; font-size:0.85rem;">Selection / Replacement Rationale Notes:</label>
                <textarea name="selection_notes" id="statusNotesInput" class="form-control" rows="3" placeholder="e.g. Candidate accepted another job offer; replaced by candidate from reserve pool." style="font-size:0.85rem;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('updateStatusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Status Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
.pif-modal {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pif-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
}
.pif-modal-card {
    position: relative;
    background: #ffffff;
    border-radius: var(--radius-lg);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    width: 92%;
    max-width: 500px;
    padding: 24px;
    z-index: 1051;
    animation: modalSlide 0.2s ease-out;
}
@keyframes modalSlide {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.round-filter-btn.active {
    background: var(--green-primary) !important;
    color: white !important;
    border-color: var(--green-primary) !important;
}
</style>

<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function selectAllModalCandidates(checked) {
    document.querySelectorAll('.modal-cand-cb:not(:disabled)').forEach(cb => {
        cb.checked = checked;
    });
}

function filterModalCandidates() {
    const query = (document.getElementById('modalCandidateSearch').value || '').toLowerCase();
    document.querySelectorAll('.modal-cand-item').forEach(item => {
        const text = item.querySelector('.cand-name-text')?.textContent?.toLowerCase() || '';
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function filterByRound(round, btn) {
    document.querySelectorAll('.round-filter-btn').forEach(b => {
        b.classList.remove('active');
        b.style.background = '#fff';
        b.style.color = 'var(--text-primary)';
        b.style.borderColor = 'var(--border)';
    });

    btn.classList.add('active');
    btn.style.background = 'var(--green-primary)';
    btn.style.color = '#fff';
    btn.style.borderColor = 'var(--green-primary)';

    const rows = document.querySelectorAll('#leaderboardTable .cand-row');
    rows.forEach(row => {
        if (round === 'all') {
            row.style.display = '';
        } else {
            const rowRound = parseInt(row.getAttribute('data-round') || '1', 10);
            row.style.display = (rowRound === parseInt(round, 10)) ? '' : 'none';
        }
    });
}

function openStatusModal(candidateId, candidateName, round, currentStatus, notes) {
    document.getElementById('statusModalCandName').textContent = candidateName;
    document.getElementById('statusSelect').value = currentStatus || 'pending';
    document.getElementById('statusRoundInput').value = round || 1;
    document.getElementById('statusNotesInput').value = notes || '';

    const form = document.getElementById('updateStatusForm');
    form.action = "{{ url('admin/assessments/' . $assessment->id . '/candidates') }}/" + candidateId + "/status";

    openModal('updateStatusModal');
}

// Initialize Dynamic Survey Bar Chart if present
(function() {
    const canvas = document.getElementById('assessmentSurveyChart');
    if (!canvas || typeof Chart === 'undefined') return;

    @php
        $surveyChartLabels = [];
        $surveyChartFull = [];
        $surveyBaseData = [];
        $surveyMidData = [];
        $surveyEndData = [];
        $surveyOverallData = [];

        if ($results['is_survey'] && !empty($results['survey_question_stats'])) {
            $qNum = 1;
            foreach ($results['survey_question_stats'] as $sq) {
                // Exclude qualitative/text questions from the bar chart
                if ($sq['type'] !== 'text' && ($sq['type'] === 'scale' || $sq['avg_score'] !== null)) {
                    $txt = $sq['question_text'];
                    $surveyChartLabels[] = "Q" . $qNum;
                    $surveyChartFull[] = "Q{$qNum}: " . $txt;
                    $surveyBaseData[] = (float) ($sq['baseline_avg'] ?? 0);
                    $surveyMidData[] = (float) ($sq['midline_avg'] ?? 0);
                    $surveyEndData[] = (float) ($sq['endline_avg'] ?? 0);
                    $surveyOverallData[] = (float) ($sq['avg_score'] ?? 0);
                    $qNum++;
                }
            }
        }
    @endphp

    const labels = {!! json_encode($surveyChartLabels) !!};
    const fullLabels = {!! json_encode($surveyChartFull) !!};

    const baseData = {!! json_encode($surveyBaseData) !!};
    const midData = {!! json_encode($surveyMidData) !!};
    const endData = {!! json_encode($surveyEndData) !!};
    const overallData = {!! json_encode($surveyOverallData) !!};

    if (labels.length === 0) return;

    const datasetsConfig = {
        baseline: {
            label: 'Baseline Stage',
            data: baseData,
            backgroundColor: 'rgba(16, 185, 129, 0.75)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 1.5,
            borderRadius: 4
        },
        midline: {
            label: 'Midline Stage',
            data: midData,
            backgroundColor: 'rgba(139, 92, 246, 0.75)',
            borderColor: 'rgb(139, 92, 246)',
            borderWidth: 1.5,
            borderRadius: 4
        },
        endline: {
            label: 'Endline Stage',
            data: endData,
            backgroundColor: 'rgba(14, 165, 233, 0.75)',
            borderColor: 'rgb(14, 165, 233)',
            borderWidth: 1.5,
            borderRadius: 4
        }
    };

    let chartInstance = null;

    function renderAssessmentChart(stage) {
        const ctx = canvas.getContext('2d');
        let activeDatasets = [];

        if (stage === 'baseline') {
            activeDatasets = [datasetsConfig.baseline];
        } else if (stage === 'midline') {
            activeDatasets = [datasetsConfig.midline];
        } else if (stage === 'endline') {
            activeDatasets = [datasetsConfig.endline];
        } else {
            // Check which stages have non-zero data
            const hasBase = baseData.some(v => v > 0);
            const hasMid = midData.some(v => v > 0);
            const hasEnd = endData.some(v => v > 0);

            if (hasBase || hasMid || hasEnd) {
                if (hasBase) activeDatasets.push(datasetsConfig.baseline);
                if (hasMid) activeDatasets.push(datasetsConfig.midline);
                if (hasEnd) activeDatasets.push(datasetsConfig.endline);
            } else {
                activeDatasets = [datasetsConfig.baseline, datasetsConfig.midline, datasetsConfig.endline];
            }
        }

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: activeDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: { stepSize: 1, font: { size: 11, weight: 'bold' } },
                        title: { display: true, text: 'Rating (1 to 5)', font: { size: 11, weight: 'bold' } },
                        grid: { color: 'rgba(0,0,0,0.06)' }
                    },
                    x: {
                        ticks: { maxRotation: 30, minRotation: 20, font: { size: 10 } },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: {
                        display: activeDatasets.length > 1,
                        position: 'top',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11, weight: '600' } }
                    },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                if (!items.length) return '';
                                return fullLabels[items[0].dataIndex] || labels[items[0].dataIndex];
                            },
                            label: function(item) {
                                return ` ${item.dataset.label}: ${Number(item.raw).toFixed(2)} / 5.00`;
                            }
                        }
                    }
                }
            }
        });
    }

    window.toggleQualitativeCollapse = function() {
        const body = document.getElementById('qualitativeFeedbackBody');
        const icon = document.getElementById('qualToggleIcon');
        if (!body) return;

        if (body.style.display === 'none') {
            body.style.display = 'grid';
            if (icon) {
                icon.textContent = '▼';
                icon.style.transform = 'rotate(0deg)';
            }
        } else {
            body.style.display = 'none';
            if (icon) {
                icon.textContent = '▶';
                icon.style.transform = 'rotate(-90deg)';
            }
        }
    };

    window.filterAssessmentSurveyStage = function(stage, btn) {
        document.querySelectorAll('.assessment-stage-btn').forEach(b => {
            b.classList.remove('active');
            b.style.background = '#fff';
            b.style.color = 'var(--text-primary)';
            b.style.borderColor = 'var(--border)';
        });

        btn.classList.add('active');
        if (stage === 'baseline') {
            btn.style.background = '#10b981';
            btn.style.borderColor = '#10b981';
            btn.style.color = '#fff';
        } else if (stage === 'midline') {
            btn.style.background = '#8b5cf6';
            btn.style.borderColor = '#8b5cf6';
            btn.style.color = '#fff';
        } else if (stage === 'endline') {
            btn.style.background = '#0ea5e9';
            btn.style.borderColor = '#0ea5e9';
            btn.style.color = '#fff';
        } else {
            btn.style.background = 'var(--green-primary)';
            btn.style.borderColor = 'var(--green-primary)';
            btn.style.color = '#fff';
        }

        // 1. Update Bar Chart
        renderAssessmentChart(stage);

        // 2. Filter Qualitative Participant Feedback items
        const qualItems = document.querySelectorAll('.qual-response-item');
        let visibleCount = 0;

        qualItems.forEach(item => {
            const itemStage = item.getAttribute('data-stage') || 'baseline';
            if (stage === 'all' || itemStage === stage) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Toggle visibility of topic blocks based on whether they contain visible responses
        const topicBlocks = document.querySelectorAll('.qual-topic-block');
        topicBlocks.forEach(block => {
            const visibleChild = block.querySelector('.qual-response-item[style*="display: flex"], .qual-response-item:not([style*="display: none"])');
            const hasVisibleResponses = Array.from(block.querySelectorAll('.qual-response-item')).some(el => el.style.display !== 'none');
            block.style.display = hasVisibleResponses ? '' : 'none';
        });

        // Update count badge & empty state banner
        const countBadge = document.getElementById('qualCountBadge');
        if (countBadge) {
            countBadge.textContent = `${visibleCount} response${visibleCount === 1 ? '' : 's'}`;
        }

        const emptyState = document.getElementById('qualEmptyState');
        if (emptyState) {
            emptyState.style.display = (visibleCount === 0 && qualItems.length > 0) ? 'block' : 'none';
            if (stage !== 'all') {
                const stageName = stage.charAt(0).toUpperCase() + stage.slice(1);
                emptyState.textContent = `💬 No qualitative text responses submitted for the ${stageName} stage yet.`;
            }
        }
    };

    renderAssessmentChart('all');
})();
</script>

@endsection
