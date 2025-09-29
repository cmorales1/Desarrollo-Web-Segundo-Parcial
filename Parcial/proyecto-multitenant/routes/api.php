<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\TaskController;

// Login central (no requiere tenant)
Route::post('/login', [LoginController::class, 'login']);
Route::post('/_probe', function (\Illuminate\Http\Request $r) {
    return response()->json(['ok' => true, 'data' => $r->all()]);
});
use Illuminate\Http\Request;

Route::any('/whoami', function (Request $r) {
    return response()->json([
        'host'   => $r->getHost(),
        'method' => $r->getMethod(),
        'uri'    => $r->getPathInfo(),
        // qué rutas ve esta app para /api/login
        'login_methods' => collect(app('router')->getRoutes())
            ->filter(fn($r) => str_starts_with($r->uri(), 'api/login'))
            ->map(fn($r) => ['method' => implode('|', $r->methods()), 'uri' => $r->uri(), 'action' => $r->getActionName()])
            ->values(),
    ]);
});



// Rutas por-tenant: primero resolvemos tenant, luego exigimos token
Route::middleware([\App\Http\Middleware\ResolveTenant::class, 'auth:sanctum'])->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::apiResource('tasks', TaskController::class);
});
