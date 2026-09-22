@extends('layouts.app')

@section('title', 'Practical Literacy Assessment Terminal — Play It Forward')

@section('content')

{{-- Breadcrumb & Top Bar --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <a href="{{ route('admin.dashboard') }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
            ← Back to Metrics Dashboard
        </a>
        <h1 style="margin: 0; font-size: 1.8rem; display: flex; align-items: center; gap: 10px;">
            💻 Digital Literacy Assessment Terminal
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 4px; margin-bottom: 0;">
            10-Task Practical Computer Literacy Grading Terminal (Score range: 0 - 20)
        </p>
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        @if($user && $user->isSuper())
            <a href="{{ route('admin.roster.index') }}" class="btn btn-outline btn-sm">
                👥 Manage Candidates
            </a>
            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('quickLiteracyImportModal').style.display='flex'">
                📥 Import Scores (Excel)
            </button>
        @endif
        <a href="{{ route('admin.dashboard', ['tab' => 'leaderboard']) }}" class="btn btn-primary btn-sm">
            🏆 View Live Leaderboard
        </a>
    </div>
</div>

@if($user && $user->isSuper())
    {{-- Quick Literacy Import Modal --}}
    <div id="quickLiteracyImportModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:1050; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="background:#fff; border-radius:14px; max-width:460px; width:92%; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h3 style="margin:0; font-size:1.15rem;">📥 Import Literacy Scores from Excel</h3>
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('quickLiteracyImportModal').style.display='none'">✕</button>
            </div>
            <p style="font-size:0.83rem; color:var(--text-secondary); margin-bottom:14px;">
                Upload an Excel sheet (.xlsx, .xls, .csv) with digital literacy scores. Candidates will be automatically mapped or created.
            </p>
            <form action="{{ route('admin.imports.literacy-scores') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="redirect_to" value="literacy">
                <div class="form-group" style="margin-bottom:14px;">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control" style="font-size:0.85rem; padding:6px;">
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <a href="{{ route('admin.imports.template', ['type' => 'literacy-scores']) }}" style="font-size:0.8rem; color:var(--green-dark);">
                        📥 Download Template
                    </a>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('quickLiteracyImportModal').style.display='none'">Cancel</button>
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

{{-- Dedicated Literacy Assessment Form --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); max-width: 900px; margin: 0 auto;">
    @include('admin.partials.literacy-form')
</div>

@endsection
