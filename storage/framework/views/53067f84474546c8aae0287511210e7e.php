<h3>&#x1F4BB; Practical Computer Literacy Grading Terminal</h3>

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

<form action="<?php echo e(route('admin.literacy.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="form-group">
        <label for="candidate_id">Select Candidate</label>
        <select name="candidate_id" id="candidate_id" class="form-control" required>
            <option value="">-- Choose a Candidate --</option>
            <?php $__currentLoopData = $candidates ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($candidate->id); ?>"><?php echo e($candidate->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="form-group">
        <label for="assessment_date">Assessment Date</label>
        <input type="date" name="assessment_date" id="assessment_date" class="form-control"
               value="<?php echo e(date('Y-m-d')); ?>" required>
    </div>

    <hr class="section-divider">

    <?php $__currentLoopData = $literacyTasks ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="question-block">
            <p><?php echo e($loop->iteration); ?>. <?php echo e($task); ?></p>
            <div class="radio-group" style="gap: 16px;">
                <label>
                    <input type="radio" name="<?php echo e($key); ?>" value="0" checked> 0 (Not Done)
                </label>
                <label>
                    <input type="radio" name="<?php echo e($key); ?>" value="1"> 1 (Partial)
                </label>
                <label>
                    <input type="radio" name="<?php echo e($key); ?>" value="2"> 2 (Complete)
                </label>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="form-group" style="margin-top: 32px;">
        <button type="submit" class="btn btn-primary btn-full" style="font-size: 1.05rem; padding: 14px 24px;">
            Save Practical Test Scores
        </button>
    </div>
</form>
<?php /**PATH C:\Users\mukuk\Downloads\Kimi_Agent_Composer Discover Error\project\resources\views/admin/partials/literacy-form.blade.php ENDPATH**/ ?>