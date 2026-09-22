<?php $__env->startSection('title', 'Candidates & Panelists Roster — Play It Forward'); ?>

<?php $__env->startSection('content'); ?>


<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" style="color:var(--text-secondary); text-decoration:none; font-size:0.85rem; display:inline-flex; align-items:center; gap:6px; margin-bottom:4px;">
            ← Back to Dashboard
        </a>
        <h1 style="margin:0; font-size:1.5rem; font-weight:800; display:flex; align-items:center; gap:8px;">
            👥 Candidates & Panelists Roster
        </h1>
        <p style="margin:2px 0 0; color:var(--text-secondary); font-size:0.85rem;">
            Manage interview candidates, panelist assignments, and import results directly from Excel.
        </p>
    </div>

    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button type="button" class="btn btn-outline btn-sm" onclick="openModal('addCandidateModal')">
            + Add Candidate
        </button>
        <button type="button" class="btn btn-outline btn-sm" onclick="openModal('addPanelistModal')">
            + Add Panelist
        </button>
        <a href="<?php echo e(route('admin.roster.index', ['tab' => 'import'])); ?>" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
            📥 Import from Excel
        </a>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="margin-bottom:16px;">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error" style="margin-bottom:16px;">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>


<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:20px;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; border-left:4px solid var(--green-primary);">
        <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase; font-weight:700;">Total Candidates</div>
        <div style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-top:2px;"><?php echo e($totalCandidates); ?></div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; border-left:4px solid #6C63FF;">
        <div style="font-size:0.75rem; color:#6C63FF; text-transform:uppercase; font-weight:700;">Panel A Candidates</div>
        <div style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-top:2px;"><?php echo e($panelACount); ?></div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; border-left:4px solid #1A7F4F;">
        <div style="font-size:0.75rem; color:#1A7F4F; text-transform:uppercase; font-weight:700;">Panel B Candidates</div>
        <div style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-top:2px;"><?php echo e($panelBCount); ?></div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px; border-left:4px solid #2563EB;">
        <div style="font-size:0.75rem; color:#2563EB; text-transform:uppercase; font-weight:700;">Total Panelists & Evaluators</div>
        <div style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-top:2px;"><?php echo e($totalPanelists); ?></div>
    </div>
</div>


<div class="tabs" style="margin-bottom:16px;">
    <a href="<?php echo e(route('admin.roster.index', ['tab' => 'candidates'])); ?>" class="tab <?php echo e($activeTab === 'candidates' ? 'active' : ''); ?>">
        🧑‍🎓 Candidates (<?php echo e($totalCandidates); ?>)
    </a>
    <a href="<?php echo e(route('admin.roster.index', ['tab' => 'panelists'])); ?>" class="tab <?php echo e($activeTab === 'panelists' ? 'active' : ''); ?>">
        👥 Panelists (<?php echo e($totalPanelists); ?>)
    </a>
    <a href="<?php echo e(route('admin.roster.index', ['tab' => 'import'])); ?>" class="tab <?php echo e($activeTab === 'import' ? 'active' : ''); ?>">
        📊 Import Results from Excel
    </a>
</div>




