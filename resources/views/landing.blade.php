@extends('layouts.app')

@section('title', 'Welcome — Play It Forward E-Hub')

@section('content')

{{-- ===== HERO SECTION ===== --}}
<div class="landing-hero">
    <div style="display:inline-flex; align-items:center; gap:8px; background:var(--green-light); border:1px solid var(--green-border); border-radius:100px; padding:6px 16px; font-size:0.8rem; font-weight:600; color:var(--green-dark); margin-bottom:24px; text-transform:uppercase; letter-spacing:0.5px;">
        🌍 Play It Forward · Cohort PIZ-C4-26
    </div>
    <h1>Dynamic Assessment &amp;<br>Survey Management Hub</h1>
    <p>
        A unified platform for administering technical interviews, configuring surveys,
        assigning panelists, collecting scores, and tracking candidate progress —
        all from one place.
    </p>
</div>

@if(session('success'))
    <div style="max-width:700px; margin:-16px auto 24px auto; padding:0 24px;">
        <div class="alert alert-success">{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div style="max-width:700px; margin:-16px auto 24px auto; padding:0 24px;">
        <div class="alert alert-error">{{ session('error') }}</div>
    </div>
@endif

{{-- ===== DUAL SECTION GRID ===== --}}
<div class="landing-grid">

    {{-- SECTION 1: AUTH PORTAL --}}
    <div class="landing-section">
        <div class="landing-section-icon" style="background:var(--green-light);">🔐</div>

        <div style="margin-bottom:20px;">
            <h2 style="font-size:1.35rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">
                Evaluator &amp; Admin Portal
            </h2>
            <p style="font-size:0.88rem; color:var(--text-secondary);">
                Secure authentication for panelists and administrators.
            </p>
        </div>

        <form action="{{ route('admin.login.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="role">Select Identity / Role <span style="color:#dc2626;">*</span></label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled selected>— Select your name or role —</option>
                    <option value="Super User">⚙️ Super User (Administrator)</option>
                    @php
                        $panelistList = \App\Models\User::where('role', 'panelist')->orderBy('name')->get();
                    @endphp
                    @foreach($panelistList as $pUser)
                        <option value="{{ $pUser->panelist_name ?: $pUser->name }}">
                            👤 {{ $pUser->panelist_name ?: $pUser->name }}
                            (Panel {{ $pUser->panel ?: 'All' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color:#dc2626;">*</span></label>
                <input type="password" name="password" id="password"
                       class="form-control"
                       placeholder="Enter your password"
                       required>
            </div>

            @error('role')
                <div class="alert alert-error" style="margin-bottom:12px;">{{ $message }}</div>
            @enderror
            @if(session('error'))
                <div class="alert alert-error" style="margin-bottom:12px;">{{ session('error') }}</div>
            @endif

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Sign In to Portal →
            </button>
        </form>

        <div style="margin-top:20px; padding-top:20px; border-top:1px solid var(--border); font-size:0.82rem; color:var(--text-muted); text-align:center;">
            Admin access · Panelist evaluation · Assessment management
        </div>
    </div>

    {{-- SECTION 2: PUBLIC STUDENT SURVEY CTA --}}
    <div class="landing-section" style="display:flex; flex-direction:column;">
        <div class="landing-section-icon" style="background:var(--blue-light);">📋</div>

        <div style="margin-bottom:20px;">
            <h2 style="font-size:1.35rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">
                Student Survey Portal
            </h2>
            <p style="font-size:0.88rem; color:var(--text-secondary);">
                Public access for candidates and students to complete surveys, feedback forms,
                and baseline / endline assessments.
            </p>
        </div>

        <div style="background:var(--green-light); border:1px solid var(--green-border); border-radius:var(--radius-md); padding:16px 18px; margin-bottom:24px; flex:1;">
            <div style="display:flex; align-items:center; gap:10px; color:var(--green-dark); font-weight:700; font-size:0.88rem; margin-bottom:6px;">
                🔑 Access Key Required
            </div>
            <div style="font-size:0.84rem; color:var(--text-secondary); line-height:1.6;">
                Have your unique survey access key ready
                (e.g. <code style="background:rgba(0,0,0,0.06); padding:2px 6px; border-radius:4px; font-size:0.82rem;">KEY-XXXXXX</code>)
                to unlock and submit your assigned survey.
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:10px; margin-top:auto;">
            <a href="{{ route('surveys.index') }}"
               class="btn btn-blue btn-full btn-lg">
                Open Survey Gallery →
            </a>
            <div style="font-size:0.78rem; color:var(--text-muted); text-align:center;">
                No login required · Open to all candidates
            </div>
        </div>
    </div>

</div>

@endsection
