@extends('layouts.app')

@section('title', 'Interview Evaluation Terminal — Play It Forward')

@section('content')

{{-- Breadcrumb & Top Bar --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <a href="{{ route('admin.dashboard') }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
            ← Back to Metrics Dashboard
        </a>
        <h1 style="margin: 0; font-size: 1.8rem; display: flex; align-items: center; gap: 10px;">
            🎙️ Interview Evaluation Terminal
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 4px; margin-bottom: 0;">
            Live Candidate Scoring Terminal · Evaluator: <strong>{{ $user->name ?? 'Panelist' }}</strong>
            @if(!$user->isSuper())
                <span class="badge badge-blue" style="margin-left: 6px;">Panel {{ $user->panel ?: 'All' }}</span>
            @else
                <span class="badge badge-green" style="margin-left: 6px;">Super Admin</span>
            @endif
        </p>
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        @if($user && $user->isSuper())
            <a href="{{ route('admin.roster.index') }}" class="btn btn-outline btn-sm">
                👥 Manage Roster
            </a>
            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('quickImportModal').style.display='flex'">
                📥 Import Scores (Excel)
            </button>
        @endif
        <a href="{{ route('admin.dashboard', ['tab' => 'leaderboard']) }}" class="btn btn-primary btn-sm">
            🏆 View Live Leaderboard
        </a>
    </div>
</div>

@if($user && $user->isSuper())
    {{-- Quick Import Modal --}}
    <div id="quickImportModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:1050; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="background:#fff; border-radius:14px; max-width:460px; width:92%; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h3 style="margin:0; font-size:1.15rem;">📥 Import Interview Scores from Excel</h3>
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('quickImportModal').style.display='none'">✕</button>
            </div>
            <p style="font-size:0.83rem; color:var(--text-secondary); margin-bottom:14px;">
                Upload an Excel sheet (.xlsx, .xls, .csv) with panelist scores. Candidates and panelists will be automatically mapped or created.
            </p>
            <form action="{{ route('admin.imports.interview-scores') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="redirect_to" value="panel">
                <div class="form-group" style="margin-bottom:14px;">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control" style="font-size:0.85rem; padding:6px;">
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <a href="{{ route('admin.imports.template', ['type' => 'interview-scores']) }}" style="font-size:0.8rem; color:var(--green-dark);">
                        📥 Download Template
                    </a>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('quickImportModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Upload &amp; Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="margin-bottom: 20px;">
        {{ session('error') }}
    </div>
@endif

{{-- Dedicated Panel Evaluation Form --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
    @include('admin.partials.panel-form')
</div>

@endsection
