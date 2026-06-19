<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'PIF E-Hub'); ?> - Play It Forward</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/pif-theme.css')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

    <button id="mobile-toggle" class="mobile-toggle" aria-label="Toggle navigation">
        &#9776;
    </button>

    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar">
            <div class="logo-area">
                <h3>PLAY IT FORWARD</h3>
                <small style="color: #999; display: block; margin-top: 2px; font-size: 0.8rem;">E-Hub</small>
            </div>

            <nav>
                <a href="<?php echo e(url('/')); ?>" class="<?php echo e(request()->is('/') ? 'active' : ''); ?>">
                    Student Survey Portal
                </a>
                <a href="<?php echo e(route('admin.login')); ?>" class="<?php echo e(request()->is('admin*') ? 'active' : ''); ?>">
                    Admin Dashboard
                </a>
            </nav>

            <div class="user-info">
                <?php if(session()->has('admin_user_id')): ?>
                    <div style="margin-bottom: 8px;">
                        <strong style="color: #59B33F;"><?php echo e(session('auth_role', 'Admin')); ?></strong>
                    </div>
                    <form action="<?php echo e(route('admin.logout')); ?>" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-danger btn-full" style="font-size: 0.85rem; padding: 6px 12px;">
                            Logout
                        </button>
                    </form>
                <?php else: ?>
                    <div style="font-size: 0.85rem; color: #888;">
                        Not authenticated
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer">
                Play It Forward E-Hub &bull; Cohort PIZ-C4-26
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <!-- Scripts -->
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/layouts/app.blade.php ENDPATH**/ ?>