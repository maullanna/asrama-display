<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\KioskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KioskController::class, 'index']);

// Handshake: mesin meminta konfigurasi saat pertama terhubung
Route::get('/iclock/cdata', [AdmsController::class, 'handshake']);

// Data absensi dikirim ke sini
Route::post('/iclock/cdata', [AdmsController::class, 'data']);

// Mesin menanyakan apakah ada perintah dari server
Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest']);

// Mesin melaporkan hasil eksekusi perintah
Route::post('/iclock/devicecmd', [AdmsController::class, 'deviceCmd']);
