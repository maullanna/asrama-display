<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KioskController::class, 'index']);

// Form laporan kondisi anggota oleh ketua kamar (diakses via QR di kiosk)
Route::get('/lapor', [ReportController::class, 'show'])->name('report.show');
Route::post('/lapor', [ReportController::class, 'store'])->name('report.store');

// Handshake: mesin meminta konfigurasi saat pertama terhubung
Route::get('/iclock/cdata', [AdmsController::class, 'handshake']);

// Data absensi dikirim ke sini
Route::post('/iclock/cdata', [AdmsController::class, 'data']);

// Mesin menanyakan apakah ada perintah dari server
Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest']);

// Mesin melaporkan hasil eksekusi perintah
Route::post('/iclock/devicecmd', [AdmsController::class, 'deviceCmd']);
