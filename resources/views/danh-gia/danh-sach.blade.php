@forelse($danhGia as $item)
    <div class="border-bottom py-3">
        <div class="d-flex justify-content-between gap-3">
            <strong>{{ $item->user->name }}</strong>
            <small class="text-muted">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
        </div>
        <div class="text-warning" aria-label="{{ $item->so_sao }} trên 5 sao">
            @for($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= $item->so_sao ? 'bi-star-fill' : 'bi-star' }}"></i>@endfor
        </div>
        <div class="mt-1" style="white-space:pre-line">{{ $item->binh_luan }}</div>
    </div>
@empty
    <p class="text-muted mb-0">Chưa có đánh giá nào cho khóa học này.</p>
@endforelse
