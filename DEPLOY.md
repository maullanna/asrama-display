# Panduan Deploy (Manual)

Aplikasi: **asrama-display** → produksi di https://asrama.akti.ac.id
Server: `/home/asrama.akti.ac.id/asrama-display` (user: `asram7645`)

Deploy dilakukan dalam **2 tahap**: (1) commit & push dari lokal, lalu (2) tarik di server + bersihkan cache.

---

## 1. Di komputer lokal

Folder project: `c:\laragon\www\asrama-display`. Buka terminal (Git Bash / PowerShell):

```bash
git add -A
git commit -m "pesan perubahan singkat"
git push origin main
```

## 2. Di server (lewat SSH)

```bash
ssh asrama-display
```

Setelah masuk server:

```bash
cd /home/asrama.akti.ac.id/asrama-display
git pull origin main
chown -R asram7645:asram7645 .
php artisan view:clear
exit
```

### Ringkas (satu baris, tanpa login shell)

Bisa langsung dari lokal tanpa masuk ke shell server:

```bash
ssh asrama-display 'cd /home/asrama.akti.ac.id/asrama-display && git pull origin main && chown -R asram7645:asram7645 . && php artisan view:clear'
```

---

## Kalau ada perubahan database (migrasi baru)

Tambahkan `migrate --force` sebelum `view:clear`:

```bash
php artisan migrate --force
```

Contoh satu baris lengkap:

```bash
ssh asrama-display 'cd /home/asrama.akti.ac.id/asrama-display && git pull origin main && php artisan migrate --force && chown -R asram7645:asram7645 . && php artisan view:clear'
```

---

## Catatan penting

- Perubahan **Blade/CSS** (tampilan) cukup `view:clear`. Di browser tekan `Ctrl+Shift+R` (hard refresh) untuk melihat hasil.
- **JANGAN** commit file `.env` / `.env.production`, folder foto mahasiswa, atau file `.zip` — sudah diatur di `.gitignore`.
- `asrama-display` adalah nama host SSH yang sudah tersimpan di config SSH lokal (`~/.ssh/config`), jadi tidak perlu ketik IP/password.
- Data **Vokasi** sudah data asli — jangan hapus/ubah lewat perintah manual di server tanpa alasan jelas.

---

## Cek cepat setelah deploy (opsional)

Memastikan halaman kiosk hidup:

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://asrama.akti.ac.id/
```

Harus mengembalikan `200`.
