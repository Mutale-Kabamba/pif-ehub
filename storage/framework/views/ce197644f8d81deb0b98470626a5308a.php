<?php $__env->startSection('title', 'Admin Dashboard — Play It Forward E-Hub'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <h1 style="margin-bottom:4px;">📊 Admin Dashboard</h1>
        <p style="color:var(--text-secondary); font-size:0.9rem;">
            Authenticated as: <strong><?php echo e($user->name ?? 'Administrator'); ?></strong>
            <?php if($user->isSuper()): ?>
                <span class="badge badge-green" style="vertical-align:middle; margin-left:6px;">Super Admin</span>
            <?php else: ?>
                <span class="badge badge-blue" style="vertical-align:middle; margin-left:6px;">
                    Panelist · Panel <?php echo e($user->panel ?: 'All'); ?>

                </span>
            <?php endif; ?>
        </p>
    </div>

    <?php if($user->isSuper()): ?>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="<?php echo e(route('admin.survey.export')); ?>" class="btn btn-outline btn-sm">
                📥 Export Survey CSV
            </a>
            <a href="<?php echo e(route('assessments.index')); ?>" class="btn btn-primary btn-sm">
                🧪 Assessment Engine
            </a>
        </div>
    <?php endif; ?>
</div>


<?php if($user->isSuper()): ?>
<div class="metric-cards" style="margin-bottom:28px;">
    <div class="metric-card" onclick="window.location='<?php echo e(route('assessments.index')); ?>'" style="cursor:pointer;">
        <div class="value">🧪</div>
        <div class="label">Assessment Engine</div>
    </div>
    <div class="metric-card" onclick="window.location='<?php echo e(route('admin.leaderboard')); ?>'" style="cursor:pointer;">
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
    <div class="metric-card" onclick="window.location='<?php echo e(route('surveys.index')); ?>'" style="cursor:pointer;">
        <div class="value" style="color:#0f766e;">📋</div>
        <div class="label">Survey Gallery</div>
    </div>
</div>
<?php endif; ?>


<div class="tabs">
    <?php if(isset($user) && $user->isSuper()): ?>
        <a href="?tab=leaderboard" class="tab <?php echo e($tab == 'leaderboard' ? 'active' : ''); ?>">
            🏆 Leaderboard
        </a>
        <a href="?tab=analytics" class="tab <?php echo e($tab == 'analytics' ? 'active' : ''); ?>">
            📊 Analytics
        </a>
        <a href="?tab=literacy" class="tab <?php echo e($tab == 'literacy' ? 'active' : ''); ?>">
            💻 Literacy
        </a>
    <?php endif; ?>
    <a href="?tab=panel" class="tab <?php echo e($tab == 'panel' ? 'active' : ''); ?>">
        📝 Panel Evaluation
    </a>
</div>


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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>