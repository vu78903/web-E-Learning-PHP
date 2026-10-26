<?php

namespace App\Models;

use App\Models\DanhGiaKhoaHoc;
use App\Notifications\DatLaiMatKhauNotification;
use App\Notifications\XacMinhEmailNotification;
use Database\Factories\NguoiDungFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable implements MustVerifyEmail
{
    protected $table = 'users';

    /** @use HasFactory<NguoiDungFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_INSTRUCTOR = 'instructor';

    public const ROLE_STUDENT = 'student';

    public const ROLES = [
        self::ROLE_ADMIN => 'Quản trị viên',
        self::ROLE_INSTRUCTOR => 'Giảng viên',
        self::ROLE_STUDENT => 'Học viên',
    ];

    // Lưu ý: 'role' KHÔNG nằm trong $fillable để người dùng không tự nâng quyền qua form.
    protected $fillable = ['name', 'email', 'phone', 'password', 'bio', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function laQuanTri(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function laGiangVien(): bool
    {
        return $this->role === self::ROLE_INSTRUCTOR;
    }

    public function laHocVien(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    /** URL ảnh đại diện (null nếu chưa upload). */
    public function duongDanAnhDaiDien(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    public function tenVaiTro(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /** Khóa học do giảng viên này phụ trách. */
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(KhoaHoc::class, 'instructor_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(DangKyKhoaHoc::class, 'user_id');
    }

    /** Các khóa học mà học viên đã đăng ký (quan hệ nhiều-nhiều qua enrollments). */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(KhoaHoc::class, 'enrollments', 'user_id', 'course_id')
            ->withPivot('enrolled_at', 'status')
            ->withTimestamps();
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(LuotLamBai::class, 'user_id');
    }

    /** Các đánh giá khóa học do người dùng viết. */
    public function danhGiaKhoaHoc(): HasMany
    {
        return $this->hasMany(DanhGiaKhoaHoc::class, 'user_id');
    }

    /** Gửi email xác minh đăng ký bằng nội dung tiếng Việt. */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new XacMinhEmailNotification);
    }

    /** Gửi email đặt lại mật khẩu bằng nội dung tiếng Việt. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new DatLaiMatKhauNotification($token));
    }

    public function daDangKyKhoaHoc(KhoaHoc $course): bool
    {
        return $this->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();
    }
}
