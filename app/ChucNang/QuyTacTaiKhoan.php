<?php

namespace App\ChucNang;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Bộ ràng buộc dữ liệu tài khoản dùng chung cho đăng ký, hồ sơ cá nhân và trang quản trị.
 * Gom một chỗ để các form luôn kiểm tra giống nhau.
 */
class QuyTacTaiKhoan
{
    /** Số điện thoại di động Việt Nam: đúng 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09. */
    public const PHONE_REGEX = '/^0[35789][0-9]{8}$/';

    /** Họ tên: chỉ gồm chữ cái (có dấu), khoảng trắng, dấu chấm, gạch nối, dấu nháy. */
    public const NAME_REGEX = "/^[\\p{L}][\\p{L}\\s.'\\-]*$/u";

    public static function hoTen(): array
    {
        return ['required', 'string', 'min:2', 'max:100', 'regex:'.self::NAME_REGEX];
    }

    /** Email phải đúng cú pháp (có @, tên miền có dấu chấm và đuôi ≥ 2 ký tự), không phân biệt hoa thường. */
    public static function email(?int $ignoreUserId = null): array
    {
        return [
            'required', 'string', 'max:255', 'email:rfc',
            'regex:/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/',
            Rule::unique('users', 'email')->ignore($ignoreUserId),
        ];
    }

    public static function soDienThoai(bool $batBuoc, ?int $boQuaNguoiDungId = null): array
    {
        return [
            $batBuoc ? 'required' : 'nullable', 'string', 'regex:'.self::PHONE_REGEX,
            Rule::unique('users', 'phone')->ignore($boQuaNguoiDungId),
        ];
    }

    /** Mật khẩu: 8–64 ký tự, có chữ hoa, chữ thường, chữ số và ký tự đặc biệt. */
    public static function matKhau(): Password
    {
        return Password::min(8)->max(64)->letters()->mixedCase()->numbers()->symbols();
    }

    /** Chuẩn hóa đầu vào trước khi kiểm tra: bỏ khoảng trắng thừa, email viết thường, SĐT chỉ giữ chữ số. */
    public static function chuanHoa(Request $request): void
    {
        $merge = [];
        if ($request->has('name')) {
            $merge['name'] = trim(preg_replace('/\s+/u', ' ', (string) $request->input('name')));
        }
        if ($request->has('email')) {
            $merge['email'] = mb_strtolower(trim((string) $request->input('email')));
        }
        if ($request->has('phone')) {
            $merge['phone'] = preg_replace('/[\s.\-]/', '', (string) $request->input('phone')) ?: null;
        }
        $request->merge($merge);
    }
}
