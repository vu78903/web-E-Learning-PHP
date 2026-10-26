{{-- File: chung-chi/nut.blade.php - Nút chứng chỉ trên thẻ khóa học ở "Khóa học của tôi". Biến: $course --}}
@php
    $nguoiDung = auth()->user();
    $chungChiCuaToi = \App\Models\ChungChi::where('user_id', $nguoiDung->id)->where('course_id', $course->id)->first();
    $duDieuKienCC = ! $chungChiCuaToi && \App\ChucNang\KiemTraDieuKienChungChi::kiemTra($nguoiDung, $course)['du_dieu_kien'];
@endphp
@if($chungChiCuaToi)
    <a href="{{ route('chung_chi.xem', $chungChiCuaToi) }}" class="btn btn-outline-success btn-sm mt-2"><i class="bi bi-award"></i> Xem chứng chỉ</a>
@elseif($duDieuKienCC)
    <form method="POST" action="{{ route('chung_chi.nhan', $course) }}" class="mt-2">@csrf
        <button class="btn btn-warning btn-sm w-100"><i class="bi bi-award-fill"></i> Nhận chứng chỉ</button>
    </form>
@elseif($course->progress === 100)
    <a href="{{ route('learn.show', $course) }}#chung-chi" class="small text-muted mt-2">Còn bài kiểm tra chưa đạt để nhận chứng chỉ</a>
@endif
