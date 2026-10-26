@extends('layouts.app')
@section('title', $lesson->exists ? 'Sửa bài học' : 'Thêm bài học')
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('instructor.curriculum', $course) }}">{{ $course->title }}</a></li>
    <li class="breadcrumb-item">{{ $section->title }}</li>
</ol></nav>
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3">{{ $lesson->exists ? 'Sửa bài học' : 'Thêm bài học' }}</h3>
        <form method="POST" enctype="multipart/form-data" action="{{ $lesson->exists ? route('instructor.lessons.update', $lesson) : route('instructor.lessons.store', $section) }}">
            @csrf @if($lesson->exists) @method('PUT') @endif
            <div class="mb-3"><label class="form-label">Tiêu đề</label>
                <input id="lesson-title" name="title" value="{{ old('title', $lesson->title) }}" class="form-control" placeholder="Tự điền từ YouTube hoặc nhập thủ công"></div>
            <div class="mb-3"><label class="form-label">Nội dung văn bản</label>
                <textarea name="content" rows="8" class="form-control">{{ old('content', $lesson->content) }}</textarea></div>
            <div class="mb-3"><label class="form-label">Liên kết video (YouTube/Vimeo)</label>
                <div class="input-group">
                    <input id="video-url" type="url" name="video_url" value="{{ old('video_url', $lesson->video_url) }}" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                    <button id="youtube-fetch" class="btn btn-outline-primary" type="button"><i class="bi bi-magic"></i> Lấy thông tin</button>
                </div>
                <div class="form-text">Link YouTube sẽ tự lấy tiêu đề và ảnh đại diện qua oEmbed, không cần API key.</div>
                <div id="youtube-error" class="text-danger small mt-1"></div>
            </div>
            <div id="youtube-preview" class="card border-0 bg-light mb-3 {{ $lesson->video_thumbnail ? '' : 'd-none' }}">
                <div class="row g-0 align-items-center">
                    <div class="col-sm-4"><img id="youtube-thumbnail" src="{{ $lesson->video_thumbnail }}" alt="" class="img-fluid rounded-start w-100" style="aspect-ratio:16/9;object-fit:cover"></div>
                    <div class="col-sm-8"><div class="card-body py-3">
                        <div class="small text-uppercase text-danger fw-semibold"><i class="bi bi-youtube"></i> YouTube</div>
                        <div id="youtube-title" class="fw-semibold">{{ $lesson->video_title }}</div>
                        <div id="youtube-author" class="small text-muted"></div>
                    </div></div>
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Liên kết tài liệu (PDF, Google Drive...)</label>
                <input type="url" name="document_url" value="{{ old('document_url', $lesson->document_url) }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label" for="attachments">Tài liệu PDF đính kèm</label>
                <input type="file" id="attachments" name="attachments[]" multiple accept="application/pdf,.pdf"
                       class="form-control @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror">
                <div class="form-text">Chọn một hoặc nhiều file PDF (tối đa {{ \App\Models\TaiLieuBaiHoc::MAX_FILES }} file/lần, mỗi file ≤ 10MB). Tài liệu chỉ học viên đã đăng ký mới xem được.</div>
                @error('attachments')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @foreach($errors->get('attachments.*') as $messages)
                    <div class="invalid-feedback">{{ $messages[0] }}</div>
                @endforeach
            </div>
            <div class="mb-3" style="max-width:180px"><label class="form-label">Thứ tự</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $lesson->sort_order) }}" class="form-control" placeholder="Tự động"></div>
            <button class="btn btn-primary">Lưu</button>
            <a href="{{ route('instructor.curriculum', $course) }}" class="btn btn-link">Hủy</a>
        </form>
    </div></div>

    @if($lesson->exists)
        <div class="card border-0 shadow-sm mt-3"><div class="card-body p-4">
            <h5 class="mb-3"><i class="bi bi-file-earmark-pdf text-danger"></i> Tài liệu đã đính kèm ({{ $lesson->attachments->count() }})</h5>
            @forelse($lesson->attachments as $attachment)
                <div class="d-flex flex-wrap align-items-center gap-2 py-2 border-top">
                    <div class="flex-grow-1 text-break">
                        <i class="bi bi-file-earmark-pdf text-danger"></i> {{ $attachment->original_name }}
                        <span class="text-muted small">({{ $attachment->dungLuongDeDoc() }})</span>
                    </div>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('attachments.view', $attachment) }}" target="_blank" rel="noopener">
                        <i class="bi bi-eye"></i> Xem</a>
                    <form method="POST" action="{{ route('instructor.attachments.destroy', $attachment) }}" class="d-inline"
                          onsubmit="return confirm('Xóa tài liệu này? Thao tác không thể hoàn tác.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Xóa</button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">Chưa có tài liệu nào. Dùng ô "Tài liệu PDF đính kèm" ở trên để tải lên.</p>
            @endforelse
        </div></div>
    @endif
</div></div>
@endsection

@push('scripts')
<script>
(() => {
    const url = document.getElementById('video-url');
    const title = document.getElementById('lesson-title');
    const button = document.getElementById('youtube-fetch');
    const preview = document.getElementById('youtube-preview');
    const error = document.getElementById('youtube-error');
    let timer;

    async function layThongTin() {
        if (!url.value || !/(youtube\.com|youtu\.be)/i.test(url.value)) return;
        button.disabled = true;
        error.textContent = '';
        try {
            const endpoint = @json(route('instructor.youtube.thong-tin')) + '?url=' + encodeURIComponent(url.value);
            const response = await fetch(endpoint, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const data = await response.json();
            if (!response.ok) throw data;
            if (!title.value.trim()) title.value = data.tieu_de;
            document.getElementById('youtube-title').textContent = data.tieu_de;
            document.getElementById('youtube-thumbnail').src = data.anh_dai_dien;
            document.getElementById('youtube-author').textContent = data.tac_gia || '';
            preview.classList.remove('d-none');
        } catch (problem) {
            preview.classList.add('d-none');
            error.textContent = problem.message || 'Không lấy được thông tin video YouTube.';
        } finally {
            button.disabled = false;
        }
    }

    button.addEventListener('click', layThongTin);
    url.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(layThongTin, 500); });
})();
</script>
@endpush
