{{-- File: xuat-csv/nut-bai-kiem-tra.blade.php - Nút xuất CSV ở trang kết quả bài kiểm tra. Biến: $quiz, $course --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('instructor.csv.ket_qua', $quiz) }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-filetype-csv"></i> Xuất kết quả bài này (CSV)</a>
    <a href="{{ route('instructor.csv.bang_diem', $course) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-table"></i> Bảng điểm cả khóa học (CSV)</a>
</div>