<?php if($activeTab === 'candidates'): ?>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; margin-bottom:20px;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
            <form method="GET" action="<?php echo e(route('admin.roster.index')); ?>" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="tab" value="candidates">
                <input type="text" name="q_candidate" value="<?php echo e(request('q_candidate')); ?>" placeholder="Search candidate name..."
                       style="padding:6px 12px; font-size:0.85rem; border:1px solid var(--border); border-radius:var(--radius-sm); min-width:200px;">

                <select name="panel_candidate" onchange="this.form.submit()" style="padding:6px 10px; font-size:0.85rem; border:1px solid var(--border); border-radius:var(--radius-sm);">
                    <option value="">All Panels</option>
                    <option value="A" <?php echo e(request('panel_candidate') === 'A' ? 'selected' : ''); ?>>Panel A</option>
                    <option value="B" <?php echo e(request('panel_candidate') === 'B' ? 'selected' : ''); ?>>Panel B</option>
                    <option value="unassigned" <?php echo e(request('panel_candidate') === 'unassigned' ? 'selected' : ''); ?>>Unassigned</option>
                </select>

                <select name="gender_candidate" onchange="this.form.submit()" style="padding:6px 10px; font-size:0.85rem; border:1px solid var(--border); border-radius:var(--radius-sm);">
                    <option value="">All Genders</option>
                    <option value="Female" <?php echo e(request('gender_candidate') === 'Female' ? 'selected' : ''); ?>>Female</option>
                    <option value="Male" <?php echo e(request('gender_candidate') === 'Male' ? 'selected' : ''); ?>>Male</option>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if(request('q_candidate') || request('panel_candidate') || request('gender_candidate')): ?>
                    <a href="<?php echo e(route('admin.roster.index', ['tab' => 'candidates'])); ?>" class="btn btn-ghost btn-sm">Clear</a>
                <?php endif; ?>
            </form>

            <div style="display:flex; gap:8px;">
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCandidateModal')">
                    + Add New Candidate
                </button>
                <a href="<?php echo e(route('admin.imports.template', ['type' => 'candidates'])); ?>" class="btn btn-outline btn-sm">
                    📥 Template (.xlsx)
                </a>
            </div>
        </div>

        
        <?php if($candidates->isEmpty()): ?>
            <div style="padding:32px; text-align:center; color:var(--text-muted);">
                <div style="font-size:2rem; margin-bottom:6px;">🧑‍🎓</div>
                <p style="margin:0 0 12px; font-size:0.9rem;">No candidates found matching your query.</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCandidateModal')">Add First Candidate</button>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="data-table" style="width:100%; font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th style="padding:8px 12px; width:50px;">#</th>
                            <th style="padding:8px 12px;">Candidate Name</th>
                            <th style="padding:8px 12px; text-align:center;">Gender</th>
                            <th style="padding:8px 12px; text-align:center;">Assigned Panel</th>
                            <th style="padding:8px 12px; text-align:center;">Literacy Score</th>
                            <th style="padding:8px 12px; text-align:center;">Panel Scores</th>
                            <th style="padding:8px 12px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $cand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="padding:8px 12px; color:var(--text-muted);"><?php echo e($idx + 1); ?></td>
                                <td style="padding:8px 12px; font-weight:600; color:var(--text-primary);">
                                    <?php echo e($cand->name); ?>

                                </td>
                                <td style="padding:8px 12px; text-align:center;">
                                    <?php if($cand->gender === 'Female'): ?>
                                        <span class="badge badge-pink" style="font-size:0.75rem; padding:2px 8px;">👩 Female</span>
                                    <?php else: ?>
                                        <span class="badge badge-blue" style="font-size:0.75rem; padding:2px 8px;">👨 Male</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:8px 12px; text-align:center;">
                                    <?php if($cand->panel === 'A'): ?>
                                        <span class="badge badge-purple" style="font-size:0.75rem; padding:2px 8px;">Panel A</span>
                                    <?php elseif($cand->panel === 'B'): ?>
                                        <span class="badge badge-green" style="font-size:0.75rem; padding:2px 8px;">Panel B</span>
                                    <?php elseif($cand->panel === 'cover'): ?>
                                        <span class="badge badge-gray" style="font-size:0.75rem; padding:2px 8px;">Cover</span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-style:italic;">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:8px 12px; text-align:center;">
                                    <?php if($cand->literacyScore): ?>
                                        <span style="font-weight:700; color:var(--green-dark);">
                                            <?php echo e($cand->literacyScore->total_score); ?> / 20
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:8px 12px; text-align:center;">
                                    <?php if($cand->panel_scores_count > 0): ?>
                                        <span class="badge badge-teal" style="font-size:0.75rem; padding:2px 8px;">
                                            <?php echo e($cand->panel_scores_count); ?> evaluation<?php echo e($cand->panel_scores_count > 1 ? 's' : ''); ?>

                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">0 evaluations</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:8px 12px; text-align:right;">
                                    <div style="display:inline-flex; gap:6px;">
                                        <button type="button" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:0.8rem;"
                                                onclick="editCandidate(<?php echo e(json_encode($cand)); ?>)">
                                            ✏️ Edit
                                        </button>
                                        <form action="<?php echo e(route('admin.roster.candidates.destroy', $cand->id)); ?>" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete <?php echo e(addslashes($cand->name)); ?>? All linked evaluation scores will also be removed.');"
                                              style="display:inline; margin:0;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:0.8rem; color:#dc2626;">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>




