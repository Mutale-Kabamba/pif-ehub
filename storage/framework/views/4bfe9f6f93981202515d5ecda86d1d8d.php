<?php $__env->startSection('title', 'Admin Login'); ?>

<?php $__env->startSection('content'); ?>
<div class="login-container">
    <div class="login-box">
        <h2 style="text-align: center; margin-bottom: 8px; color: #1a1a1a;">Restricted Administration Portal</h2>

        <div class="info-box warning" style="margin-bottom: 24px;">
            Please choose your system entity assignment to authenticate access.
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-error">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 16px;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.login.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="role">Select Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="">-- Select System Entity --</option>
                    <option value="Super User">Mutale</option>
                    <option value="Blessing">Blessing (Panel A)</option>
                    <option value="Sarah">Sarah (Panel A)</option>
                    <option value="Bracious">Bracious (Panel A)</option>
                    <option value="Jacqueline">Jacqueline (Panel B)</option>
                    <option value="Florence">Florence (Panel B)</option>
                    <option value="Mwiinga">Mwiinga (Cover)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="Enter your authentication password" required>
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary btn-full">
                    Unlock Dashboard Session
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mukuk\Documents\GitHub\pif-ehub\resources\views/admin/login.blade.php ENDPATH**/ ?>