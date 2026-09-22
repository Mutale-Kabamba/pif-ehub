

<?php $__env->startSection('title', $assessment->title . ' — Assessment Hub'); ?>

<?php $__env->startSection('content'); ?>

<?php
    $currentUser = auth()->user() ?: \App\Models\User::find(session('admin_user_id'));
    $isSuper     = $currentUser && $currentUser->isSuper();
    $activeTab   = request()->query('tab', 'results');

    $typeBadgeClass = match($assessment->type) {
        'interview' => 'badge-blue',
        'survey'    => 'badge-purple',
        default     => 'badge-teal',
    };
    $statusBadgeClass = match($assessment->status) {
        'active'    => 'badge-green',
        'draft'     => 'badge-gray',
        'completed' => 'badge-orange',
        default     => 'badge-gray',
    };
    $typeIcon = match($assessment->type) {
        'interview' => '🎤',
        'survey'    => '📋',
        default     => '📝',
    };
?>


<div class="hub-header">
    <div class="hub-header-left">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
            <span style="font-size:2rem;"><?php echo e($typeIcon); ?></span>
            <h1><?php echo e($assessment->title); ?></h1>
        </div>
        <?php if($assessment->description): ?>
            <p><?php echo e($assessment->description); ?></p>
        <?php endif; ?>
        <div class="hub-header-badges" style="margin-top:10px;">
            <span class="badge <?php echo e($statusBadgeClass); ?>"><?php echo e($assessment->status); ?></span>
            <span class="badge <?php echo e($typeBadgeClass); ?>"><?php echo e($assessment->type); ?></span>
            <?php if($results['is_survey']): ?>
                <span class="badge badge-purple">
                    📌 <?php echo e(ucfirst($results['survey_stage'])); ?> Survey
                </span>
                <span class="badge <?php echo e($results['is_anonymous'] ? 'badge-blue' : 'badge-teal'); ?>">
                    <?php echo e($results['is_anonymous'] ? '🔒 Anonymous Responses' : '🧑‍🎓 Identified Participants'); ?>

                </span>
            <?php endif; ?>
            <?php if($assessment->access_key): ?>
                <span class="badge badge-gray" style="font-family:monospace; letter-spacing:1px;">
                    🔑 <?php echo e($assessment->access_key); ?>

                </span>
            <?php endif; ?>
            <?php if(!$results['is_survey'] && $assessment->rule?->passing_threshold !== null): ?>
                <span class="badge badge-teal">
                    Pass: <?php echo e(number_format($assessment->rule->passing_threshold, 1)); ?>

                </span>
            <?php endif; ?>
            <?php if(!$results['is_survey'] && $assessment->rule?->score_cap !== null): ?>
                <span class="badge badge-orange">
                    Cap: <?php echo e(number_format($assessment->rule->score_cap, 1)); ?>

                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="hub-header-actions">
        <button type="button" class="btn btn-sm" style="background:#2563eb; color:white;" onclick="openModal('importResultsModal')">
            📥 Import Results
        </button>
        <?php if($results['is_survey']): ?>
            <a href="<?php echo e(route('surveys.take', $assessment->id)); ?>"
               class="btn btn-sm" style="background:#7c3aed; color:white;" target="_blank">
                📋 Take / Open Survey ↗
            </a>
        <?php else: ?>
            <a href="<?php echo e(route('assessments.evaluate', $assessment->id)); ?>"
               class="btn btn-sm" style="background:#0f766e; color:white;">
                ✏️ Grade / Evaluate
            </a>
        <?php endif; ?>
        <?php if($isSuper): ?>
            <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>"
               class="btn btn-outline btn-sm">⚙️ Edit</a>
        <?php endif; ?>
        <a href="<?php echo e(route('assessments.index')); ?>"
           class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error"><?php echo e(session('error')); ?></div>
<?php endif; ?>


