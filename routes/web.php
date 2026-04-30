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
Route::get('/produccion/eficiencia', [ProduccionController::class, 'controlar'])->middleware('auth')->name('produccion.eficiencia');
Route::get('/produccion/exportar', [ProduccionController::class, 'exportarCuadre'])->middleware('auth')->name('produccion.exportar');
Route::get('/produccion/excel', [ProduccionController::class, 'exportarExcel'])->middleware('auth')->name('produccion.excel');

Route::get('/produccion/eficiencia/pdf', [ProduccionController::class, 'controlarPdf'])->middleware('auth')->name('produccion.eficiencia.pdf');
Route::get('/produccion/eficiencia/{pais}/pdf', [ProduccionController::class, 'controlarPdf'])->middleware('auth')->name('produccion.eficiencia.pais.pdf');
Route::get('/produccion/{pais}/pdf', [ProduccionController::class, 'exportarPdf'])->middleware('auth')->name('produccion.pdf');

Route::get('/maquinas/listado', [ProduccionController::class, 'listadoMaquinas'])->middleware('auth')->name('maquinas.listado');
Route::get('/maquinas/inactivas', [ProduccionController::class, 'listadoMaquinasInactivas'])->middleware('auth')->name('maquinas.inactivas');
Route::post('/maquinas/toggle-rmt', [ProduccionController::class, 'toggleRMT'])->middleware('auth')->name('maquinas.toggleRMT');
Route::post('/maquinas/actualizar', [ProduccionController::class, 'actualizarMaquina'])->middleware('auth')->name('maquinas.actualizar');
Route::post('/api/maquinas/actualizar', [ProduccionController::class, 'actualizarMaquinaApi'])->middleware('auth')->name('api.maquinas.actualizar');
