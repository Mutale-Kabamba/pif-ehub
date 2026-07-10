<?php $__env->startSection('title', 'Student Survey Portal'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <h1>Play It Forward (PIF) E-Hub</h1>
    <h4><strong>Professional Cohort (PIZ-C4-26)</strong> | Trainee Digital Self-Efficacy &amp; Mindset Survey</h4>
</div>

<div class="info-box">
    <p style="margin-bottom: 0;">
        Welcome to the Play It Forward (PIF) Professional Cohort E-Hub. This anonymous survey captures your
        Digital Self-Efficacy and Mindset at the <strong>start (Day 1/Baseline)</strong> and
        <strong>completion (Day 156/Endline)</strong> of your 6-month skills programme.
        Your honest responses help us measure the programme's impact and improve future cohorts.
        Please answer all questions truthfully — there are no right or wrong answers.
    </p>
</div>

<button id="tts-btn" class="btn btn-primary" style="margin-bottom: 20px;"
        data-text="Welcome to the Play It Forward (PIF) Professional Cohort E-Hub. This anonymous survey captures your Digital Self-Efficacy and Mindset at the start (Day 1, Baseline) and completion (Day 156, Endline) of your 6-month skills programme. Your honest responses help us measure the programme's impact and improve future cohorts. Please answer all questions truthfully — there are no right or wrong answers.">
    &#128266; Listen to Introduction (Read Aloud)
</button>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<form action="<?php echo e(route('survey.store')); ?>" method="POST" id="survey-form">
    <?php echo csrf_field(); ?>

    <div class="form-group">
        <label for="survey_type">Survey Type</label>
        <select name="survey_type" id="survey_type" class="form-control" required>
            <option value="">-- Select Survey Timing --</option>
            <option value="baseline">Baseline (Day 1)</option>
            <option value="endline">Endline (Day 156)</option>
        </select>
    </div>

    <hr class="section-divider">

    <h3>Part 1: Technical &amp; Mindset Self-Assessment</h3>

    <div class="legend-box">
        <strong>How to Answer:</strong> Rate your scale of agreement:
        <strong>1:</strong> Strongly Disagree |
        <strong>2:</strong> Disagree |
        <strong>3:</strong> Neutral |
        <strong>4:</strong> Agree |
        <strong>5:</strong> Strongly Agree
    </div>

    <?php $__currentLoopData = $quantQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="question-block">
            <p><?php echo e($loop->iteration); ?>. <?php echo e($question); ?></p>
            <div class="radio-group">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <label>
                        <input type="radio" name="<?php echo e($key); ?>" value="<?php echo e($i); ?>" <?php echo e($i == 3 ? 'checked' : ''); ?> required>
                        <?php echo e($i); ?>

                    </label>
                <?php endfor; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <hr class="section-divider">

    <h3>Part 2: Qualitative Insight Responses</h3>

    <?php $__currentLoopData = $qualQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="form-group question-block">
            <p><?php echo e($question); ?></p>
            <textarea name="<?php echo e($key); ?>" class="form-control" rows="4"
                      placeholder="Share your honest thoughts..." required></textarea>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="form-group" style="margin-top: 32px;">
        <button type="submit" class="btn btn-primary btn-full" style="font-size: 1.1rem; padding: 14px 24px;">
            Submit Survey Safely
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/survey/index.blade.php ENDPATH**/ ?>