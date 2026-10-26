# E-Learning Mini

Website quản lý học tập trực tuyến viết bằng Laravel 12, Blade, Bootstrap 5, MySQL và Chart.js. Hệ thống có ba vai trò: quản trị viên, giảng viên và học viên.

## Chức năng hiện có

| STT | Chức năng | Trạng thái |
|---:|---|---|
| 1 | Dán link YouTube, tự lấy tiêu đề và thumbnail bằng YouTube oEmbed, không cần API key | Hoàn thành |
| 2 | Xác minh email khi đăng ký và quên/đặt lại mật khẩu qua email | Hoàn thành |
| 3 | Biểu đồ dashboard: đăng ký theo tuần, phân bố điểm, top khóa học | Hoàn thành |
| 4 | Đánh giá sao và bình luận khóa học bằng AJAX | Hoàn thành |
| 5 | Chứng chỉ hoàn thành, in hoặc lưu PDF, có mã xác thực | Hoàn thành |
| 6 | Email khi đăng ký khóa học và khi có kết quả bài thi | Hoàn thành |
| 7 | Xuất CSV danh sách học viên, bảng điểm và kết quả bài thi | Hoàn thành |
| 8 | Upload nhiều tài liệu PDF cho bài học; xem nhanh, mở, tải và xóa có phân quyền | Hoàn thành |
| 9 | Xáo trộn câu hỏi/phương án, giới hạn lượt làm và câu nhiều đáp án đúng | Hoàn thành |

Ngoài ra, hệ thống có CRUD khóa học/chương/bài học/bài kiểm tra, đăng ký học, theo dõi tiến độ, quản lý người dùng và danh mục, upload ảnh, tìm kiếm/lọc bằng AJAX và giao diện thống nhất theo vai trò.

## Cấu trúc mã nguồn đã làm sạch

Các thư mục theo tên thành viên cũ đã được loại bỏ. Mã chức năng dùng chung được gom về một nơi:

```text
app/
├── ChucNang/                   # oEmbed, email, biểu đồ, CSV, chứng chỉ, quy tắc tài khoản
├── Http/
│   ├── Controllers/            # controller tên tiếng Việt
│   └── Middleware/             # middleware tên tiếng Việt
├── Models/                     # model tên tiếng Việt
├── Notifications/              # thông báo email
└── Policies/                   # chính sách phân quyền

database/
├── migrations/
├── seeders/
└── factories/

resources/views/                # giao diện Blade
public/css/elearning.css        # toàn bộ hệ thống giao diện dùng chung
routes/web.php                  # tất cả route web
tests/Feature/                  # toàn bộ feature test
```

Tên file, lớp và hàm nghiệp vụ tự viết dùng tiếng Việt không dấu. Các tên bắt buộc bởi Laravel/PHP như `boot`, `handle`, `toMail`, `register` của service provider và các quan hệ Eloquent chuẩn được giữ theo quy ước framework.

## Cài đặt trên XAMPP

Yêu cầu PHP 8.2+, Composer 2 và MySQL/MariaDB.

```bash
cd "/Applications/XAMPP/htdocs/do an/elearning"
composer install
cp .env.example .env
php artisan key:generate
```

Tạo database `elearning` trong phpMyAdmin, sau đó sửa `.env`:

```env
APP_NAME="E-Learning Mini"
APP_ENV=local
APP_DEBUG=true
APP_URL="http://localhost/do%20an/elearning/public"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elearning
DB_USERNAME=root
DB_PASSWORD=
```

Tạo bảng, dữ liệu demo và liên kết thư mục ảnh:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
```

Mở: `http://localhost/do%20an/elearning/public`

Nếu dùng Apache VirtualHost, nên đặt `DocumentRoot` thẳng tới thư mục `public` và cập nhật `APP_URL` theo tên miền local. Đây là cách ổn định nhất cho asset và link email có chữ ký.

## Cấu hình gửi email thật