<?php if($activeTab === 'panelists'): ?>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; margin-bottom:20px;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
            <form method="GET" action="<?php echo e(route('admin.roster.index')); ?>" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="tab" value="panelists">
                <input type="text" name="q_panelist" value="<?php echo e(request('q_panelist')); ?>" placeholder="Search panelist name or email..."
                       style="padding:6px 12px; font-size:0.85rem; border:1px solid var(--border); border-radius:var(--radius-sm); min-width:240px;">

                <select name="panel_panelist" onchange="this.form.submit()" style="padding:6px 10px; font-size:0.85rem; border:1px solid var(--border); border-radius:var(--radius-sm);">
                    <option value="">All Panels</option>
                    <option value="A" <?php echo e(request('panel_panelist') === 'A' ? 'selected' : ''); ?>>Panel A</option>
                    <option value="B" <?php echo e(request('panel_panelist') === 'B' ? 'selected' : ''); ?>>Panel B</option>
                    <option value="cover" <?php echo e(request('panel_panelist') === 'cover' ? 'selected' : ''); ?>>Cover / Observer</option>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if(request('q_panelist') || request('panel_panelist')): ?>
                    <a href="<?php echo e(route('admin.roster.index', ['tab' => 'panelists'])); ?>" class="btn btn-ghost btn-sm">Clear</a>
                <?php endif; ?>
            </form>

            <div style="display:flex; gap:8px;">
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addPanelistModal')">
                    + Add New Panelist
                </button>
                <a href="<?php echo e(route('admin.imports.template', ['type' => 'panelists'])); ?>" class="btn btn-outline btn-sm">
                    📥 Template (.xlsx)
                </a>
            </div>
        </div>

        
        <div style="overflow-x:auto;">
            <table class="data-table" style="width:100%; font-size:0.85rem;">
                <thead>
                    <tr>
                        <th style="padding:8px 12px; width:50px;">#</th>
                        <th style="padding:8px 12px;">Panelist Name</th>
                        <th style="padding:8px 12px;">Email Address</th>
                        <th style="padding:8px 12px; text-align:center;">Assigned Panel</th>
                        <th style="padding:8px 12px; text-align:center;">Role</th>
                        <th style="padding:8px 12px; text-align:center;">Interviews Scored</th>
                        <th style="padding:8px 12px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $panelists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td style="padding:8px 12px; color:var(--text-muted);"><?php echo e($idx + 1); ?></td>
                            <td style="padding:8px 12px; font-weight:600; color:var(--text-primary);">
                                <?php echo e($p->panelist_name ?: $p->name); ?>

                            </td>
                            <td style="padding:8px 12px; color:var(--text-secondary); font-family:monospace; font-size:0.82rem;">
                                <?php echo e($p->email); ?>

                            </td>
                            <td style="padding:8px 12px; text-align:center;">
                                <?php if($p->panel === 'A'): ?>
                                    <span class="badge badge-purple" style="font-size:0.75rem; padding:2px 8px;">Panel A</span>
                                <?php elseif($p->panel === 'B'): ?>
                                    <span class="badge badge-green" style="font-size:0.75rem; padding:2px 8px;">Panel B</span>
                                <?php elseif($p->panel === 'cover'): ?>
                                    <span class="badge badge-gray" style="font-size:0.75rem; padding:2px 8px;">Cover</span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-style:italic;">All / Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:8px 12px; text-align:center;">
                                <?php if($p->isSuper()): ?>
                                    <span class="badge badge-green" style="font-size:0.75rem; padding:2px 8px;">Super Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-blue" style="font-size:0.75rem; padding:2px 8px;">Panelist</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:8px 12px; text-align:center;">
                                <span class="badge badge-teal" style="font-size:0.75rem; padding:2px 8px;">
                                    <?php echo e($p->panel_scores_count); ?> scored
                                </span>
                            </td>
                            <td style="padding:8px 12px; text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button type="button" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:0.8rem;"
                                            onclick="editPanelist(<?php echo e(json_encode($p)); ?>)">
                                        ✏️ Edit
                                    </button>
                                    <?php if($p->id !== auth()->id() && $p->id !== session('admin_user_id')): ?>
                                        <form action="<?php echo e(route('admin.roster.panelists.destroy', $p->id)); ?>" method="POST"
                                              onsubmit="return confirm('Are you sure you want to remove panelist <?php echo e(addslashes($p->name)); ?>?');"
                                              style="display:inline; margin:0;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:0.8rem; color:#dc2626;">
                                                🗑️
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>




