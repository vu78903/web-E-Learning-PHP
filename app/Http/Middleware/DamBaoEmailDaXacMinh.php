<?php

namespace App\Http\Middleware;

use App\Models\NguoiDung;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * File: DamBaoEmailDaXacMinh.php
 * Chặn học viên/giảng viên chưa xác minh email sử dụng chức năng nội bộ.
 */
class DamBaoEmailDaXacMinh
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $duocPhepKhiChuaXacMinh = in_array($request->route()?->getName(), [
            'logout', 'profile.edit', 'profile.update',
        ], true);
        $canXacMinh = $user && in_array($user->role, [NguoiDung::ROLE_STUDENT, NguoiDung::ROLE_INSTRUCTOR], true);

        if (! $duocPhepKhiChuaXacMinh && $canXacMinh && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