<?php if($results['is_survey']): ?>
    
    <div class="metric-cards" style="margin-bottom:24px;">
        <div class="metric-card">
            <div class="value" style="color:#2563eb;"><?php echo e($results['total_evaluations_submitted']); ?></div>
            <div class="label">Total Responses</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#7c3aed;"><?php echo e(ucfirst($results['survey_stage'])); ?></div>
            <div class="label">M&amp;E Stage</div>
        </div>
        <div class="metric-card">
            <div class="value"><?php echo e($results['total_questions']); ?></div>
            <div class="label">Questions</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:var(--green-primary);">
                <?php echo e($results['overall_survey_average'] > 0 ? number_format($results['overall_survey_average'], 2) : '—'); ?>

            </div>
            <div class="label">Avg Rating (1-5)</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#0284c7;">
                <?php echo e(count($results['qualitative_feedback'])); ?>

            </div>
            <div class="label">Qualitative Topics</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#64748b; font-size:1.1rem;">
                <?php echo e($results['is_anonymous'] ? 'Anonymous' : 'Identified'); ?>

            </div>
            <div class="label">Response Mode</div>
        </div>
    </div>
<?php else: ?>
    
    <div class="metric-cards" style="margin-bottom:24px;">
        <div class="metric-card">
            <div class="value"><?php echo e($results['total_candidates']); ?></div>
            <div class="label">Candidates</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#2563eb;"><?php echo e($results['total_panelists']); ?></div>
            <div class="label">Evaluators</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#7c3aed;"><?php echo e($results['total_questions']); ?></div>
            <div class="label">Questions</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:var(--green-primary);">
                <?php echo e(collect($results['candidate_results'])->where('passed', true)->count()); ?>

            </div>
            <div class="label">Passed</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#dc2626;">
                <?php echo e(collect($results['candidate_results'])->where('passed', false)->count()); ?>

            </div>
            <div class="label">Failed</div>
        </div>
        <div class="metric-card">
            <div class="value" style="color:#c2410c;">
                <?php echo e(collect($results['candidate_results'])->where('passed', null)->count()); ?>

            </div>
            <div class="label">Ungraded</div>
        </div>
    </div>
<?php endif; ?>


<div class="hub-tabs">
    <a href="<?php echo e(route('assessments.show', [$assessment->id, 'tab' => 'results'])); ?>"
       class="hub-tab <?php echo e($activeTab === 'results' ? 'active' : ''); ?>">
        <?php echo e($results['is_survey'] ? '📊 Survey Analysis & Responses' : '🏆 Results & Leaderboard'); ?>

    </a>
    <a href="<?php echo e(route('assessments.show', [$assessment->id, 'tab' => 'questions'])); ?>"
       class="hub-tab <?php echo e($activeTab === 'questions' ? 'active' : ''); ?>">
        ❓ Questions &amp; Criteria
    </a>
    <?php if($isSuper): ?>
        <?php if(!$results['is_survey']): ?>
            <a href="<?php echo e(route('assessments.show', [$assessment->id, 'tab' => 'panelists'])); ?>"
               class="hub-tab <?php echo e($activeTab === 'panelists' ? 'active' : ''); ?>">
                👥 Panelists
            </a>
            <a href="<?php echo e(route('assessments.show', [$assessment->id, 'tab' => 'candidates'])); ?>"
               class="hub-tab <?php echo e($activeTab === 'candidates' ? 'active' : ''); ?>">
                🧑‍🎓 Candidates
            </a>
        <?php endif; ?>
        <a href="<?php echo e(route('assessments.show', [$assessment->id, 'tab' => 'rules'])); ?>"
           class="hub-tab <?php echo e($activeTab === 'rules' ? 'active' : ''); ?>">
            📐 <?php echo e($results['is_survey'] ? 'Survey Settings' : 'Rules & Config'); ?>

        </a>
    <?php endif; ?>
</div>


