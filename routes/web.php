<?php


use Illuminate\Support\Facades\Route;

// AUTH
use App\Http\Controllers\AuthController;

// ADMIN
//use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\KriteriaController;
use App\Http\Controllers\Admin\AHPController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Admin\KlasifikasiController;
use App\Http\Controllers\Admin\VIKORController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\AlternatifController;

use Illuminate\Support\Facades\Artisan;
//use App\Http\Controllers\Admin\ThresholdController;

// GIS
use App\Http\Controllers\GISController;

// DINAS
use App\Http\Controllers\dinas\EvaluasiController;
use App\Http\Controllers\dinas\RekomendasiController; 

Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function() {

    Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');

    //Route::resource('users', UserController::class);
    Route::resource('kriteria', KriteriaController::class)->except(['show']);

    // AHP MATRIX
    Route::get('kriteria/matrix', [AHPController::class, 'matrixForm'])->name('ahp.matrix');
    Route::post('kriteria/matrix', [AHPController::class, 'saveMatrix'])->name('ahp.matrix.save');

    // AHP HITUNG
    Route::get('ahp/hitung', [AHPController::class, 'hitungBobot'])->name('ahp.hitung');


    // ALTERNATIF
   
    Route::get('/alternatif/nilai', [AlternatifController::class, 'formNilai'])->name('alternatif.index');
    Route::post('/alternatif/nilai', [AlternatifController::class, 'simpanNilai'])->name('alternatif.nilai.simpan');

    // WILAYAH
    Route::resource('wilayah', WilayahController::class);
    Route::post('wilayah/nilai', [WilayahController::class, 'storeNilai'])->name('wilayah.nilai');
    

    Route::get('/evaluasi/run', function () {
        Artisan::call('evaluasi:lahan');

        return back()->with('success', 'Evaluasi lahan berhasil dijalankan!');
    })->name('evaluasi.run');

    // VIKOR
    Route::get('vikor/hitung', [VIKORController::class, 'proses'])->name('vikor.hitung');
    Route::get('vikor/hasil', [VIKORController::class, 'hasil'])->name('vikor.hasil');
    Route::post('vikor/proses', [VIKORController::class, 'proses'])->name('vikor.proses');

    // LAPORAN
    Route::resource('laporan', LaporanController::class);

    Route::post('/evaluation/run', [\App\Http\Controllers\EvaluationController::class, 'run'])
    ->middleware('auth');

});

Route::middleware(['auth', 'role:dinas'])
    ->prefix('dinas')
    ->name('dinas.')
    ->group(function() {

    Route::get('/', fn() => view('dinas.dashboard'))->name('dashboard');

    Route::get('evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::get('peta', [GISController::class, 'index'])->name('peta.index');

    Route::resource('rekomendasi', RekomendasiController::class);

    Route::post('laporan/{id}/review', [LaporanController::class, 'review'])->name('laporan.review');
    Route::post('laporan/{id}/approve', [LaporanController::class, 'approve'])->name('laporan.approve');
    Route::get('laporan/{id}/download', [LaporanController::class, 'download'])->name('laporan.download');
});

Route::middleware(['auth', 'role:penyuluh'])
    ->prefix('penyuluh')
    ->name('penyuluh.')
    ->group(function() {

    Route::get('/', fn() => view('penyuluh.dashboard'))->name('dashboard');

    Route::get('evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::get('peta', [GISController::class, 'index'])->name('peta.index');

    Route::get('rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi.index');
    Route::get('laporan/{id}/download', [LaporanController::class, 'download'])->name('laporan.download');

});

Route::get('/map/geojson', [GISController::class, 'geojson'])->name('map.geojson');
Route::get('/peta', function() {
    return view('gis.index');
});

Route::get('/', function () {
    return view('welcome');
});
