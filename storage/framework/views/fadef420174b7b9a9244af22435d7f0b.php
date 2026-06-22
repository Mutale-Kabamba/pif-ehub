<h3>🏆 Live Gender-Segmented Selection Leaderboard</h3>

<div class="info-box" style="margin-bottom: 20px;">
    Separated Selection Tracks: <strong>Top 7 Females</strong> and <strong>Top 3 Males</strong> are dynamically highlighted as ACCEPTED based on cumulative assessment scores (Literacy metrics out of 20 and average Panel values out of 20).
</div>

<?php if(isset($leaderboard) && ($leaderboard['females']->count() > 0 || $leaderboard['males']->count() > 0)): ?>
    <a href="?tab=leaderboard&amp;format=csv" class="btn btn-primary export-btn" style="margin-bottom: 20px; display: inline-block;">
        📥 Export Separated Leaderboards CSV
    </a>

    <div style="margin-bottom: 40px;">
        <h4 style="color: #d81b60; margin-bottom: 12px; font-weight: 600;">👩‍🦰 Female Selection Track (Top 7 Advance)</h4>
        <?php if($leaderboard['females']->count() > 0): ?>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate Name</th>
                            <th>Grand Total Score (/40)</th>
                            <th>Literacy Score (/20)</th>
                            <th>Interview Score (/20)</th>
                            <th>Panelists Scored</th>
                            <th>Admission Status</th>
                            <th>Assessment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $leaderboard['females']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="<?php echo e($entry['status'] === 'ACCEPTED' ? 'accepted' : ''); ?>">
                                <td><strong>#<?php echo e($entry['rank']); ?></strong></td>
                                <td><?php echo e($entry['candidate_name']); ?></td>
                                <td><strong><?php echo e(number_format($entry['grand_total'], 1)); ?></strong></td>
                                <td><?php echo e(number_format($entry['literacy_score'], 1)); ?></td>
                                <td><?php echo e(number_format($entry['interview_score'], 1)); ?></td>
                                <td><?php echo e($entry['panelists_scored']); ?></td>
                                <td>
                                    <?php if($entry['status'] === 'ACCEPTED'): ?>
                                        <span style="color: #2e7d32; font-weight: bold;">🟢 ACCEPTED (Top 7)</span>
                                    <?php else: ?>
                                        <span style="color: #C62828;">🔴 WAITLIST</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($entry['assessment_date'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="info-box info">No female candidate scores found yet.</div>
        <?php endif; ?>
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="color: #1565c0; margin-bottom: 12px; font-weight: 600;">👨 Male Selection Track (Top 3 Advance)</h4>
        <?php if($leaderboard['males']->count() > 0): ?>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate Name</th>
                            <th>Grand Total Score (/40)</th>
                            <th>Literacy Score (/20)</th>
                            <th>Interview Score (/20)</th>
                            <th>Panelists Scored</th>
                            <th>Admission Status</th>
                            <th>Assessment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $leaderboard['males']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="<?php echo e($entry['status'] === 'ACCEPTED' ? 'accepted' : ''); ?>">
                                <td><strong>#<?php echo e($entry['rank']); ?></strong></td>
                                <td><?php echo e($entry['candidate_name']); ?></td>
                                <td><strong><?php echo e(number_format($entry['grand_total'], 1)); ?></strong></td>
                                <td><?php echo e(number_format($entry['literacy_score'], 1)); ?></td>
                                <td><?php echo e(number_format($entry['interview_score'], 1)); ?></td>
                                <td><?php echo e($entry['panelists_scored']); ?></td>
                                <td>
                                    <?php if($entry['status'] === 'ACCEPTED'): ?>
                                        <span style="color: #2e7d32; font-weight: bold;">🟢 ACCEPTED (Top 3)</span>
                                    <?php else: ?>
                                        <span style="color: #C62828;">🔴 WAITLIST</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($entry['assessment_date'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="info-box info">No male candidate scores found yet.</div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="info-box info" style="margin-top: 20px;">
        Leaderboard is functional, but no candidate evaluation matrices have been recorded yet.
    </div>
<?php endif; ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/admin/partials/leaderboard.blade.php ENDPATH**/ ?>