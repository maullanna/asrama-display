# Skema Database — Sistem Display Asrama AKTI

Dokumen ini berisi rancangan database untuk sistem monitoring kamar asrama dengan
input absensi dari mesin fingerprint Solution X105-ID dan input izin/sakit dari
ketua kamar melalui form QR.

**Nama database:** `asra_akti`

**Status:** draft, capacity dan jumlah kamar masih asumsi (4 kamar per lantai, kapasitas 8).

---

## Setup Database

### Lokal (Laragon)

Buat database dengan nama yang sama seperti di produksi agar tidak ada perbedaan
konfigurasi saat deploy.

```bash
mysql -u root -e "CREATE DATABASE asra_akti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Konfigurasi `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=asra_akti
DB_USERNAME=root
DB_PASSWORD=
```

Laragon secara default memakai user `root` tanpa password. Untuk produksi, buat
user khusus dengan hak akses terbatas hanya ke database ini, jangan memakai root.

### Menjalankan Migration

```bash
php artisan make:migration create_asrama_tables
php artisan migrate
```

Setelah berhasil, akan terbentuk lima tabel: `floors`, `rooms`, `students`,
`attendance_logs`, dan `student_conditions`.

---

## Ringkasan Tabel

| Tabel                | Fungsi                                                    |
| -------------------- | --------------------------------------------------------- |
| `floors`             | Daftar lantai asrama                                      |
| `rooms`              | Daftar kamar, kapasitas, dan PIN akses ketua kamar        |
| `students`           | Data mahasiswa, foto, kamar, dan PIN di mesin fingerprint |
| `attendance_logs`    | Catatan mentah scan fingerprint, arah CI atau CO          |
| `student_conditions` | Status sakit atau izin dengan rentang tanggal             |

---

## Relasi Antar Tabel

```
floors (1) ──── (n) rooms (1) ──── (n) students
                                        │
                                        ├──── (n) attendance_logs
                                        └──── (n) student_conditions
```

---

## Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                  // "Lantai 1"
            $table->string('slug', 30)->unique();                    // "lantai-1", dipakai di URL kiosk
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->string('room_number', 20);                       // "101"
            $table->unsignedTinyInteger('capacity')->default(8);
            $table->string('access_pin', 10)->nullable();            // PIN ketua kamar untuk form izin
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['floor_id', 'room_number']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_code', 30)->unique();            // NIM
            $table->string('device_pin', 20)->nullable()->unique();  // PIN di mesin fingerprint
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->boolean('is_room_leader')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('device_pin');
            $table->index(['room_id', 'is_active']);
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_pin', 20);                        // apa adanya dari mesin
            $table->string('device_sn', 40)->nullable();             // serial number mesin
            $table->timestamp('scanned_at');
            $table->enum('direction', ['ci', 'co'])->default('ci');
            $table->string('method', 20)->default('fingerprint');    // fingerprint | qr | manual
            $table->timestamps();

            $table->index(['student_id', 'scanned_at']);
            $table->index('scanned_at');
            $table->index('device_pin');
        });

        Schema::create('student_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['sakit', 'izin']);
            $table->enum('direction', ['ci', 'co'])->nullable();      // hanya diisi kalau type = izin
            $table->date('start_date');
            $table->date('end_date')->nullable();                     // null = masih berlangsung
            $table->string('note')->nullable();
            $table->foreignId('reported_by_student_id')->nullable()
                  ->constrained('students')->nullOnDelete();          // ketua kamar yang input
            $table->timestamps();

            $table->index(['student_id', 'start_date']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_conditions');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('students');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('floors');
    }
};
```

---

## Alasan Keputusan Desain

### Kenapa `device_pin` ada di tabel `students`

Mesin fingerprint tidak mengetahui nama atau NIM mahasiswa. Yang dikirim mesin ke
server hanyalah nomor PIN yang didaftarkan saat proses enroll sidik jari. Tanpa
kolom ini, data absensi tetap masuk ke server tetapi tidak bisa dihubungkan ke
mahasiswa mana pun.

Ini adalah kolom yang paling sering terlupakan dalam integrasi mesin absensi.

### Kenapa `device_pin` juga disimpan di `attendance_logs`

Sengaja diduplikasi. Kolom di `attendance_logs` berfungsi sebagai catatan mentah
apa adanya dari mesin. Kalau suatu saat mapping PIN ke mahasiswa diubah, riwayat
lama tetap bisa dibaca sesuai kondisi saat kejadian.

### Kenapa `student_id` di `attendance_logs` boleh null

Kalau ada orang yang scan tetapi PIN-nya belum terdaftar di sistem, log tetap
tersimpan dengan `student_id` bernilai null. Datanya tidak hilang dan bisa diaudit
belakangan. Kalau kolom ini di-set `not null`, data akan ditolak secara diam-diam
dan tidak ada yang tahu ada masalah.

### Kenapa tidak ada kolom `occupancy` di tabel `rooms`