Mặc định `MAIL_MAILER=log`: email được ghi vào `storage/logs/laravel.log`, phù hợp để thử local nhưng không gửi tới hộp thư.

Ví dụ cấu hình Gmail SMTP:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_TIMEOUT=10
MAIL_USERNAME=your-account@gmail.com
MAIL_PASSWORD="mat-khau-ung-dung-16-ky-tu"
MAIL_FROM_ADDRESS=your-account@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Sau khi đổi `.env`:

```bash
php artisan optimize:clear
```

Gmail yêu cầu bật xác minh hai bước và tạo **Mật khẩu ứng dụng**; không dùng mật khẩu đăng nhập Gmail. `APP_URL` phải đúng URL đang mở website vì link xác minh email có chữ ký và hết hạn sau 60 phút.

Hai quy trình đã được kiểm thử:

- Đăng ký → gửi email → chặn chức năng nội bộ khi chưa xác minh → mở link có chữ ký → xác minh thành công.
- Quên mật khẩu → gửi token → mở form từ email → đặt mật khẩu mới → token bị hủy → đăng nhập bằng mật khẩu mới.

Hệ thống không tiết lộ email có tồn tại hay không trên form quên mật khẩu, giới hạn số lần gửi và từ chối link sai, link hết hạn, token sai hoặc token đã dùng.

## YouTube oEmbed

Trong form tạo/sửa bài học, dán một trong các dạng sau:

```text
https://www.youtube.com/watch?v=VIDEO_ID
https://youtu.be/VIDEO_ID
https://youtube.com/shorts/VIDEO_ID
https://youtube.com/live/VIDEO_ID
https://youtube.com/embed/VIDEO_ID
```

Giao diện tự gọi endpoint nội bộ để hiển thị tiêu đề, tác giả và thumbnail. Khi lưu, máy chủ gọi lại oEmbed để không tin dữ liệu phía trình duyệt. Host giả như `youtube.com.evil.test` bị từ chối. Nếu ô tiêu đề trống, tiêu đề video được dùng làm tên bài học; nếu giảng viên đã nhập tiêu đề thì hệ thống giữ nguyên.

## Tài liệu PDF bài học

- Tối đa 10 file cho mỗi lần upload.
- Tối đa 10 MB mỗi file.
- Kiểm tra cả đuôi `.pdf`, MIME khai báo và MIME từ nội dung thật.
- File nằm trên disk riêng tư `lesson_files`, không đặt công khai trong `public`.
- Chỉ học viên đã đăng ký, giảng viên sở hữu khóa học hoặc admin được xem/tải.
- Xóa tài liệu, bài học, chương hoặc khóa học sẽ dọn cả bản ghi và file trên đĩa.

## Tài khoản demo

Tất cả dùng mật khẩu `password`:

| Vai trò | Email |
|---|---|
| Quản trị viên | `admin@elearning.test` |
| Giảng viên | `gv1@elearning.test`, `gv2@elearning.test` |
| Học viên | `hv1@elearning.test` đến `hv5@elearning.test` |

## Chạy kiểm thử

```bash
php artisan test
```

Test dùng SQLite trong bộ nhớ, không ảnh hưởng database MySQL. Các nhóm test quan trọng:

```bash
php artisan test tests/Feature/ThongTinYoutubeTest.php
php artisan test tests/Feature/TaiLieuPdfTest.php
php artisan test tests/Feature/XacMinhEmailVaMatKhauTest.php
```

## Lưu ý bảo mật

- Không đưa `.env`, mật khẩu SMTP hoặc `APP_KEY` lên Git.
- Quyền truy cập được kiểm tra ở máy chủ bằng middleware và policy.
- Form có CSRF; mật khẩu được băm; role không mass-assign được.
- File PDF không phục vụ trực tiếp từ thư mục public.
- CSV có chống formula injection.
- URL xác minh email dùng chữ ký và thời hạn; token đặt lại mật khẩu chỉ dùng một lần.
