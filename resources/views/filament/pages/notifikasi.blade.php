<x-filament-panels::page>
    <div style="max-width: 640px;">
        <p style="margin-bottom: 16px; color:#4b5563;">
            Aktifkan notifikasi di HP ini agar menerima peringatan saat ada mahasiswa
            <b>abnormal</b> (belum CI setelah jam batas & tanpa keterangan). Cukup lakukan
            sekali per perangkat. Pastikan pakai <b>Chrome di Android</b>.
        </p>

        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
            <button type="button" id="btn-enable"
                class="fi-btn fi-btn-size-md"
                style="background:#0b1f4d;color:#fff;padding:10px 18px;border-radius:8px;font-weight:600;cursor:pointer;border:none;">
                Aktifkan Notifikasi HP
            </button>
            <button type="button" id="btn-test"
                style="background:#e5e7eb;color:#111827;padding:10px 18px;border-radius:8px;font-weight:600;cursor:pointer;border:none;">
                Kirim Uji Notifikasi
            </button>
        </div>

        <div id="notif-status" style="font-size:14px;color:#374151;padding:10px 14px;background:#f3f4f6;border-radius:8px;min-height:20px;">
            Status: belum diaktifkan di perangkat ini.
        </div>
    </div>

    <script>
        (function () {
            const VAPID_PUBLIC = @json(config('webpush.vapid.public_key'));
            const SUBSCRIBE_URL = @json(route('push.subscribe'));
            const TEST_URL = @json(route('push.test'));
            const CSRF = @json(csrf_token());

            const statusEl = document.getElementById('notif-status');
            const setStatus = (t) => { if (statusEl) statusEl.textContent = 'Status: ' + t; };

            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const raw = atob(base64);
                return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
            }

            async function enableNotif() {
                if (!VAPID_PUBLIC) { setStatus('VAPID public key belum diset di server.'); return; }
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    setStatus('Browser ini tidak mendukung notifikasi push.'); return;
                }
                setStatus('Memproses...');
                try {
                    const reg = await navigator.serviceWorker.register('/sw.js');
                    await navigator.serviceWorker.ready;
                    const perm = await Notification.requestPermission();
                    if (perm !== 'granted') { setStatus('Izin notifikasi ditolak. Aktifkan di pengaturan situs.'); return; }
                    const sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC),
                    });
                    const res = await fetch(SUBSCRIBE_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(sub),
                    });
                    setStatus(res.ok ? '✅ Notifikasi aktif di perangkat ini.' : 'Gagal mendaftar ke server.');
                } catch (e) {
                    setStatus('Error: ' + (e && e.message ? e.message : e));
                }
            }

            async function testNotif() {
                setStatus('Mengirim uji...');
                try {
                    const res = await fetch(TEST_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
                    const j = await res.json().catch(() => ({}));
                    setStatus(res.ok ? ('Uji terkirim ke ' + (j.sent || 0) + ' perangkat. Cek notifikasi HP.') : (j.message || 'Gagal kirim uji.'));
                } catch (e) {
                    setStatus('Error: ' + (e && e.message ? e.message : e));
                }
            }

            document.getElementById('btn-enable')?.addEventListener('click', enableNotif);
            document.getElementById('btn-test')?.addEventListener('click', testNotif);
        })();
    </script>
</x-filament-panels::page>
