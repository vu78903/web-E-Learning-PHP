<?php

namespace App\ChucNang;

use Illuminate\Support\Facades\Http;
use Throwable;

/** Lấy thông tin video từ YouTube oEmbed, không cần API key. */
class LayThongTinYoutube
{
    private const OEMBED_URL = 'https://www.youtube.com/oembed';

    /**
     * @return array{id_video: string, tieu_de: string, anh_dai_dien: string, tac_gia: ?string}|null
     */
    public function lay(string $url): ?array
    {
        $idVideo = $this->layIdVideo($url);
        if ($idVideo === null) {
            return null;
        }

        try {
            $phanHoi = Http::acceptJson()->timeout(5)->retry(2, 150)
                // Chuẩn hóa về watch URL để oEmbed xử lý đồng nhất cả link rút gọn,
                // Shorts, Live, Embed và link có tham số thời gian.
                ->get(self::OEMBED_URL, [
                    'url' => "https://www.youtube.com/watch?v={$idVideo}",
                    'format' => 'json',
                ]);

            if (! $phanHoi->successful()) {
                return null;
            }

            $tieuDe = trim((string) $phanHoi->json('title'));
            if ($tieuDe === '') {
                return null;
            }

            return [
                'id_video' => $idVideo,
                'tieu_de' => $tieuDe,
                'anh_dai_dien' => (string) ($phanHoi->json('thumbnail_url') ?: "https://i.ytimg.com/vi/{$idVideo}/hqdefault.jpg"),
                'tac_gia' => $phanHoi->json('author_name'),
            ];
        } catch (Throwable $loi) {
            report($loi);

            return null;
        }
    }

    public function laLinkYoutube(?string $url): bool
    {
        return $url !== null && $this->layIdVideo($url) !== null;
    }

    public function layIdVideo(string $url): ?string
    {
        $url = trim($url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);
        $duongDan = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $id = null;

        if ($host === 'youtu.be') {
            $id = explode('/', $duongDan)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $truyVan);
            $id = $truyVan['v'] ?? null;

            if (! $id && preg_match('~^(?:embed|shorts|live)/([A-Za-z0-9_-]{6,20})~', $duongDan, $ketQua)) {
                $id = $ketQua[1];
            }
        }

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id) ? $id : null;
    }
}
