{{-- File: chung-chi/in.blade.php - Chứng chỉ khổ A4 ngang, in trực tiếp hoặc "Lưu dưới dạng PDF" từ hộp thoại in. --}}
@php
    $course = $chungChi->course;
    $urlXacThuc = route('chung_chi.xac_thuc', $chungChi);
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chứng chỉ - {{ $chungChi->user->name }} - {{ $course->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap&subset=vietnamese" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --navy: #1e2a4a; --gold: #b8913a; --gold-light: #e6cf8f; --paper: #fffdf7; --ink: #2b2b2b; --muted: #6b6b6b; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9ebf0; font-family: 'Be Vietnam Pro', 'Segoe UI', Arial, sans-serif; color: var(--ink); }
        .thanh-cong-cu { display: flex; gap: .5rem; justify-content: center; align-items: center; flex-wrap: wrap; padding: 16px; }
        .thanh-cong-cu a, .thanh-cong-cu button { font: inherit; font-size: 14px; border: 1px solid #c9ccd6; background: #fff; color: #222; padding: 8px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; }
        .thanh-cong-cu .chinh { background: var(--navy); border-color: var(--navy); color: #fff; }
        .thanh-cong-cu .ghi-chu { width: 100%; text-align: center; color: var(--muted); font-size: 13px; }
        .thong-bao { max-width: 297mm; margin: 0 auto 12px; padding: 10px 14px; background: #d1e7dd; color: #0f5132; border-radius: 6px; font-size: 14px; }
        .khung-ngoai { overflow-x: auto; padding: 0 16px 32px; }
        .trang { width: 297mm; height: 210mm; margin: 0 auto; background: var(--paper); position: relative; padding: 10mm; box-shadow: 0 6px 24px rgba(0,0,0,.15); }
        .vien { position: absolute; inset: 8mm; border: 2.5mm solid var(--navy); }
        .vien::after { content: ""; position: absolute; inset: 2.5mm; border: .6mm solid var(--gold); }
        .goc { position: absolute; width: 18mm; height: 18mm; border-color: var(--gold); border-style: solid; }
        .goc.tl { top: 14mm; left: 14mm; border-width: 1mm 0 0 1mm; }
        .goc.tr { top: 14mm; right: 14mm; border-width: 1mm 1mm 0 0; }
        .goc.bl { bottom: 14mm; left: 14mm; border-width: 0 0 1mm 1mm; }
        .goc.br { bottom: 14mm; right: 14mm; border-width: 0 1mm 1mm 0; }
        .noi-dung { position: relative; height: 100%; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 16mm 26mm 12mm; }
        .thuong-hieu { font-weight: 600; letter-spacing: .25em; text-transform: uppercase; color: var(--navy); font-size: 11pt; }
        .thuong-hieu i { color: var(--gold); }
        h1 { font-family: 'Playfair Display', Georgia, serif; font-size: 34pt; color: var(--navy); margin: 6mm 0 1mm; letter-spacing: .04em; }
        .phu-de { letter-spacing: .3em; text-transform: uppercase; color: var(--gold); font-size: 10pt; font-weight: 600; }
        .gach { width: 60mm; height: .6mm; background: linear-gradient(90deg, transparent, var(--gold), transparent); margin: 5mm auto; }
        .nhan { color: var(--muted); font-size: 12pt; }
        .ten { font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-size: 32pt; color: var(--ink); margin: 3mm 0 2mm; padding: 0 10mm 2mm; border-bottom: .4mm solid var(--gold-light); }
        .khoa-hoc { font-size: 17pt; font-weight: 600; color: var(--navy); margin: 2mm 0 4mm; max-width: 200mm; }
        .thong-tin { display: flex; gap: 12mm; justify-content: center; color: var(--muted); font-size: 10.5pt; }
        .thong-tin b { color: var(--ink); font-weight: 600; }
        .chan { margin-top: auto; width: 100%; display: grid; grid-template-columns: 1fr auto 1fr; align-items: end; gap: 10mm; }
        .ky { text-align: center; font-size: 10.5pt; }
        .ky .dong-ky { border-top: .3mm solid var(--ink); padding-top: 2mm; margin: 0 6mm; }
        .ky .chuc-danh { color: var(--muted); font-size: 9pt; }
        .ky .chu-ky { font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-size: 16pt; color: var(--navy); min-height: 10mm; }
        .con-dau { width: 30mm; height: 30mm; border-radius: 50%; background: radial-gradient(circle at 35% 30%, #f3dc95, var(--gold) 60%, #8a6a22); color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 0 0 1.2mm var(--paper), 0 0 0 1.8mm var(--gold); font-size: 7.5pt; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .con-dau i { font-size: 15pt; }
        .xac-thuc { display: flex; gap: 3mm; align-items: center; justify-content: flex-end; text-align: left; font-size: 8.5pt; color: var(--muted); }
        .xac-thuc .ma { font-family: ui-monospace, Menlo, Consolas, monospace; color: var(--ink); font-size: 9.5pt; font-weight: 600; }
        #qr { width: 22mm; height: 22mm; }
        #qr img, #qr canvas { width: 22mm !important; height: 22mm !important; }
        .url { word-break: break-all; max-width: 55mm; }
        @page { size: A4 landscape; margin: 0; }
        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .thanh-cong-cu, .thong-bao { display: none !important; }
            .khung-ngoai { padding: 0; overflow: visible; }
            .trang { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>
<div class="thanh-cong-cu">
    <a href="{{ auth()->user()->laHocVien() ? route('learn.show', $course) : route('instructor.courses.students', $course) }}"><i class="bi bi-arrow-left"></i> Quay lại</a>
    <button type="button" class="chinh" onclick="window.print()"><i class="bi bi-printer"></i> In / Lưu PDF</button>
    <a href="{{ $urlXacThuc }}" target="_blank" rel="noopener"><i class="bi bi-patch-check"></i> Trang xác thực</a>
    <div class="ghi-chu">Để xuất PDF: bấm "In / Lưu PDF" → chọn máy in "Lưu dưới dạng PDF" (Save as PDF), khổ A4 ngang, bật "Đồ họa nền" (Background graphics).</div>
</div>
@if(session('success'))<div class="thong-bao">{{ session('success') }}</div>@endif

<div class="khung-ngoai">
<div class="trang">
    <div class="vien"></div>
    <div class="goc tl"></div><div class="goc tr"></div><div class="goc bl"></div><div class="goc br"></div>
    <div class="noi-dung">
        <div class="thuong-hieu"><i class="bi bi-mortarboard-fill"></i> {{ config('app.name') }}</div>
        <h1>CHỨNG CHỈ HOÀN THÀNH</h1>
        <div class="phu-de">Certificate of Completion</div>
        <div class="gach"></div>
        <div class="nhan">Chứng nhận học viên</div>
        <div class="ten">{{ $chungChi->user->name }}</div>
        <div class="nhan">đã hoàn thành xuất sắc khóa học</div>
        <div class="khoa-hoc">{{ $course->title }}</div>
        <div class="thong-tin">
            <span>Số bài học: <b>{{ $soBaiHoc }}</b></span>
            @if($diemKiemTra !== null)
                <span>Điểm kiểm tra trung bình: <b>{{ rtrim(rtrim(number_format($diemKiemTra, 1), '0'), '.') }}/100</b></span>
            @endif
            <span>Ngày cấp: <b>{{ $chungChi->cap_luc->format('d/m/Y') }}</b></span>
        </div>

        <div class="chan">
            <div class="ky">
                <div class="chu-ky">{{ $course->instructor->name }}</div>
                <div class="dong-ky">{{ $course->instructor->name }}</div>
                <div class="chuc-danh">Giảng viên phụ trách</div>
            </div>
            <div class="con-dau"><i class="bi bi-award-fill"></i>Đã xác thực</div>
            <div class="xac-thuc">
                <div>
                    Mã chứng chỉ<br><span class="ma">{{ $chungChi->ma_chung_chi }}</span><br>
                    <span class="url">Xác thực tại: {{ $urlXacThuc }}</span>
                </div>
                <div id="qr" aria-label="Mã QR xác thực chứng chỉ"></div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Mã QR trỏ tới trang xác thực; nếu không có mạng thì vẫn còn mã và đường dẫn dạng chữ. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    if (window.QRCode) {
        new QRCode(document.getElementById('qr'), { text: @json($urlXacThuc), width: 160, height: 160, colorDark: '#1e2a4a', colorLight: '#fffdf7' });
    }
</script>
</body>
</html>
