<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KriteriaController;
use App\Http\Controllers\AHPController;
use App\Http\Controllers\AlternatifController;
use App\Http\Controllers\KlasifikasiController;
use App\Http\Controllers\VIKORController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\GISController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\RekomendasiController; 

Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function() {
    Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('kriteria', KriteriaController::class);
    Route::get('ahp/matrix', [AHPController::class, 'formMatrix'])->name('ahp.matrix');
    Route::post('ahp/matrix', [AHPController::class, 'storeMatrix']);
    Route::post('ahp/hitung', [AHPController::class, 'hitungBobot'])->name('ahp.hitung');
    Route::resource('alternatif', AlternatifController::class);
    Route::get('alternatif/{id}/nilai', [AlternatifController::class, 'formNilai'])->name('alternatif.nilai');
    Route::post('alternatif/{id}/nilai', [AlternatifController::class, 'storeNilai']);
    Route::post('klasifikasi/proses', [KlasifikasiController::class, 'proses'])->name('klasifikasi.proses');
    Route::post('vikor/proses', [VIKORController::class, 'proses'])->name('vikor.proses');
    Route::get('vikor/hasil', [VIKORController::class, 'hasil'])->name('vikor.hasil');
    Route::resource('laporan', LaporanController::class);
    Route::post('laporan/{id}/publish', [LaporanController::class, 'publish'])->name('laporan.publish');
    Route::get('peta', [GISController::class, 'index'])->name('peta.index');

});

Route::middleware(['auth', 'role:dinas'])->prefix('dinas')->name('dinas.')->group(function() {
    Route::get('/', fn() => view('dinas.dashboard'))->name('dashboard');
    Route::get('evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::get('peta', [GISController::class, 'index'])->name('peta.index');
    Route::resource('rekomendasi', RekomendasiController::class);
    Route::post('laporan/{id}/review', [LaporanController::class, 'review'])->name('laporan.review');
    Route::post('laporan/{id}/approve', [LaporanController::class, 'approve'])->name('laporan.approve');
    Route::get('laporan/{id}/download', [LaporanController::class, 'download'])->name('laporan.download');
});

Route::middleware(['auth', 'role:penyuluh'])->prefix('penyuluh')->name('penyuluh.')->group(function() {

    Route::get('/', fn() => view('penyuluh.dashboard'))->name('dashboard');
    Route::get('evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::get('peta', [GISController::class, 'index'])->name('peta.index');
    Route::get('rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi.index');
    Route::get('laporan/{id}/download', [LaporanController::class, 'download'])->name('laporan.download');

});

Route::get('/peta', [GISController::class, 'publicPeta'])->name('peta.public');

Route::get('/', function () {
    return view('welcome');
});
