<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=1920, initial-scale=1">
        <meta http-equiv="refresh" content="600">
        <title>Dormitory Room Chart</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }

            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background: #eef1f6;
                color: #1b1f2a;
            }

            header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #0b1f4d;
                color: #fff;
                padding: 10px 28px;
            }

            .brand { display: flex; align-items: center; gap: 12px; }
            .brand svg { width: 26px; height: 26px; }
            .brand h1 { font-size: 20px; letter-spacing: .5px; }

            .header-right { display: flex; align-items: center; gap: 18px; }
            .datetime { text-align: right; font-size: 14px; opacity: 0.9; line-height: 1.35; }

            .qr-box { display: flex; align-items: center; gap: 10px; padding-left: 18px; border-left: 1px solid rgba(255,255,255,0.25); }
            .qr { background: #fff; padding: 5px; border-radius: 6px; width: 58px; height: 58px; display: flex; }
            .qr svg { width: 100%; height: 100%; display: block; }
            .qr-label { font-size: 11px; line-height: 1.3; opacity: 0.9; }

            main { padding: 24px 28px; }

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
                margin-bottom: 18px;
            }

            .room-card {
                background: #fff;
                border-radius: 6px;
                overflow: hidden;
                box-shadow: 0 1px 6px rgba(11,31,77,0.08);
            }

            .room-header {
                background: #0b1f4d;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 7px 10px;
                font-weight: 700;
                font-size: 12px;
            }

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
                border-radius: 5px;
                object-fit: cover;
                background: #cbd5e1;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: 700;
                font-size: 11px;
                margin-bottom: 3px;
            }

            .student .name {
                font-size: 9.5px;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .student .time { font-size: 9px; color: #16a34a; font-weight: 600; }

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
        </style>
    </head>
    <body>
        <header>
            <div class="brand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 21h18M4 21V9l8-5 8 5v12M9 21v-6h6v6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1>DORMITORY ROOM CHART</h1>
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

            // Polling halus tiap 10 detik: ambil kartu kamar terbaru, ganti tanpa reload.
            async function refreshRooms() {
                try {
                    const res = await fetch('{{ route('kiosk.rooms') }}', { cache: 'no-store' });
                    if (!res.ok) return;
                    const html = await res.text();
                    document.getElementById('rooms').innerHTML = html;
                } catch (e) { /* abaikan blip jaringan, coba lagi siklus berikutnya */ }
            }
            setInterval(refreshRooms, 10000);
        </script>
    </body>
</html>