<?php if($activeTab === 'import'): ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; margin-bottom:24px;">

        
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:20px; display:flex; flex-direction:column; justify-content:space-between; border-top:4px solid #2563EB;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
                        🎙️ Interview Panel Scores
                    </h3>
                    <span class="badge badge-blue" style="font-size:0.75rem;">Panel Scores</span>
                </div>
                <p style="font-size:0.83rem; color:var(--text-secondary); line-height:1.4; margin-bottom:14px;">
                    Upload interview score sheets containing panelist evaluations across Motivation, Availability, Resilience, and Communication (1-5).
                </p>

                <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:10px 12px; margin-bottom:14px; font-size:0.78rem; color:var(--text-secondary);">
                    <strong>Required Columns:</strong> Panelist Name, Candidate Name, Motivation, Availability, Resilience, Communication, Comments (optional).
                </div>
            </div>

            <div>
                <form action="<?php echo e(route('admin.imports.interview-scores')); ?>" method="POST" enctype="multipart/form-data" style="margin-bottom:10px;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="redirect_to" value="roster">
                    <div style="margin-bottom:10px;">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               style="font-size:0.82rem; width:100%; padding:6px; border:1px dashed var(--border); border-radius:var(--radius-sm); background:#fff;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full" style="padding:8px 14px;">
                        ⬆️ Upload &amp; Import Interview Scores
                    </button>
                </form>

                <div style="text-align:center;">
                    <a href="<?php echo e(route('admin.imports.template', ['type' => 'interview-scores'])); ?>" style="font-size:0.8rem; color:var(--green-dark); text-decoration:none;">
                        📥 Download Sample Template (.xlsx)
                    </a>
                </div>
            </div>
        </div>

        
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:20px; display:flex; flex-direction:column; justify-content:space-between; border-top:4px solid var(--green-primary);">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
                        💻 Literacy Assessment Scores
                    </h3>
                    <span class="badge badge-green" style="font-size:0.75rem;">Digital Literacy</span>
                </div>
                <p style="font-size:0.83rem; color:var(--text-secondary); line-height:1.4; margin-bottom:14px;">
                    Upload digital literacy scores for candidates (Total Score /20 or individual Task 1 to Task 10 scores).
                </p>

                <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:10px 12px; margin-bottom:14px; font-size:0.78rem; color:var(--text-secondary);">
                    <strong>Required Columns:</strong> Candidate Name, Assessment Date, Total Score (0-20) or Task1..Task10 (0-2).
                </div>
            </div>

            <div>
                <form action="<?php echo e(route('admin.imports.literacy-scores')); ?>" method="POST" enctype="multipart/form-data" style="margin-bottom:10px;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="redirect_to" value="roster">
                    <div style="margin-bottom:10px;">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               style="font-size:0.82rem; width:100%; padding:6px; border:1px dashed var(--border); border-radius:var(--radius-sm); background:#fff;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full" style="padding:8px 14px;">
                        ⬆️ Upload &amp; Import Literacy Scores
                    </button>
                </form>

                <div style="text-align:center;">
                    <a href="<?php echo e(route('admin.imports.template', ['type' => 'literacy-scores'])); ?>" style="font-size:0.8rem; color:var(--green-dark); text-decoration:none;">
                        📥 Download Sample Template (.xlsx)
                    </a>
                </div>
            </div>
        </div>

        
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:20px; display:flex; flex-direction:column; justify-content:space-between; border-top:4px solid #6C63FF;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
                        🧑‍🎓 Bulk Candidate Roster
                    </h3>
                    <span class="badge badge-purple" style="font-size:0.75rem;">Candidates</span>
                </div>
                <p style="font-size:0.83rem; color:var(--text-secondary); line-height:1.4; margin-bottom:14px;">
                    Add or update multiple candidates at once. Easily assign candidates to Panel A, Panel B, or gender tracks.
                </p>

                <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:10px 12px; margin-bottom:14px; font-size:0.78rem; color:var(--text-secondary);">
                    <strong>Required Columns:</strong> Candidate Name, Gender (Female/Male), Panel (Panel A / Panel B).
                </div>
            </div>

            <div>
                <form action="<?php echo e(route('admin.imports.candidates')); ?>" method="POST" enctype="multipart/form-data" style="margin-bottom:10px;">
                    <?php echo csrf_field(); ?>
                    <div style="margin-bottom:10px;">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               style="font-size:0.82rem; width:100%; padding:6px; border:1px dashed var(--border); border-radius:var(--radius-sm); background:#fff;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full" style="padding:8px 14px;">
                        ⬆️ Upload &amp; Import Candidates
                    </button>
                </form>

                <div style="text-align:center;">
                    <a href="<?php echo e(route('admin.imports.template', ['type' => 'candidates'])); ?>" style="font-size:0.8rem; color:var(--green-dark); text-decoration:none;">
                        📥 Download Sample Template (.xlsx)
                    </a>
                </div>
            </div>
        </div>

        
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:20px; display:flex; flex-direction:column; justify-content:space-between; border-top:4px solid #7c3aed;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
                        👥 Bulk Panelist Accounts
                    </h3>
                    <span class="badge badge-purple" style="font-size:0.75rem;">Evaluators</span>
                </div>
                <p style="font-size:0.83rem; color:var(--text-secondary); line-height:1.4; margin-bottom:14px;">
                    Batch create panelist login accounts, set panel assignment (A/B/Cover), and initialize temporary passwords.
                </p>

                <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:10px 12px; margin-bottom:14px; font-size:0.78rem; color:var(--text-secondary);">
                    <strong>Required Columns:</strong> Panelist Name, Email, Panel, Role, Password.
                </div>
            </div>

            <div>
                <form action="<?php echo e(route('admin.imports.panelists')); ?>" method="POST" enctype="multipart/form-data" style="margin-bottom:10px;">
                    <?php echo csrf_field(); ?>
                    <div style="margin-bottom:10px;">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               style="font-size:0.82rem; width:100%; padding:6px; border:1px dashed var(--border); border-radius:var(--radius-sm); background:#fff;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full" style="padding:8px 14px;">
                        ⬆️ Upload &amp; Import Panelists
                    </button>
                </form>

        
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:20px; display:flex; flex-direction:column; justify-content:space-between; border-top:4px solid #10B981;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
                        📋 Dynamic Assessments &amp; Interviews
                    </h3>
                    <span class="badge badge-green" style="font-size:0.75rem;">Dynamic Modules</span>
                </div>
                <p style="font-size:0.83rem; color:var(--text-secondary); line-height:1.4; margin-bottom:14px;">
                    Import custom rubric questions, scale ratings, and text responses for any created Assessment, Structured Interview, or Survey.
                </p>

                <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:10px 12px; margin-bottom:14px; font-size:0.78rem; color:var(--text-secondary);">
                    <strong>How it works:</strong> Select target assessment below. Download tailored template with actual rubric columns, fill scores, and upload.
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:0.8rem; font-weight:700; display:block; margin-bottom:4px;">Select Assessment / Module:</label>
                    <select id="dynamicAssessmentSelect" class="form-control" style="font-size:0.85rem;" onchange="updateDynamicAssessmentActions(this.value)">
                        <?php if($assessments->isEmpty()): ?>
                            <option value="">No assessments created yet</option>
                        <?php else: ?>
                            <?php $__currentLoopData = $assessments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($asm->id); ?>" data-type="<?php echo e($asm->type); ?>">
                                    [<?php echo e(ucfirst($asm->type)); ?>] <?php echo e($asm->title); ?> (<?php echo e($asm->questions_count); ?> Qs, <?php echo e($asm->candidates_count); ?> cands)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div>
                <form id="dynamicAssessmentForm" method="POST" enctype="multipart/form-data" style="margin-bottom:10px;">
                    <?php echo csrf_field(); ?>
                    <div style="margin-bottom:10px;">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               style="font-size:0.82rem; width:100%; padding:6px; border:1px dashed var(--border); border-radius:var(--radius-sm); background:#fff;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full" style="padding:8px 14px;">
                        ⬆️ Upload &amp; Import Module Results
                    </button>
                </form>

                <div style="display:flex; justify-content:center; gap:12px; font-size:0.8rem;">
                    <a id="dynamicTemplateXlsx" href="#" style="color:var(--green-dark); text-decoration:none;">
                        📥 Template (.xlsx)
                    </a>
                    <span style="color:var(--border);">|</span>
                    <a id="dynamicTemplateCsv" href="#" style="color:var(--green-dark); text-decoration:none;">
                        📥 Template (.csv)
                    </a>
                </div>
            </div>
        </div>

        
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-md); padding:20px; display:flex; flex-direction:column; justify-content:space-between; border-top:4px solid #EC4899;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
                        📝 Survey Responses (M&amp;E)
                    </h3>
                    <span class="badge badge-pink" style="font-size:0.75rem;">M&amp;E Surveys</span>
                </div>
                <p style="font-size:0.83rem; color:var(--text-secondary); line-height:1.4; margin-bottom:14px;">
                    Upload quantitative Likert scores (Q1-Q11, 1-5) and qualitative responses (Qual1-Qual4) for Baseline and Endline surveys.
                </p>

                <div style="background:var(--surface-alt); border-radius:var(--radius-sm); padding:10px 12px; margin-bottom:14px; font-size:0.78rem; color:var(--text-secondary);">
                    <strong>Required Columns:</strong> Candidate Name, Survey Type (Baseline/Endline), Gender, Q1..Q11 (1-5), Qual1..Qual4.
                </div>
            </div>

            <div>
                <form action="<?php echo e(route('admin.imports.survey-responses')); ?>" method="POST" enctype="multipart/form-data" style="margin-bottom:10px;">
                    <?php echo csrf_field(); ?>
                    <div style="margin-bottom:10px;">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               style="font-size:0.82rem; width:100%; padding:6px; border:1px dashed var(--border); border-radius:var(--radius-sm); background:#fff;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full" style="padding:8px 14px;">
                        ⬆️ Upload &amp; Import Survey Responses
                    </button>
                </form>

                <div style="display:flex; justify-content:center; gap:12px; font-size:0.8rem;">
                    <a href="<?php echo e(route('admin.imports.template', ['type' => 'survey-responses', 'format' => 'xlsx'])); ?>" style="color:var(--green-dark); text-decoration:none;">
                        📥 Template (.xlsx)
                    </a>
                    <span style="color:var(--border);">|</span>
                    <a href="<?php echo e(route('admin.imports.template', ['type' => 'survey-responses', 'format' => 'csv'])); ?>" style="color:var(--green-dark); text-decoration:none;">
                        📥 Template (.csv)
                    </a>
                </div>
            </div>
        </div>

    </div>
