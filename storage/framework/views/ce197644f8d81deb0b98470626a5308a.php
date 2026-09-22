<?php $__env->startSection('title', 'Dashboard — Play It Forward E-Hub'); ?>

<?php $__env->startSection('content'); ?>


<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
    <div>
        <h1 style="margin:0; font-size:1.45rem; font-weight:800; display:flex; align-items:center; gap:8px;">
            📊 Central Dashboard
            <?php if(isset($user) && $user->isSuper()): ?>
                <span class="badge badge-green" style="font-size:0.75rem; padding:2px 8px;">Super Admin</span>
            <?php else: ?>
                <span class="badge badge-blue" style="font-size:0.75rem; padding:2px 8px;">Panelist · Panel <?php echo e($user->panel ?: 'All'); ?></span>
            <?php endif; ?>
        </h1>
        <p style="margin:2px 0 0; color:var(--text-secondary); font-size:0.85rem;">
            Select any Survey, Assessment, or Interview to inspect its respective results and scores.
        </p>
    </div>

    <?php if(isset($user) && $user->isSuper()): ?>
        <div style="display:flex; gap:8px;">
            <a href="<?php echo e(route('assessments.create')); ?>" class="btn btn-primary btn-sm" style="font-size:0.85rem; padding:6px 14px;">
                + Create New Engine
            </a>
        </div>
    <?php endif; ?>
</div>


<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:20px;">
    
    <div onclick="window.location='<?php echo e(route('admin.dashboard', ['type' => 'survey'])); ?>'"
         style="cursor:pointer; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-left:4px solid var(--green-primary); transition:transform 0.15s, box-shadow 0.15s;">
        <div>
            <div style="font-size:0.75rem; color:var(--green-dark); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                Surveys
            </div>
            <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); line-height:1.2; margin-top:2px;">
                <?php echo e($totalSurveys ?? 0); ?>

            </div>
        </div>
        <div style="font-size:1.8rem; opacity:0.85;">📋</div>
    </div>

    
    <div onclick="window.location='<?php echo e(route('admin.dashboard', ['type' => 'assessment'])); ?>'"
         style="cursor:pointer; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-left:4px solid #7c3aed; transition:transform 0.15s, box-shadow 0.15s;">
        <div>
            <div style="font-size:0.75rem; color:#7c3aed; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                Assessments
            </div>
            <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); line-height:1.2; margin-top:2px;">
                <?php echo e($totalAssessments ?? 0); ?>

            </div>
        </div>
        <div style="font-size:1.8rem; opacity:0.85;">💻</div>
    </div>

    
    <div onclick="window.location='<?php echo e(route('admin.dashboard', ['type' => 'interview'])); ?>'"
         style="cursor:pointer; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-left:4px solid #2563eb; transition:transform 0.15s, box-shadow 0.15s;">
        <div>
            <div style="font-size:0.75rem; color:#2563eb; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                Interviews
            </div>
            <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); line-height:1.2; margin-top:2px;">
                <?php echo e($totalInterviews ?? 0); ?>

            </div>
        </div>
        <div style="font-size:1.8rem; opacity:0.85;">🎙️</div>
    </div>
</div>


<div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:10px 14px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
    
    <div class="tabs" style="margin:0;">
        <a href="<?php echo e(route('admin.dashboard', array_filter(['status' => $statusFilter, 'q' => $searchQuery]))); ?>"
           class="tab <?php echo e(empty($typeFilter) ? 'active' : ''); ?>" style="padding:4px 12px; font-size:0.82rem;">
            All Engines
        </a>
        <a href="<?php echo e(route('admin.dashboard', array_filter(['type' => 'survey', 'status' => $statusFilter, 'q' => $searchQuery]))); ?>"
           class="tab <?php echo e($typeFilter === 'survey' ? 'active' : ''); ?>" style="padding:4px 12px; font-size:0.82rem;">
            📋 Surveys
        </a>
        <a href="<?php echo e(route('admin.dashboard', array_filter(['type' => 'assessment', 'status' => $statusFilter, 'q' => $searchQuery]))); ?>"
           class="tab <?php echo e($typeFilter === 'assessment' ? 'active' : ''); ?>" style="padding:4px 12px; font-size:0.82rem;">
            💻 Assessments
        </a>
        <a href="<?php echo e(route('admin.dashboard', array_filter(['type' => 'interview', 'status' => $statusFilter, 'q' => $searchQuery]))); ?>"
           class="tab <?php echo e($typeFilter === 'interview' ? 'active' : ''); ?>" style="padding:4px 12px; font-size:0.82rem;">
            🎙️ Interviews
        </a>
    </div>

    
    <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" style="display:flex; gap:6px; align-items:center; margin:0;">
        <?php if($typeFilter): ?>
            <input type="hidden" name="type" value="<?php echo e($typeFilter); ?>">
        <?php endif; ?>

        <select name="status" onchange="this.form.submit()" style="padding:4px 8px; font-size:0.8rem; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--text-primary);">
            <option value="">All Statuses</option>
            <option value="active" <?php echo e($statusFilter === 'active' ? 'selected' : ''); ?>>Active</option>
            <option value="draft" <?php echo e($statusFilter === 'draft' ? 'selected' : ''); ?>>Draft</option>
            <option value="completed" <?php echo e($statusFilter === 'completed' ? 'selected' : ''); ?>>Completed</option>
        </select>

        <input type="text" name="q" value="<?php echo e($searchQuery ?? ''); ?>" placeholder="Search by name or key..."
               style="padding:4px 10px; font-size:0.8rem; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--text-primary); width:180px;">

        <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:0.78rem;">
            🔍
        </button>

        <?php if($typeFilter || $statusFilter || $searchQuery): ?>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-ghost btn-sm" style="padding:4px 6px; font-size:0.75rem;" title="Reset filters">
                ✕ Reset
            </a>
        <?php endif; ?>
    </form>
