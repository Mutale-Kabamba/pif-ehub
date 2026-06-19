<h3>&#x1F3C6; Live Candidate Selection Leaderboard</h3>

<div class="info-box" style="margin-bottom: 20px;">
    Aggregates practical literacy metrics (Assessor out of 20) and Panel interview scores
    (Averaged across active logins out of 20) for a total out of 40.
</div>

<?php if(isset($leaderboard) && count($leaderboard) > 0): ?>
    <a href="?tab=leaderboard&amp;format=csv" class="btn btn-primary export-btn">
        &#x1F4E5; Export CSV
    </a>

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
                <?php $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="<?php echo e($entry['rank'] <= 10 ? 'accepted' : ''); ?>">
                        <td><strong>#<?php echo e($entry['rank']); ?></strong></td>
                        <td><?php echo e($entry['candidate_name']); ?></td>
                        <td><strong><?php echo e(number_format($entry['grand_total'], 1)); ?></strong></td>
                        <td><?php echo e(number_format($entry['literacy_score'], 1)); ?></td>
                        <td><?php echo e(number_format($entry['interview_score'], 1)); ?></td>
                        <td><?php echo e($entry['panelists_scored']); ?></td>
                        <td>
                            <?php if($entry['rank'] <= 10): ?>
                                <span style="color: #2e7d32;">&#x1F7E2; ACCEPTED</span>
                            <?php else: ?>
                                <span style="color: #C62828;">&#x1F534; WAITLIST / CUT-OFF</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($entry['assessment_date'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="info-box info" style="margin-top: 20px;">
        Leaderboard is functional, but no candidate evaluation matrices have been recorded yet.
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/admin/partials/leaderboard.blade.php ENDPATH**/ ?>