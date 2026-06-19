<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1>Admin Dashboard</h1>
        <p style="color: #555; font-size: 0.9rem;">
            Session Authenticated Successfully as: <strong><?php echo e($user->name ?? session('auth_role', 'Admin')); ?></strong>
        </p>
    </div>

    <form action="<?php echo e(route('admin.logout')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-danger" style="font-size: 0.85rem; padding: 8px 16px;">
            Logout
        </button>
    </form>
</div>

<!-- Tab Navigation -->
<div class="tabs">
    <?php if(isset($user) && $user->isSuper()): ?>
        <a href="?tab=leaderboard" class="tab <?php echo e($tab == 'leaderboard' ? 'active' : ''); ?>">
            &#x1F3C6; Leaderboard
        </a>
        <a href="?tab=analytics" class="tab <?php echo e($tab == 'analytics' ? 'active' : ''); ?>">
            &#x1F4CA; Analytics
        </a>
        <a href="?tab=literacy" class="tab <?php echo e($tab == 'literacy' ? 'active' : ''); ?>">
            &#x1F4BB; Literacy
        </a>
    <?php endif; ?>
    <a href="?tab=panel" class="tab <?php echo e($tab == 'panel' ? 'active' : ''); ?>">
        &#x1F4DD; Panel Evaluation
    </a>
</div>

<!-- Tab Content -->
<?php if($tab == 'leaderboard' && isset($user) && $user->isSuper()): ?>
    <?php echo $__env->make('admin.partials.leaderboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($tab == 'analytics' && isset($user) && $user->isSuper()): ?>
    <?php echo $__env->make('admin.partials.analytics', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($tab == 'literacy' && isset($user) && $user->isSuper()): ?>
    <?php echo $__env->make('admin.partials.literacy-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($tab == 'panel'): ?>
    <?php echo $__env->make('admin.partials.panel-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Downloads\Kimi_Agent_Composer Discover Error\project\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>