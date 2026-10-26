<?php

namespace Database\Seeders;

use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use App\Models\DangKyKhoaHoc;
use App\Models\TienDoBaiHoc;
use App\Models\CauHoi;
use App\Models\BaiKiemTra;
use App\Models\LuotLamBai;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu demo. Tất cả tài khoản dùng mật khẩu: password
 *   admin@elearning.test | gv1@elearning.test, gv2@elearning.test | hv1..hv5@elearning.test
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->taoNguoiDung();
        $this->taoDanhMuc();
        $this->taoKhoaHoc();
        $this->taoDuLieuHoatDong();
    }

    private int $phoneSeq = 0;

    private function taoMotNguoiDung(string $name, string $email, string $role, ?string $bio = null): NguoiDung
    {
        $u = new NguoiDung(['name' => $name, 'email' => $email, 'password' => 'password', 'bio' => $bio,
            'phone' => '0901000'.str_pad((string) ++$this->phoneSeq, 3, '0', STR_PAD_LEFT)]);
        $u->role = $role;
        $u->email_verified_at = now();
        $u->save();

        return $u;
    }

    private function taoNguoiDung(): void
    {
        $this->taoMotNguoiDung('Quản trị viên', 'admin@elearning.test', NguoiDung::ROLE_ADMIN);
        $this->taoMotNguoiDung('Nguyễn Văn An', 'gv1@elearning.test', NguoiDung::ROLE_INSTRUCTOR, 'Giảng viên lập trình web với hơn 8 năm kinh nghiệm.');
        $this->taoMotNguoiDung('Trần Thị Bình', 'gv2@elearning.test', NguoiDung::ROLE_INSTRUCTOR, 'Giảng viên cơ sở dữ liệu và phân tích dữ liệu.');
        foreach (['Lê Minh Châu', 'Phạm Quốc Dũng', 'Hoàng Thu Hà', 'Vũ Đức Huy', 'Đỗ Mai Lan'] as $i => $name) {
            $this->taoMotNguoiDung($name, 'hv'.($i + 1).'@elearning.test', NguoiDung::ROLE_STUDENT);
        }
    }

    private function taoDanhMuc(): void
    {
        foreach ([
            'Lập trình web' => 'PHP, Laravel, HTML/CSS, JavaScript...',
            'Cơ sở dữ liệu' => 'MySQL, thiết kế CSDL, truy vấn SQL.',
            'Công cụ & quy trình' => 'Git, GitHub, làm việc nhóm.',
            'Kỹ năng mềm' => 'Thuyết trình, quản lý thời gian.',
        ] as $name => $desc) {
            DanhMuc::create(['name' => $name, 'description' => $desc]);
        }
    }

    private function taoMotKhoaHoc(string $email, string $category, string $title, string $desc, string $status, array $sections): KhoaHoc
    {
        $course = new KhoaHoc([
            'category_id' => DanhMuc::where('name', $category)->value('id'),
            'title' => $title,
            'description' => $desc,
            'status' => $status,
        ]);
        $course->instructor_id = NguoiDung::where('email', $email)->value('id');
        $course->save();

        foreach ($sections as $si => $sec) {
            $section = $course->sections()->create(['title' => $sec['title'], 'sort_order' => $si + 1]);
            foreach ($sec['lessons'] as $li => [$lt, $content, $video]) {
                $section->lessons()->create([
                    'title' => $lt, 'content' => $content, 'video_url' => $video, 'sort_order' => $li + 1,
                ]);
            }
        }

        return $course;
    }

    /**
     * $correct là vị trí đáp án đúng (câu một đáp án) hoặc mảng vị trí (câu nhiều đáp án).
     * $options: shuffle_questions, max_attempts.
     */
    private function taoBaiKiemTra(KhoaHoc $course, string $title, int $minutes, int $pass, array $questions, array $options = []): BaiKiemTra
    {
        $quiz = $course->quizzes()->create(['title' => $title, 'duration_minutes' => $minutes, 'pass_score' => $pass] + $options);
        foreach ($questions as [$text, $answers, $correct]) {
            $q = $quiz->questions()->create([
                'question_text' => $text,
                'type' => is_array($correct) ? CauHoi::MULTIPLE : CauHoi::SINGLE,
            ]);
            foreach ($answers as $i => $a) {
                $q->answers()->create(['answer_text' => $a, 'is_correct' => in_array($i, (array) $correct, true)]);
            }
        }

        return $quiz;
    }

    private function taoKhoaHoc(): void
    {
        $laravel = $this->taoMotKhoaHoc('gv1@elearning.test', 'Lập trình web', 'Laravel cơ bản cho người mới bắt đầu',
            "Làm quen với framework Laravel: mô hình MVC, định tuyến, Blade, Eloquent và xây dựng một ứng dụng web hoàn chỉnh.\nPhù hợp cho sinh viên đã biết PHP cơ bản.",
            KhoaHoc::STATUS_PUBLISHED, [
                ['title' => 'Chương 1: Làm quen với Laravel', 'lessons' => [
                    ['Giới thiệu Laravel và mô hình MVC', "Laravel là framework PHP mã nguồn mở theo mô hình MVC:\n- Model: làm việc với cơ sở dữ liệu\n- View: giao diện (Blade)\n- Controller: xử lý nghiệp vụ", 'https://www.youtube.com/watch?v=ImtZ5yENzgE'],
                    ['Cài đặt môi trường với Laragon và Composer', "Các bước:\n1. Cài Laragon\n2. Chạy: composer create-project laravel/laravel ten-du-an\n3. Cấu hình file .env", null],
                    ['Route và Controller', "Route nhận yêu cầu và gọi Controller. Ví dụ: Route::get('/courses', [CourseController::class, 'index']);", null],
                ]],
                ['title' => 'Chương 2: Cơ sở dữ liệu với Eloquent', 'lessons' => [
                    ['Migration và Seeder', 'Migration mô tả cấu trúc bảng bằng mã PHP, giúp cả nhóm đồng bộ cơ sở dữ liệu. Seeder tạo dữ liệu mẫu.', null],
                    ['Model và quan hệ', 'Các quan hệ phổ biến: hasMany, belongsTo, belongsToMany. Ví dụ: một khóa học thuộc một danh mục.', null],
                    ['Blade template', 'Blade cho phép dùng @extends, @section, @foreach, @if... để dựng giao diện từ dữ liệu.', null],
                ]],
            ]);
        $this->taoBaiKiemTra($laravel, 'Kiểm tra Chương 1: Nhập môn Laravel', 10, 60, [
            ['Laravel thuộc mô hình kiến trúc nào?', ['MVC', 'MVVM', 'Microservices', 'Client-Server thuần'], 0],
            ['Công cụ nào dùng để cài Laravel?', ['npm', 'Composer', 'pip', 'Maven'], 1],
            ['File nào chứa cấu hình kết nối cơ sở dữ liệu?', ['routes/web.php', 'composer.json', '.env', 'artisan'], 2],
            ['Lệnh nào tạo bảng từ các Migration?', ['php artisan migrate', 'php artisan serve', 'php artisan make:model', 'composer update'], 0],
            ['Template engine mặc định của Laravel là gì?', ['Twig', 'Smarty', 'Blade', 'Mustache'], 2],
        ]);
        // Bài demo cho các tính năng nâng cấp: xáo trộn, tối đa 2 lượt, có câu nhiều đáp án.
        $this->taoBaiKiemTra($laravel, 'Kiểm tra Chương 2: Eloquent và Blade', 15, 60, [
            ['Những lệnh nào là lệnh Artisan? (chọn tất cả đáp án đúng)', ['php artisan migrate', 'npm run build', 'php artisan make:model', 'composer install'], [0, 2]],
            ['Những quan hệ nào có trong Eloquent? (chọn tất cả đáp án đúng)', ['hasMany', 'belongsTo', 'joinWith', 'belongsToMany'], [0, 1, 3]],
            ['Thư mục nào chứa các file view Blade?', ['app/Models', 'resources/views', 'routes', 'public'], 1],
            ['Lệnh nào tạo một Model mới?', ['php artisan make:model', 'php artisan new:model', 'composer model', 'php artisan serve'], 0],
            ['Cú pháp Blade nào dùng để kế thừa layout?', ['@include', '@extends', '@yield', '@php'], 1],
        ], ['shuffle_questions' => true, 'max_attempts' => 2]);

        $mysql = $this->taoMotKhoaHoc('gv2@elearning.test', 'Cơ sở dữ liệu', 'MySQL từ cơ bản đến thực hành',
            'Thiết kế cơ sở dữ liệu quan hệ, khóa chính/khóa ngoại và các câu truy vấn SQL thường dùng.',
            KhoaHoc::STATUS_PUBLISHED, [
                ['title' => 'Chương 1: Nền tảng CSDL quan hệ', 'lessons' => [
                    ['Bảng, khóa chính, khóa ngoại', 'Mỗi bảng có khóa chính định danh duy nhất từng dòng. Khóa ngoại liên kết dữ liệu giữa các bảng.', null],
                    ['Thiết kế ERD', 'ERD thể hiện thực thể và quan hệ 1-1, 1-N, N-N. Quan hệ N-N cần bảng trung gian (ví dụ: enrollments).', null],
                ]],
                ['title' => 'Chương 2: Truy vấn SQL', 'lessons' => [
                    ['SELECT, WHERE, ORDER BY', 'SELECT * FROM courses WHERE status = \'published\' ORDER BY created_at DESC;', null],
                    ['JOIN và GROUP BY', 'Dùng JOIN để ghép bảng và GROUP BY cùng COUNT/AVG để thống kê.', null],
                ]],
            ]);
        $this->taoBaiKiemTra($mysql, 'Kiểm tra thiết kế CSDL', 8, 50, [
            ['Quan hệ nhiều-nhiều giữa học viên và khóa học cần gì?', ['Thêm cột vào bảng users', 'Một bảng trung gian', 'Không cần gì', 'Một khóa chính kép trong courses'], 1],
            ['Ràng buộc nào ngăn trùng email?', ['PRIMARY KEY', 'UNIQUE', 'DEFAULT', 'INDEX thường'], 1],
            ['Câu lệnh nào dùng để ghép hai bảng?', ['MERGE', 'JOIN', 'UNION ALL', 'COMBINE'], 1],
        ]);

        $this->taoMotKhoaHoc('gv1@elearning.test', 'Công cụ & quy trình', 'Git và GitHub cho làm việc nhóm',
            'Quản lý phiên bản, nhánh, pull request và quy trình phối hợp trong nhóm phát triển phần mềm.',
            KhoaHoc::STATUS_PUBLISHED, [
                ['title' => 'Chương 1: Git cơ bản', 'lessons' => [
                    ['Commit, branch, merge', 'Các lệnh nền tảng: git add, git commit, git branch, git merge.', null],
                    ['Pull request và review code', 'Mỗi tính năng làm trên một nhánh riêng, mở pull request để cả nhóm review trước khi gộp.', null],
                ]],
            ]);

        $this->taoMotKhoaHoc('gv2@elearning.test', 'Kỹ năng mềm', 'Kỹ năng thuyết trình đồ án (bản nháp)',
            'Khóa học đang soạn, chưa công khai.', KhoaHoc::STATUS_DRAFT, [
                ['title' => 'Chương 1: Chuẩn bị', 'lessons' => [['Xây dựng kịch bản demo', 'Nội dung đang cập nhật.', null]]],
            ]);
    }

    private function taoDuLieuHoatDong(): void
    {
        $students = NguoiDung::where('role', 'student')->orderBy('id')->get();
        $laravel = KhoaHoc::where('title', 'like', 'Laravel%')->first();
        $mysql = KhoaHoc::where('title', 'like', 'MySQL%')->first();
        $git = KhoaHoc::where('title', 'like', 'Git%')->first();

        $plan = [
            [$laravel, $students[0], 5], [$laravel, $students[1], 3], [$laravel, $students[2], 1], [$laravel, $students[3], 0],
            [$mysql, $students[0], 2], [$mysql, $students[2], 4], [$git, $students[1], 1], [$git, $students[4], 2],
        ];

        foreach ($plan as [$course, $student, $doneLessons]) {
            DangKyKhoaHoc::create([
                'user_id' => $student->id, 'course_id' => $course->id,
                'enrolled_at' => now()->subDays(rand(1, 20)), 'status' => 'active',
            ]);
            foreach ($course->lessons()->orderBy('sections.sort_order')->orderBy('lessons.sort_order')->take($doneLessons)->get() as $lesson) {
                TienDoBaiHoc::create([
                    'user_id' => $student->id, 'lesson_id' => $lesson->id,
                    'is_completed' => true, 'completed_at' => now()->subDays(rand(0, 10)),
                ]);
            }
        }

        // Một số lượt làm bài đã chấm điểm (đúng số câu cho trước).
        $this->taoLuotLam($laravel->quizzes->first(), $students[0], 4);
        $this->taoLuotLam($laravel->quizzes->first(), $students[1], 2);
        $this->taoLuotLam($mysql->quizzes->first(), $students[2], 3);
    }

    private function taoLuotLam(BaiKiemTra $quiz, NguoiDung $student, int $correctCount): void
    {
        $quiz->load('questions.answers');
        $total = $quiz->questions->count();
        $score = round($correctCount / $total * 100, 2);
        $attempt = LuotLamBai::create([
            'quiz_id' => $quiz->id, 'user_id' => $student->id, 'score' => $score,
            'correct_count' => $correctCount, 'total_questions' => $total,
            'passed' => $score >= $quiz->pass_score,
            'started_at' => now()->subDays(2)->subMinutes(8), 'submitted_at' => now()->subDays(2),
        ]);

        foreach ($quiz->questions->values() as $i => $q) {
            $pick = $i < $correctCount
                ? $q->answers->firstWhere('is_correct', true)
                : $q->answers->firstWhere('is_correct', false);
            $attempt->answers()->create(['question_id' => $q->id, 'answer_id' => $pick->id]);
        }
    }
}
