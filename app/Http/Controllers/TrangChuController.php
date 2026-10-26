<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use App\Models\NguoiDung;
use Illuminate\View\View;

class TrangChuController extends Controller
{
    public function hienThi(): View
    {
        $courses = KhoaHoc::congKhai()
            ->with(['category', 'instructor'])
            ->withCount('enrollments')
            ->latest()
            ->take(6)
            ->get();

        return view('home', [
            'courses' => $courses,
            'categories' => DanhMuc::withCount(['courses' => fn ($q) => $q->congKhai()])->orderBy('name')->get(),
            'stats' => [
                'courses' => KhoaHoc::congKhai()->count(),
                'students' => NguoiDung::where('role', NguoiDung::ROLE_STUDENT)->count(),
                'instructors' => NguoiDung::where('role', NguoiDung::ROLE_INSTRUCTOR)->count(),
            ],
        ]);
    }
}
