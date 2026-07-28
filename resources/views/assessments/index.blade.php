@extends('layouts.app')

@section('title', 'Assessment Engine')

@section('content')

{{-- Page Header --}}
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
    <div>
        <h1 style="margin-bottom:4px;">🧪 Assessment Engine</h1>
        <p style="color:var(--text-secondary); font-size:0.9rem;">
            Manage assessments, define questions &amp; scoring rules, assign panelists and candidates.
        </p>
    </div>
    @php $currentUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id')); @endphp
    @if($currentUser && $currentUser->isSuper())
        <a href="{{ route('assessments.create') }}" class="btn btn-primary">
            + New Assessment
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Metric Summary --}}
<div class="metric-cards" style="margin-bottom:28px;">
    <div class="metric-card">
        <div class="value">{{ $assessments->total() }}</div>
        <div class="label">Total Configured</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:#2563eb;">
            {{ $assessments->where('type','interview')->count() }}
        </div>
        <div class="label">Interviews</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:#7c3aed;">
            {{ $assessments->where('type','survey')->count() }}
        </div>
        <div class="label">Surveys</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:var(--green-primary);">
            {{ $assessments->where('status','active')->count() }}
        </div>
        <div class="label">Active</div>
    </div>
    <div class="metric-card">
        <div class="value" style="color:#c2410c;">
            {{ $assessments->where('status','completed')->count() }}
        </div>
        <div class="label">Completed</div>
    </div>
</div>

{{-- Filter Tabs --}}
<div class="tabs" style="margin-bottom:24px;">
    <a href="{{ route('assessments.index') }}"
       class="tab {{ !request('type') && !request('status') ? 'active' : '' }}">All</a>
    <a href="{{ route('assessments.index', ['type'=>'interview']) }}"
       class="tab {{ request('type')=='interview' ? 'active' : '' }}">Interviews</a>
    <a href="{{ route('assessments.index', ['type'=>'assessment']) }}"
       class="tab {{ request('type')=='assessment' ? 'active' : '' }}">Assessments</a>
    <a href="{{ route('assessments.index', ['type'=>'survey']) }}"
       class="tab {{ request('type')=='survey' ? 'active' : '' }}">Surveys</a>
    <a href="{{ route('assessments.index', ['status'=>'active']) }}"
       class="tab {{ request('status')=='active' ? 'active' : '' }}">Active</a>
    <a href="{{ route('assessments.index', ['status'=>'draft']) }}"
       class="tab {{ request('status')=='draft' ? 'active' : '' }}">Drafts</a>
</div>

{{-- Assessment Cards Grid --}}
@if($assessments->isEmpty())
    <div style="text-align:center; padding:60px 24px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg);">
        <div style="font-size:3rem; margin-bottom:16px;">🧪</div>
        <h3 style="color:var(--text-primary); margin-bottom:8px;">No assessments found</h3>
        <p style="color:var(--text-secondary); margin-bottom:24px;">
            Get started by creating your first assessment, interview, or survey.
        </p>
        @if($currentUser && $currentUser->isSuper())
            <a href="{{ route('assessments.create') }}" class="btn btn-primary">
                + Create First Assessment
            </a>
        @endif
    </div>
@else
    <div class="assessment-grid">
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
                    'interview'  => '🎤',
                    'survey'     => '📋',
                    default      => '📝',
                };
            @endphp
            <div class="assessment-card" onclick="window.location='{{ route('assessments.show', $item->id) }}'">
                {{-- Top row --}}
                <div class="assessment-card-top">
                    <div>
                        <div style="font-size:1.6rem; margin-bottom:8px;">{{ $typeIcon }}</div>
                        <div class="assessment-card-title">{{ $item->title }}</div>
                        @if($item->description)
                            <div class="assessment-card-desc">{{ Str::limit($item->description, 90) }}</div>
                        @endif
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end; flex-shrink:0;">
                        <span class="badge {{ $statusBadgeClass }}">{{ $item->status }}</span>
                        <span class="badge {{ $typeBadgeClass }}">{{ $item->type }}</span>
                    </div>
                </div>

                {{-- Stats row --}}
                <div class="assessment-card-meta">
                    <div class="assessment-card-stat">
                        <span>❓</span>
                        <strong>{{ $item->questions_count }}</strong> questions
                    </div>
                    <div class="assessment-card-stat">
                        <span>👥</span>
                        <strong>{{ $item->panelists_count }}</strong> panelists
                    </div>
                    <div class="assessment-card-stat">
                        <span>🧑‍🎓</span>
                        <strong>{{ $item->candidates_count }}</strong> candidates
                    </div>
                    <div style="margin-left:auto; font-size:0.75rem; color:var(--text-muted);">
                        {{ $item->created_at->format('M d, Y') }}
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="assessment-card-actions" onclick="event.stopPropagation();">
                    <a href="{{ route('assessments.show', $item->id) }}"
                       class="btn btn-primary btn-sm">View Hub</a>
                    <a href="{{ route('assessments.evaluate', $item->id) }}"
                       class="btn btn-sm" style="background:#0f766e; color:white;">Grade</a>
                    @if($currentUser && $currentUser->isSuper())
                        <a href="{{ route('assessments.edit', $item->id) }}"
                           class="btn btn-outline btn-sm">Edit</a>
                        <form action="{{ route('assessments.destroy', $item->id) }}" method="POST"
                              onsubmit="return confirm('Delete this assessment? This cannot be undone.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div style="margin-top:20px;">
        {{ $assessments->withQueryString()->links() }}
    </div>
@endif

@endsection