<?php if($activeTab === 'results'): ?>
    <?php if($results['is_survey']): ?>
        
        <div style="display:grid; gap:20px;">
            
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <span class="card-title">📈 Question-by-Question M&amp;E Ratings</span>
                        <div class="info-box info" style="margin:4px 0 0; padding:4px 12px; font-size:0.75rem; border-radius:100px; display:inline-block;">
                            <strong>M&amp;E Note:</strong> Surveys are for tracking participant baseline, midline, and endline feedback without pass/fail cutoffs.
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="<?php echo e(route('assessments.template', ['assessment' => $assessment->id, 'format' => 'xlsx'])); ?>"
                           class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                            📥 Template (.xlsx)
                        </a>
                        <a href="<?php echo e(route('assessments.template', ['assessment' => $assessment->id, 'format' => 'csv'])); ?>"
                           class="btn btn-ghost btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                            .csv
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('importResultsModal')" style="display:inline-flex; align-items:center; gap:6px;">
                            📤 Import Results
                        </button>
                    </div>
                </div>

                <div style="padding:20px; display:grid; gap:16px;">
                    <?php $__empty_1 = true; $__currentLoopData = $results['survey_question_stats']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div style="background:var(--surface-alt); border:1px solid var(--border); border-radius:var(--radius-sm); padding:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; gap:12px;">
                                <div style="font-weight:700; color:var(--text-primary); font-size:0.92rem;">
                                    <?php echo e($qStat['question_text']); ?>

                                </div>
                                <?php if($qStat['avg_score'] !== null): ?>
                                    <div style="font-size:1.1rem; font-weight:800; color:var(--green-dark); flex-shrink:0;">
                                        <?php echo e(number_format($qStat['avg_score'], 2)); ?> <span style="font-size:0.75rem; color:var(--text-muted);">/ 5.0</span>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-gray" style="font-size:0.75rem;">Text Feedback</span>
                                <?php endif; ?>
                            </div>

                            <?php if($qStat['type'] === 'scale' && $qStat['response_count'] > 0): ?>
                                <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:8px; margin-top:10px;">
                                    <?php for($r = 1; $r <= 5; $r++): ?>
                                        <?php
                                            $count = $qStat['distribution'][$r] ?? 0;
                                            $pct = $qStat['response_count'] > 0 ? round(($count / $qStat['response_count']) * 100) : 0;
                                        ?>
                                        <div style="background:#fff; border:1px solid var(--border); border-radius:4px; padding:6px 8px; text-align:center;">
                                            <div style="font-size:0.72rem; color:var(--text-muted);">Rating <?php echo e($r); ?></div>
                                            <div style="font-weight:700; font-size:0.9rem; color:var(--text-primary);"><?php echo e($count); ?> <span style="font-size:0.7rem; color:var(--text-muted);">(<?php echo e($pct); ?>%)</span></div>
                                            <div style="height:4px; background:#e5e7eb; border-radius:2px; margin-top:4px; overflow:hidden;">
                                                <div style="height:100%; width:<?php echo e($pct); ?>%; background:var(--green-primary);"></div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div style="text-align:center; padding:32px; color:var(--text-muted);">
                            No survey questions configured yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php if(!empty($results['qualitative_feedback'])): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">💬 Qualitative Participant Feedback</span>
                    </div>
                    <div style="padding:20px; display:grid; gap:16px;">
                        <?php $__currentLoopData = $results['qualitative_feedback']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qFeed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="background:var(--surface-alt); border:1px solid var(--border); border-radius:var(--radius-sm); padding:16px;">
                                <div style="font-weight:700; color:var(--text-primary); font-size:0.9rem; margin-bottom:12px;">
                                    ❓ <?php echo e($qFeed['question_text']); ?>

                                </div>
                                <div style="display:grid; gap:8px;">
                                    <?php $__currentLoopData = $qFeed['responses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $respText): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div style="background:#fff; border-left:3px solid var(--green-primary); padding:10px 14px; border-radius:4px; font-size:0.85rem; color:var(--text-primary); line-height:1.4;">
                                            "<?php echo e($respText); ?>"
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <span class="card-title">🏆 Candidate Evaluation Leaderboard</span>
                    <div class="info-box info" style="margin:4px 0 0; padding:4px 12px; font-size:0.75rem; border-radius:100px; display:inline-block;">
                        <strong>Formula:</strong> Avg<sub>panelists</sub>(Σ question_score × weight), capped at limit
                    </div>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="<?php echo e(route('assessments.template', ['assessment' => $assessment->id, 'format' => 'xlsx'])); ?>"
                       class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                        📥 Template (.xlsx)
                    </a>
                    <a href="<?php echo e(route('assessments.template', ['assessment' => $assessment->id, 'format' => 'csv'])); ?>"
                       class="btn btn-ghost btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                        .csv
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('importResultsModal')" style="display:inline-flex; align-items:center; gap:6px;">
                        📤 Import Results
                    </button>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">Rank</th>
                            <th>Candidate</th>
                            <th>Panel</th>
                            <th style="text-align:center;">Evaluations</th>
                            <th style="text-align:right;">Weighted Total</th>
                            <th style="text-align:right;">Final Score</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $results['candidate_results']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($res['passed'] === true ? 'accepted' : ''); ?>">
                                <td style="font-weight:700; color:var(--text-muted);">
                                    #<?php echo e($idx + 1); ?>

                                </td>
                                <td>
                                    <strong><?php echo e($res['candidate']->name); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-gray">Panel <?php echo e($res['candidate']->panel); ?></span>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:600;"><?php echo e($res['evaluator_count']); ?></span>
                                </td>
                                <td style="text-align:right; font-weight:600; color:var(--text-secondary);">
                                    <?php echo e(number_format($res['weighted_total'], 2)); ?>

                                </td>
                                <td style="text-align:right; font-weight:800; font-size:1.1rem; color:var(--green-primary);">
                                    <?php echo e(number_format($res['final_score'], 2)); ?>

                                </td>
                                <td style="text-align:center;">
                                    <?php if($res['passed'] === true): ?>
                                        <span class="badge badge-green">PASSED</span>
                                    <?php elseif($res['passed'] === false): ?>
                                        <span class="badge badge-red">FAILED</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">UNGRADED</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <a href="<?php echo e(route('assessments.evaluate', [$assessment->id, 'candidate_id' => $res['candidate']->id])); ?>"
                                       class="btn btn-primary btn-sm">Grade</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">
                                    No candidates assigned or evaluated yet.
                                    <?php if($isSuper): ?>
                                        <a href="javascript:void(0)" onclick="openModal('addCandidatesModal')" style="color:var(--green-primary); font-weight:600;">+ Add candidates now</a>
                                        or
                                        <a href="javascript:void(0)" onclick="openModal('importResultsModal')" style="color:#2563eb; font-weight:600;">📥 Import Results from Excel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>


<?php if($activeTab === 'questions'): ?>
    <div style="display:grid; gap:16px;">
        <?php $__empty_1 = true; $__currentLoopData = $assessment->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $qTypeClass = match($q->type) {
                    'scale'           => 'badge-blue',
                    'multiple_choice' => 'badge-purple',
                    'boolean'         => 'badge-teal',
                    default           => 'badge-gray',
                };
            ?>
            <div class="question-builder-item">
                <div class="q-handle"><?php echo e($q->order); ?></div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:10px;">
                    <strong style="font-size:0.95rem; line-height:1.4; color:var(--text-primary); flex:1;">
                        <?php echo e($q->question_text); ?>

                    </strong>
                    <div style="display:flex; gap:6px; flex-shrink:0; align-items:center;">
                        <span class="badge <?php echo e($qTypeClass); ?>"><?php echo e($q->type); ?></span>
                        <span class="badge badge-green"><?php echo e(number_format($q->weight, 1)); ?>× weight</span>
                    </div>
                </div>

                <?php if($q->type === 'scale'): ?>
                    <div style="font-size:0.82rem; color:var(--text-secondary);">
                        Scale rating (1–10 or configured range). Evaluator enters a numeric score.
                    </div>
                <?php elseif($q->type === 'text'): ?>
                    <div style="font-size:0.82rem; color:var(--text-secondary);">
                        Open-ended text response. No numeric score calculated.
                    </div>
                <?php endif; ?>

                <?php if($q->options->isNotEmpty()): ?>
                    <div style="margin-top:10px; font-size:0.82rem; color:var(--text-secondary);">
                        <strong>Options:</strong>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:6px;">
                            <?php $__currentLoopData = $q->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge badge-gray" style="font-size:0.78rem;">
                                    <?php echo e($opt->option_label); ?>

                                    <span style="color:var(--green-primary); font-weight:700; margin-left:4px;">
                                        +<?php echo e(number_format($opt->option_value, 1)); ?>

                                    </span>
                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align:center; padding:40px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text-muted);">
                No questions configured.
                <?php if($isSuper): ?>
                    <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>" style="color:var(--green-primary);">Add questions →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


