<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Http\Middleware\EsAdmin;
use Illuminate\Support\Facades\Route;


Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::patch('productos/{producto}/reponer', [ProductoController::class, 'reponer'])->name('productos.reponer');
    Route::resource('productos', ProductoController::class)->except('show');
    Route::resource('clientes', ClienteController::class)->except('show');
    Route::resource('proveedores', ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor'])
        ->except('show');

    // Ventas
    Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('ventas/nueva', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('ventas/{venta}/nota', [VentaController::class, 'nota'])->name('ventas.nota');

    // Solo administrador
    Route::middleware(EsAdmin::class)->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';