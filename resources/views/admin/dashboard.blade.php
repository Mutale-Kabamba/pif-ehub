@extends('layouts.app')

@section('title', 'Admin Dashboard — Play It Forward E-Hub')

@section('content')

<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <h1 style="margin-bottom:4px;">📊 Admin Dashboard</h1>
        <p style="color:var(--text-secondary); font-size:0.9rem;">
            Authenticated as: <strong>{{ $user->name ?? 'Administrator' }}</strong>
            @if($user->isSuper())
                <span class="badge badge-green" style="vertical-align:middle; margin-left:6px;">Super Admin</span>
            @else
                <span class="badge badge-blue" style="vertical-align:middle; margin-left:6px;">
                    Panelist · Panel {{ $user->panel ?: 'All' }}
                </span>
            @endif
        </p>
    </div>

    @if($user->isSuper())
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin.survey.export') }}" class="btn btn-outline btn-sm">
                📥 Export Survey CSV
            </a>
            <a href="{{ route('assessments.index') }}" class="btn btn-primary btn-sm">
                🧪 Assessment Engine
            </a>
        </div>
    @endif
</div>

{{-- Quick Action Cards for Super Admin --}}
@if($user->isSuper())
<div class="metric-cards" style="margin-bottom:28px;">
    <div class="metric-card" onclick="window.location='{{ route('assessments.index') }}'" style="cursor:pointer;">
        <div class="value">🧪</div>
        <div class="label">Assessment Engine</div>
    </div>
    <div class="metric-card" onclick="window.location='{{ route('admin.leaderboard') }}'" style="cursor:pointer;">
        <div class="value" style="color:#2563eb;">🏆</div>
        <div class="label">Leaderboard</div>
    </div>
    <div class="metric-card" onclick="window.location='?tab=literacy'" style="cursor:pointer;">
        <div class="value" style="color:#7c3aed;">💻</div>
        <div class="label">Literacy Tests</div>
    </div>
    <div class="metric-card" onclick="window.location='?tab=analytics'" style="cursor:pointer;">
        <div class="value" style="color:#c2410c;">📈</div>
        <div class="label">Analytics</div>
    </div>
    <div class="metric-card" onclick="window.location='{{ route('surveys.index') }}'" style="cursor:pointer;">
        <div class="value" style="color:#0f766e;">📋</div>
        <div class="label">Survey Gallery</div>
    </div>
</div>
@endif

{{-- Tab Navigation --}}
<div class="tabs">
    @if(isset($user) && $user->isSuper())
        <a href="?tab=leaderboard" class="tab {{ $tab == 'leaderboard' ? 'active' : '' }}">
            🏆 Leaderboard
        </a>
        <a href="?tab=analytics" class="tab {{ $tab == 'analytics' ? 'active' : '' }}">
            📊 Analytics
        </a>
        <a href="?tab=literacy" class="tab {{ $tab == 'literacy' ? 'active' : '' }}">
            💻 Literacy
        </a>
    @endif
    <a href="?tab=panel" class="tab {{ $tab == 'panel' ? 'active' : '' }}">
        📝 Panel Evaluation
    </a>
</div>

{{-- Tab Content --}}
@if($tab == 'leaderboard' && isset($user) && $user->isSuper())
    @include('admin.partials.leaderboard')
@elseif($tab == 'analytics' && isset($user) && $user->isSuper())
    @include('admin.partials.analytics')
@elseif($tab == 'literacy' && isset($user) && $user->isSuper())
    @include('admin.partials.literacy-form')
@elseif($tab == 'panel')
    @include('admin.partials.panel-form')
@endif

@endsection
