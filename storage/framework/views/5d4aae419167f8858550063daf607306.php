<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'Trang chủ'); ?> · <?php echo e(config('app.name')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('css/elearning.css')); ?>" rel="stylesheet">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
<?php ($u = auth()->user()); ?>
<nav class="navbar navbar-expand-xl navbar-light app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand app-brand" href="<?php echo e(route('home')); ?>">
            <span class="brand-symbol"><i class="bi bi-mortarboard-fill"></i></span>
            <span><?php echo e(config('app.name')); ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto app-nav-links">
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('courses.*') ? 'active' : ''); ?>" href="<?php echo e(route('courses.index')); ?>">Khám phá</a></li>
                <?php if($u?->laHocVien()): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('my-courses', 'learn.*', 'quizzes.*', 'attempts.*') ? 'active' : ''); ?>" href="<?php echo e(route('my-courses')); ?>">Học tập</a></li>
                <?php endif; ?>
                <?php if($u?->laGiangVien()): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('instructor.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('instructor.dashboard')); ?>">Tổng quan</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('instructor.courses.*', 'instructor.curriculum', 'instructor.lessons.*', 'instructor.quizzes.*') ? 'active' : ''); ?>" href="<?php echo e(route('instructor.courses.index')); ?>">Giảng dạy</a></li>
                <?php endif; ?>
                <?php if($u?->laQuanTri()): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">Tổng quan</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo e(request()->routeIs('admin.users.*', 'admin.categories.*', 'admin.courses.*') ? 'active' : ''); ?>" href="#" data-bs-toggle="dropdown">Quản trị</a>
                        <ul class="dropdown-menu app-dropdown">
                            <li><a class="dropdown-item" href="<?php echo e(route('admin.users.index')); ?>"><i class="bi bi-people"></i> Người dùng</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('admin.categories.index')); ?>"><i class="bi bi-grid"></i> Danh mục</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('admin.courses.index')); ?>"><i class="bi bi-journal-check"></i> Duyệt khóa học</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('instructor.courses.index')); ?>"><i class="bi bi-list-task"></i> Quản lý nội dung</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-xl-center gap-xl-2">
                <?php if(auth()->guard()->check()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-menu" href="#" data-bs-toggle="dropdown">
                            <?php if($u->duongDanAnhDaiDien()): ?><img src="<?php echo e($u->duongDanAnhDaiDien()); ?>" alt="" class="user-avatar"><?php else: ?><span class="user-avatar user-avatar-fallback"><?php echo e(mb_strtoupper(mb_substr($u->name, 0, 1))); ?></span><?php endif; ?>
                            <span class="d-inline-flex flex-column lh-sm"><strong><?php echo e($u->name); ?></strong><small><?php echo e($u->tenVaiTro()); ?></small></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end app-dropdown">
                            <li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>"><i class="bi bi-person-gear"></i> Hồ sơ cá nhân</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?>
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link fw-semibold" href="<?php echo e(route('login')); ?>">Đăng nhập</a></li>
                    <li class="nav-item"><a class="btn btn-primary px-3" href="<?php echo e(route('register')); ?>">Bắt đầu học</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container app-main">
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldContent('content'); ?>
</main>

<footer class="app-footer">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2"><span class="brand-symbol brand-symbol-sm"><i class="bi bi-mortarboard-fill"></i></span><strong><?php echo e(config('app.name')); ?></strong></div>
        <span>Nền tảng học tập trực tuyến — học thật, tiến bộ thật.</span>
        <span>&copy; <?php echo e(date('Y')); ?></span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/layouts/app.blade.php ENDPATH**/ ?>