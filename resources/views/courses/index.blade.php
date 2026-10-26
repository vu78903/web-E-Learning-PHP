@extends('layouts.app')
@section('title', 'Khóa học')
@section('content')
<div class="page-heading">
    <div class="eyebrow">Kho tri thức</div>
    <h1>Khám phá khóa học</h1>
    <p class="text-muted mb-0">Tìm nội dung phù hợp với mục tiêu và nhịp học của bạn.</p>
</div>
<form method="GET" class="row g-2 surface-panel filter-panel">
    <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên hoặc mô tả..."></div>
    <div class="col-md-3">
        <select name="category" class="form-select">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="sort" class="form-select">
            <option value="newest" @selected(request('sort') === 'newest')>Mới nhất</option>
            <option value="popular" @selected(request('sort') === 'popular')>Nhiều học viên</option>
            <option value="title" @selected(request('sort') === 'title')>Theo tên A-Z</option>
        </select>
    </div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary"><i class="bi bi-search me-1"></i> Tìm kiếm</button></div>
</form>

<div id="course-list">@include('courses._list')</div>

<script>
// AJAX: tìm kiếm/lọc/phân trang không tải lại trang (vẫn hoạt động bình thường nếu tắt JavaScript).
(function () {
    const form = document.querySelector('form[method="GET"]');
    const box = document.getElementById('course-list');
    let timer;
    async function load(url) {
        box.style.opacity = .5;
        try {
            const res = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!res.ok) throw new Error(res.status);
            box.innerHTML = await res.text();
            history.replaceState(null, '', url);
        } catch (e) { window.location = url; }
        box.style.opacity = 1;
    }
    function submit() {
        const qs = new URLSearchParams(new FormData(form)).toString();
        load(form.action.split('?')[0] + '?' + qs);
    }
    form.addEventListener('submit', e => { e.preventDefault(); submit(); });
    form.querySelector('input[name=q]').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(submit, 350); });
    form.querySelectorAll('select').forEach(el => el.addEventListener('change', submit));
    box.addEventListener('click', e => {
        const a = e.target.closest('.pagination a');
        if (a) { e.preventDefault(); load(a.href); }
    });
})();
</script>
@endsection
