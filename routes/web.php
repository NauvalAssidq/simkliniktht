<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegPeriksaController;

Route::get('/', function () {
    return redirect()->route('registration.index');
});

Route::get('/registration', [RegPeriksaController::class, 'index'])->name('registration.index');
Route::post('/registration', [RegPeriksaController::class, 'store'])->name('registration.store');
Route::post('/bridging/{no_rawat}', [RegPeriksaController::class, 'bridging'])->name('bridging.send')->where('no_rawat', '.*');

// Examination Routes
use App\Http\Controllers\PemeriksaanController;

Route::get('/pemeriksaan', [PemeriksaanController::class, 'index'])->name('pemeriksaan.index');
Route::get('/pemeriksaan/queue', [PemeriksaanController::class, 'getQueue'])->name('pemeriksaan.queue');
Route::get('/pemeriksaan/data', [PemeriksaanController::class, 'getExamData']);
Route::get('/api/search/diagnosis', [PemeriksaanController::class, 'searchDiagnosis']);
Route::get('/api/search/procedures', [PemeriksaanController::class, 'searchProcedures']);
Route::post('/pemeriksaan/store', [PemeriksaanController::class, 'store'])->name('pemeriksaan.store');

// Queue / Antrian Routes
use App\Http\Controllers\AntrianController;
Route::get('/antrian/display', [AntrianController::class, 'display'])->name('antrian.display');
Route::get('/antrian/data', [AntrianController::class, 'getData']);
Route::post('/antrian/call', [AntrianController::class, 'callPatient']);
