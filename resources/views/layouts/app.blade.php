<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Trang chủ') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/elearning.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@php($u = auth()->user())
<nav class="navbar navbar-expand-xl navbar-light app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand app-brand" href="{{ route('home') }}">
            <span class="brand-symbol"><i class="bi bi-mortarboard-fill"></i></span>
            <span>{{ config('app.name') }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto app-nav-links">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">Khám phá</a></li>
                @if($u?->laHocVien())
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('my-courses', 'learn.*', 'quizzes.*', 'attempts.*') ? 'active' : '' }}" href="{{ route('my-courses') }}">Học tập</a></li>
                @endif
                @if($u?->laGiangVien())
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}" href="{{ route('instructor.dashboard') }}">Tổng quan</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('instructor.courses.*', 'instructor.curriculum', 'instructor.lessons.*', 'instructor.quizzes.*') ? 'active' : '' }}" href="{{ route('instructor.courses.index') }}">Giảng dạy</a></li>
                @endif
                @if($u?->laQuanTri())
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Tổng quan</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.users.*', 'admin.categories.*', 'admin.courses.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Quản trị</a>
                        <ul class="dropdown-menu app-dropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Người dùng</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}"><i class="bi bi-grid"></i> Danh mục</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.courses.index') }}"><i class="bi bi-journal-check"></i> Duyệt khóa học</a></li>
                            <li><a class="dropdown-item" href="{{ route('instructor.courses.index') }}"><i class="bi bi-list-task"></i> Quản lý nội dung</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
            <ul class="navbar-nav align-items-xl-center gap-xl-2">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-menu" href="#" data-bs-toggle="dropdown">
                            @if($u->duongDanAnhDaiDien())<img src="{{ $u->duongDanAnhDaiDien() }}" alt="" class="user-avatar">@else<span class="user-avatar user-avatar-fallback">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>@endif
                            <span class="d-inline-flex flex-column lh-sm"><strong>{{ $u->name }}</strong><small>{{ $u->tenVaiTro() }}</small></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end app-dropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear"></i> Hồ sơ cá nhân</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link fw-semibold" href="{{ route('login') }}">Đăng nhập</a></li>
                    <li class="nav-item"><a class="btn btn-primary px-3" href="{{ route('register') }}">Bắt đầu học</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container app-main">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="app-footer">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2"><span class="brand-symbol brand-symbol-sm"><i class="bi bi-mortarboard-fill"></i></span><strong>{{ config('app.name') }}</strong></div>
        <span>Nền tảng học tập trực tuyến — học thật, tiến bộ thật.</span>
        <span>&copy; {{ date('Y') }}</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
