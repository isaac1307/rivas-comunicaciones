<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BitacoraController as AdminBitacoraController;
use App\Http\Controllers\Admin\ClienteController as AdminClienteController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MensajeContactoController as AdminMensajeContactoController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PromocionController as AdminPromocionController;
use App\Http\Controllers\Admin\ReservacionController as AdminReservacionController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPlanController;
use App\Http\Controllers\PublicPromocionController;
use App\Http\Controllers\PublicReservacionController;
use App\Http\Controllers\PublicTicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Frontend)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/nosotros', [HomeController::class, 'nosotros'])->name('nosotros');
Route::get('/cobertura', [HomeController::class, 'cobertura'])->name('cobertura');

Route::get('/planes', [PublicPlanController::class, 'index'])->name('planes.index');
Route::get('/promociones', [PublicPromocionController::class, 'index'])->name('promociones.index');

Route::get('/reservaciones/crear', [PublicReservacionController::class, 'create'])->name('reservaciones.create');
Route::post('/reservaciones', [PublicReservacionController::class, 'store'])->name('reservaciones.store');
Route::get('/reservaciones/{folio}', [PublicReservacionController::class, 'show'])->name('reservaciones.show');

Route::get('/soporte/ticket/crear', [PublicTicketController::class, 'create'])->name('tickets.create');
Route::post('/soporte/ticket', [PublicTicketController::class, 'store'])->name('tickets.store');
Route::get('/soporte/ticket/{folio}', [PublicTicketController::class, 'show'])->name('tickets.show');

Route::get('/contacto', [ContactoController::class, 'index'])->name('contacto');
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');

/*
|--------------------------------------------------------------------------
| Panel Administrativo (/admin/...)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('planes', AdminPlanController::class);
        Route::resource('promociones', AdminPromocionController::class);
        Route::resource('clientes', AdminClienteController::class);

        Route::resource('reservaciones', AdminReservacionController::class)->only(['index', 'show', 'edit', 'update']);
        Route::resource('tickets', AdminTicketController::class);
        Route::resource('mensajes', AdminMensajeContactoController::class)->only(['index', 'show', 'update', 'destroy']);

        Route::get('/bitacoras', [AdminBitacoraController::class, 'index'])
            ->middleware('role:administrador')
            ->name('bitacoras.index');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas del Starter Kit / Livewire
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
