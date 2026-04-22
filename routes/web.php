<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduccionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
})->name('home');

Route::inertia('/login', 'Login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

Route::get('/produccion/peru', [ProduccionController::class, 'peru'])->middleware('auth')->name('produccion.peru');
Route::get('/produccion/chile', [ProduccionController::class, 'chile'])->middleware('auth')->name('produccion.chile');
Route::get('/produccion/colombia', [ProduccionController::class, 'colombia'])->middleware('auth')->name('produccion.colombia');
Route::get('/produccion/australia', [ProduccionController::class, 'australia'])->middleware('auth')->name('produccion.australia');
Route::get('/produccion/hora', [ProduccionController::class, 'hora'])->middleware('auth')->name('produccion.hora');
Route::get('/produccion/controlar', [ProduccionController::class, 'controlar'])->middleware('auth')->name('produccion.controlar');
Route::get('/produccion/exportar', [ProduccionController::class, 'exportarCuadre'])->middleware('auth')->name('produccion.exportar');
Route::get('/produccion/excel', [ProduccionController::class, 'exportarExcel'])->middleware('auth')->name('produccion.excel');
