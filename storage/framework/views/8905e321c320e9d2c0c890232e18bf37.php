<?php $__env->startSection('title', 'Quản lý người dùng'); ?>
<?php $__env->startSection('content'); ?>
<h2 class="mb-3">Quản lý người dùng</h2>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Tìm theo tên hoặc email"></div>
    <div class="col-md-3"><select name="role" class="form-select">
        <option value="">Mọi vai trò</option>
        <?php $__currentLoopData = \App\Models\NguoiDung::ROLES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('role') === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Lọc</button></div>
</form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>#</th><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th></th></tr></thead>
    <tbody>
    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($u->id); ?></td><td><?php echo e($u->name); ?></td><td><?php echo e($u->email); ?></td>
            <td><span class="badge text-bg-<?php echo e(['admin' => 'danger', 'instructor' => 'primary', 'student' => 'secondary'][$u->role]); ?>"><?php echo e($u->tenVaiTro()); ?></span></td>
            <td>
                <?php if(! $u->is_active): ?>
                    <span class="badge text-bg-dark">Đã khóa</span>
                <?php elseif(! $u->hasVerifiedEmail()): ?>
                    <span class="badge text-bg-warning">Chờ xác minh</span>
                <?php else: ?>
                    <span class="badge text-bg-success">Hoạt động</span>
                <?php endif; ?>
            </td>
            <td><?php echo e($u->created_at->format('d/m/Y')); ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.users.edit', $u)); ?>">Sửa</a>
                <?php if($u->id !== auth()->id()): ?>
                <form method="POST" action="<?php echo e(route('admin.users.destroy', $u)); ?>" class="d-inline" onsubmit="return confirm('Xóa tài khoản này?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table></div></div>
<div class="mt-3"><?php echo e($users->links('pagination::bootstrap-5')); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/admin/users/index.blade.php ENDPATH**/ ?>