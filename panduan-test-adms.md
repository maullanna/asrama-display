# Panduan Pengujian Koneksi ADMS — Solution X105-ID

Dokumen ini berisi langkah pengujian koneksi antara mesin fingerprint dan server,
sebelum sistem lengkap dibangun. Tujuannya membuktikan bahwa mesin bisa mengirim
data absensi ke server sendiri, bukan lewat cloud vendor.

**Status:** belum diuji.

---

## Kenapa Diuji Lebih Dulu

Seluruh sistem display asrama bergantung pada kemampuan mesin mengirim data ke
server. Kalau ternyata tidak bisa, konsekuensinya bukan sekadar satu fitur gagal,
melainkan perubahan arsitektur menyeluruh: perlu mini PC di asrama, perlu menulis
service penarik data, dan perlu mengajukan tambahan anggaran.

Semakin lama hal ini diketahui, semakin mahal perubahannya. Karena itu bagian yang
paling belum pasti dikerjakan lebih dulu, meskipun urutan kerja yang biasa
menempatkannya di akhir.

Pengujian ini tidak memerlukan database, tampilan, maupun parser. Cukup satu
endpoint yang mencatat apa pun yang dikirim mesin ke file log.

---

## Informasi Mesin

| Item | Nilai |
|---|---|
| Model | Solution X105-ID |
| Nomor seri | NHZ4234600128 |
| MAC address | 00:17:61:10:16:07 |
| Versi firmware | 8.0.4.3-20230515 |
| Push Service | 2.0.33S-20220613 |
| Platform | ZLM60_TFT |

Keberadaan komponen **Push Service** di firmware adalah indikasi kuat bahwa mesin
mendukung pengiriman data ke server ADMS. Tanpa komponen ini, menu ADMS hanya
tampilan tanpa fungsi.

Nomor seri dipakai mesin sebagai identitas dirinya di setiap permintaan ke server,
dikirim lewat parameter `SN`.

---

## Persiapan Sisi Server

### 1. Channel log khusus

Tambahkan di `config/logging.php`, di dalam array `channels`:

```php
'adms' => [
    'driver' => 'single',
    'path' => storage_path('logs/adms.log'),
    'level' => 'debug',
],
```

Log dipisahkan supaya isinya tidak tercampur dengan log aplikasi lain dan mudah
dipantau.

### 2. Pengecualian CSRF

Laravel 11 ke atas, di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: ['iclock/*']);
})
```

Mesin tidak mengirim token CSRF. Tanpa pengecualian ini, semua data yang dikirim
mesin akan ditolak dengan error 419 dan tidak akan pernah masuk log.

### 3. Route ADMS

Ditulis di `routes/web.php`, bukan `api.php`, karena mesin tidak mengirim header
JSON.

```php
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

// Handshake: mesin meminta konfigurasi saat pertama terhubung
Route::get('/iclock/cdata', function (Request $request) {
    Log::channel('adms')->info('HANDSHAKE', $request->query());

    $sn = $request->query('SN');

    return response(
        "GET OPTION FROM: {$sn}\n" .
        "Stamp=9999\n" .
        "OpStamp=9999\n" .
        "ErrorDelay=30\n" .
        "Delay=10\n" .
        "TransTimes=00:00;14:05\n" .
        "TransInterval=1\n" .
        "TransFlag=1111000000\n" .
        "TimeZone=7\n" .
        "Realtime=1\n" .
        "Encrypt=0\n",
        200
    )->header('Content-Type', 'text/plain');
});

// Data absensi dikirim ke sini
Route::post('/iclock/cdata', function (Request $request) {
    Log::channel('adms')->info('DATA', [
        'query' => $request->query(),
        'body'  => $request->getContent(),
    ]);

    return response('OK', 200)->header('Content-Type', 'text/plain');
});

// Mesin menanyakan apakah ada perintah dari server
Route::get('/iclock/getrequest', function (Request $request) {
    return response('OK', 200)->header('Content-Type', 'text/plain');
});

