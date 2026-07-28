<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PIF E-Hub') — Play It Forward</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pif-theme.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @yield('head')
</head>
<body>

    <!-- ===== TOP NAVIGATION BAR ===== -->
    <nav class="topnav">
        {{-- Brand --}}
        <a href="{{ route('landing') }}" class="topnav-brand">
            <div class="topnav-brand-logo">PIF</div>
            <div class="topnav-brand-text">
                <span class="topnav-brand-title">Play It Forward</span>
                <span class="topnav-brand-sub">E-Hub Platform</span>
            </div>
        </a>

        {{-- Primary Links --}}
        <div class="topnav-links" id="topnav-links">
            <a href="{{ route('landing') }}"
               class="topnav-link {{ request()->routeIs('landing') ? 'active' : '' }}">
                <span class="topnav-link-icon">🏠</span> Home
            </a>

            <a href="{{ route('surveys.index') }}"
               class="topnav-link {{ request()->is('surveys*') ? 'active' : '' }}">
                <span class="topnav-link-icon">📋</span> Survey Portal
            </a>

            @if(session()->has('admin_user_id') || auth()->check())
                <div class="topnav-divider"></div>

                <a href="{{ route('admin.dashboard') }}"
                   class="topnav-link {{ request()->is('admin') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="topnav-link-icon">📊</span> Dashboard
                </a>

                <a href="{{ route('assessments.index') }}"
                   class="topnav-link {{ request()->is('admin/assessments*') ? 'active' : '' }}">
                    <span class="topnav-link-icon">🧪</span> Assessment Engine
                </a>

                @php $navUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id')); @endphp
                @if($navUser && $navUser->isSuper())
                    <a href="{{ route('admin.leaderboard') }}"
                       class="topnav-link {{ request()->routeIs('admin.leaderboard') ? 'active' : '' }}">
                        <span class="topnav-link-icon">🏆</span> Leaderboard
                    </a>
                @endif
            @endif
        </div>

        {{-- User Area / Auth --}}
        @if(session()->has('admin_user_id') || auth()->check())
            @php $currentUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id')); @endphp
            <div class="topnav-user" id="topnav-user">
                <button class="topnav-user-btn" id="user-dropdown-btn" type="button" aria-expanded="false">
                    <div class="topnav-avatar">
                        {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                    </div>
                    <span style="max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $currentUser->name ?? 'User' }}
                    </span>
                    <span class="topnav-chevron">▼</span>
                </button>

                <div class="topnav-dropdown" id="user-dropdown" role="menu">
                    <div class="topnav-dropdown-header">
                        <div class="topnav-dropdown-name">{{ $currentUser->name ?? 'User' }}</div>
                        <div class="topnav-dropdown-role">{{ ucfirst($currentUser->role ?? 'admin') }}</div>
                    </div>

                    <a href="{{ route('admin.dashboard') }}" class="topnav-dropdown-item">
                        📊 My Dashboard
                    </a>

                    @if($currentUser && $currentUser->isSuper())
                        <a href="{{ route('admin.survey.export') }}" class="topnav-dropdown-item">
                            📥 Export Survey CSV
                        </a>
                    @endif

                    <div class="topnav-dropdown-logout">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit">🚪 Sign Out</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <a href="{{ route('admin.login') }}"
               class="btn btn-outline btn-sm"
               style="border-color: rgba(255,255,255,0.2); color: #e5e7eb; margin-left: auto;">
                Sign In
            </a>
        @endif

        {{-- Mobile hamburger --}}
        <button class="nav-mobile-toggle" id="mobile-nav-toggle" aria-label="Toggle navigation" type="button">
            ☰
        </button>
    </nav>

    <!-- ===== MAIN CONTENT AREA ===== -->
    <div class="app-wrapper">
        <main class="main-content @yield('content_class')">
            @yield('content')
        </main>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        // ---- User Dropdown ----
        const userBtn   = document.getElementById('user-dropdown-btn');
        const userEl    = document.getElementById('topnav-user');
        const userDrop  = document.getElementById('user-dropdown');

        if (userBtn && userEl) {
            userBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = userEl.classList.toggle('open');
                userBtn.setAttribute('aria-expanded', isOpen);
            });

            document.addEventListener('click', function () {
                userEl.classList.remove('open');
                if (userBtn) userBtn.setAttribute('aria-expanded', 'false');
            });
        }

        // ---- Mobile Nav Toggle ----
        const mobileToggle = document.getElementById('mobile-nav-toggle');
        const navLinks     = document.getElementById('topnav-links');

        if (mobileToggle && navLinks) {
            mobileToggle.addEventListener('click', function () {
                navLinks.classList.toggle('mobile-open');
                mobileToggle.textContent = navLinks.classList.contains('mobile-open') ? '✕' : '☰';
            });
        }

        // ---- Flash message auto-dismiss ----
        document.querySelectorAll('.alert').forEach(function (el) {
            setTimeout(function () {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 500);
            }, 5000);
        });
    </script>
    @yield('scripts')

</body>
</html>
