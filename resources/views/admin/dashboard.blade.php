@extends('layouts.app')

@section('title', 'Dashboard — Play It Forward E-Hub')

@section('content')

{{-- Page Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
    <div>
        <h1 style="margin:0; font-size:1.45rem; font-weight:800; display:flex; align-items:center; gap:8px;">
            📊 Central Dashboard
            @if(isset($user) && $user->isSuper())
                <span class="badge badge-green" style="font-size:0.75rem; padding:2px 8px;">Super Admin</span>
            @else
                <span class="badge badge-blue" style="font-size:0.75rem; padding:2px 8px;">Panelist · Panel {{ $user->panel ?: 'All' }}</span>
            @endif
        </h1>
        <p style="margin:2px 0 0; color:var(--text-secondary); font-size:0.85rem;">
            Select any Survey, Assessment, or Interview to inspect its respective results and scores.
        </p>
    </div>

    @if(isset($user) && $user->isSuper())
        <div style="display:flex; gap:8px;">
            <a href="{{ route('assessments.create') }}" class="btn btn-primary btn-sm" style="font-size:0.85rem; padding:6px 14px;">
                + Create New Engine
            </a>
        </div>
    @endif
</div>

{{-- Top Primary Engine Counters (Surveys, Assessments, Interviews) --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:20px;">
    {{-- 1. Surveys Counter --}}
    <div onclick="window.location='{{ route('admin.dashboard', ['type' => 'survey']) }}'"
         style="cursor:pointer; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-left:4px solid var(--green-primary); transition:transform 0.15s, box-shadow 0.15s;">
        <div>
            <div style="font-size:0.75rem; color:var(--green-dark); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                Surveys
            </div>
            <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); line-height:1.2; margin-top:2px;">
                {{ $totalSurveys ?? 0 }}
            </div>
        </div>
        <div style="font-size:1.8rem; opacity:0.85;">📋</div>
    </div>

    {{-- 2. Assessments Counter --}}
    <div onclick="window.location='{{ route('admin.dashboard', ['type' => 'assessment']) }}'"
         style="cursor:pointer; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-left:4px solid #7c3aed; transition:transform 0.15s, box-shadow 0.15s;">
        <div>
            <div style="font-size:0.75rem; color:#7c3aed; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                Assessments
            </div>
            <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); line-height:1.2; margin-top:2px;">
                {{ $totalAssessments ?? 0 }}
            </div>
        </div>
        <div style="font-size:1.8rem; opacity:0.85;">💻</div>
    </div>

    {{-- 3. Interviews Counter --}}
    <div onclick="window.location='{{ route('admin.dashboard', ['type' => 'interview']) }}'"
         style="cursor:pointer; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-left:4px solid #2563eb; transition:transform 0.15s, box-shadow 0.15s;">
        <div>
            <div style="font-size:0.75rem; color:#2563eb; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                Interviews
            </div>
            <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); line-height:1.2; margin-top:2px;">
                {{ $totalInterviews ?? 0 }}
            </div>
        </div>
        <div style="font-size:1.8rem; opacity:0.85;">🎙️</div>
    </div>
</div>

{{-- Filters & Search Toolbar --}}
<div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:10px 14px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
    {{-- Engine Type Filter Tabs --}}
    <div class="tabs" style="margin:0;">
        <a href="{{ route('admin.dashboard', array_filter(['status' => $statusFilter, 'q' => $searchQuery])) }}"
           class="tab {{ empty($typeFilter) ? 'active' : '' }}" style="padding:4px 12px; font-size:0.82rem;">
            All Engines
        </a>
        <a href="{{ route('admin.dashboard', array_filter(['type' => 'survey', 'status' => $statusFilter, 'q' => $searchQuery])) }}"
           class="tab {{ $typeFilter === 'survey' ? 'active' : '' }}" style="padding:4px 12px; font-size:0.82rem;">
            📋 Surveys
        </a>
        <a href="{{ route('admin.dashboard', array_filter(['type' => 'assessment', 'status' => $statusFilter, 'q' => $searchQuery])) }}"
           class="tab {{ $typeFilter === 'assessment' ? 'active' : '' }}" style="padding:4px 12px; font-size:0.82rem;">
            💻 Assessments
        </a>
        <a href="{{ route('admin.dashboard', array_filter(['type' => 'interview', 'status' => $statusFilter, 'q' => $searchQuery])) }}"
           class="tab {{ $typeFilter === 'interview' ? 'active' : '' }}" style="padding:4px 12px; font-size:0.82rem;">
            🎙️ Interviews
        </a>
    </div>

    {{-- Search & Status Filter --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" style="display:flex; gap:6px; align-items:center; margin:0;">
        @if($typeFilter)
            <input type="hidden" name="type" value="{{ $typeFilter }}">
        @endif

        <select name="status" onchange="this.form.submit()" style="padding:4px 8px; font-size:0.8rem; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--text-primary);">
            <option value="">All Statuses</option>
            <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
            <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
        </select>

        <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Search by name or key..."
               style="padding:4px 10px; font-size:0.8rem; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--text-primary); width:180px;">

        <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:0.78rem;">
            🔍
        </button>

        @if($typeFilter || $statusFilter || $searchQuery)
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-sm" style="padding:4px 6px; font-size:0.75rem;" title="Reset filters">
                ✕ Reset
            </a>
        @endif
    </form>
</div>

{{-- Engine List & Result Launchers --}}
@if($assessments->isEmpty())
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:40px 20px; text-align:center; color:var(--text-muted);">
        <div style="font-size:2.5rem; margin-bottom:8px;">🔍</div>
        <h3 style="color:var(--text-primary); font-size:1.1rem; margin-bottom:4px;">No matching engines found</h3>
        <p style="font-size:0.85rem; margin:0 0 16px;">Try adjusting your filters or search terms.</p>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">Clear Filters</a>
    </div>
@else
    <div style="display:flex; flex-direction:column; gap:10px;">
        @foreach($assessments as $item)
            @php
                $typeBadgeClass = match($item->type) {
                    'interview'  => 'badge-blue',
                    'survey'     => 'badge-purple',
                    default      => 'badge-teal',
                };
                $statusBadgeClass = match($item->status) {
                    'active'    => 'badge-green',
                    'draft'     => 'badge-gray',
                    'completed' => 'badge-orange',
                    default     => 'badge-gray',
                };
                $typeIcon = match($item->type) {
                    'interview'  => '🎙️',
                    'survey'     => '📋',
                    default      => '💻',
                };
                $borderLeftColor = match($item->type) {
                    'interview'  => '#2563eb',
                    'survey'     => 'var(--green-primary)',
                    default      => '#7c3aed',
                };
            @endphp

            <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px 16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-left:4px solid {{ $borderLeftColor }}; transition:box-shadow 0.15s;">
                {{-- Left info --}}
                <div style="flex:1; min-width:260px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <span style="font-size:1.3rem;">{{ $typeIcon }}</span>
                        <a href="{{ route('assessments.show', $item->id) }}" style="color:var(--text-primary); text-decoration:none; font-weight:700; font-size:1.02rem;">
                            {{ $item->title }}
                        </a>
                        <span class="badge {{ $typeBadgeClass }}" style="font-size:0.72rem; padding:1px 6px;">{{ ucfirst($item->type) }}</span>
                        <span class="badge {{ $statusBadgeClass }}" style="font-size:0.72rem; padding:1px 6px;">{{ ucfirst($item->status) }}</span>
                    </div>

                    @if($item->description)
                        <p style="margin:0 0 8px; font-size:0.82rem; color:var(--text-secondary); line-height:1.4;">
                            {{ Str::limit($item->description, 130) }}
                        </p>
                    @endif

                    {{-- Metrics metadata tags --}}
                    <div style="display:flex; gap:12px; font-size:0.78rem; color:var(--text-muted); flex-wrap:wrap; align-items:center;">
                        <span>❓ <strong>{{ $item->questions_count }}</strong> Questions</span>
                        <span>👥 <strong>{{ $item->panelists_count }}</strong> Evaluators</span>
                        <span>🧑‍🎓 <strong>{{ $item->candidates_count }}</strong> Candidates</span>
                        <span>📊 <strong>{{ $item->evaluation_scores_count ?? 0 }}</strong> Responses Logged</span>
                        @if($item->access_key)
                            <span style="font-family:monospace; background:rgba(0,0,0,0.04); padding:1px 6px; border-radius:4px;">
                                🔑 {{ $item->access_key }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Right Actions: View Results Button --}}
                <div style="display:flex; gap:6px; align-items:center; flex-shrink:0;">
                    <a href="{{ route('assessments.show', $item->id) }}" class="btn btn-primary btn-sm" style="padding:6px 14px; font-size:0.85rem; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
                        📊 View Results →
                    </a>

                    @if($item->type === 'survey')
                        <a href="{{ route('surveys.take', $item->id) }}" class="btn btn-outline btn-sm" style="padding:6px 10px; font-size:0.82rem;" target="_blank">
                            🌐 Take Survey
                        </a>
                    @else
                        <a href="{{ route('assessments.evaluate', $item->id) }}" class="btn btn-outline btn-sm" style="padding:6px 10px; font-size:0.82rem;">
                            ✏️ Grade
                        </a>
                    @endif

                    @if(isset($user) && $user->isSuper())
                        <a href="{{ route('assessments.edit', $item->id) }}" class="btn btn-ghost btn-sm" style="padding:6px 8px; font-size:0.8rem;" title="Edit assessment">
                            ⚙️
                        </a>
                        <form action="{{ route('assessments.destroy', $item->id) }}" method="POST"
                              onsubmit="return confirm('Permanently delete this {{ $item->type }} (\'{{ addslashes($item->title) }}\')? All evaluation scores will be deleted.');"
                              style="display:inline; margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm" style="padding:6px 8px; font-size:0.8rem; color:#dc2626;" title="Delete {{ $item->type }}">
                                🗑️
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