<?php endif; ?>






<div id="addCandidateModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('addCandidateModal')"></div>
    <div class="pif-modal-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0; font-size:1.2rem;">+ Add New Candidate</h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('addCandidateModal')">✕</button>
        </div>

        <form action="<?php echo e(route('admin.roster.candidates.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="cand_name" style="font-weight:600; font-size:0.85rem;">Full Name *</label>
                <input type="text" name="name" id="cand_name" class="form-control" placeholder="e.g. Mary Tembo" required>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="cand_gender" style="font-weight:600; font-size:0.85rem;">Gender Track *</label>
                <select name="gender" id="cand_gender" class="form-control" required>
                    <option value="Female">Female (Top 7 Advance Track)</option>
                    <option value="Male">Male (Top 3 Advance Track)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:18px;">
                <label for="cand_panel" style="font-weight:600; font-size:0.85rem;">Assigned Panel</label>
                <select name="panel" id="cand_panel" class="form-control">
                    <option value="A">Panel A</option>
                    <option value="B">Panel B</option>
                    <option value="cover">Cover / Observer</option>
                    <option value="">Unassigned</option>
                </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCandidateModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Candidate</button>
            </div>
        </form>
    </div>
</div>


<div id="editCandidateModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('editCandidateModal')"></div>
    <div class="pif-modal-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0; font-size:1.2rem;">✏️ Edit Candidate</h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('editCandidateModal')">✕</button>
        </div>

        <form id="editCandidateForm" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit_cand_name" style="font-weight:600; font-size:0.85rem;">Full Name *</label>
                <input type="text" name="name" id="edit_cand_name" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit_cand_gender" style="font-weight:600; font-size:0.85rem;">Gender Track *</label>
                <select name="gender" id="edit_cand_gender" class="form-control" required>
                    <option value="Female">Female (Top 7 Advance Track)</option>
                    <option value="Male">Male (Top 3 Advance Track)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:18px;">
                <label for="edit_cand_panel" style="font-weight:600; font-size:0.85rem;">Assigned Panel</label>
                <select name="panel" id="edit_cand_panel" class="form-control">
                    <option value="A">Panel A</option>
                    <option value="B">Panel B</option>
                    <option value="cover">Cover / Observer</option>
                    <option value="">Unassigned</option>
                </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCandidateModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Candidate</button>
            </div>
        </form>
    </div>
