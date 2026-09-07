<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KioskController::class, 'index']);

// Endpoint polling AJAX untuk update kartu kamar tanpa reload halaman
Route::get('/kiosk-rooms', [KioskController::class, 'rooms'])->name('kiosk.rooms');

// Form laporan kondisi anggota oleh ketua kamar (diakses via QR di kiosk)
Route::get('/lapor', [ReportController::class, 'show'])->name('report.show');
Route::post('/lapor', [ReportController::class, 'store'])->name('report.store');

// Template Excel untuk import mahasiswa (hanya untuk admin yang login)
Route::get('/import-template-mahasiswa.xlsx', function () {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->getActiveSheet()->fromArray([
        ['nim', 'nama', 'pin', 'lantai', 'kamar', 'ketua'],
        ['2026001', 'Nama Mahasiswa Satu', '2026001', '1', '101', '1'],
        ['2026002', 'Nama Mahasiswa Dua', '2026002', '1', '101', '0'],
    ]);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    return response()->streamDownload(
        fn () => $writer->save('php://output'),
        'template-mahasiswa.xlsx',
        ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    );
})->middleware('auth')->name('students.import.template');

// Handshake: mesin meminta konfigurasi saat pertama terhubung
Route::get('/iclock/cdata', [AdmsController::class, 'handshake']);

// Data absensi dikirim ke sini
Route::post('/iclock/cdata', [AdmsController::class, 'data']);

// Mesin menanyakan apakah ada perintah dari server
Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest']);

// Mesin melaporkan hasil eksekusi perintah
Route::post('/iclock/devicecmd', [AdmsController::class, 'deviceCmd']);
