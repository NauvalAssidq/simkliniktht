<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegPeriksaController;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\AntrianController;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'admin') {
            return redirect()->route('satusehat.practitioner');
        } elseif ($role === 'dokter') {
            return redirect()->route('pemeriksaan.index');
        }
        return redirect()->route('registration.index');
    }
    return redirect()->route('login');
});

// Authentication
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SatuSehatAdminController;
Route::middleware('guest')->get('/masuk', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->name('login.post');
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

// Group 1: Registration (Receptionist / Pendaftaran Only)
Route::middleware(['auth', 'role:pendaftaran'])->group(function () {
    Route::get('/pendaftaran', [RegPeriksaController::class, 'index'])->name('registration.index');
    Route::post('/pendaftaran', [RegPeriksaController::class, 'store'])->name('registration.store');
});

// Group 4: Admin Only (System Settings)
Route::middleware(['auth', 'role:admin'])->group(function () {
    // SatuSehat Admin Routes
    // SatuSehat Admin Routes
    Route::get('/satusehat/praktisi', [\App\Http\Controllers\SatuSehatAdminController::class, 'index'])->name('satusehat.practitioner');
    Route::post('/satusehat/praktisi', [\App\Http\Controllers\SatuSehatAdminController::class, 'storePractitioner'])->name('satusehat.practitioner.store');
    Route::put('/satusehat/praktisi/{kd_dokter}', [\App\Http\Controllers\SatuSehatAdminController::class, 'updatePractitioner'])->name('satusehat.practitioner.update');
    Route::delete('/satusehat/praktisi/{kd_dokter}', [\App\Http\Controllers\SatuSehatAdminController::class, 'destroyPractitioner'])->name('satusehat.practitioner.destroy');
    
    // API/Ajax Search
    Route::post('/satusehat/praktisi/cari', [\App\Http\Controllers\SatuSehatAdminController::class, 'search'])->name('satusehat.practitioner.search');

    // ICD-10 Management
    Route::get('/satusehat/icd10', [\App\Http\Controllers\Icd10Controller::class, 'index'])->name('satusehat.icd10');
    Route::post('/satusehat/icd10', [\App\Http\Controllers\Icd10Controller::class, 'store'])->name('satusehat.icd10.store');
    Route::put('/satusehat/icd10/{kd_penyakit}', [\App\Http\Controllers\Icd10Controller::class, 'update'])->name('satusehat.icd10.update');
    Route::delete('/satusehat/icd10/{kd_penyakit}', [\App\Http\Controllers\Icd10Controller::class, 'destroy'])->name('satusehat.icd10.destroy');
    Route::post('/satusehat/icd10/import', [\App\Http\Controllers\Icd10Controller::class, 'import'])->name('satusehat.icd10.import');

    // ICD-9 Management
    Route::get('/satusehat/icd9', [\App\Http\Controllers\Icd9Controller::class, 'index'])->name('satusehat.icd9');
    Route::post('/satusehat/icd9', [\App\Http\Controllers\Icd9Controller::class, 'store'])->name('satusehat.icd9.store');
    Route::put('/satusehat/icd9/{code}', [\App\Http\Controllers\Icd9Controller::class, 'update'])->name('satusehat.icd9.update');
    Route::delete('/satusehat/icd9/{code}', [\App\Http\Controllers\Icd9Controller::class, 'destroy'])->name('satusehat.icd9.destroy');
    Route::post('/satusehat/icd9/import', [\App\Http\Controllers\Icd9Controller::class, 'import'])->name('satusehat.icd9.import');

    // KFA / Drug Master Management
    Route::get('/satusehat/kfa', [\App\Http\Controllers\DatabarangController::class, 'index'])->name('satusehat.kfa');
    Route::post('/satusehat/kfa', [\App\Http\Controllers\DatabarangController::class, 'store'])->name('satusehat.kfa.store');
    Route::put('/satusehat/kfa/{kd_brng}', [\App\Http\Controllers\DatabarangController::class, 'update'])->name('satusehat.kfa.update');
    Route::delete('/satusehat/kfa/{kd_brng}', [\App\Http\Controllers\DatabarangController::class, 'destroy'])->name('satusehat.kfa.destroy');
    Route::post('/satusehat/kfa/import', [\App\Http\Controllers\DatabarangController::class, 'import'])->name('satusehat.kfa.import');

    // Staff Management (Receptionist & Pharmacy)
    Route::get('/staff', [\App\Http\Controllers\StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [\App\Http\Controllers\StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{id}', [\App\Http\Controllers\StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [\App\Http\Controllers\StaffController::class, 'destroy'])->name('staff.destroy');
});

// Group 5: Pharmacy (Apotek)
Route::middleware(['auth', 'role:apotek'])->group(function () {
    Route::redirect('/apotek', '/apotek/beranda');
    Route::get('/apotek/beranda', [\App\Http\Controllers\ApotekController::class, 'index'])->name('apotek.index');
    Route::get('/apotek/resep/{no_resep}', [\App\Http\Controllers\ApotekController::class, 'show'])->name('apotek.show');
    Route::post('/apotek/resep/{no_resep}/dispense', [\App\Http\Controllers\ApotekController::class, 'dispense'])->name('apotek.dispense');
    Route::post('/apotek/resep/{no_resep}/cancel', [\App\Http\Controllers\ApotekController::class, 'cancel'])->name('apotek.cancel');
});

// Group 2: Examination (Doctor)
Route::middleware(['auth', 'role:dokter'])->group(function () {
    Route::get('/pemeriksaan', [PemeriksaanController::class, 'index'])->name('pemeriksaan.index');
    Route::get('/pemeriksaan/antrian', [PemeriksaanController::class, 'getQueue'])->name('pemeriksaan.queue');
    Route::get('/pemeriksaan/data', [PemeriksaanController::class, 'getExamData']);
    Route::post('/pemeriksaan/simpan', [PemeriksaanController::class, 'store'])->name('pemeriksaan.store');
    Route::post('/bridging/{no_rawat}', [RegPeriksaController::class, 'bridging'])->name('bridging.send')->where('no_rawat', '.*');
    // API Routes for Doctor
    Route::get('/api/cari/diagnosis', [PemeriksaanController::class, 'searchDiagnosis']);
    Route::get('/api/search/diagnosis', [PemeriksaanController::class, 'searchDiagnosis']); // alias for examination form
    Route::get('/api/cari/prosedur', [PemeriksaanController::class, 'searchProcedures']);
    Route::get('/api/cari/obat', [\App\Http\Controllers\DatabarangController::class, 'apiSearch']);
    Route::get('/api/riwayat-medis', [PemeriksaanController::class, 'getMedicalHistory'])->name('pemeriksaan.history');
});

// Shared API Routes (Authenticated Users)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/satusehat/patient/{nik}', [App\Http\Controllers\SatuSehatController::class, 'searchPatientByNik']);
});

// Group 3: Shared/Public/Display
Route::get('/antrian/layar', [AntrianController::class, 'display'])->name('antrian.display');
Route::get('/antrian/data', [AntrianController::class, 'getData']);
Route::post('/antrian/panggil', [AntrianController::class, 'callPatient']); // Ideally this should be protected for Admin/Doctor but kept open for simplicity or shared
Route::post('/antrian/reset', [AntrianController::class, 'reset']);

Route::get('/', function () {
    return redirect()->route('login');
});