</div>


<div id="addPanelistModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('addPanelistModal')"></div>
    <div class="pif-modal-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0; font-size:1.2rem;">+ Add New Panelist</h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('addPanelistModal')">✕</button>
        </div>

        <form action="<?php echo e(route('admin.roster.panelists.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="p_name" style="font-weight:600; font-size:0.85rem;">Panelist Name *</label>
                <input type="text" name="name" id="p_name" class="form-control" placeholder="e.g. Sarah Banda" required>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="p_email" style="font-weight:600; font-size:0.85rem;">Email Address *</label>
                <input type="email" name="email" id="p_email" class="form-control" placeholder="e.g. sarah@pif.zm" required>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="p_panel" style="font-weight:600; font-size:0.85rem;">Panel Assignment</label>
                <select name="panel" id="p_panel" class="form-control">
                    <option value="A">Panel A</option>
                    <option value="B">Panel B</option>
                    <option value="cover">Cover / Observer</option>
                    <option value="">All / Unassigned</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="p_role" style="font-weight:600; font-size:0.85rem;">Role</label>
                <select name="role" id="p_role" class="form-control">
                    <option value="panelist">Panelist (Evaluator)</option>
                    <option value="super">Super Administrator</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:18px;">
                <label for="p_password" style="font-weight:600; font-size:0.85rem;">Password *</label>
                <input type="password" name="password" id="p_password" class="form-control" placeholder="Min 6 characters" required>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPanelistModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Panelist</button>
            </div>
        </form>
    </div>
