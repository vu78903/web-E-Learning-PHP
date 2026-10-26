<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\TaiKhoanController;
use App\Http\Controllers\KhoaHocController;
use App\Http\Controllers\BangDieuKhienController;
use App\Http\Controllers\DangKyKhoaHocController;
use App\Http\Controllers\TrangChuController;
use App\Http\Controllers\Instructor;
use App\Http\Controllers\HocTapController;
use App\Http\Controllers\HoSoController;
use App\Http\Controllers\LamBaiKiemTraController;
use App\Http\Controllers\ChungChiController;
use App\Http\Controllers\DanhGiaKhoaHocController;
use App\Http\Controllers\XuatBaoCaoController;
use App\Http\Controllers\Auth\QuenMatKhauController;
use App\Http\Controllers\Auth\XacMinhEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Công khai
|--------------------------------------------------------------------------
*/
Route::get('/', [TrangChuController::class, 'hienThi'])->name('home');
Route::get('/courses', [KhoaHocController::class, 'danhSach'])->name('courses.index');
Route::get('/courses/{course}', [KhoaHocController::class, 'chiTiet'])->name('courses.show');

/*
|--------------------------------------------------------------------------
| Khách (chưa đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [TaiKhoanController::class, 'hienThiDangKy'])->name('register');
    Route::post('/register', [TaiKhoanController::class, 'dangKy'])->middleware('throttle:10,1');
    Route::get('/login', [TaiKhoanController::class, 'hienThiDangNhap'])->name('login');
    Route::post('/login', [TaiKhoanController::class, 'dangNhap'])->middleware('throttle:10,1');
});

// Khôi phục mật khẩu và xác minh địa chỉ email.
Route::get('/xac-minh-email/{id}/{hash}', [XacMinhEmailController::class, 'xacMinh'])
    ->middleware('signed')->name('verification.verify');
Route::middleware('guest')->group(function () {
    Route::get('/quen-mat-khau', [QuenMatKhauController::class, 'hienThiYeuCau'])->name('password.request');
    Route::post('/quen-mat-khau', [QuenMatKhauController::class, 'guiLienKet'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [QuenMatKhauController::class, 'hienThiDatLai'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [QuenMatKhauController::class, 'datLaiMatKhau'])
        ->middleware('throttle:5,1')->name('password.update');
});
Route::middleware('auth')->group(function () {
    Route::get('/xac-minh-email', [XacMinhEmailController::class, 'thongBao'])->name('verification.notice');
    Route::get('/xac-minh-email/trang-thai', [XacMinhEmailController::class, 'trangThai'])
        ->middleware('throttle:30,1')->name('verification.status');
    Route::post('/xac-minh-email/gui-lai', [XacMinhEmailController::class, 'guiLai'])
        ->middleware('throttle:6,1')->name('verification.send');
});

// Trang xác thực chứng chỉ được công khai để người nhận có thể chia sẻ mã hoặc QR.
Route::get('/xac-thuc-chung-chi/{chungChi:ma_chung_chi}', [ChungChiController::class, 'xacThuc'])
    ->name('chung_chi.xac_thuc');

/*
|--------------------------------------------------------------------------
| Đã đăng nhập
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'email.xac_minh'])->group(function () {
    Route::post('/logout', [TaiKhoanController::class, 'dangXuat'])->name('logout');
    Route::get('/dashboard', [BangDieuKhienController::class, 'chuyenHuong'])->name('dashboard');
    Route::get('/profile', [HoSoController::class, 'chinhSua'])->name('profile.edit');
    Route::put('/profile', [HoSoController::class, 'capNhat'])->name('profile.update');

    // ---- Học viên ----
    Route::middleware('role:student')->group(function () {
        Route::post('/courses/{course}/enroll', [DangKyKhoaHocController::class, 'dangKy'])->name('courses.enroll');
        Route::get('/my-courses', [DangKyKhoaHocController::class, 'khoaHocCuaToi'])->name('my-courses');
        Route::post('/lessons/{lesson}/complete', [HocTapController::class, 'danhDauHoanThanh'])->name('lessons.complete');
    });

    // ---- Học nội dung (quyền kiểm tra bằng ChinhSachKhoaHoc::truyCap ở Controller) ----
    Route::get('/learn/{course}', [HocTapController::class, 'tongQuan'])->name('learn.show');
    Route::get('/learn/{course}/lessons/{lesson}', [HocTapController::class, 'baiHoc'])->name('learn.lesson');
    Route::get('/attachments/{attachment}', [HocTapController::class, 'xemTaiLieu'])->name('attachments.view');
    Route::get('/attachments/{attachment}/download', [HocTapController::class, 'taiTaiLieu'])->name('attachments.download');

    Route::post('/khoa-hoc/{course}/danh-gia', [DanhGiaKhoaHocController::class, 'luuDanhGia'])
        ->middleware('role:student')->name('courses.reviews.store');
    Route::post('/khoa-hoc/{course}/chung-chi', [ChungChiController::class, 'nhan'])
        ->middleware('role:student')->name('chung_chi.nhan');
    Route::get('/chung-chi/{chungChi:ma_chung_chi}', [ChungChiController::class, 'xem'])
        ->name('chung_chi.xem');

    // ---- Làm bài kiểm tra ----
    Route::get('/quizzes/{quiz}', [LamBaiKiemTraController::class, 'chiTiet'])->name('quizzes.show');
    Route::post('/quizzes/{quiz}/start', [LamBaiKiemTraController::class, 'batDau'])->name('quizzes.start');
    Route::get('/attempts/{attempt}', [LamBaiKiemTraController::class, 'lamBai'])->name('attempts.take');
    Route::post('/attempts/{attempt}', [LamBaiKiemTraController::class, 'nopBai'])->name('attempts.submit');
    Route::get('/attempts/{attempt}/result', [LamBaiKiemTraController::class, 'ketQua'])->name('attempts.result');

    // ---- Giảng viên (admin cũng quản lý được mọi khóa học) ----
    Route::middleware('role:admin,instructor')->prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/courses', [Instructor\KhoaHocController::class, 'danhSach'])->name('courses.index');
        Route::get('/courses/{course}/edit', [Instructor\KhoaHocController::class, 'chinhSua'])->name('courses.edit');
        Route::put('/courses/{course}', [Instructor\KhoaHocController::class, 'capNhat'])->name('courses.update');
        Route::delete('/courses/{course}', [Instructor\KhoaHocController::class, 'xoa'])->name('courses.destroy');
        Route::get('/courses/{course}/students', [Instructor\KhoaHocController::class, 'hocVien'])->name('courses.students');

        // Chương & bài học
        Route::get('/courses/{course}/curriculum', [Instructor\NoiDungKhoaHocController::class, 'danhSach'])->name('curriculum');
        Route::get('/youtube/thong-tin', [Instructor\NoiDungKhoaHocController::class, 'thongTinYoutube'])->name('youtube.thong-tin');
        Route::post('/courses/{course}/sections', [Instructor\NoiDungKhoaHocController::class, 'luuChuong'])->name('sections.store');
        Route::put('/sections/{section}', [Instructor\NoiDungKhoaHocController::class, 'capNhatChuong'])->name('sections.update');
        Route::delete('/sections/{section}', [Instructor\NoiDungKhoaHocController::class, 'xoaChuong'])->name('sections.destroy');
        Route::get('/sections/{section}/lessons/create', [Instructor\NoiDungKhoaHocController::class, 'taoBaiHoc'])->name('lessons.create');
        Route::post('/sections/{section}/lessons', [Instructor\NoiDungKhoaHocController::class, 'luuBaiHoc'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [Instructor\NoiDungKhoaHocController::class, 'chinhSuaBaiHoc'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [Instructor\NoiDungKhoaHocController::class, 'capNhatBaiHoc'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [Instructor\NoiDungKhoaHocController::class, 'xoaBaiHoc'])->name('lessons.destroy');
        Route::delete('/attachments/{attachment}', [Instructor\NoiDungKhoaHocController::class, 'xoaTaiLieu'])->name('attachments.destroy');

        // Bài kiểm tra, câu hỏi, phương án
        Route::get('/courses/{course}/quizzes', [Instructor\BaiKiemTraController::class, 'danhSach'])->name('quizzes.index');
        Route::post('/courses/{course}/quizzes', [Instructor\BaiKiemTraController::class, 'luu'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}/edit', [Instructor\BaiKiemTraController::class, 'chinhSua'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [Instructor\BaiKiemTraController::class, 'capNhat'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [Instructor\BaiKiemTraController::class, 'xoa'])->name('quizzes.destroy');
        Route::get('/quizzes/{quiz}/results', [Instructor\BaiKiemTraController::class, 'ketQua'])->name('quizzes.results');
        Route::post('/quizzes/{quiz}/questions', [Instructor\BaiKiemTraController::class, 'luuCauHoi'])->name('questions.store');
        Route::get('/questions/{question}/edit', [Instructor\BaiKiemTraController::class, 'chinhSuaCauHoi'])->name('questions.edit');
        Route::put('/questions/{question}', [Instructor\BaiKiemTraController::class, 'capNhatCauHoi'])->name('questions.update');
        Route::delete('/questions/{question}', [Instructor\BaiKiemTraController::class, 'xoaCauHoi'])->name('questions.destroy');

        Route::get('/courses/{course}/xuat-csv/hoc-vien', [XuatBaoCaoController::class, 'hocVien'])->name('csv.hoc_vien');
        Route::get('/courses/{course}/xuat-csv/bang-diem', [XuatBaoCaoController::class, 'bangDiem'])->name('csv.bang_diem');
        Route::get('/quizzes/{quiz}/xuat-csv', [XuatBaoCaoController::class, 'ketQuaBaiKiemTra'])->name('csv.ket_qua');
    });

    // Chỉ giảng viên: dashboard thống kê khóa học của mình, tạo khóa học mới (gắn với instructor_id của người tạo)
    Route::middleware('role:instructor')->prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/dashboard', [Instructor\BangDieuKhienController::class, 'hienThi'])->name('dashboard');
        Route::get('/courses/create', [Instructor\KhoaHocController::class, 'taoMoi'])->name('courses.create');
        Route::post('/courses', [Instructor\KhoaHocController::class, 'luu'])->name('courses.store');
    });

    // ---- Quản trị viên ----
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [Admin\BangDieuKhienController::class, 'hienThi'])->name('dashboard');
        Route::get('/users', [Admin\NguoiDungController::class, 'danhSach'])->name('users.index');
        Route::get('/users/{user}/edit', [Admin\NguoiDungController::class, 'chinhSua'])->name('users.edit');
        Route::put('/users/{user}', [Admin\NguoiDungController::class, 'capNhat'])->name('users.update');
        Route::delete('/users/{user}', [Admin\NguoiDungController::class, 'xoa'])->name('users.destroy');
        Route::get('/categories', [Admin\DanhMucController::class, 'danhSach'])->name('categories.index');
        Route::get('/categories/create', [Admin\DanhMucController::class, 'taoMoi'])->name('categories.create');
        Route::post('/categories', [Admin\DanhMucController::class, 'luu'])->name('categories.store');
        Route::get('/categories/{category}/edit', [Admin\DanhMucController::class, 'chinhSua'])->name('categories.edit');
        Route::match(['put', 'patch'], '/categories/{category}', [Admin\DanhMucController::class, 'capNhat'])->name('categories.update');
        Route::delete('/categories/{category}', [Admin\DanhMucController::class, 'xoa'])->name('categories.destroy');
        Route::get('/courses', [Admin\KhoaHocController::class, 'danhSach'])->name('courses.index');
        Route::patch('/courses/{course}/status', [Admin\KhoaHocController::class, 'capNhatTrangThai'])->name('courses.status');
    });
});
