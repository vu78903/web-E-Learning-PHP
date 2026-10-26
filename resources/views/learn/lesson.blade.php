@extends('layouts.app')
@section('title', $lesson->title)
@section('content')
@php($current = $lesson)
<div class="row g-4">
    <div class="col-lg-4">@include('learn._sidebar')</div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0"><div class="card-body p-4">
            <div class="text-muted small mb-1">{{ $lesson->section->title }}</div>
            <h3 class="mb-3">{{ $lesson->title }}</h3>

            @if($lesson->duongDanNhungVideo())
                <div class="ratio ratio-16x9 mb-3">
                    <iframe src="{{ $lesson->duongDanNhungVideo() }}" allowfullscreen loading="lazy" title="{{ $lesson->title }}"></iframe>
                </div>
            @elseif($lesson->video_url)
                <p><a href="{{ $lesson->video_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-camera-video"></i> Mở video bài học</a></p>
            @endif

            @if($lesson->content)
                <div class="lesson-content">{!! nl2br(e($lesson->content)) !!}</div>
            @endif

            @if($lesson->document_url)
                <p class="mt-3"><a class="btn btn-outline-secondary btn-sm" href="{{ $lesson->document_url }}" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-file-earmark-text"></i> Tài liệu đính kèm</a></p>
            @endif

            @if($lesson->attachments->isNotEmpty())
                <h5 class="mt-4 mb-2"><i class="bi bi-file-earmark-pdf text-danger"></i> Tài liệu bài học</h5>
                <ul class="list-group mb-3">
                    @foreach($lesson->attachments as $attachment)
                        <li class="list-group-item">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="flex-grow-1 text-break">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> {{ $attachment->original_name }}
                                    <span class="text-muted small">({{ $attachment->dungLuongDeDoc() }})</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary d-none d-md-inline-block js-pdf-toggle"
                                            data-bs-toggle="collapse" data-bs-target="#pdf-preview-{{ $attachment->id }}" aria-expanded="false">
                                        <i class="bi bi-layout-text-window"></i> Xem nhanh</button>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('attachments.view', $attachment) }}" target="_blank" rel="noopener">
                                        <i class="bi bi-box-arrow-up-right"></i> Mở</a>
                                    <a class="btn btn-sm btn-primary" href="{{ route('attachments.download', $attachment) }}">
                                        <i class="bi bi-download"></i> Tải về</a>
                                </div>
                            </div>
                            <div class="collapse mt-2" id="pdf-preview-{{ $attachment->id }}">
                                <div class="ratio" style="--bs-aspect-ratio: 120%;">
                                    <iframe data-src="{{ route('attachments.view', $attachment) }}" title="{{ $attachment->original_name }}" class="border rounded"></iframe>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <hr>
            <div class="d-flex flex-wrap gap-2 justify-content-between">
                <div>
                    @if($prev)<a class="btn btn-outline-secondary" href="{{ route('learn.lesson', [$course, $prev]) }}">&laquo; Bài trước</a>@endif
                </div>
                <div class="d-flex gap-2">
                    @if($laHocVien)
                        @php($done = in_array($lesson->id, $completedIds))
                        <form method="POST" action="{{ route('lessons.complete', $lesson) }}">@csrf
                            <button class="btn {{ $done ? 'btn-success' : 'btn-outline-success' }}">
                                <i class="bi bi-check2-circle"></i> {{ $done ? 'Đã hoàn thành' : 'Đánh dấu hoàn thành' }}
                            </button>
                        </form>
                    @endif
                    @if($next)<a class="btn btn-primary" href="{{ route('learn.lesson', [$course, $next]) }}">Bài tiếp &raquo;</a>@endif
                </div>
            </div>
        </div></div>

        @if($laHocVien && $progress === 100)
            <div class="alert alert-success mt-3">
                <i class="bi bi-trophy-fill"></i>
                Bạn đã hoàn thành khóa học.
                <a class="alert-link" href="{{ route('learn.show', $course) }}#khu-vuc-danh-gia">Đánh giá khóa học ngay</a>.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Chỉ tải PDF vào khung xem khi người dùng mở "Xem nhanh".
    document.querySelectorAll('[id^="pdf-preview-"]').forEach(function (el) {
        el.addEventListener('show.bs.collapse', function () {
            var frame = el.querySelector('iframe[data-src]');
            if (frame && !frame.getAttribute('src')) frame.setAttribute('src', frame.dataset.src);
        });
    });
</script>
@endpush
