<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduccionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('/login', 'Login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

Route::get('/produccion/peru',      [ProduccionController::class, 'peru'])->name('produccion.peru');
Route::get('/produccion/chile',     [ProduccionController::class, 'chile'])->name('produccion.chile');
Route::get('/produccion/colombia',  [ProduccionController::class, 'colombia'])->name('produccion.colombia');
Route::get('/produccion/australia', [ProduccionController::class, 'australia'])->name('produccion.australia');
Route::get('/produccion/hora',      [ProduccionController::class, 'hora'])->name('produccion.hora');
Route::get('/produccion/controlar', [ProduccionController::class, 'controlar'])->name('produccion.controlar');
Route::get('/produccion/exportar',  [ProduccionController::class, 'exportarCuadre'])->name('produccion.exportar');