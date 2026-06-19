@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1>Admin Dashboard</h1>
        <p style="color: #555; font-size: 0.9rem;">
            Session Authenticated Successfully as: <strong>{{ $user->name ?? session('auth_role', 'Admin') }}</strong>
        </p>
    </div>

    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-danger" style="font-size: 0.85rem; padding: 8px 16px;">
            Logout
        </button>
    </form>
</div>

<!-- Tab Navigation -->
<div class="tabs">
    @if(isset($user) && $user->isSuper())
        <a href="?tab=leaderboard" class="tab {{ $tab == 'leaderboard' ? 'active' : '' }}">
            &#x1F3C6; Leaderboard
        </a>
        <a href="?tab=analytics" class="tab {{ $tab == 'analytics' ? 'active' : '' }}">
            &#x1F4CA; Analytics
        </a>
        <a href="?tab=literacy" class="tab {{ $tab == 'literacy' ? 'active' : '' }}">
            &#x1F4BB; Literacy
        </a>
    @endif
    <a href="?tab=panel" class="tab {{ $tab == 'panel' ? 'active' : '' }}">
        &#x1F4DD; Panel Evaluation
    </a>
</div>

<!-- Tab Content -->
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
