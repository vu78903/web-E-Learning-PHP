<?php

use App\Http\Middleware\DamBaoNguoiDungCoVaiTro;
use App\Http\Middleware\DamBaoTaiKhoanHoatDong;
use App\Http\Middleware\DamBaoEmailDaXacMinh;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => DamBaoNguoiDungCoVaiTro::class,
            'email.xac_minh' => DamBaoEmailDaXacMinh::class,
        ]);
        $middleware->appendToGroup('web', DamBaoTaiKhoanHoatDong::class);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
