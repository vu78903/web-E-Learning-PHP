<?php

namespace App\ChucNang;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * File: XuatBaoCaoCsv.php
 * Tạo file CSV tải về. Ghi BOM UTF-8 để Excel hiển thị đúng tiếng Việt có dấu.
 */
class XuatBaoCaoCsv
{
    /**
     * @param  list<string>  $tieuDe
     * @param  iterable<array<int, mixed>>  $cacDong
     */
    public static function taiVe(string $tenFile, array $tieuDe, iterable $cacDong): StreamedResponse
    {
        $tenFile = Str::slug($tenFile).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($tieuDe, $cacDong) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $tieuDe, ',', '"', '');
            foreach ($cacDong as $dong) {
                fputcsv($out, array_map([self::class, 'anToan'], $dong), ',', '"', '');
            }
            fclose($out);
        }, $tenFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Chống "CSV injection": chuỗi do người dùng nhập bắt đầu bằng = + - @
     * có thể bị Excel hiểu là công thức, nên thêm dấu ' phía trước.
     */
    public static function anToan(mixed $giaTri): mixed
    {
        if (is_string($giaTri) && $giaTri !== '' && in_array($giaTri[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$giaTri;
        }

        return $giaTri;
    }

    /** Hiển thị điểm gọn: 75.00 -> 75, 66.67 -> 66.67. */
    public static function diem(?float $diem): string
    {
        return $diem === null ? '' : rtrim(rtrim(number_format($diem, 2, '.', ''), '0'), '.');
    }
}
