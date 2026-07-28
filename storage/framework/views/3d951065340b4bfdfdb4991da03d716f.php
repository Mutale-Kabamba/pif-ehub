<?php $__env->startSection('title', 'Student Survey Gallery — Play It Forward'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
    <div>
        <h1 style="margin-bottom:4px;">📋 Student Survey Gallery</h1>
        <p style="color:var(--text-secondary); font-size:0.9rem;">
            Select an active survey below and enter your unique access key to begin.
        </p>
    </div>
    <a href="<?php echo e(route('landing')); ?>" class="btn btn-ghost btn-sm">← Back to Home</a>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<?php if(session('key_error')): ?>
    <div class="alert alert-error"><?php echo e(session('key_error')); ?></div>
<?php endif; ?>


<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:24px; margin-bottom:40px;">
    <?php $__empty_1 = true; $__currentLoopData = $surveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $survey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $typeIcon = match($survey->type) {
                'interview'  => '🎤',
                'survey'     => '📋',
                default      => '📝',
            };
            $typeBadge = match($survey->type) {
                'interview' => 'badge-blue',
                'survey'    => 'badge-purple',
                default     => 'badge-teal',
            };
        ?>
        <div class="survey-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                <div class="survey-card-icon"><?php echo e($typeIcon); ?></div>
                <div style="display:flex; flex-direction:column; gap:4px; align-items:flex-end;">
                    <span class="badge <?php echo e($typeBadge); ?>"><?php echo e($survey->type); ?></span>
                    <span class="badge badge-gray"><?php echo e($survey->questions_count); ?> Questions</span>
                </div>
            </div>

            <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-bottom:8px; line-height:1.3;">
                <?php echo e($survey->title); ?>

            </h3>

            <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.6; margin-bottom:0; flex:1;">
                <?php echo e(Str::limit($survey->description ?: 'No detailed description provided.', 110)); ?>

            </p>

            <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border);">
                <button type="button"
                        onclick="openKeyModal('<?php echo e($survey->id); ?>', '<?php echo e(addslashes($survey->title)); ?>')"
                        class="btn btn-blue btn-full">
                    🔑 Unlock &amp; Take Survey
                </button>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column:1/-1; text-align:center; padding:60px 24px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); color:var(--text-muted);">
            <div style="font-size:3rem; margin-bottom:16px;">📋</div>
            <h3 style="color:var(--text-primary); margin-bottom:8px;">No Active Surveys</h3>
            <p>There are no active public surveys or assessments configured at this time. Please check back later.</p>
        </div>
    <?php endif; ?>
</div>


<div id="keyModal"
     style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:var(--surface); border-radius:var(--radius-lg); width:100%; max-width:420px; padding:32px; box-shadow:var(--shadow-lg); animation:keyModalIn 0.22s ease;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:1.15rem; font-weight:700; color:var(--text-primary);">
                🔑 Enter Access Key
            </h3>
            <button type="button"
                    onclick="closeKeyModal()"
                    style="background:transparent; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted); line-height:1; padding:2px 6px; border-radius:4px; transition:background 0.15s;"
                    onmouseover="this.style.background='var(--surface-alt)'"
                    onmouseout="this.style.background='transparent'">
                ×
            </button>
        </div>

        <p id="modalSurveyTitle"
           style="font-weight:600; color:var(--green-primary); margin-bottom:20px; font-size:0.95rem; padding:10px 14px; background:var(--green-light); border-radius:var(--radius-sm); border:1px solid var(--green-border);">
        </p>

        <form action="<?php echo e(route('surveys.verify')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="assessment_id" id="modalAssessmentId">

            <div class="form-group">
                <label for="access_key_input" style="font-weight:600;">
                    Unique Access Key <span style="color:#dc2626;">*</span>
                </label>
                <input type="text"
                       name="access_key"
                       id="access_key_input"
                       class="access-key-input"
                       placeholder="KEY-XXXXXX"
                       required
                       autocomplete="off"
                       autocapitalize="characters">
                <div class="form-hint" style="text-align:center;">
                    Enter the access key provided by your instructor or administrator.
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button"
                        onclick="closeKeyModal()"
                        class="btn btn-outline"
                        style="flex:1;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" style="flex:2;">
                    Verify &amp; Open →
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
@keyframes keyModalIn {
    from { opacity: 0; transform: scale(0.95) translateY(-12px); }
    to   { opacity: 1; transform: scale(1)    translateY(0); }
}
</style>
<script>
function openKeyModal(assessmentId, surveyTitle) {
    document.getElementById('modalAssessmentId').value = assessmentId;
    document.getElementById('modalSurveyTitle').textContent = surveyTitle;
    const modal = document.getElementById('keyModal');
    modal.style.display = 'flex';
    setTimeout(function() {
        document.getElementById('access_key_input').focus();
    }, 50);
}

function closeKeyModal() {
    document.getElementById('keyModal').style.display = 'none';
}

// Close modal on backdrop click
document.getElementById('keyModal').addEventListener('click', function(e) {
    if (e.target === this) closeKeyModal();
});

// ESC to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeKeyModal();
});

// Auto-uppercase access key input
document.getElementById('access_key_input').addEventListener('input', function() {
    const pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});

<?php if(session('target_assessment_id')): ?>
    document.addEventListener('DOMContentLoaded', function() {
        openKeyModal('<?php echo e(session("target_assessment_id")); ?>', 'Survey Verification Required');
    });
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/survey/gallery.blade.php ENDPATH**/ ?>