<?php if($activeTab === 'panelists' && $isSuper): ?>
    <div class="card">
        <div class="card-header">
            <span class="card-title">👥 Assigned Panelists / Evaluators</span>
            <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>" class="btn btn-outline btn-sm">
                Manage Panelists
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Panel</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $assessment->panelists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $assignment = $assessment->assignments
                                ->where('user_id', $p->id)
                                ->where('role', 'panelist')
                                ->first();
                        ?>
                        <tr>
                            <td><strong><?php echo e($p->name); ?></strong></td>
                            <td style="color:var(--text-secondary); font-size:0.85rem;"><?php echo e($p->email); ?></td>
                            <td>
                                <span class="badge badge-gray">
                                    <?php echo e($assignment?->panel_name ?: ($p->panel ?: 'All')); ?>

                                </span>
                            </td>
                            <td><span class="badge badge-blue"><?php echo e(ucfirst($p->role)); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:32px; color:var(--text-muted);">
                                No panelists assigned.
                                <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>" style="color:var(--green-primary);">Assign panelists →</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>


<?php if($activeTab === 'candidates' && $isSuper): ?>
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <span class="card-title">🧑‍🎓 Assigned Candidates (<?php echo e($assessment->candidates->count()); ?>)</span>
                <p style="margin:2px 0 0; color:var(--text-secondary); font-size:0.82rem;">
                    Manage candidates assigned to this assessment, add new candidates, or remove existing participants.
                </p>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCandidatesModal')">
                    + Add Candidates
                </button>
                <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>" class="btn btn-outline btn-sm">
                    ⚙️ Configure Roster
                </a>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Candidate Name</th>
                        <th>Gender Track</th>
                        <th>Assigned Panel</th>
                        <th style="text-align:center;">Evaluated</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $assessment->candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $candAssignment = $assessment->assignments
                                ->where('candidate_id', $c->id)
                                ->where('role', 'candidate')
                                ->first();

                            $hasScores = \App\Models\EvaluationScore::where('assessment_id', $assessment->id)
                                ->where('candidate_id', $c->id)
                                ->exists();
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo e($c->name); ?></strong>
                            </td>
                            <td>
                                <span class="badge <?php echo e($c->gender === 'Female' ? 'badge-purple' : 'badge-blue'); ?>">
                                    <?php echo e($c->gender ?: 'N/A'); ?>

                                </span>
                            </td>
                            <td>
                                <span class="badge badge-gray">
                                    Panel <?php echo e($candAssignment?->panel_name ?: ($c->panel ?: 'A')); ?>

                                </span>
                            </td>
                            <td style="text-align:center;">
                                <?php if($hasScores): ?>
                                    <span class="badge badge-green">Scored</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px; align-items:center;">
                                    <a href="<?php echo e(route('assessments.evaluate', [$assessment->id, 'candidate_id' => $c->id])); ?>"
                                       class="btn btn-primary btn-sm" style="padding:4px 10px; font-size:0.8rem;">
                                        Grade
                                    </a>
                                    <form action="<?php echo e(route('assessments.candidates.remove', ['assessment' => $assessment->id, 'candidate' => $c->id])); ?>" method="POST"
                                          onsubmit="return confirm('Remove candidate <?php echo e(addslashes($c->name)); ?> from this assessment? Associated assessment evaluation scores will be detached.');"
                                          style="display:inline; margin:0;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:0.85rem; color:#dc2626;" title="Remove from this assessment">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:36px; color:var(--text-muted);">
                                No candidates assigned to this assessment yet.
                                <div style="margin-top:10px;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCandidatesModal')">
                                        + Add Candidates Now
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>


