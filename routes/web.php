<?php


use Illuminate\Support\Facades\Route;

// AUTH
use App\Http\Controllers\AuthController;

// ADMIN
//use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\KriteriaController;
use App\Http\Controllers\Admin\AHPController;
use App\Http\Controllers\Admin\AHPMultiExpertController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Admin\VIKORController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\BatasController;
use App\Http\Controllers\AlternatifController;
use App\Http\Controllers\LaporanEvaluasiController;
use App\Http\Controllers\Admin\EvaluationPipelineController;

use Illuminate\Support\Facades\Artisan;
//use App\Http\Controllers\Admin\ThresholdController;

// GIS
use App\Http\Controllers\GISController;

// DINAS
use App\Http\Controllers\dinas\EvaluasiController;
use App\Http\Controllers\dinas\RekomendasiController; 

// DASHBOARD
use App\Http\Controllers\DashboardController;

Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function() {

    Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');
    Route::resource('kriteria', KriteriaController::class)->except(['show']);
    
    // ALTERNATIF
    Route::get('/alternatif/nilai', [AlternatifController::class, 'formNilai'])->name('alternatif.index');
    Route::post('/alternatif/nilai', [AlternatifController::class, 'simpanNilai'])->name('alternatif.nilai.simpan');
    // WILAYAH
    Route::resource('wilayah', WilayahController::class);
    Route::post('wilayah/nilai', [WilayahController::class, 'storeNilai'])->name('wilayah.nilai');
    // KLASIFIKASI LAHAN
    Route::get('/evaluasi/run', function () {
        Artisan::call('evaluasi:lahan');
        return back()->with('success', 'Proses Evaluasi lahan berhasil dijalankan!');
    })->name('evaluasi.run');

    // VIKOR
    Route::get('vikor/hitung', [VIKORController::class, 'proses'])->name('vikor.hitung');
    Route::get('vikor/hasil', [VIKORController::class, 'hasil'])->name('vikor.hasil');
    Route::post('vikor/proses', [VIKORController::class, 'proses'])->name('vikor.proses');
    Route::get('/batas', [BatasController::class, 'index'])->name('batas.index');
    Route::post('/batas', [BatasController::class, 'update'])->name('batas.update');
    
    //PAKAR AHP MULTI
    Route::get('/ahp/experts', [AHPMultiExpertController::class,'index'])->name('ahp.experts');
    Route::post('/ahp/experts', [AHPMultiExpertController::class,'createExpert'])->name('ahp.experts.store');
    // FORM INPUT MATRIX PAKAR
    Route::get('/ahp/experts/{expert}/matrix',[AHPMultiExpertController::class,'inputMatrixForm'])->name('ahp.experts.matrix');
    Route::post('/ahp/experts/{expert}/matrix',[AHPMultiExpertController::class,'saveExpertMatrix'])->name('ahp.experts.matrix.save');
    Route::put('/ahp/experts/{expert}', [AHPMultiExpertController::class,'updateExpert'])->name('ahp.experts.update');
    Route::delete('/ahp/experts/{expert}', [AHPMultiExpertController::class,'deleteExpert'])->name('ahp.experts.destroy');
    Route::post('/ahp/aggregate', [AHPMultiExpertController::class,'aggregateResult'])->name('ahp.aggregate');


Route::post('/pipeline/run', [EvaluationPipelineController::class, 'run'])
    ->name('pipeline.run');
    // LAPORAN
    Route::get('/ringkasanluas', [GISController::class, 'ringkasanLuas'])
     ->name('ringkasan.luas');

     Route::get('/laporan', [LaporanEvaluasiController::class, 'laporanEvaluasi'])
    ->name('laporan.index');

    Route::get('/map/export-view', function () {
    return view('laporan.map-export');

    
});




    Route::get('/ringkasan-chart', function () {
        return view('gis.ringkasan-chart');
    })->name('ringkasan.chart');


});

Route::middleware(['auth', 'role:dinas'])
    ->prefix('dinas')
    ->name('dinas.')
    ->group(function() {
    Route::get('/', fn() => view('dinas.dashboard'))->name('dashboard');

    Route::get('evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
   
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
    
    Route::get('rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi.index');
    Route::get('laporan/{id}/download', [LaporanController::class, 'download'])->name('laporan.download');

});

Route::get('/map/geojson', [GISController::class, 'geojson'])->name('map.geojson');
Route::get('/peta', function() {
    return view('gis.index');
});
Route::get('/map/atribut', [GISController::class, 'atribut'])->name('map.atribut');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/export-pdf', [DashboardController::class, 'exportPDF'])
     ->name('dashboard.export.pdf');
