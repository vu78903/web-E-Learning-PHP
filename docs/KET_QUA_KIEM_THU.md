# Kết quả kiểm thử bản hoàn thiện

Ngày chạy: 26/10/2026

## Tổng thể

- 121/121 test đạt.
- 650 assertions đạt.
- 79 route web được đăng ký hợp lệ.
- Toàn bộ file PHP trong `app`, `database`, `routes`, `tests`, `config` qua kiểm tra cú pháp.
- `composer.json` hợp lệ.

## Chức năng YouTube oEmbed

File test: `tests/Feature/ThongTinYoutubeTest.php` — 10 test.

- Nhận dạng link `watch`, `youtu.be`, `shorts`, `live`, `embed`.
- Từ chối host giả mạo.
- Chuẩn hóa URL trước khi gọi oEmbed.
- Trả tiêu đề, thumbnail, tác giả cho giao diện.
- Tự dùng tiêu đề video khi tiêu đề bài học trống.
- Giữ tiêu đề do giảng viên nhập.
- Lưu và xóa metadata đúng khi đổi loại video.
- Từ chối video không tồn tại hoặc oEmbed lỗi.
- Chỉ giảng viên/admin được gọi endpoint quản trị.
- Tạo đúng URL nhúng cho mọi dạng link được hỗ trợ.

## Upload tài liệu PDF

File test: `tests/Feature/TaiLieuPdfTest.php` — 8 test.

- Upload một hoặc nhiều PDF và lưu metadata.
- Bổ sung PDF khi sửa bài học.
- Từ chối file giả PDF, sai đuôi hoặc sai MIME.
- Từ chối file trên 10 MB và trên 10 file/lần.
- Phân quyền xem/tải cho khách, học viên chưa đăng ký, học viên đã đăng ký, giảng viên và admin.
- Kiểm tra header xem trực tiếp và tải xuống.
- Xóa tài liệu xóa cả bản ghi và file.
- Xóa bài học/chương/khóa học tự dọn file liên quan.

## Xác minh email và quên mật khẩu

File test chuyên sâu: `tests/Feature/XacMinhEmailVaMatKhauTest.php` — 6 test; ngoài ra còn test tích hợp trong `DanhGiaVaEmailTest.php`.

- Gửi lại email và kiểm tra nội dung có URL ký hợp lệ.
- Từ chối link bị sửa hoặc đã hết hạn.
- Link hợp lệ chỉ xác minh đúng tài khoản.
- Email quên mật khẩu được chuẩn hóa chữ thường và khoảng trắng.
- Dùng token trong chính notification để đặt lại mật khẩu.
- Token bị hủy sau khi dùng; token sai hoặc dùng lại bị từ chối.
- Đăng nhập thành công bằng mật khẩu mới.
- Không gửi link cho email không tồn tại, tài khoản bị khóa hoặc tài khoản admin.

## SMTP

Laravel đã đọc đúng mailer SMTP, host Gmail, cổng 587, địa chỉ gửi và mật khẩu ứng dụng 16 ký tự từ `.env`. Môi trường kiểm thử hiện tại chặn DNS/kết nối mạng đi `smtp.gmail.com`, vì vậy không thể xác nhận giao thư ra Internet tại đây. Khi chạy bằng XAMPP có Internet, có thể thử trực tiếp bằng luồng đăng ký hoặc quên mật khẩu.

Không ghi mật khẩu SMTP vào tài liệu, `.env.example` hay mã nguồn. Mật khẩu chỉ nằm trong file `.env` cục bộ theo yêu cầu.