<?php if($activeTab === 'rules' && $isSuper): ?>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        
        <div class="card">
            <div class="card-header">
                <span class="card-title"><?php echo e($results['is_survey'] ? '📋 M&E Survey Configuration' : '📐 Scoring Rules'); ?></span>
                <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>" class="btn btn-outline btn-sm">Edit</a>
            </div>
            <div class="card-body">
                <?php if($assessment->rule): ?>
                    <table style="width:100%; font-size:0.9rem; border-collapse:collapse;">
                        <?php if($results['is_survey']): ?>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">M&amp;E Survey Stage</td>
                                <td style="padding:10px 0; font-weight:700; text-align:right;">
                                    <span class="badge badge-purple"><?php echo e(ucfirst($results['survey_stage'])); ?> Survey</span>
                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">Response Mode</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right;">
                                    <?php echo e($results['is_anonymous'] ? '🔒 Anonymous (No names recorded)' : '🧑‍🎓 Identified Responses'); ?>

                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">Pass / Fail Scoring</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right; color:var(--text-muted);">
                                    Disabled (M&amp;E Analytics Only)
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">Max Panelists</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right;">
                                    <?php echo e($assessment->rule->max_panelists ?? 'Unlimited'); ?>

                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">Score Cap</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right;">
                                    <?php echo e($assessment->rule->score_cap !== null ? number_format($assessment->rule->score_cap, 2) : 'None'); ?>

                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-secondary);">Passing Threshold</td>
                                <td style="padding:10px 0; font-weight:600; text-align:right;">
                                    <?php echo e($assessment->rule->passing_threshold !== null ? number_format($assessment->rule->passing_threshold, 2) : 'N/A'); ?>

                                </td>
                            </tr>
                            <?php if(!empty($assessment->rule->rules_json['number_of_panels'])): ?>
                                <tr>
                                    <td style="padding:10px 0; color:var(--text-secondary);">Number of Panels</td>
                                    <td style="padding:10px 0; font-weight:600; text-align:right;">
                                        <?php echo e($assessment->rule->rules_json['number_of_panels']); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </table>

                    <?php if(!empty($assessment->rule->rules_json) && count($assessment->rule->rules_json) > 0): ?>
                        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border);">
                            <div style="font-size:0.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Rules &amp; Parameters JSON</div>
                            <pre style="background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm); font-size:0.78rem; overflow-x:auto; border:1px solid var(--border);"><?php echo e(json_encode($assessment->rule->rules_json, JSON_PRETTY_PRINT)); ?></pre>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="color:var(--text-muted); font-size:0.9rem; padding:8px 0;">
                        No scoring rules configured.
                        <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>" style="color:var(--green-primary);">Add rules →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">
                <span class="card-title">ℹ️ Assessment Details</span>
            </div>
            <div class="card-body">
                <table style="width:100%; font-size:0.9rem; border-collapse:collapse;">
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Assessment ID</td>
                        <td style="padding:10px 0; font-weight:600; text-align:right; font-family:monospace;">#<?php echo e($assessment->id); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Type</td>
                        <td style="padding:10px 0; text-align:right;">
                            <span class="badge <?php echo e($typeBadgeClass); ?>"><?php echo e($assessment->type); ?></span>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Status</td>
                        <td style="padding:10px 0; text-align:right;">
                            <span class="badge <?php echo e($statusBadgeClass); ?>"><?php echo e($assessment->status); ?></span>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Survey Access Key</td>
                        <td style="padding:10px 0; font-weight:700; text-align:right; font-family:monospace; color:var(--green-primary);">
                            <?php echo e($assessment->access_key ?: 'None'); ?>

                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 0; color:var(--text-secondary);">Created</td>
                        <td style="padding:10px 0; text-align:right;"><?php echo e($assessment->created_at->format('M d, Y H:i')); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:var(--text-secondary);">Last Updated</td>
                        <td style="padding:10px 0; text-align:right;"><?php echo e($assessment->updated_at->format('M d, Y H:i')); ?></td>
                    </tr>
                </table>

                <div style="margin-top:20px; display:flex; flex-direction:column; gap:8px;">
                    <a href="<?php echo e(route('assessments.edit', $assessment->id)); ?>" class="btn btn-primary btn-full">
                        ⚙️ Edit Full Configuration
                    </a>
                    <form action="<?php echo e(route('assessments.destroy', $assessment->id)); ?>" method="POST"
                          onsubmit="return confirm('Permanently delete this assessment and all its data?');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger btn-full">
                            🗑️ Delete Assessment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<div id="importResultsModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('importResultsModal')"></div>
    <div class="pif-modal-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <h3 style="margin:0; font-size:1.2rem; display:flex; align-items:center; gap:8px;">
                📥 Import Results: <?php echo e($assessment->title); ?>

            </h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('importResultsModal')">✕</button>
        </div>

        <div style="margin-bottom:16px; font-size:0.85rem; color:var(--text-secondary); line-height:1.4;">
            Upload an Excel (.xlsx, .xls) or CSV spreadsheet containing evaluated candidate scores. Any new candidates will be created and assigned automatically.
        </div>

        <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:12px; margin-bottom:16px;">
            <div style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">Step 1: Download Customized Template</div>
            <div style="display:flex; gap:8px; align-items:center;">
                <a href="<?php echo e(route('assessments.template', ['assessment' => $assessment->id, 'format' => 'xlsx'])); ?>" class="btn btn-outline btn-sm" style="font-size:0.8rem;">
                    📥 Template (.xlsx)
                </a>
                <a href="<?php echo e(route('assessments.template', ['assessment' => $assessment->id, 'format' => 'csv'])); ?>" class="btn btn-ghost btn-sm" style="font-size:0.8rem;">
                    📥 Template (.csv)
                </a>
            </div>
        </div>

        <div style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">Step 2: Upload Completed Score Sheet</div>
        <form action="<?php echo e(route('assessments.import-results', $assessment->id)); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-group" style="margin-bottom:16px;">
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required style="padding:8px; font-size:0.85rem;">
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Max file size: 10MB.</small>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('importResultsModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#2563eb;">Upload &amp; Import</button>
            </div>
        </form>
    </div>
</div>


<div id="addCandidatesModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('addCandidatesModal')"></div>
    <div class="pif-modal-card" style="max-width:560px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <h3 style="margin:0; font-size:1.2rem; display:flex; align-items:center; gap:8px;">
                🧑‍🎓 Add Candidates to Assessment
            </h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('addCandidatesModal')">✕</button>
        </div>

        <?php
            $assignedCandIds = $assessment->candidates->pluck('id')->toArray();
        ?>

        <form action="<?php echo e(route('assessments.candidates.add', $assessment->id)); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label style="font-weight:700; font-size:0.85rem; color:var(--text-primary); margin:0;">
                        1. Select From Existing Candidates:
                    </label>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="selectAllModalCandidates(true)" class="btn btn-ghost btn-sm" style="font-size:0.75rem; padding:2px 6px;">Select All</button>
                        <button type="button" onclick="selectAllModalCandidates(false)" class="btn btn-ghost btn-sm" style="font-size:0.75rem; padding:2px 6px;">Clear</button>
                    </div>
                </div>

                <input type="text" id="modalCandidateSearch" onkeyup="filterModalCandidates()" placeholder="Search candidates..."
                       style="width:100%; padding:6px 10px; font-size:0.82rem; border:1px solid var(--border); border-radius:var(--radius-sm); margin-bottom:8px;">

                <div id="modalCandidatesList" style="max-height:180px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius-sm); padding:8px 12px; background:var(--surface-alt);">
                    <?php $__currentLoopData = $allCandidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isAlreadyAssigned = in_array($cand->id, $assignedCandIds);
                        ?>
                        <label class="modal-cand-item" style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:4px 0; margin:0; font-weight:normal; cursor:<?php echo e($isAlreadyAssigned ? 'default' : 'pointer'); ?>; opacity:<?php echo e($isAlreadyAssigned ? '0.6' : '1'); ?>;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="candidate_ids[]" value="<?php echo e($cand->id); ?>" class="modal-cand-cb" <?php echo e($isAlreadyAssigned ? 'disabled checked' : ''); ?> style="accent-color:var(--green-primary);">
                                <span class="cand-name-text"><?php echo e($cand->name); ?></span>
                            </div>
                            <span class="badge badge-gray" style="font-size:0.72rem;">
                                <?php echo e($isAlreadyAssigned ? 'Already Assigned' : 'Panel ' . ($cand->panel ?: 'A')); ?>

                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="assign_panel_name" style="font-weight:700; font-size:0.85rem;">Assign Panel to Selected Candidates</label>
                <select name="panel_name" id="assign_panel_name" class="form-control" style="font-size:0.85rem;">
                    <option value="">Keep Candidate's Default Panel</option>
                    <option value="A">Panel A</option>
                    <option value="B">Panel B</option>
                    <option value="cover">Cover / Observer</option>
                </select>
            </div>

            <div style="border-top:1px dashed #ddd; padding-top:12px; margin-bottom:16px;">
                <div style="font-weight:700; font-size:0.85rem; color:var(--text-primary); margin-bottom:8px;">
                    2. OR Quick Register a New Candidate:
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:8px;">
                    <input type="text" name="new_candidate_name" placeholder="Full name" class="form-control" style="font-size:0.82rem; height:34px;">
                    <select name="new_candidate_gender" class="form-control" style="font-size:0.82rem; height:34px;">
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                    </select>
                    <select name="new_candidate_panel" class="form-control" style="font-size:0.82rem; height:34px;">
                        <option value="A">Panel A</option>
                        <option value="B">Panel B</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCandidatesModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save &amp; Assign Candidates</button>
            </div>
        </form>
    </div>
</div>

<style>
.pif-modal {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pif-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
}
.pif-modal-card {
    position: relative;
    background: #ffffff;
    border-radius: var(--radius-lg);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    width: 92%;
    max-width: 500px;
    padding: 24px;
    z-index: 1051;
    animation: modalSlide 0.2s ease-out;
}
@keyframes modalSlide {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function selectAllModalCandidates(checked) {
    document.querySelectorAll('.modal-cand-cb:not(:disabled)').forEach(cb => {
        cb.checked = checked;
    });
}

function filterModalCandidates() {
    const query = (document.getElementById('modalCandidateSearch').value || '').toLowerCase();
    document.querySelectorAll('.modal-cand-item').forEach(item => {
        const text = item.querySelector('.cand-name-text')?.textContent?.toLowerCase() || '';
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/assessments/show.blade.php ENDPATH**/ ?>