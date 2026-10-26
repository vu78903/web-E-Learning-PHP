{{-- File: chung-chi/the.blade.php - Thẻ "Chứng chỉ hoàn thành" ở trang học (learn.show). Biến: $course --}}
@php
    $nguoiDung = auth()->user();
    $chungChiCuaToi = $nguoiDung?->laHocVien()
        ? \App\Models\ChungChi::where('user_id', $nguoiDung->id)->where('course_id', $course->id)->first()
        : null;
    $dieuKienCC = $nguoiDung?->laHocVien() && ! $chungChiCuaToi
        ? \App\ChucNang\KiemTraDieuKienChungChi::kiemTra($nguoiDung, $course)
        : null;
@endphp
@if($nguoiDung?->laHocVien())
<div class="card border-0 shadow-sm mt-3" id="chung-chi">
    <div class="card-body">
        <h5 class="mb-2"><i class="bi bi-award text-warning"></i> Chứng chỉ hoàn thành</h5>
        @if($chungChiCuaToi)
            <p class="mb-2">Bạn đã nhận chứng chỉ ngày {{ $chungChiCuaToi->cap_luc->format('d/m/Y') }}
                (mã <code>{{ $chungChiCuaToi->ma_chung_chi }}</code>).</p>
            <a href="{{ route('chung_chi.xem', $chungChiCuaToi) }}" class="btn btn-success"><i class="bi bi-printer"></i> Xem / In chứng chỉ</a>
        @elseif($dieuKienCC['du_dieu_kien'])
            <p class="mb-2">Bạn đã đủ điều kiện nhận chứng chỉ cho khóa học này.</p>
            <form method="POST" action="{{ route('chung_chi.nhan', $course) }}">@csrf
                <button class="btn btn-warning"><i class="bi bi-award-fill"></i> Nhận chứng chỉ</button>
            </form>
        @else
            <p class="small text-muted mb-2">Hoàn thành các điều kiện sau để nhận chứng chỉ (in được hoặc lưu PDF):</p>
            <ul class="list-unstyled small mb-0">
                <li>
                    <i class="bi {{ $dieuKienCC['tien_do'] === 100 ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }}"></i>
                    Hoàn thành 100% bài học (hiện tại {{ $dieuKienCC['tien_do'] }}%)
                </li>
                @foreach($dieuKienCC['bai_kiem_tra'] as $bai)
                    <li>
                        <i class="bi {{ $bai['dat'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }}"></i>
                        Đạt bài kiểm tra "{{ $bai['ten'] }}"
                        @if($bai['diem'] !== null)<span class="text-muted">(điểm cao nhất {{ rtrim(rtrim(number_format($bai['diem'], 2), '0'), '.') }})</span>@endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endif