</div>


<div id="editPanelistModal" class="pif-modal" style="display:none;">
    <div class="pif-modal-backdrop" onclick="closeModal('editPanelistModal')"></div>
    <div class="pif-modal-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0; font-size:1.2rem;">✏️ Edit Panelist</h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('editPanelistModal')">✕</button>
        </div>

        <form id="editPanelistForm" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit_p_name" style="font-weight:600; font-size:0.85rem;">Panelist Name *</label>
                <input type="text" name="name" id="edit_p_name" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit_p_email" style="font-weight:600; font-size:0.85rem;">Email Address *</label>
                <input type="email" name="email" id="edit_p_email" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit_p_panel" style="font-weight:600; font-size:0.85rem;">Panel Assignment</label>
                <select name="panel" id="edit_p_panel" class="form-control">
                    <option value="A">Panel A</option>
                    <option value="B">Panel B</option>
                    <option value="cover">Cover / Observer</option>
                    <option value="">All / Unassigned</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit_p_role" style="font-weight:600; font-size:0.85rem;">Role</label>
                <select name="role" id="edit_p_role" class="form-control">
                    <option value="panelist">Panelist (Evaluator)</option>
                    <option value="super">Super Administrator</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:18px;">
                <label for="edit_p_password" style="font-weight:600; font-size:0.85rem;">Change Password (leave blank to keep current)</label>
                <input type="password" name="password" id="edit_p_password" class="form-control" placeholder="New password">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editPanelistModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Panelist</button>
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
    max-width: 480px;
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

