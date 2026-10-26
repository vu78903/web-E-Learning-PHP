# Báo cáo kiểm thử

Bộ kiểm thử tự động được gom trong `tests/Feature`, chạy bằng `php artisan test`. Test dùng SQLite trong bộ nhớ nên mỗi test bắt đầu với CSDL sạch.

Kết quả lần chạy gần nhất: **79 đạt / 0 lỗi, 408 assertions** bằng PHPUnit 11. Ngoài ra toàn bộ Blade template đã biên dịch thành công bằng `php artisan view:cache`.

## Đối chiếu với danh sách kiểm thử ở mục 7 của kế hoạch

| Hạng mục kế hoạch | Test tương ứng |
|---|---|
| Đăng ký và đăng nhập hợp lệ | `AuthAndRolesTest::test_user_can_register_as_student_and_is_logged_in`, `test_login_logout_and_wrong_password`, `test_inactive_user_cannot_login` |
| Người dùng không có quyền bị chặn khi vào trang quản trị | `test_non_admin_cannot_access_admin_area`, `test_student_cannot_access_instructor_area`, `test_guests_are_redirected_to_login`, `AdminTest::test_non_admin_cannot_change_course_status_or_users` |
| Giảng viên tạo và sửa được khóa học của mình (và không sửa được của người khác) | `CourseTest::test_instructor_creates_course_owned_by_self`, `test_instructor_can_edit_own_course_but_not_others` |
| Học viên đăng ký khóa học và không bị đăng ký trùng | `CourseTest::test_student_can_enroll_and_duplicate_enrollment_is_prevented`, `test_database_unique_key_blocks_duplicate_enrollment` |
| Chỉ học viên đã đăng ký mới xem được nội dung khóa học | `LearningTest::test_only_enrolled_student_can_view_content`, `test_cancelled_enrollment_loses_access`, `test_lesson_must_belong_to_course`, `test_cannot_complete_lesson_of_unenrolled_course` |
| Tiến độ thay đổi khi đánh dấu hoàn thành bài học | `LearningTest::test_progress_changes_when_lessons_marked_complete`, `test_progress_is_per_student`, `test_progress_of_empty_course_is_zero` |
| Hệ thống chấm điểm đúng theo đáp án | `QuizTest::test_full_flow_grades_correctly_and_stores_history`, `test_failing_score_and_boundary_pass_mark`, `test_unanswered_questions_score_zero`, `test_answer_from_another_question_does_not_count` |
| Điểm và lịch sử làm bài được lưu chính xác | `QuizTest::test_full_flow_grades_correctly_and_stores_history` (kiểm tra `quiz_attempts` và `quiz_attempt_answers`), `test_cannot_submit_twice`, `test_start_resumes_unfinished_attempt` |
| Dashboard hiển thị số liệu đúng | `AdminTest::test_dashboard_shows_correct_numbers` |
| Đăng ký gửi email và bắt buộc xác minh | `Vu2001231056Test::test_dang_ky_gui_email_va_bat_buoc_xac_minh` |
| Quên/đặt lại mật khẩu qua email | `Vu2001231056Test::test_hoc_vien_co_the_dat_lai_mat_khau_qua_email` |
| Đánh giá sao và bình luận qua AJAX | `Vu2001231056Test::test_hoc_vien_da_dang_ky_co_the_danh_gia_bang_ajax` |
| Email đăng ký khóa học và kết quả thi | `Vu2001231056Test::test_gui_email_khi_dang_ky_khoa_hoc_va_co_ket_qua_thi` |
| CSDL và chức năng hoạt động sau khi cài đặt lại từ hướng dẫn | Chạy `php artisan migrate:fresh --seed` rồi đăng nhập các tài khoản demo (xem README, mục 2–3) |

## Các tình huống bảo mật / biên khác đã kiểm tra

- Ràng buộc tài khoản: email sai cú pháp (thiếu `@`, thiếu đuôi tên miền, có khoảng trắng...) bị từ chối; số điện thoại phải đủ 10 số đúng đầu số; mật khẩu thiếu chữ hoa/thường/số/ký tự đặc biệt hoặc quá ngắn bị từ chối; email và SĐT không trùng; đăng nhập quá 10 lần/phút trả về 429 (`AuthAndRolesTest`).
- Đăng ký không thể chọn vai trò admin; hồ sơ không thể tự nâng quyền qua trường `role`.
- Giảng viên không tự đặt trạng thái "ẩn" và không thể công khai lại khóa học bị admin ẩn.
- Admin không thể tự hạ quyền, tự khóa hoặc tự xóa chính mình; không xóa giảng viên còn khóa học; không xóa danh mục còn khóa học.
- Tài khoản bị khóa bị đăng xuất ở request kế tiếp.
- Khóa học nháp/ẩn trả về 404 với người không phải chủ khóa học hoặc admin.
- Nội dung bài học chứa `<script>` được escape; URL video/tài liệu sai định dạng hoặc dạng `javascript:` bị từ chối.
- Trang làm bài không chứa thông tin đáp án đúng; học viên khác không truy cập được lượt làm bài của người khác.
- Nộp bài sau khi hết giờ (quá 60 giây ân hạn) không được tính điểm; lượt quá hạn tự đóng khi mở lại.
- Không thể dùng `id` phương án của câu hỏi khác để "chiếm" khi sửa câu hỏi.

## Giới hạn đã biết

- Test chạy trên SQLite; ứng dụng dùng MySQL khi triển khai. Migration chỉ dùng kiểu dữ liệu chuẩn của Laravel nên tương thích MySQL/MariaDB, nhưng nhóm nên chạy `php artisan migrate:fresh --seed` trên MySQL thật của Laragon một lần để xác nhận trước khi bảo vệ.
- Chưa có kiểm thử giao diện trên trình duyệt (chỉ kiểm tra HTML trả về của server).
