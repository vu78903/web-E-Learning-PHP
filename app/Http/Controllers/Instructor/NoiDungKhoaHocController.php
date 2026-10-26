<?php

namespace App\Http\Controllers\Instructor;

use App\ChucNang\LayThongTinYoutube;
use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\BaiHoc;
use App\Models\TaiLieuBaiHoc;
use App\Models\ChuongHoc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

/** Quản lý chương và bài học của một khóa học. */
class NoiDungKhoaHocController extends Controller
{
    public function __construct(private readonly LayThongTinYoutube $youtube) {}

    /** Trả thông tin oEmbed để giao diện tự điền ngay khi người dùng dán link. */
    public function thongTinYoutube(Request $request): JsonResponse
    {
        $data = $request->validate(['url' => ['required', 'url', 'max:255']]);
        $thongTin = $this->youtube->lay($data['url']);

        if ($thongTin === null) {
            return response()->json([
                'message' => 'Không lấy được thông tin video. Hãy kiểm tra link YouTube và thử lại.',
            ], 422);
        }

        return response()->json($thongTin);
    }

    public function danhSach(KhoaHoc $course): View
    {
        Gate::authorize('quanLy', $course);

        return view('instructor.curriculum.index', [
            'course' => $course->load(['sections.lessons' => fn ($q) => $q->withCount('attachments')]),
        ]);
    }

    // ---------- Chương ----------

    public function luuChuong(Request $request, KhoaHoc $course): RedirectResponse
    {
        Gate::authorize('quanLy', $course);

        $data = $request->validate(['title' => ['required', 'string', 'max:255']]);
        $course->sections()->create($data + ['sort_order' => ($course->sections()->max('sort_order') ?? 0) + 1]);

        return back()->with('success', 'Đã thêm chương.');
    }

    public function capNhatChuong(Request $request, ChuongHoc $section): RedirectResponse
    {
        Gate::authorize('quanLy', $section->course);

        $section->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]));

        return back()->with('success', 'Đã cập nhật chương.');
    }

    public function xoaChuong(ChuongHoc $section): RedirectResponse
    {
        Gate::authorize('quanLy', $section->course);
        $section->delete();

        return back()->with('success', 'Đã xóa chương và các bài học bên trong.');
    }

    // ---------- Bài học ----------

    public function taoBaiHoc(ChuongHoc $section): View
    {
        Gate::authorize('quanLy', $section->course);

        return view('instructor.curriculum.lesson-form', [
            'section' => $section,
            'course' => $section->course,
            'lesson' => new BaiHoc,
        ]);
    }

    public function luuBaiHoc(Request $request, ChuongHoc $section): RedirectResponse
    {
        Gate::authorize('quanLy', $section->course);

        $data = $this->duLieuHopLe($request);
        $data['sort_order'] ??= ($section->lessons()->max('sort_order') ?? 0) + 1;
        $lesson = $section->lessons()->create($data);
        $count = $this->luuTaiLieu($request, $lesson);

        return redirect()->route('instructor.curriculum', $section->course)->with('success', 'Đã thêm bài học'.($count ? " và {$count} tài liệu PDF." : '.'));
    }

    public function chinhSuaBaiHoc(BaiHoc $lesson): View
    {
        $course = $lesson->section->course;
        Gate::authorize('quanLy', $course);

        return view('instructor.curriculum.lesson-form', [
            'section' => $lesson->section,
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }

    public function capNhatBaiHoc(Request $request, BaiHoc $lesson): RedirectResponse
    {
        $course = $lesson->section->course;
        Gate::authorize('quanLy', $course);

        $data = $this->duLieuHopLe($request);
        $data['sort_order'] ??= $lesson->sort_order;
        $lesson->update($data);
        $count = $this->luuTaiLieu($request, $lesson);

        return redirect()->route('instructor.curriculum', $course)->with('success', 'Đã cập nhật bài học'.($count ? ", thêm {$count} tài liệu PDF." : '.'));
    }

    public function xoaBaiHoc(BaiHoc $lesson): RedirectResponse
    {
        $course = $lesson->section->course;
        Gate::authorize('quanLy', $course);
        $lesson->delete();

        return redirect()->route('instructor.curriculum', $course)->with('success', 'Đã xóa bài học.');
    }

    /** Xóa một tài liệu PDF đã đính kèm (file trên đĩa được xóa trong sự kiện của model). */
    public function xoaTaiLieu(TaiLieuBaiHoc $attachment): RedirectResponse
    {
        Gate::authorize('quanLy', $attachment->lesson->section->course);
        $attachment->delete();

        return back()->with('success', 'Đã xóa tài liệu "'.$attachment->original_name.'".');
    }

    /** Lưu các file PDF tải lên vào disk riêng tư, trả về số file đã lưu. */
    private function luuTaiLieu(Request $request, BaiHoc $lesson): int
    {
        $files = $request->file('attachments', []);
        foreach ($files as $file) {
            // Chỉ giữ phần tên file, loại bỏ ký tự điều khiển; tên lưu trên đĩa là chuỗi ngẫu nhiên.
            $name = preg_replace('/[\x00-\x1F\x7F]/u', '', basename($file->getClientOriginalName())) ?: 'tai-lieu.pdf';
            $lesson->attachments()->create([
                'original_name' => mb_substr($name, 0, 255),
                'path' => $file->store((string) $lesson->id, TaiLieuBaiHoc::DISK),
                'size' => $file->getSize(),
                'mime_type' => 'application/pdf',
            ]);
        }

        return count($files);
    }

    private function duLieuHopLe(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:50000'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'document_url' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'attachments' => ['nullable', 'array', 'max:'.TaiLieuBaiHoc::MAX_FILES],
            // mimes/mimetypes kiểm tra theo nội dung thật của file (finfo), extensions kiểm tra đuôi tên file.
            'attachments.*' => ['file', 'extensions:pdf', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.TaiLieuBaiHoc::MAX_KB],
        ], [
            'attachments.max' => 'Chỉ được tải lên tối đa :max file mỗi lần.',
            'attachments.*.extensions' => 'Tài liệu phải là file PDF.',
            'attachments.*.mimes' => 'Tài liệu phải là file PDF hợp lệ.',
            'attachments.*.mimetypes' => 'Tài liệu phải là file PDF hợp lệ.',
            'attachments.*.max' => 'Mỗi file PDF tối đa 10MB.',
            'attachments.*.uploaded' => 'Không tải lên được file (có thể vượt quá giới hạn dung lượng).',
        ]);
        unset($data['attachments']); // file được lưu riêng trong storeAttachments()

        $data['title'] = trim((string) ($data['title'] ?? ''));
        $urlVideo = $data['video_url'] ?? null;
        if ($this->youtube->laLinkYoutube($urlVideo)) {
            $thongTin = $this->youtube->lay($urlVideo);
            if ($thongTin === null) {
                throw ValidationException::withMessages([
                    'video_url' => 'Không lấy được thông tin video từ YouTube oEmbed. Hãy kiểm tra link và thử lại.',
                ]);
            }

            $data['title'] = $data['title'] ?: $thongTin['tieu_de'];
            $data['video_title'] = $thongTin['tieu_de'];
            $data['video_thumbnail'] = $thongTin['anh_dai_dien'];
        } else {
            $data['video_title'] = null;
            $data['video_thumbnail'] = null;
        }

        if ($data['title'] === '') {
            throw ValidationException::withMessages(['title' => 'Vui lòng nhập tiêu đề bài học.']);
        }

        return $data;
    }
}
