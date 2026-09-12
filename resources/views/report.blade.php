<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="/favicon.svg?v=2" type="image/svg+xml">
        <title>Lapor Kondisi Anggota — Asrama AKTI</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background: #eef1f6;
                color: #1b1f2a;
                padding: 16px;
                line-height: 1.5;
            }
            .card {
                max-width: 460px;
                margin: 0 auto;
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 12px rgba(11,31,77,0.1);
                overflow: hidden;
            }
            .card-header {
                background: #0b1f4d;
                color: #fff;
                padding: 18px 20px;
            }
            .card-header h1 { font-size: 18px; }
            .card-header p { font-size: 13px; opacity: 0.85; margin-top: 4px; }
            .card-body { padding: 20px; }

            label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; margin-top: 14px; }
            label:first-child { margin-top: 0; }
            .req { color: #dc2626; }

            select, input[type="text"], input[type="password"], input[type="date"], textarea {
                width: 100%;
                padding: 11px 12px;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                font-size: 15px;
                font-family: inherit;
                background: #fff;
            }
            select:focus, input:focus, textarea:focus { outline: none; border-color: #0b1f4d; }
            textarea { resize: vertical; min-height: 60px; }

            .hint { font-size: 12px; color: #64748b; margin-top: 4px; }

            button {
                width: 100%;
                margin-top: 22px;
                padding: 13px;
                background: #0b1f4d;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
            }
            button:active { background: #09193d; }

            .alert { padding: 12px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
            .alert-success { background: #e9f9ef; color: #15803d; border: 1px solid #bbf7d0; }
            .alert-error { background: #fdeceb; color: #b91c1c; border: 1px solid #fecaca; }
            .field-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="card-header">
                <h1>Lapor Kondisi Anggota</h1>
                <p>Untuk ketua kamar — laporkan anggota yang sakit, di rumah sakit, training, atau dinas luar.</p>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error">Periksa kembali isian yang ditandai di bawah.</div>
                @endif

                <form method="POST" action="{{ route('report.store') }}">
                    @csrf

                    <label>Kamar <span class="req">*</span></label>
                    <select name="room_id" id="room_id" required>
                        <option value="">— Pilih kamar —</option>
                        @foreach ($roomOptions as $room)
                            <option value="{{ $room['id'] }}" @selected(old('room_id') == $room['id'])>{{ $room['label'] }}</option>
                        @endforeach
                    </select>
                    @error('room_id') <div class="field-error">{{ $message }}</div> @enderror

                    <label>PIN Kamar <span class="req">*</span></label>
                    <input type="password" name="access_pin" inputmode="numeric" autocomplete="off" required>
                    <div class="hint">PIN yang hanya diketahui ketua kamar (dari admin).</div>
                    @error('access_pin') <div class="field-error">{{ $message }}</div> @enderror

                    <label>Anggota <span class="req">*</span></label>
                    <select name="student_id" id="student_id" required>
                        <option value="">— Pilih kamar dulu —</option>
                    </select>
                    @error('student_id') <div class="field-error">{{ $message }}</div> @enderror

                    <label>Kondisi <span class="req">*</span></label>
                    <select name="reason" required>
                        <option value="">— Pilih kondisi —</option>
                        @foreach ($reasons as $key => $r)
                            <option value="{{ $key }}" @selected(old('reason') == $key)>{{ $r['label'] }}</option>
                        @endforeach
                    </select>
                    @error('reason') <div class="field-error">{{ $message }}</div> @enderror

                    <label>Tanggal Mulai <span class="req">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required>
                    @error('start_date') <div class="field-error">{{ $message }}</div> @enderror

                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}">
                    <div class="hint">Kosongkan jika belum tahu sampai kapan.</div>
                    @error('end_date') <div class="field-error">{{ $message }}</div> @enderror

                    <label>Catatan</label>
                    <textarea name="note" maxlength="255" placeholder="Opsional, mis. nama RS / lokasi training">{{ old('note') }}</textarea>
                    @error('note') <div class="field-error">{{ $message }}</div> @enderror

                    <button type="submit">Simpan Laporan</button>
                </form>
            </div>
        </div>

        <script>
            const studentsByRoom = @json($studentsByRoom);
            const oldStudent = @json(old('student_id'));
            const roomSelect = document.getElementById('room_id');
            const studentSelect = document.getElementById('student_id');

            function fillStudents() {
                const roomId = roomSelect.value;
                const list = studentsByRoom[roomId] || [];
                studentSelect.innerHTML = '';
                if (!roomId) {
                    studentSelect.innerHTML = '<option value="">— Pilih kamar dulu —</option>';
                    return;
                }
                if (list.length === 0) {
                    studentSelect.innerHTML = '<option value="">(belum ada anggota di kamar ini)</option>';
                    return;
                }
                studentSelect.insertAdjacentHTML('beforeend', '<option value="">— Pilih anggota —</option>');
                list.forEach(s => {
                    const sel = String(s.id) === String(oldStudent) ? ' selected' : '';
                    studentSelect.insertAdjacentHTML('beforeend',
                        `<option value="${s.id}"${sel}>${s.name} (${s.code})</option>`);
                });
            }

            roomSelect.addEventListener('change', fillStudents);
            if (roomSelect.value) fillStudents();
        </script>
    </body>
</html>