Occupancy adalah nilai turunan yang dihitung dari `attendance_logs` dan
`student_conditions` pada hari berjalan. Kalau disimpan sebagai kolom tersendiri,
cepat atau lambat nilainya akan tidak sinkron dengan data sebenarnya, terutama
kalau ada penambahan atau pemindahan mahasiswa.

Jumlah kamar di asrama tergolong sedikit, jadi menghitung on-the-fly tidak
membebani database.

### Kenapa `student_conditions` memakai rentang tanggal, bukan flag boolean

Status sakit dan izin sifatnya sementara. Kalau hanya memakai kolom `is_sick`
bertipe boolean, pengelola harus ingat mematikannya secara manual, dan pasti akan
terlupa. Dengan `start_date` dan `end_date`, status berakhir dengan sendirinya
tanpa intervensi.

### Kenapa `direction` di `student_conditions` boleh null

Sesuai kebutuhan, izin memerlukan pilihan CI atau CO, sedangkan sakit tidak
memerlukannya. Kolom dibuat nullable agar tidak memaksa pengisian data yang tidak
relevan.

### Kenapa ada `access_pin` di tabel `rooms`

Form izin diakses lewat satu QR untuk semua kamar, dibuka dari HP, tanpa login.
Tanpa verifikasi, siapa pun yang pernah memotret QR tersebut bisa mengubah status
mahasiswa mana pun. PIN kamar yang hanya diketahui ketua kamar menutup celah ini
dengan menambah satu layar saja.

---

## Logika Status Mahasiswa di Kiosk

Status ditentukan berurutan, dievaluasi untuk tanggal hari ini:

| Prioritas | Kondisi                                                     | Status ditampilkan   |
| --------- | ----------------------------------------------------------- | -------------------- |
| 1         | Ada `student_conditions` tipe `sakit` yang aktif            | Sakit                |
| 2         | Ada `attendance_logs` arah `co`                             | Sudah CO, jam sekian |
| 3         | Ada `student_conditions` tipe `izin` arah `co` tanpa log CO | Belum melakukan CO   |
| 4         | Ada `attendance_logs` arah `ci`                             | Sudah CI, jam sekian |
| 5         | Tidak ada catatan apa pun                                   | Belum ada aktivitas  |

**Catatan:** definisi angka occupancy di header kamar (misalnya "5/8") masih perlu
disepakati. Ada dua kemungkinan tafsir, yaitu jumlah yang sudah melakukan CI, atau
jumlah yang sedang berada di kamar saat ini. Keduanya menghasilkan angka berbeda.

---

## Seeder Contoh

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Floor;
use App\Models\Room;

class AsramaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Lantai 1', 'slug' => 'lantai-1', 'rooms' => ['101', '102', '103', '104']],
            ['name' => 'Lantai 2', 'slug' => 'lantai-2', 'rooms' => ['201', '202', '203', '204']],
        ];

        foreach ($data as $i => $item) {
            $floor = Floor::create([
                'name'       => $item['name'],
                'slug'       => $item['slug'],
                'sort_order' => $i,
            ]);

            foreach ($item['rooms'] as $j => $number) {
                Room::create([
                    'floor_id'    => $floor->id,
                    'room_number' => $number,
                    'capacity'    => 8,
                    'access_pin'  => str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'sort_order'  => $j,
                ]);
            }
        }
    }
}
```

---

## Yang Masih Perlu Dikonfirmasi

1. Jumlah kamar sebenarnya di Lantai 1 dan Lantai 2
2. Kapasitas tiap kamar, apakah seragam 8 orang atau berbeda-beda
3. Definisi angka occupancy yang ditampilkan di header kamar
4. Apakah satu TV menampilkan kedua lantai sekaligus, atau tiap lantai punya TV sendiri

---

## Catatan Keamanan

Beberapa hal yang perlu diperhatikan sebelum sistem ini dipakai produksi.

**User database.** Jangan memakai user `root` di server produksi. Buat user khusus
yang hanya punya hak akses ke database `asra_akti`, dengan izin terbatas pada
operasi yang memang diperlukan aplikasi.

**Akses phpMyAdmin.** phpMyAdmin yang terbuka ke publik merupakan salah satu target
serangan brute force yang paling umum. Kalau memungkinkan, batasi aksesnya lewat
firewall sehingga hanya bisa dibuka dari jaringan kantor, atau tambahkan lapisan
autentikasi di level web server.

**Foto mahasiswa.** File foto tersimpan di server dan ditampilkan di layar publik.
Pastikan direktori penyimpanannya tidak bisa di-listing dari browser, dan nama file
tidak mudah ditebak, misalnya memakai UUID dan bukan nama mahasiswa.

**Halaman kiosk.** Halaman ini menampilkan nama, foto, dan keberadaan mahasiswa.
Meskipun ditujukan untuk TV di dalam asrama, halaman ini tetap bisa diakses siapa
pun yang tahu URL-nya. Pertimbangkan membatasi akses berdasarkan IP jaringan
asrama, atau memakai token pada URL kiosk.