// Mesin melaporkan hasil eksekusi perintah
Route::post('/iclock/devicecmd', function (Request $request) {
    Log::channel('adms')->info('CMD', ['body' => $request->getContent()]);
    return response('OK', 200)->header('Content-Type', 'text/plain');
});
```

### Kenapa balasan handshake harus berformat khusus

Mesin tidak akan mengirim data absensi sampai menerima konfigurasi yang dikenalinya.
Kalau server hanya menjawab `OK`, proses berhenti di langkah ini dan tidak akan ada
data yang masuk selamanya.

Beberapa nilai yang penting:

| Parameter | Arti |
|---|---|
| `Realtime=1` | Mesin mengirim seketika setiap ada scan, bukan menunggu jadwal |
| `TimeZone=7` | Zona waktu WIB |
| `Stamp` | Penanda posisi data terakhir yang sudah diterima server |
| `TransInterval` | Jeda pengiriman dalam menit |
| `Encrypt=0` | Data dikirim tanpa enkripsi tambahan |

---

## Menghubungkan Mesin ke Server Lokal

Mesin berada di jaringan `192.168.0.x`, sedangkan laptop pengembang berada di
`172.16.20.x`. Keduanya tidak saling terjangkau, sehingga mesin tidak bisa langsung
mengakses Laravel di laptop.

Solusinya memakai tunnel, yang memberikan URL publik untuk aplikasi lokal.

```bash
# Terminal 1
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2
cloudflared tunnel --url http://localhost:8000
```

Tunnel akan menampilkan URL acak berakhiran `trycloudflare.com`. URL inilah yang
diisikan ke mesin.

### Keterbatasan tunnel

Cloudflare Tunnel hanya melayani HTTPS, sehingga pengujian dengan HTTP polos tidak
memungkinkan. Kalau mesin gagal terhubung, sulit membedakan apakah penyebabnya
sertifikat TLS atau hal lain.

URL tunnel juga berubah setiap kali proses dijalankan ulang, sehingga setting mesin
harus diperbarui setiap kali. Karena itu tunnel hanya cocok untuk pengujian singkat,
bukan pemakaian jangka panjang.

Alternatifnya adalah menguji langsung di VPS, di mana HTTP dan HTTPS bisa diatur
sepenuhnya dan alamatnya tetap.

---

## Uji Endpoint Sebelum Menyentuh Mesin

Panggil endpoint handshake dari browser atau terminal:

```bash
curl "https://URL-TUNNEL/iclock/cdata?SN=NHZ4234600128&options=all"
```

Hasil yang benar adalah teks konfigurasi yang berisi `Stamp`, `TimeZone`, dan
`Realtime`.

| Hasil | Penyebab | Tindakan |
|---|---|---|
| Teks konfigurasi | Berhasil | Lanjut ke setting mesin |
| 404 Not Found | Route belum terpasang atau salah path | Periksa `routes/web.php` |
| 419 Page Expired | CSRF belum dikecualikan | Periksa `bootstrap/app.php` |
| 500 Server Error | Channel log belum dibuat | Periksa `config/logging.php` |
| Halaman welcome Laravel | Route tidak terbaca | Jalankan `php artisan route:clear` |

Jangan lanjut ke mesin sebelum langkah ini berhasil.

---

## Setting Mesin Fingerprint

Masuk menu, lalu ke **Comm Setting** dan **Pengaturan Server Cloud**.

| Setting | Nilai untuk tunnel | Nilai untuk VPS dengan HTTP |
|---|---|---|
| Server Mode | ADMS | ADMS |
| Aktifkan nama domain | ON | ON |
| Alamat server | URL tunnel tanpa `https://` | `asrama.akti.ac.id` |
| Server port | 443 | 80 |
| Enable Proxy Server | OFF | OFF |
| HTTPS | ON | OFF |

Periksa juga menu **Ethernet**, pastikan IP, gateway, dan DNS terisi benar, serta
mesin memang bisa menjangkau internet dan bukan hanya jaringan lokal.

Restart mesin setelah menyimpan.

### Setting lama yang perlu dicatat

Sebelum mengubah, catat nilai yang ada sekarang agar bisa dikembalikan bila perlu.
Pada pemeriksaan terakhir, alamat server terisi `119.235.252.10` dengan port `80`,
opsi nama domain OFF, dan HTTPS ON.

Kombinasi HTTPS aktif dengan port 80 saling bertentangan, karena port 80 adalah
jalur HTTP biasa. Kemungkinan besar konfigurasi ini memang tidak pernah berfungsi.

---

## Memantau Hasil

```bash
tail -f storage/logs/adms.log
```

### Tahap 1: Handshake

Dalam satu sampai dua menit setelah mesin menyala, harus muncul entri `HANDSHAKE`
berisi nomor seri mesin. Ini membuktikan mesin berhasil menjangkau server.

### Tahap 2: Data absensi

Daftarkan satu sidik jari dengan PIN yang mudah diingat, misalnya `1`. Lakukan scan,
lalu perhatikan log. Harus muncul entri `DATA` berisi PIN, waktu, dan kode status.

**Simpan isi log ini.** Format aslinya yang akan menjadi acuan pembuatan parser,
karena format yang dikirim tiap firmware sering berbeda dari dokumentasi yang
beredar di internet.

---

## Kalau Gagal

### Handshake tidak muncul sama sekali

| Kemungkinan penyebab | Cara memeriksa |
|---|---|
| Mesin tidak punya akses internet keluar | Periksa gateway dan DNS di menu Ethernet |
| Opsi nama domain belum aktif | Mesin akan tetap mencari IP lama, bukan domain |
| Firmware menolak sertifikat TLS | Uji ulang di VPS dengan HTTPS dimatikan |
| Firewall jaringan memblokir keluar | Coba dari jaringan lain |

### Handshake muncul tapi data tidak

| Kemungkinan penyebab | Cara memeriksa |
|---|---|
| Balasan handshake salah format | Periksa isi respons, harus plain text |
| `Realtime` tidak aktif | Pastikan bernilai 1 di balasan |
| Route POST kena CSRF | Periksa apakah ada error 419 di log Laravel |
| Sidik jari belum terdaftar | Pastikan proses enroll berhasil |

### Kalau tetap tidak berhasil

Hubungi IT support Solution dengan menyebutkan nomor seri `NHZ4234600128` dan versi
firmware. Tanyakan secara spesifik apakah mesin mendukung pengiriman data ke server
sendiri, bukan ke cloud vendor.

Kalau ternyata tidak didukung, rencana cadangannya adalah memakai metode tarik data
seperti yang sudah berjalan di kantor, dengan konsekuensi perlu menyediakan mini PC
di asrama yang menyala terus-menerus.

---

## Setelah Berhasil

Yang perlu dikerjakan berikutnya:

1. Rancang parser sesuai format data asli yang tercatat di log
2. Hubungkan PIN mesin ke kolom `device_pin` di tabel `students`
3. Simpan data ke tabel `attendance_logs` dengan arah CI atau CO
4. Kembalikan setting mesin ke kondisi semula bila mesin masih dipakai keperluan lain
