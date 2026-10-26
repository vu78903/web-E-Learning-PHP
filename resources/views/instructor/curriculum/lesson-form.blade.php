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
        <form id="lesson-form" method="POST" enctype="multipart/form-data" action="{{ $lesson->exists ? route('instructor.lessons.update', $lesson) : route('instructor.lessons.store', $section) }}">
            @csrf @if($lesson->exists) @method('PUT') @endif
            <input type="hidden" id="cho-phep-trung" name="cho_phep_trung" value="0">
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
                @if($errors->has('attachments_trung'))
                    <div class="alert alert-warning small mt-2 mb-0">
                        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle"></i> Phát hiện tài liệu trùng, chưa có gì được lưu:</div>
                        <ul class="mb-2 ps-3">@foreach($errors->get('attachments_trung') as $message)<li>{{ $message }}</li>@endforeach</ul>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="xac-nhan-trung"
                                   onchange="document.getElementById('cho-phep-trung').value = this.checked ? 1 : 0">
                            <label class="form-check-label" for="xac-nhan-trung">Tôi vẫn muốn tải lên các file trùng (chọn lại file rồi bấm Lưu)</label>
                        </div>
                    </div>
                @endif
            </div>
            <div class="mb-3" style="max-width:180px"><label class="form-label">Thứ tự</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $lesson->sort_order) }}" class="form-control" placeholder="Tự động"></div>
            <button class="btn btn-primary">Lưu</button>
            <a href="{{ route('instructor.curriculum', $course) }}" class="btn btn-link">Hủy</a>
        </form>
    </div></div>

    <div class="modal fade" id="modal-file-trung" tabindex="-1" aria-labelledby="modal-file-trung-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-file-trung-title"><i class="bi bi-exclamation-triangle text-warning"></i> Phát hiện tài liệu trùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <ul id="danh-sach-trung" class="mb-2 ps-3"></ul>
                <p class="text-muted small mb-0">Nếu vẫn tải lên, bài học sẽ có thêm bản sao của các tài liệu này.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy, chọn lại file</button>
                <button type="button" id="van-tai-len" class="btn btn-warning">Vẫn tải lên</button>
            </div>
        </div></div>
    </div>

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

// Cảnh báo file PDF trùng (tên hoặc nội dung) trước khi gửi form; máy chủ vẫn kiểm tra lại khi lưu.
(() => {
    const form = document.getElementById('lesson-form');
    const input = document.getElementById('attachments');
    const choPhep = document.getElementById('cho-phep-trung');
    const modalEl = document.getElementById('modal-file-trung');
    const modal = new bootstrap.Modal(modalEl);
    const endpoint = @json($lesson->exists ? route('instructor.lessons.attachments.kiem-tra-trung', $lesson) : null);
    let daKiemTra = false;

    // Web Crypto chỉ có trong secure context (https/localhost); không có thì chỉ so theo tên.
    async function sha256(file) {
        if (!window.crypto?.subtle) return null;
        const buffer = await crypto.subtle.digest('SHA-256', await file.arrayBuffer());
        return [...new Uint8Array(buffer)].map(b => b.toString(16).padStart(2, '0')).join('');
    }

    function trungTrongLanNay(files) {
        const ketQua = [];
        files.forEach((f, i) => {
            const truoc = files.slice(0, i).find(p => p.name.toLowerCase() === f.name.toLowerCase() || (f.hash && p.hash === f.hash));
            if (truoc) ketQua.push({thong_bao: `"${f.name}" trùng với file "${truoc.name}" trong cùng lần tải lên.`});
        });
        return ketQua;
    }

    async function timFileTrung() {
        const files = await Promise.all([...input.files].map(async f => ({name: f.name, hash: await sha256(f)})));
        if (!endpoint) return trungTrongLanNay(files);

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                      'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value},
            body: JSON.stringify({files}),
        });
        if (!response.ok) throw new Error('Không kiểm tra được file trùng.');
        return (await response.json()).trung;
    }

    // Chọn lại file thì phải kiểm tra lại, trừ khi đã tick ô đồng ý ở cảnh báo của máy chủ.
    input.addEventListener('change', () => {
        daKiemTra = false;
        choPhep.value = document.getElementById('xac-nhan-trung')?.checked ? 1 : 0;
    });

    form.addEventListener('submit', async (event) => {
        if (daKiemTra || choPhep.value === '1' || !input.files.length) return;
        event.preventDefault();
        const nutLuu = form.querySelector('button.btn-primary');
        nutLuu.disabled = true;
        let trung = [];
        try {
            trung = await timFileTrung();
        } catch (e) {
            trung = []; // lỗi mạng: cứ gửi, máy chủ sẽ chặn nếu thật sự trùng
        } finally {
            nutLuu.disabled = false;
        }

        daKiemTra = true;
        if (!trung.length) return form.requestSubmit();

        const list = document.getElementById('danh-sach-trung');
        list.replaceChildren(...trung.map(t => Object.assign(document.createElement('li'), {textContent: t.thong_bao})));
        modal.show();
    });

    modalEl.addEventListener('hidden.bs.modal', () => { if (choPhep.value !== '1') daKiemTra = false; });
    document.getElementById('van-tai-len').addEventListener('click', () => {
        choPhep.value = 1;
        modal.hide();
        form.requestSubmit();
    });
})();
</script>
@endpush
