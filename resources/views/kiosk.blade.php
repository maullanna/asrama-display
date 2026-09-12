<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=1920, initial-scale=1">
        <meta http-equiv="refresh" content="600">
        <link rel="icon" href="/favicon.svg?v=2" type="image/svg+xml">
        <title>Dormitory Room Occupation</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }

            html, body { height: 100%; }
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background: #eef1f6;
                color: #1b1f2a;
                display: flex;
                flex-direction: column;
                overflow: hidden;   /* tanpa scroll — pakai slideshow geser */
            }

            header {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #0b1f4d;
                color: #fff;
                padding: 10px 28px;
            }

            .brand { display: flex; align-items: center; gap: 12px; }
            .brand-logo { width: 44px; height: 44px; display: block; }
            .brand h1 { font-size: 20px; letter-spacing: .5px; }

            .header-right { display: flex; align-items: center; gap: 18px; }
            .datetime { text-align: right; font-size: 14px; opacity: 0.9; line-height: 1.35; }

            .qr-box { display: flex; align-items: center; gap: 10px; padding-left: 18px; border-left: 1px solid rgba(255,255,255,0.25); }
            .qr { background: #fff; padding: 5px; border-radius: 6px; width: 58px; height: 58px; display: flex; }
            .qr svg { width: 100%; height: 100%; display: block; }
            .qr-label { font-size: 11px; line-height: 1.3; opacity: 0.9; }

            main#rooms { flex: 1; position: relative; overflow: hidden; }

            /* Tiap slide menempati satu layar penuh; geser dengan fade + slide halus. */
            .slide {
                position: absolute;
                inset: 0;
                padding: 20px 28px;
                opacity: 0;
                transform: translateX(70px) scale(0.985);
                transition: opacity .9s ease, transform .9s cubic-bezier(.22,.61,.36,1);
                pointer-events: none;
            }
            .slide.active {
                opacity: 1;
                transform: none;
                pointer-events: auto;
            }
            #rooms.no-anim .slide { transition: none !important; }
            #rooms.has-alert .slide { padding-bottom: 72px; }

            .floor-title {
                font-size: 15px;
                font-weight: 700;
                color: #0b1f4d;
                margin: 4px 0 10px;
            }

            .room-grid {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 12px;
                margin-bottom: 16px;
            }

            .room-card {
                background: #fff;
                border: 1.5px solid #0b1f4d;
                border-radius: 7px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            /* Header tabel biru (navy), font putih: Room | Capacity/Occupancy | 5R Point | checkbox warna */
            .rhead { display: flex; align-items: stretch; background: #0b1f4d; color: #fff; }
            .rhead > div { padding: 4px 7px; border-right: 1px solid rgba(255,255,255,0.22); display: flex; }
            .rhead > div:last-child { border-right: none; }

            .rhead .rnum { align-items: center; font-weight: 800; font-size: 13px; color: #fff; min-width: 58px; }
            .rhead .capocc { flex-direction: column; justify-content: center; gap: 2px; font-size: 9.5px; flex: 1; color: #d5dbe6; }
            .rhead .capocc .r { display: flex; justify-content: space-between; gap: 6px; }
            .rhead .capocc b { color: #fff; font-size: 10.5px; }

            .rhead .r5lbl { align-items: center; justify-content: center; font-weight: 700; font-size: 9.5px; color: #fff; text-align: center; line-height: 1.1; }

            .rhead .r5checks { flex-direction: column; justify-content: center; gap: 2px; font-size: 9px; }
            .rhead .r5checks .chk { display: flex; align-items: center; gap: 4px; color: rgba(255,255,255,0.7); }
            .rhead .r5checks .box { width: 9px; height: 9px; border: 1.5px solid rgba(255,255,255,0.55); border-radius: 2px; display: inline-block; flex-shrink: 0; }
            .rhead .r5checks .chk.on { font-weight: 700; color: #fff; }
            .rhead .r5checks .chk.on.hijau .box { background: #22c55e; border-color: #22c55e; }
            .rhead .r5checks .chk.on.kuning .box { background: #eab308; border-color: #eab308; }
            .rhead .r5checks .chk.on.merah .box { background: #ef4444; border-color: #ef4444; }

            /* Keterangan bergaris di bawah kartu: mahasiswa sakit/izin dari form ketua kamar */
            .room-ket { border-top: 1.5px solid #0b1f4d; padding: 4px 8px; font-size: 9.5px; color: #475569; margin-top: auto; line-height: 1.35; }
            .room-ket b { color: #0b1f4d; }
            .room-ket .k-item { color: #b91c1c; font-weight: 600; }

            /* Ruang isolasi: kartu & status khusus */
            .isolation-card { border-color: #b91c1c; }
            .isolation-card .rhead { background: #b91c1c; }
            .rhead .iso-lbl { text-transform: none; }
            .student.status-isolasi .avatar { border-color: #8b5cf6; }
            .student.status-isolasi .time { color: #7c3aed; }

            .r5-legend { display: flex; gap: 16px; font-size: 12px; color: #fff; }
            .r5-legend .lg { display: flex; align-items: center; gap: 6px; }

            .room-body { padding: 8px 10px 10px; }

            .pill {
                display: flex;
                align-items: center;
                gap: 6px;
                border-radius: 4px;
                padding: 5px 8px;
                font-weight: 700;
                font-size: 11px;
                margin-bottom: 8px;
            }

            .pill-status { background: #e9f9ef; color: #16a34a; }

            .students {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 6px;
                margin-bottom: 6px;
            }

            .student { text-align: center; min-width: 0; }

            .avatar {
                width: 100%;
                aspect-ratio: 1;
                border-radius: 6px;
                object-fit: cover;
                background: #eef1f6;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #94a3b8;
                margin-bottom: 3px;
                border: 2.5px solid #cbd5e1;   /* default: belum fingerprint (abu-abu) */
            }
            .avatar svg { width: 60%; height: 60%; }

            /* Sudah fingerprint (hadir) -> border hijau */
            .student.status-present .avatar { border-color: #16a34a; }

            .student .name {
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .student .time { font-size: 10px; font-weight: 700; color: #94a3b8; }
            .student.status-present .time { color: #16a34a; }
            .student.status-sakit .time { color: #dc2626; }
            .student.status-izin .time { color: #d97706; }

            .empty-state {
                text-align: center;
                color: #94a3b8;
                font-size: 11px;
                padding: 6px 0 3px;
            }

            .conditions { border-top: 1px solid #eef1f6; margin-top: 6px; padding-top: 6px; }

            .condition-row {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 4px 6px;
                border-radius: 5px;
                margin-bottom: 4px;
                font-size: 11px;
            }

            .condition-row.sakit { background: #fdeceb; }
            .condition-row.izin { background: #fff4e0; }

            .condition-badge {
                border-radius: 4px;
                padding: 2px 6px;
                font-weight: 700;
                font-size: 9px;
                color: #fff;
                white-space: nowrap;
            }

            .condition-badge.sakit { background: #dc2626; }
            .condition-badge.izin { background: #d97706; }

            .condition-info { min-width: 0; }
            .condition-info .name { font-weight: 700; font-size: 10.5px; color: #1b1f2a; }
            .condition-info .note { font-size: 9.5px; color: #64748b; }

            /* ===== Alert Abnormality ===== */
            /* Bar merah tetap di bawah layar saat ada mahasiswa belum CI. */
            #abnormal-bar {
                display: none;
                position: fixed;
                left: 0; right: 0; bottom: 0;
                z-index: 50;
                background: #dc2626;
                color: #fff;
                padding: 12px 24px;
                align-items: center;
                gap: 14px;
                font-size: 16px;
                font-weight: 700;
                box-shadow: 0 -4px 16px rgba(0,0,0,0.2);
                animation: abnormalPulse 1.4s ease-in-out infinite;
            }
            #abnormal-bar .warn { font-size: 22px; flex-shrink: 0; }
            #abnormal-bar .txt { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            @keyframes abnormalPulse {
                0%, 100% { background: #dc2626; }
                50% { background: #b91c1c; }
            }

            /* Flash besar di tengah, muncul berkala untuk menarik perhatian. */
            #abnormal-flash {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 60;
                background: rgba(153,27,27,0.94);
                color: #fff;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                gap: 18px;
                text-align: center;
            }
            #abnormal-flash.show { display: flex; animation: flashIn .3s ease; }
            #abnormal-flash .big { font-size: 64px; }
            #abnormal-flash .head { font-size: 40px; font-weight: 800; letter-spacing: 1px; }
            #abnormal-flash .sub { font-size: 22px; opacity: .9; max-width: 80vw; }
            @keyframes flashIn { from { opacity: 0; transform: scale(.9); } to { opacity: 1; transform: scale(1); } }
        </style>
    </head>
    <body>
        <header>
            <div class="brand">
                <img src="{{ asset('favicon.svg') }}?v=2" alt="Logo Asrama AKTI" class="brand-logo">
                <h1>Dormitory Room Occupation</h1>
            </div>

            <div class="header-right">
                <div class="datetime">
                    <div id="live-date">{{ $today->format('l, d F Y') }}</div>
                    <div id="live-clock">{{ now()->format('H:i:s') }} WIB</div>
                </div>
                <div class="qr-box">
                    <div class="qr">{!! $reportQr !!}</div>
                    <div class="qr-label">Scan to report<br>sick / on-duty</div>
                </div>
            </div>
        </header>

        <main id="rooms">
            @include('partials.kiosk-rooms')
        </main>

        <!-- Alert abnormality (mahasiswa belum CI setelah jam batas) -->
        <div id="abnormal-bar">
            <span class="warn">&#9888;</span>
            <span class="txt" id="abnormal-text"></span>
        </div>
        <div id="abnormal-flash">
            <div class="big">&#9888;</div>
            <div class="head" id="abnormal-flash-head"></div>
            <div class="sub" id="abnormal-flash-sub"></div>
        </div>

        <script>
            // Jam berjalan (client-side).
            function tick() {
                const el = document.getElementById('live-clock');
                if (!el) return;
                const now = new Date();
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const ss = String(now.getSeconds()).padStart(2, '0');
                el.textContent = `${hh}:${mm}:${ss} WIB`;
            }
            setInterval(tick, 1000);

            // Baca daftar abnormal dari JSON yang disisipkan partial.
            function readAbnormal() {
                const el = document.getElementById('abnormal-json');
                if (!el) return [];
                try { return JSON.parse(el.textContent || '[]'); } catch (e) { return []; }
            }

            // Update bar merah bawah sesuai daftar abnormal terbaru.
            function updateAbnormalBar() {
                const list = readAbnormal();
                const bar = document.getElementById('abnormal-bar');
                const rooms = document.getElementById('rooms');
                if (!list.length) {
                    bar.style.display = 'none';
                    rooms.classList.remove('has-alert');
                    return;
                }
                const names = list.map(s => `${s.name} (${s.room})`).join('   •   ');
                document.getElementById('abnormal-text').textContent =
                    `BELUM CI/CO — ${list.length} mahasiswa: ${names}`;
                bar.style.display = 'flex';
                rooms.classList.add('has-alert');
            }

            // Polling halus tiap 10 detik: ambil kartu kamar terbaru, ganti tanpa reload.
            async function refreshRooms() {
                try {
                    const res = await fetch('{{ route('kiosk.rooms') }}', { cache: 'no-store' });
                    if (!res.ok) return;
                    const html = await res.text();
                    document.getElementById('rooms').innerHTML = html;
                    updateAbnormalBar();
                    showSlides(true); // tampilkan slide aktif saat ini tanpa animasi (anti-kedip)
                } catch (e) { /* abaikan blip jaringan, coba lagi siklus berikutnya */ }
            }
            setInterval(refreshRooms, 10000);
            updateAbnormalBar(); // saat load awal

            // Flash besar berkala tiap 30 detik bila ada abnormal (muncul ~6 detik).
            setInterval(() => {
                const list = readAbnormal();
                if (!list.length) return;
                const flash = document.getElementById('abnormal-flash');
                document.getElementById('abnormal-flash-head').textContent =
                    `${list.length} MAHASISWA BELUM ADA STATUS CI/CO`;
                document.getElementById('abnormal-flash-sub').textContent =
                    list.map(s => s.name).join(', ');
                flash.classList.add('show');
                setTimeout(() => flash.classList.remove('show'), 6000);
            }, 30000);

            // ===== Slideshow: geser otomatis antar slide tiap 30 detik (bukan scroll) =====
            const SLIDE_INTERVAL = 30000; // ms per slide
            let slideIndex = 0;

            function getSlides() {
                return Array.from(document.querySelectorAll('#rooms .slide'));
            }

            // Tampilkan slide aktif. instant=true -> tanpa animasi (untuk load/refresh).
            function showSlides(instant) {
                const slides = getSlides();
                if (!slides.length) return;
                if (slideIndex >= slides.length) slideIndex = 0;
                const rooms = document.getElementById('rooms');
                if (instant) rooms.classList.add('no-anim');
                slides.forEach((s, i) => s.classList.toggle('active', i === slideIndex));
                if (instant) {
                    // lepas 'no-anim' setelah 2 frame agar perubahan berikutnya beranimasi
                    requestAnimationFrame(() => requestAnimationFrame(() => rooms.classList.remove('no-anim')));
                }
            }

            function nextSlide() {
                const slides = getSlides();
                if (slides.length <= 1) return; // 1 slide: diam saja
                slideIndex = (slideIndex + 1) % slides.length;
                showSlides(false);
            }

            showSlides(true);                    // tampilkan slide pertama saat load
            setInterval(nextSlide, SLIDE_INTERVAL);
        </script>
    </body>
</html>
