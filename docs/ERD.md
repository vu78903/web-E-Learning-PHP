# Sơ đồ ERD và mô tả cơ sở dữ liệu

Hiển thị sơ đồ bằng GitHub, VS Code (extension *Markdown Preview Mermaid Support*) hoặc dán vào <https://mermaid.live>.

```mermaid
erDiagram
    users ||--o{ courses : "giảng viên (instructor_id)"
    categories ||--o{ courses : "có"
    users ||--o{ enrollments : "đăng ký"
    courses ||--o{ enrollments : "được đăng ký"
    courses ||--o{ sections : "gồm"
    sections ||--o{ lessons : "gồm"
    users ||--o{ lesson_progress : "học"
    lessons ||--o{ lesson_progress : "được học"
    courses ||--o{ quizzes : "có"
    quizzes ||--o{ questions : "gồm"
    questions ||--o{ answers : "có phương án"
    users ||--o{ quiz_attempts : "làm bài"
    quizzes ||--o{ quiz_attempts : "được làm"
    users ||--o{ course_reviews : "viết"
    courses ||--o{ course_reviews : "được đánh giá"
    quiz_attempts ||--o{ quiz_attempt_answers : "lưu lựa chọn"
    questions ||--o{ quiz_attempt_answers : ""
    answers ||--o{ quiz_attempt_answers : "được chọn"

    users { bigint id PK
        string name
        string email UK
        string phone UK "10 số"
        string password
        string role "admin|instructor|student"
        boolean is_active
        text bio
        string avatar }
    categories { bigint id PK
        string name UK
        text description }
    courses { bigint id PK
        bigint category_id FK
        bigint instructor_id FK
        string title
        text description
        string thumbnail
        string status "draft|published|hidden" }
    enrollments { bigint id PK
        bigint user_id FK
        bigint course_id FK
        timestamp enrolled_at
        string status "active|cancelled" }
    sections { bigint id PK
        bigint course_id FK
        string title
        int sort_order }
    lessons { bigint id PK
        bigint section_id FK
        string title
        longtext content
        string video_url
        string document_url
        int sort_order }
    lesson_progress { bigint id PK
        bigint user_id FK
        bigint lesson_id FK
        boolean is_completed
        timestamp completed_at }
    quizzes { bigint id PK
        bigint course_id FK
        string title
        int duration_minutes
        tinyint pass_score "0-100 (%)" }
    questions { bigint id PK
        bigint quiz_id FK
        text question_text }
    answers { bigint id PK
        bigint question_id FK
        string answer_text
        boolean is_correct }
    quiz_attempts { bigint id PK
        bigint quiz_id FK
        bigint user_id FK
        decimal score "thang 100"
        int correct_count
        int total_questions
        boolean passed
        timestamp started_at
        timestamp submitted_at }
    quiz_attempt_answers { bigint id PK
        bigint attempt_id FK
        bigint question_id FK
        bigint answer_id FK }
    course_reviews { bigint id PK
        bigint user_id FK
        bigint course_id FK
        tinyint so_sao "1-5"
        text binh_luan }
```

Ngoài 13 bảng nghiệp vụ, Laravel dùng thêm các bảng hệ thống: `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`.

## Ràng buộc chính

| Ràng buộc | Mục đích |
|---|---|
| `users.email` UNIQUE | không trùng tài khoản |
| `enrollments (user_id, course_id)` UNIQUE | **ngăn đăng ký trùng** |
| `lesson_progress (user_id, lesson_id)` UNIQUE | mỗi học viên một bản ghi tiến độ cho mỗi bài |
| `quiz_attempt_answers (attempt_id, question_id)` UNIQUE | mỗi câu một lựa chọn trong một lượt làm bài |
| `course_reviews (user_id, course_id)` UNIQUE | mỗi học viên chỉ có một đánh giá cho mỗi khóa học |
| `categories.name` UNIQUE | không trùng danh mục |
| Khóa ngoại `courses.category_id` → `RESTRICT` | không xóa danh mục còn khóa học |
| Các khóa ngoại còn lại → `CASCADE` | xóa khóa học kéo theo chương, bài học, đăng ký, đề thi... |
| `quiz_attempt_answers.answer_id` → `SET NULL` | xóa phương án không làm mất lịch sử làm bài |

Tiến độ khóa học = số bài đã hoàn thành ÷ tổng số bài của khóa học × 100 (làm tròn), tính tại `Course::progressFor()`.
Điểm bài kiểm tra = số câu đúng ÷ tổng số câu × 100; đạt khi `score >= pass_score`.

## Quan hệ N-N

Học viên ↔ Khóa học là quan hệ nhiều-nhiều qua bảng `enrollments` (khai báo bằng `belongsToMany` ở `User::enrolledCourses()` và `Course::students()`).
