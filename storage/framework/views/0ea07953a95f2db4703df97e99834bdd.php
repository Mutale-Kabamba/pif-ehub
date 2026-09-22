<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'PIF E-Hub'); ?> — Play It Forward</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/pif-theme.css')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php echo $__env->yieldContent('head'); ?>
</head>
<body>

    <!-- ===== TOP NAVIGATION BAR ===== -->
    <nav class="topnav">
        
        <a href="<?php echo e(route('landing')); ?>" class="topnav-brand">
            <div class="topnav-brand-logo">PIF</div>
            <div class="topnav-brand-text">
                <span class="topnav-brand-title">Play It Forward</span>
                <span class="topnav-brand-sub">E-Hub Platform</span>
            </div>
        </a>

        
        <div class="topnav-links" id="topnav-links">
            <a href="<?php echo e(route('landing')); ?>"
               class="topnav-link <?php echo e(request()->routeIs('landing') ? 'active' : ''); ?>">
                <span class="topnav-link-icon">🏠</span> Home
            </a>

            <?php if(session()->has('admin_user_id') || auth()->check()): ?>
                <?php $navUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id')); ?>

                <a href="<?php echo e(route('admin.dashboard')); ?>"
                   class="topnav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                    <span class="topnav-link-icon">📊</span> Dashboard
                </a>

                
                <?php
                    $isAssessmentsActive = request()->is('admin/assessments*') || request()->is('admin/panel*') || request()->is('admin/literacy*') || request()->is('surveys*') || request()->is('survey*');
                ?>
                <div class="topnav-nav-dropdown <?php echo e($isAssessmentsActive ? 'has-active-child' : ''); ?>">
                    <button type="button" class="topnav-dropdown-trigger <?php echo e($isAssessmentsActive ? 'active' : ''); ?>" aria-expanded="false">
                        <span class="topnav-link-icon">🧪</span>
                        <span>Assessments</span>
                        <span class="topnav-caret">▼</span>
                    </button>
                    <div class="topnav-dropdown-menu">
                        <div class="topnav-dropdown-section-title">Evaluation Modules</div>
                        <a href="<?php echo e(route('assessments.index')); ?>" class="topnav-menu-item <?php echo e(request()->is('admin/assessments*') ? 'active' : ''); ?>">
                            <span class="topnav-menu-icon">🧪</span>
                            <div class="topnav-menu-content">
                                <div class="topnav-menu-title">Assessment Engine</div>
                                <div class="topnav-menu-desc">Dynamic rubrics, surveys &amp; interviews</div>
                            </div>
                        </a>
                        <a href="<?php echo e(route('admin.panel')); ?>" class="topnav-menu-item <?php echo e(request()->is('admin/panel*') ? 'active' : ''); ?>">
                            <span class="topnav-menu-icon">🎙️</span>
                            <div class="topnav-menu-content">
                                <div class="topnav-menu-title">Interview Panel</div>
                                <div class="topnav-menu-desc">Multi-panel candidate evaluation terminal</div>
                            </div>
                        </a>
                        <?php if($navUser && $navUser->isSuper()): ?>
                            <a href="<?php echo e(route('admin.literacy')); ?>" class="topnav-menu-item <?php echo e(request()->is('admin/literacy*') ? 'active' : ''); ?>">
                                <span class="topnav-menu-icon">💻</span>
                                <div class="topnav-menu-content">
                                    <div class="topnav-menu-title">Digital Literacy</div>
                                    <div class="topnav-menu-desc">Livingstone 10-task scoring &amp; verification</div>
                                </div>
                            </a>
                        <?php endif; ?>
                        <div class="topnav-menu-divider"></div>
                        <a href="<?php echo e(route('surveys.index')); ?>" class="topnav-menu-item <?php echo e(request()->is('surveys*') ? 'active' : ''); ?>">
                            <span class="topnav-menu-icon">📋</span>
                            <div class="topnav-menu-content">
                                <div class="topnav-menu-title">Survey Portal</div>
                                <div class="topnav-menu-desc">Public baseline &amp; endline candidate access</div>
                            </div>
                        </a>
                    </div>
                </div>

                <?php if($navUser && $navUser->isSuper()): ?>
                    
                    <?php
                        $isRosterActive = request()->is('admin/roster*');
                    ?>
                    <div class="topnav-nav-dropdown <?php echo e($isRosterActive ? 'has-active-child' : ''); ?>">
                        <button type="button" class="topnav-dropdown-trigger <?php echo e($isRosterActive ? 'active' : ''); ?>" aria-expanded="false">
                            <span class="topnav-link-icon">👥</span>
                            <span>Cohort &amp; Roster</span>
                            <span class="topnav-caret">▼</span>
                        </button>
                        <div class="topnav-dropdown-menu">
                            <div class="topnav-dropdown-section-title">Cohort Management</div>
                            <a href="<?php echo e(route('admin.roster.index')); ?>" class="topnav-menu-item <?php echo e(request()->is('admin/roster*') && request('tab') !== 'import' ? 'active' : ''); ?>">
                                <span class="topnav-menu-icon">🧑‍🎓</span>
                                <div class="topnav-menu-content">
                                    <div class="topnav-menu-title">Candidates &amp; Panelists</div>
                                    <div class="topnav-menu-desc">Panel assignments, tracks &amp; credentials</div>
                                </div>
                            </a>
                            <a href="<?php echo e(route('admin.roster.index', ['tab' => 'import'])); ?>" class="topnav-menu-item <?php echo e(request()->is('admin/roster*') && request('tab') === 'import' ? 'active' : ''); ?>">
                                <span class="topnav-menu-icon">📥</span>
                                <div class="topnav-menu-content">
                                    <div class="topnav-menu-title">Import Results (Excel)</div>
                                    <div class="topnav-menu-desc">Spreadsheet score sheets &amp; batch ingestion</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    
                    <?php
                        $isAnalyticsActive = request()->routeIs('admin.leaderboard') || request()->routeIs('admin.analytics') || request()->routeIs('admin.scoresheet') || request()->routeIs('admin.survey.export');
                    ?>
                    <div class="topnav-nav-dropdown <?php echo e($isAnalyticsActive ? 'has-active-child' : ''); ?>">
                        <button type="button" class="topnav-dropdown-trigger <?php echo e($isAnalyticsActive ? 'active' : ''); ?>" aria-expanded="false">
                            <span class="topnav-link-icon">🏆</span>
                            <span>Analytics</span>
                            <span class="topnav-caret">▼</span>
                        </button>
                        <div class="topnav-dropdown-menu">
                            <div class="topnav-dropdown-section-title">Rankings &amp; Performance</div>
                            <a href="<?php echo e(route('admin.leaderboard')); ?>" class="topnav-menu-item <?php echo e(request()->routeIs('admin.leaderboard') ? 'active' : ''); ?>">
                                <span class="topnav-menu-icon">🏆</span>
                                <div class="topnav-menu-content">
                                    <div class="topnav-menu-title">Leaderboard &amp; Rankings</div>
                                    <div class="topnav-menu-desc">Real-time composite scores &amp; top advance list</div>
                                </div>
                            </a>
                            <a href="<?php echo e(route('admin.analytics')); ?>" class="topnav-menu-item <?php echo e(request()->routeIs('admin.analytics') ? 'active' : ''); ?>">
                                <span class="topnav-menu-icon">📈</span>
                                <div class="topnav-menu-content">
                                    <div class="topnav-menu-title">Cohort Analytics</div>
                                    <div class="topnav-menu-desc">Gender distributions, averages &amp; insights</div>
                                </div>
                            </a>
                            <div class="topnav-menu-divider"></div>
                            <div class="topnav-dropdown-section-title">Data Exports</div>
                            <a href="<?php echo e(route('admin.scoresheet')); ?>" class="topnav-menu-item">
                                <span class="topnav-menu-icon">📑</span>
                                <div class="topnav-menu-content">
                                    <div class="topnav-menu-title">Export Scoresheet (CSV)</div>
                                    <div class="topnav-menu-desc">Comprehensive candidate assessment matrix</div>
                                </div>
                            </a>
                            <a href="<?php echo e(route('admin.survey.export')); ?>" class="topnav-menu-item">
                                <span class="topnav-menu-icon">📊</span>
                                <div class="topnav-menu-content">
                                    <div class="topnav-menu-title">Export Survey Data (CSV)</div>
                                    <div class="topnav-menu-desc">M&amp;E survey responses dataset</div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo e(route('surveys.index')); ?>"
                   class="topnav-link <?php echo e(request()->is('surveys*') ? 'active' : ''); ?>">
                    <span class="topnav-link-icon">📋</span> Survey Portal
                </a>
            <?php endif; ?>
        </div>

        
        <?php if(session()->has('admin_user_id') || auth()->check()): ?>
            <?php $currentUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id')); ?>
            <div class="topnav-user" id="topnav-user">
                <button class="topnav-user-btn" id="user-dropdown-btn" type="button" aria-expanded="false">
                    <div class="topnav-avatar">
                        <?php echo e(strtoupper(substr($currentUser->name ?? 'U', 0, 1))); ?>

                    </div>
                    <span style="max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?php echo e($currentUser->name ?? 'User'); ?>

                    </span>
                    <span class="topnav-chevron">▼</span>
                </button>

                <div class="topnav-dropdown" id="user-dropdown" role="menu">
                    <div class="topnav-dropdown-header">
                        <div class="topnav-dropdown-name"><?php echo e($currentUser->name ?? 'User'); ?></div>
                        <div class="topnav-dropdown-role"><?php echo e(ucfirst($currentUser->role ?? 'admin')); ?></div>
                    </div>

                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="topnav-dropdown-item">
                        📊 My Dashboard
                    </a>

                    <?php if($currentUser && $currentUser->isSuper()): ?>
                        <a href="<?php echo e(route('admin.roster.index')); ?>" class="topnav-dropdown-item">
                            👥 Candidates &amp; Panelists
                        </a>

                        <a href="<?php echo e(route('admin.roster.index', ['tab' => 'import'])); ?>" class="topnav-dropdown-item">
                            📊 Import Results (Excel)
                        </a>

                        <a href="<?php echo e(route('admin.survey.export')); ?>" class="topnav-dropdown-item">
                            📥 Export Survey CSV
                        </a>
                    <?php endif; ?>

                    <div class="topnav-dropdown-logout">
                        <form action="<?php echo e(route('admin.logout')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit">🚪 Sign Out</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo e(route('admin.login')); ?>"
               class="btn btn-outline btn-sm"
               style="border-color: rgba(255,255,255,0.2); color: #e5e7eb; margin-left: auto;">
                Sign In
            </a>
        <?php endif; ?>

        
        <button class="nav-mobile-toggle" id="mobile-nav-toggle" aria-label="Toggle navigation" type="button">
            ☰
        </button>
    </nav>

    <!-- ===== MAIN CONTENT AREA ===== -->
    <div class="app-wrapper">
        <main class="main-content <?php echo $__env->yieldContent('content_class'); ?>">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <script>
        // ---- Navigation Dropdowns (Desktop & Mobile) ----
        document.querySelectorAll('.topnav-nav-dropdown').forEach(function(dropdown) {
            const trigger = dropdown.querySelector('.topnav-dropdown-trigger');
            if (trigger) {
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const wasOpen = dropdown.classList.contains('open');
                    // Close all other dropdowns
                    document.querySelectorAll('.topnav-nav-dropdown').forEach(d => d.classList.remove('open'));
                    if (userEl) userEl.classList.remove('open');

                    if (!wasOpen) {
                        dropdown.classList.add('open');
                        trigger.setAttribute('aria-expanded', 'true');
                    } else {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });

        // ---- User Dropdown ----
        const userBtn   = document.getElementById('user-dropdown-btn');
        const userEl    = document.getElementById('topnav-user');
        const userDrop  = document.getElementById('user-dropdown');

        if (userBtn && userEl) {
            userBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                // Close navigation dropdowns if open
                document.querySelectorAll('.topnav-nav-dropdown').forEach(d => d.classList.remove('open'));
                const isOpen = userEl.classList.toggle('open');
                userBtn.setAttribute('aria-expanded', isOpen);
            });
        }

        // Close any dropdown when clicking outside
        document.addEventListener('click', function () {
            document.querySelectorAll('.topnav-nav-dropdown').forEach(d => d.classList.remove('open'));
            if (userEl) userEl.classList.remove('open');
            if (userBtn) userBtn.setAttribute('aria-expanded', 'false');
        });

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
    <?php echo $__env->yieldContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/layouts/app.blade.php ENDPATH**/ ?>