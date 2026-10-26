<?php $__env->startSection('title', 'Hồ sơ cá nhân'); ?>
<?php $__env->startSection('content'); ?>
<div class="row justify-content-center"><div class="col-md-7">
    <div class="card shadow-sm border-0"><div class="card-body p-4">
        <h3 class="mb-3">Hồ sơ cá nhân <span class="badge text-bg-secondary fs-6"><?php echo e($user->tenVaiTro()); ?></span></h3>
        <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="mb-3 d-flex align-items-center gap-3">
                <?php if($user->duongDanAnhDaiDien()): ?>
                    <img src="<?php echo e($user->duongDanAnhDaiDien()); ?>" alt="Ảnh đại diện" class="rounded-circle border" width="72" height="72" style="object-fit:cover">
                <?php else: ?>
                    <i class="bi bi-person-circle text-secondary" style="font-size:4.5rem;line-height:1"></i>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <label class="form-label mb-1">Ảnh đại diện (JPG/PNG/WEBP, tối đa 2 MB)</label>
                    <input type="file" name="avatar" accept="image/*" class="form-control <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php if($user->avatar): ?>
                        <div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="rm"><label class="form-check-label small" for="rm">Xóa ảnh hiện tại</label></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Họ và tên</label>
                <input name="name" value="<?php echo e(old('name', $user->name)); ?>" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label>
                <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Số điện thoại</label>
                <input type="tel" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       inputmode="numeric" maxlength="10" pattern="0[35789][0-9]{8}" placeholder="VD: 0912345678">
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
            <div class="mb-3"><label class="form-label">Giới thiệu</label>
                <textarea name="bio" rows="3" class="form-control"><?php echo e(old('bio', $user->bio)); ?></textarea></div>
            <hr>
            <p class="text-muted small">Chỉ điền phần dưới nếu muốn đổi mật khẩu (8–64 ký tự, gồm chữ hoa, chữ thường, chữ số và ký tự đặc biệt).</p>
            <div class="mb-3"><label class="form-label">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" class="form-control"></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Nhập lại mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <button class="btn btn-primary">Lưu thay đổi</button>
        </form>
    </div></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/profile/edit.blade.php ENDPATH**/ ?>