function editCandidate(cand) {
    const form = document.getElementById('editCandidateForm');
    form.action = `/admin/roster/candidates/${cand.id}`;
    document.getElementById('edit_cand_name').value = cand.name;
    document.getElementById('edit_cand_gender').value = cand.gender || 'Female';
    document.getElementById('edit_cand_panel').value = cand.panel || '';
    openModal('editCandidateModal');
}

function editPanelist(panelist) {
    const form = document.getElementById('editPanelistForm');
    form.action = `/admin/roster/panelists/${panelist.id}`;
    document.getElementById('edit_p_name').value = panelist.panelist_name || panelist.name;
    document.getElementById('edit_p_email').value = panelist.email;
    document.getElementById('edit_p_panel').value = panelist.panel || '';
    document.getElementById('edit_p_role').value = panelist.role || 'panelist';
    document.getElementById('edit_p_password').value = '';
    openModal('editPanelistModal');
}

function updateDynamicAssessmentActions(id) {
    const form = document.getElementById('dynamicAssessmentForm');
    const xlsxLink = document.getElementById('dynamicTemplateXlsx');
    const csvLink = document.getElementById('dynamicTemplateCsv');
    if (!id || !form) return;

    form.action = `/assessments/${id}/import-results`;
    if (xlsxLink) xlsxLink.href = `/assessments/${id}/template?format=xlsx`;
    if (csvLink) csvLink.href = `/assessments/${id}/template?format=csv`;
}

document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('dynamicAssessmentSelect');
    if (sel && sel.value) {
        updateDynamicAssessmentActions(sel.value);
    }
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/admin/roster/index.blade.php ENDPATH**/ ?>