@php $iso = $room->is_isolation ?? false; @endphp
<div class="room-card{{ $iso ? ' isolation-card' : '' }}">
    <div class="rhead">
        <div class="rnum">{{ $iso ? 'ISOLASI ROOM' : 'ROOM '.$room->room_number }}</div>
        <div class="capocc">
            <div class="r"><span>Capacity</span><b>{{ $room->capacity }}</b></div>
            <div class="r"><span>Occupancy</span><b>{{ $room->occupancy }}</b></div>
        </div>
        @if ($iso)
            <div class="r5lbl iso-lbl">RUANG<br>ISOLASI</div>
        @else
            <div class="r5lbl">5R<br>Point</div>
            <div class="r5checks">
                <div class="chk hijau {{ $room->status_color === 'hijau' ? 'on' : '' }}"><span class="box"></span>Hijau</div>
                <div class="chk kuning {{ $room->status_color === 'kuning' ? 'on' : '' }}"><span class="box"></span>Kuning</div>
                <div class="chk merah {{ $room->status_color === 'merah' ? 'on' : '' }}"><span class="box"></span>Merah</div>
            </div>
        @endif
    </div>

    <div class="room-body">
        @php
            // Ruang isolasi: selalu tampilkan slot sebanyak kapasitas (2), terisi/kosong.
            $slots = $iso
                ? collect(range(0, max(1, (int) $room->capacity ?: 2) - 1))->map(fn ($i) => $room->students->get($i))
                : $room->students;
        @endphp
        <div class="students">
            @foreach ($slots as $student)
                @if ($student)
                    <div class="student status-{{ $student->status }}">
                        @if (! empty($student->bed_number))
                            <div class="bed">{{ $student->bed_number }}</div>
                        @endif
                        {{-- Foto muncul kalau sudah fingerprint (present/keluar). Selain itu ikon abu-abu. --}}
                        @if (in_array($student->status, ['present', 'checkout']) && $student->photo_path)
                            <img class="avatar" src="{{ asset('storage/'.$student->photo_path) }}" alt="{{ $student->name }}">
                        @else
                            <div class="avatar">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.34 0-10 1.67-10 5v2h20v-2c0-3.33-6.66-5-10-5z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="name">{{ $student->name }}</div>
                        <div class="time">
                            @if ($student->status === 'present') CI {{ $student->ci_time?->format('H:i') }}
                            @elseif ($student->status === 'checkout') CO {{ $student->ci_time?->format('H:i') }}
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Bed kosong (belum ada pasien isolasi) --}}
                    <div class="student status-empty">
                        <div class="avatar">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.34 0-10 1.67-10 5v2h20v-2c0-3.33-6.66-5-10-5z"/>
                            </svg>
                        </div>
                        <div class="name">Empty</div>
                        <div class="time"></div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="room-ket">
        <b>Keterangan:</b>
        @forelse ($room->students_with_condition as $student)
            <span class="k-item">{{ $student->name }} &mdash; {{ $student->condition->note ?: ucfirst($student->condition->type) }}</span>@if (! $loop->last), @endif
        @empty
            -
        @endforelse
    </div>
</div>
