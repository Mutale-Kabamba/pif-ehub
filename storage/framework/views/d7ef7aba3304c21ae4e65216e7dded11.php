

<?php $__env->startSection('title', 'Survey: ' . $assessment->title); ?>

<?php $__env->startSection('content'); ?>
<?php
    $rules = $assessment->rules ? (is_array($assessment->rules->rules_json) ? $assessment->rules->rules_json : json_decode($assessment->rules->rules_json, true)) : [];
    $surveyStage = $rules['survey_stage'] ?? 'general';
    $isAnonymous = !empty($rules['is_anonymous']);
    $stageLabels = [
        'baseline' => ['name' => 'Baseline Survey', 'desc' => 'Initial assessment of knowledge, expectations, and skills before training.', 'badge' => 'badge-blue', 'icon' => '🌱'],
        'midline' => ['name' => 'Midline Survey', 'desc' => 'Progress checkpoint to evaluate learning velocity, challenges, and mid-course adaptation.', 'badge' => 'badge-teal', 'icon' => '⚖️'],
        'endline' => ['name' => 'Endline Survey', 'desc' => 'Culminating impact evaluation measuring skill acquisition, confidence, and career readiness.', 'badge' => 'badge-purple', 'icon' => '🎓'],
        'general' => ['name' => 'Feedback Survey', 'desc' => 'Monitoring and evaluation feedback questionnaire.', 'badge' => 'badge-gray', 'icon' => '📋'],
    ];
    $stageInfo = $stageLabels[$surveyStage] ?? $stageLabels['general'];
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span class="badge <?php echo e($stageInfo['badge']); ?>" style="font-size: 0.85rem; padding: 4px 10px;">
                <?php echo e($stageInfo['icon']); ?> <?php echo e($stageInfo['name']); ?>

            </span>
            <?php if($isAnonymous): ?>
                <span class="badge badge-purple" style="font-size: 0.85rem; padding: 4px 10px;">
                    🔒 Anonymous Response
                </span>
            <?php else: ?>
                <span class="badge badge-teal" style="font-size: 0.85rem; padding: 4px 10px;">
                    🧑‍🎓 Identified Response
                </span>
            <?php endif; ?>
        </div>
        <h1 style="margin: 0 0 6px 0;"><?php echo e($assessment->title); ?></h1>
        <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem;">
            <?php echo e($assessment->description ?: $stageInfo['desc']); ?>

        </p>
    </div>
    <div>
        <a href="<?php echo e(route('surveys.index')); ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
            &larr; Back to Gallery
        </a>
    </div>
</div>

<div style="background: var(--surface); border: 1px solid var(--border); border-left: 4px solid #8b5cf6; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px;">
    <div style="font-size: 1.5rem;">ℹ️</div>
    <div style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5;">
        <strong style="color: var(--text-primary);">Monitoring &amp; Evaluation Instrument:</strong>
        This survey is designed to track longitudinal program outcomes. It is not an exam, has no pass/fail grading, and your candid answers help improve training quality and impact measurement.
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-error">
        <strong>Please complete all required questions:</strong>
        <ul style="margin-top: 8px; padding-left: 20px;">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?php echo e(route('surveys.submit', $assessment->id)); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <?php if(!$isAnonymous): ?>
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px;">
            <div class="form-group" style="margin: 0;">
                <label for="respondent_name" style="font-weight: 600;">Your Name / Student ID (Optional)</label>
                <input type="text" name="respondent_name" id="respondent_name" class="form-control" placeholder="Enter your full name or student ID...">
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">Leave blank if you prefer not to attach your name.</small>
            </div>
        </div>
    <?php else: ?>
        <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem;">🔒</span>
            <span style="font-size: 0.85rem; color: #5b21b6; font-weight: 500;">
                <strong>Anonymous Mode Active:</strong> Your individual identity will not be collected or linked to your responses.
            </span>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 30px;">
        <?php $__empty_1 = true; $__currentLoopData = $assessment->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="evaluation-block" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 12px 0; font-size: 1.1rem; color: #1a1a1a;">
                    Question #<?php echo e($idx + 1); ?>: <?php echo e($question->question_text); ?>

                </h4>

                <input type="hidden" name="scores[<?php echo e($idx); ?>][question_id]" value="<?php echo e($question->id); ?>">

                <?php if($question->type === 'scale'): ?>
                    <!-- Numeric / 1-5 Scale -->
                    <div style="margin-top: 12px;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: #555; display: block; margin-bottom: 8px;">
                            Select Rating (1 = Strongly Disagree / Poor, 5 = Strongly Agree / Excellent):
                        </label>
                        <div class="radio-group" style="display: flex; gap: 16px; flex-wrap: wrap;">
                            <?php for($s = 1; $s <= 5; $s++): ?>
                                <label style="display: flex; align-items: center; gap: 6px; padding: 10px 18px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; cursor: pointer;">
                                    <input type="radio" name="scores[<?php echo e($idx); ?>][score]" value="<?php echo e($s); ?>" required style="accent-color: #59B33F;">
                                    <strong style="font-size: 1rem;"><?php echo e($s); ?></strong>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php elseif($question->type === 'multiple_choice' && $question->options->isNotEmpty()): ?>
                    <!-- Multiple Choice -->
                    <div style="margin-top: 12px;">
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1px solid #ddd; border-radius: 6px; background: #fafafa; cursor: pointer;">
                                    <input type="radio" name="scores[<?php echo e($idx); ?>][score]" value="<?php echo e($opt->option_value); ?>" required style="accent-color: #59B33F; width: 18px; height: 18px;">
                                    <span><?php echo e($opt->option_label); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php elseif($question->type === 'boolean'): ?>
                    <!-- Yes / No -->
                    <div style="margin-top: 12px;">
                        <div class="radio-group" style="display: flex; gap: 16px;">
                            <?php
                                $opts = $question->options->isNotEmpty() ? $question->options : collect([
                                    (object)['option_label' => 'Yes', 'option_value' => 1.0],
                                    (object)['option_label' => 'No', 'option_value' => 0.0],
                                ]);
                            ?>
                            <?php $__currentLoopData = $opts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; cursor: pointer;">
                                    <input type="radio" name="scores[<?php echo e($idx); ?>][score]" value="<?php echo e($opt->option_value); ?>" required style="accent-color: #59B33F;">
                                    <strong><?php echo e($opt->option_label); ?></strong>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php elseif($question->type === 'text'): ?>
                    <!-- Written Text Response -->
                    <div class="form-group" style="margin-top: 12px; margin-bottom: 0;">
                        <textarea name="scores[<?php echo e($idx); ?>][text_response]" class="form-control" rows="3" placeholder="Type your answer or feedback here..." required></textarea>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="background: #fff; padding: 40px; text-align: center; border-radius: 8px; border: 1px solid #ddd; color: #777;">
                No questions configured for this survey.
            </div>
        <?php endif; ?>

        <?php if($assessment->questions->isNotEmpty()): ?>
            <div style="text-align: right; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 36px; font-size: 1.05rem; font-weight: bold;">
                    Submit Survey Response
                </button>
            </div>
        <?php endif; ?>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/survey/take.blade.php ENDPATH**/ ?>