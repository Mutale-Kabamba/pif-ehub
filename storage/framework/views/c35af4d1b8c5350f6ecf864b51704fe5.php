<h3>&#x1F4DD; Live Selection Interview Sheet: Panelist <?php echo e($user->panelist_name ?? $user->name ?? 'Panelist'); ?></h3>

<div class="info-box" style="margin-bottom: 24px;">
    Evaluate candidate interview performance metrics below. Match candidate selection directly from the roster dropdown.
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<form action="<?php echo e(route('admin.panel.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="form-group">
        <label for="candidate_id">Select Candidate from Roster</label>
        <select name="candidate_id" id="candidate_id" class="form-control" required>
            <option value="">-- Choose a Candidate --</option>
            <?php $__currentLoopData = $candidates ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($candidate->id); ?>"><?php echo e($candidate->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <!-- Evaluation Block 1: Motivation -->
    <div class="evaluation-block">
        <h4>&#x1F5E3;&#xFE0F; Motivation Questionnaire Prompt:</h4>
        <p class="prompt">"Why do you specifically want to learn digital product development at PIF?"</p>
        <div class="radio-group">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="crit1_motivation" value="<?php echo e($i); ?>" <?php echo e($i == 3 ? 'checked' : ''); ?>>
                    <?php echo e($i); ?>

                </label>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Evaluation Block 2: Availability -->
    <div class="evaluation-block">
        <h4>&#x1F5E3;&#xFE0F; Availability Questionnaire Prompt:</h4>
        <p class="prompt">"Are you able to commit to the full 6-month programme schedule including daily sessions and project assignments?"</p>
        <div class="radio-group">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="crit2_availability" value="<?php echo e($i); ?>" <?php echo e($i == 3 ? 'checked' : ''); ?>>
                    <?php echo e($i); ?>

                </label>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Evaluation Block 3: Resilience -->
    <div class="evaluation-block">
        <h4>&#x1F5E3;&#xFE0F; Resilience Questionnaire Prompt:</h4>
        <p class="prompt">"Describe a time you faced a major challenge or setback. How did you respond and what did you learn?"</p>
        <div class="radio-group">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="crit3_resilience" value="<?php echo e($i); ?>" <?php echo e($i == 3 ? 'checked' : ''); ?>>
                    <?php echo e($i); ?>

                </label>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Evaluation Block 4: Communication -->
    <div class="evaluation-block">
        <h4>&#x1F5E3;&#xFE0F; Communication Skills Assessment:</h4>
        <p class="prompt">Evaluate the candidate's ability to articulate thoughts clearly, listen actively, and respond coherently during the interview session.</p>
        <div class="radio-group">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="crit4_communication" value="<?php echo e($i); ?>" <?php echo e($i == 3 ? 'checked' : ''); ?>>
                    <?php echo e($i); ?>

                </label>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Comments -->
    <div class="form-group">
        <label for="comments">Your Panelist Interviewer Notes &amp; Observations</label>
        <textarea name="comments" id="comments" class="form-control" rows="6"
                  placeholder="Record any additional observations, context, or notes about this candidate's interview performance..."></textarea>
    </div>

    <!-- Submit -->
    <div class="form-group" style="margin-top: 32px;">
        <button type="submit" class="btn btn-primary btn-full" style="font-size: 1.05rem; padding: 14px 24px;">
            Record My Evaluation Metrics
        </button>
    </div>
</form>
<?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/admin/partials/panel-form.blade.php ENDPATH**/ ?>