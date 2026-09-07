<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=1920, initial-scale=1">
        <meta http-equiv="refresh" content="60">
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

            .datetime { text-align: right; font-size: 14px; opacity: 0.9; line-height: 1.35; }

            main { padding: 24px 28px; }

            .floor-title {
                font-size: 20px;
                font-weight: 700;
                color: #0b1f4d;
                margin: 8px 0 16px;
            }

            .room-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                margin-bottom: 28px;
            }

            .room-card {
                background: #fff;
                border-radius: 6px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(11,31,77,0.08);
            }

            .room-header {
                background: #0b1f4d;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 16px;
                font-weight: 700;
                font-size: 16px;
            }

            .room-body { padding: 14px 16px 18px; }

            .pill {
                display: flex;
                align-items: center;
                gap: 8px;
                border-radius: 4px;
                padding: 8px 12px;
                font-weight: 700;
                font-size: 14px;
                margin-bottom: 12px;
            }

            .pill-status { background: #e9f9ef; color: #16a34a; }

            .students {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 10px;
                margin-bottom: 12px;
            }

            .student { text-align: center; min-width: 0; }

            .avatar {
                width: 100%;
                aspect-ratio: 1;
                border-radius: 6px;
                object-fit: cover;
                background: #cbd5e1;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: 700;
                font-size: 15px;
                margin-bottom: 4px;
            }

            .student .name {
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .student .time { font-size: 10.5px; color: #16a34a; font-weight: 600; }

            .empty-state {
                text-align: center;
                color: #94a3b8;
                font-size: 13px;
                padding: 8px 0 4px;
            }

            .conditions { border-top: 1px solid #eef1f6; margin-top: 8px; padding-top: 10px; }

            .condition-row {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 8px;
                border-radius: 6px;
                margin-bottom: 6px;
                font-size: 12px;
            }

            .condition-row.sakit { background: #fdeceb; }
            .condition-row.izin { background: #fff4e0; }

            .condition-badge {
                border-radius: 4px;
                padding: 2px 7px;
                font-weight: 700;
                font-size: 10.5px;
                color: #fff;
                white-space: nowrap;
            }

            .condition-badge.sakit { background: #dc2626; }
            .condition-badge.izin { background: #d97706; }

            .condition-info { min-width: 0; }
            .condition-info .name { font-weight: 700; font-size: 12px; color: #1b1f2a; }
            .condition-info .note { font-size: 11px; color: #64748b; }
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

            <div class="datetime">
                <div id="live-date">{{ $today->format('l, d F Y') }}</div>
                <div id="live-clock">{{ now()->format('H:i:s') }} WIB</div>
            </div>
        </header>

        <main>
            @forelse ($floors as $floor)
                <div class="floor-title">{{ str_replace('Lantai', 'Floor', $floor->name) }}</div>

                <div class="room-grid">
                    @foreach ($floor->rooms as $room)
                        <div class="room-card">
                            <div class="room-header">
                                <span>ROOM {{ $room->room_number }}</span>
                                <span>{{ $room->capacity }} PEOPLE</span>
                            </div>

                            <div class="room-body">
                                <div class="pill pill-status">
                                    <span>OCCUPANCY {{ $room->occupancy }}/{{ $room->capacity }}</span>
                                </div>

                                @if ($room->occupants->isEmpty())
                                    <div class="empty-state">No one checked in yet</div>
                                @else
                                    <div class="students">
                                        @foreach ($room->occupants as $student)
                                            <div class="student">
                                                @if ($student->photo_path)
                                                    <img class="avatar" src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}">
                                                @else
                                                    <div class="avatar">{{ collect(explode(' ', $student->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</div>
                                                @endif
                                                <div class="name">{{ $student->name }}</div>
                                                <div class="time">CI: {{ $student->ci_time?->format('H:i') }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($room->students_with_condition->isNotEmpty())
                                    <div class="conditions">
                                        @foreach ($room->students_with_condition as $student)
                                            <div class="condition-row {{ $student->condition->type }}">
                                                <span class="condition-badge {{ $student->condition->type }}">
                                                    {{ $student->condition->type === 'sakit' ? 'SICK' : 'LEAVE ' . strtoupper($student->condition->direction ?? '') }}
                                                </span>
                                                <div class="condition-info">
                                                    <div class="name">{{ $student->name }} · {{ $student->student_code }}</div>
                                                    @if ($student->condition->note)
                                                        <div class="note">{{ \Illuminate\Support\Str::limit($student->condition->note, 32) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div style="text-align:center; color:#94a3b8; font-size:18px; padding:80px 0;">
                    No students assigned yet.
                </div>
            @endforelse
        </main>

        <script>
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
        </script>
    </body>
</html>
