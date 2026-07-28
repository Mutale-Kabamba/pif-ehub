<?php $__env->startSection('title', 'Welcome — Play It Forward E-Hub'); ?>

<?php $__env->startSection('content'); ?>


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

<?php if(session('success')): ?>
    <div style="max-width:700px; margin:-16px auto 24px auto; padding:0 24px;">
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div style="max-width:700px; margin:-16px auto 24px auto; padding:0 24px;">
        <div class="alert alert-error"><?php echo e(session('error')); ?></div>
    </div>
<?php endif; ?>


<div class="landing-grid">

    
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

        <form action="<?php echo e(route('admin.login.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="role">Select Identity / Role <span style="color:#dc2626;">*</span></label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled selected>— Select your name or role —</option>
                    <option value="Super User">⚙️ Super User (Administrator)</option>
                    <?php
                        $panelistList = \App\Models\User::where('role', 'panelist')->orderBy('name')->get();
                    ?>
                    <?php $__currentLoopData = $panelistList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pUser->panelist_name ?: $pUser->name); ?>">
                            👤 <?php echo e($pUser->panelist_name ?: $pUser->name); ?>

                            (Panel <?php echo e($pUser->panel ?: 'All'); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color:#dc2626;">*</span></label>
                <input type="password" name="password" id="password"
                       class="form-control"
                       placeholder="Enter your password"
                       required>
            </div>

            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="alert alert-error" style="margin-bottom:12px;"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php if(session('error')): ?>
                <div class="alert alert-error" style="margin-bottom:12px;"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Sign In to Portal →
            </button>
        </form>

        <div style="margin-top:20px; padding-top:20px; border-top:1px solid var(--border); font-size:0.82rem; color:var(--text-muted); text-align:center;">
            Admin access · Panelist evaluation · Assessment management
        </div>
    </div>

    
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
            <a href="<?php echo e(route('surveys.index')); ?>"
               class="btn btn-blue btn-full btn-lg">
                Open Survey Gallery →
            </a>
            <div style="font-size:0.78rem; color:var(--text-muted); text-align:center;">
                No login required · Open to all candidates
            </div>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/landing.blade.php ENDPATH**/ ?>