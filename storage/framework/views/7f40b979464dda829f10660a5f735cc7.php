<?php $__env->startSection('title', 'Đặt lại mật khẩu'); ?>
<?php $__env->startSection('content'); ?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3>Đặt lại mật khẩu</h3>
        <form method="POST" action="<?php echo e(route('password.update')); ?>"><?php echo csrf_field(); ?>
            <input type="hidden" name="token" value="<?php echo e($token); ?>">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="<?php echo e(old('email', $email)); ?>" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu mới</label>
                <input type="password" name="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-text">8–64 ký tự, có chữ hoa, chữ thường, số và ký tự đặc biệt.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Nhập lại mật khẩu mới</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Lưu mật khẩu mới</button>
        </form>
    </div></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/auth/dat-lai-mat-khau.blade.php ENDPATH**/ ?>