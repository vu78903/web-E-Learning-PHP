@extends('layouts.app')
@section('title', 'Dashboard quản trị')
@section('content')
<div class="page-heading"><div class="eyebrow">Trung tâm điều hành</div><h1>Dashboard quản trị</h1><p class="text-muted mb-0">Theo dõi toàn cảnh hoạt động của nền tảng.</p></div>
@php
    $cards = [
        ['Người dùng', $stats['users'], 'people', 'primary', $stats['verified_users'].' hoạt động · '.$stats['pending_verification'].' chờ xác minh'],
        ['Khóa học', $stats['courses'], 'journal-richtext', 'success', $stats['published'].' đang công khai'],
        ['Lượt đăng ký', $stats['enrollments'], 'person-plus', 'info', 'tổng đăng ký khóa học'],
        ['Bài kiểm tra đã nộp', $stats['attempts'], 'patch-check', 'warning',
            $stats['attempts'] ? 'TB '.number_format($stats['avg_score'], 1).' điểm · đạt '.$stats['pass_rate'].'%' : 'chưa có lượt nộp'],
    ];
@endphp
<div class="row g-3 mb-4">
    @foreach($cards as [$label, $value, $icon, $color, $sub])
        <div class="col-sm-6 col-lg-3"><div class="card border-0 h-100 dashboard-card"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div><div class="text-muted">{{ $label }}</div><div class="display-6 fw-bold">{{ $value }}</div></div>
                <span class="dashboard-icon"><i class="bi bi-{{ $icon }} fs-4 text-{{ $color }}"></i></span>
            </div>
            <div class="small text-muted">{{ $sub }}</div>
        </div></div></div>
    @endforeach
</div>

<div class="row g-4">
    @include('partials.dashboard-charts')
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Đăng ký gần đây</div>
            <ul class="list-group list-group-flush">
                @forelse($recentEnrollments as $e)
                    <li class="list-group-item small">{{ $e->user->name }} → <strong>{{ $e->course->title }}</strong>
                        <span class="text-muted float-end">{{ $e->enrolled_at?->diffForHumans() }}</span></li>
                @empty<li class="list-group-item text-muted">Chưa có đăng ký.</li>@endforelse
            </ul></div>
    </div>
    <div class="col-12">
        <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Kết quả kiểm tra gần đây</div>
            <table class="table mb-0 small">
                <thead><tr><th>Học viên</th><th>Khóa học</th><th>Bài kiểm tra</th><th>Điểm</th><th>Kết quả</th><th>Thời điểm</th></tr></thead>
                <tbody>
                @forelse($recentAttempts as $a)
                    <tr><td>{{ $a->user->name }}</td><td>{{ $a->quiz->course->title }}</td><td>{{ $a->quiz->title }}</td>
                        <td>{{ rtrim(rtrim(number_format($a->score, 2), '0'), '.') }}</td>
                        <td><span class="badge text-bg-{{ $a->passed ? 'success' : 'danger' }}">{{ $a->passed ? 'Đạt' : 'Chưa đạt' }}</span></td>
                        <td>{{ $a->submitted_at->format('d/m/Y H:i') }}</td></tr>
                @empty<tr><td colspan="6" class="text-muted text-center py-3">Chưa có lượt nộp bài.</td></tr>@endforelse
                </tbody>
            </table></div>
    </div>
</div>
@endsection