</div>


<?php if($assessments->isEmpty()): ?>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:40px 20px; text-align:center; color:var(--text-muted);">
        <div style="font-size:2.5rem; margin-bottom:8px;">🔍</div>
        <h3 style="color:var(--text-primary); font-size:1.1rem; margin-bottom:4px;">No matching engines found</h3>
        <p style="font-size:0.85rem; margin:0 0 16px;">Try adjusting your filters or search terms.</p>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline btn-sm">Clear Filters</a>
    </div>
<?php else: ?>
    <div style="display:flex; flex-direction:column; gap:10px;">
        <?php $__currentLoopData = $assessments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $typeBadgeClass = match($item->type) {
                    'interview'  => 'badge-blue',
                    'survey'     => 'badge-purple',
                    default      => 'badge-teal',
                };
                $statusBadgeClass = match($item->status) {
                    'active'    => 'badge-green',
                    'draft'     => 'badge-gray',
                    'completed' => 'badge-orange',
                    default     => 'badge-gray',
                };
                $typeIcon = match($item->type) {
                    'interview'  => '🎙️',
                    'survey'     => '📋',
                    default      => '💻',
                };
                $borderLeftColor = match($item->type) {
                    'interview'  => '#2563eb',
                    'survey'     => 'var(--green-primary)',
                    default      => '#7c3aed',
                };
            ?>

            <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px 16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-left:4px solid <?php echo e($borderLeftColor); ?>; transition:box-shadow 0.15s;">
                
                <div style="flex:1; min-width:260px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <span style="font-size:1.3rem;"><?php echo e($typeIcon); ?></span>
                        <a href="<?php echo e(route('assessments.show', $item->id)); ?>" style="color:var(--text-primary); text-decoration:none; font-weight:700; font-size:1.02rem;">
                            <?php echo e($item->title); ?>

                        </a>
                        <span class="badge <?php echo e($typeBadgeClass); ?>" style="font-size:0.72rem; padding:1px 6px;"><?php echo e(ucfirst($item->type)); ?></span>
                        <span class="badge <?php echo e($statusBadgeClass); ?>" style="font-size:0.72rem; padding:1px 6px;"><?php echo e(ucfirst($item->status)); ?></span>
                    </div>

                    <?php if($item->description): ?>
                        <p style="margin:0 0 8px; font-size:0.82rem; color:var(--text-secondary); line-height:1.4;">
                            <?php echo e(Str::limit($item->description, 130)); ?>

                        </p>
                    <?php endif; ?>

                    
                    <div style="display:flex; gap:12px; font-size:0.78rem; color:var(--text-muted); flex-wrap:wrap; align-items:center;">
                        <span>❓ <strong><?php echo e($item->questions_count); ?></strong> Questions</span>
                        <span>👥 <strong><?php echo e($item->panelists_count); ?></strong> Evaluators</span>
                        <span>🧑‍🎓 <strong><?php echo e($item->candidates_count); ?></strong> Candidates</span>
                        <span>📊 <strong><?php echo e($item->evaluation_scores_count ?? 0); ?></strong> Responses Logged</span>
                        <?php if($item->access_key): ?>
                            <span style="font-family:monospace; background:rgba(0,0,0,0.04); padding:1px 6px; border-radius:4px;">
                                🔑 <?php echo e($item->access_key); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div style="display:flex; gap:6px; align-items:center; flex-shrink:0;">
                    <a href="<?php echo e(route('assessments.show', $item->id)); ?>" class="btn btn-primary btn-sm" style="padding:6px 14px; font-size:0.85rem; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
                        📊 View Results →
                    </a>

                    <?php if($item->type === 'survey'): ?>
                        <a href="<?php echo e(route('surveys.take', $item->id)); ?>" class="btn btn-outline btn-sm" style="padding:6px 10px; font-size:0.82rem;" target="_blank">
                            🌐 Take Survey
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('assessments.evaluate', $item->id)); ?>" class="btn btn-outline btn-sm" style="padding:6px 10px; font-size:0.82rem;">
                            ✏️ Grade
                        </a>
                    <?php endif; ?>

                    <?php if(isset($user) && $user->isSuper()): ?>
                        <a href="<?php echo e(route('assessments.edit', $item->id)); ?>" class="btn btn-ghost btn-sm" style="padding:6px 8px; font-size:0.8rem;" title="Edit assessment">
                            ⚙️
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>