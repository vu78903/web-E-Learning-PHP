{{-- File: xuat-csv/nut-khoa-hoc.blade.php - Nút xuất CSV ở trang học viên của khóa học. Biến: $course --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('instructor.csv.hoc_vien', $course) }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-filetype-csv"></i> Xuất danh sách học viên (CSV)</a>
    <a href="{{ route('instructor.csv.bang_diem', $course) }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-table"></i> Xuất bảng điểm (CSV)</a>
</div>
