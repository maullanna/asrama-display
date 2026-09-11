<script type="application/json" id="abnormal-json">@json($abnormal ?? [])</script>
@php
    // Susun "slide": tiap lantai jadi satu slide; maksimal 10 kamar per slide (5 x 2).
    $slides = collect();
    foreach ($floors as $floor) {
        foreach ($floor->rooms->chunk(10) as $chunk) {
            $slides->push(['floor' => $floor, 'rooms' => $chunk]);
        }
    }
@endphp
@forelse ($slides as $i => $slide)
    <div class="slide{{ $i === 0 ? ' active' : '' }}">
        <div class="floor-title">{{ str_replace('Lantai', 'Floor', $slide['floor']->name) }}</div>

        <div class="room-grid">
            @foreach ($slide['rooms'] as $room)
                <div class="room-card r5-{{ $room->status_color ?? 'none' }}">
                    <div class="room-header">
                        <span class="room-title">
                            <span class="r5-dot r5-{{ $room->status_color ?? 'none' }}"></span>
                            ROOM {{ $room->room_number }}
                        </span>
                        <span>{{ $room->capacity }} PEOPLE</span>
                    </div>

                    <div class="room-body">
                        <div class="pill pill-status">
                            <span>OCCUPANCY {{ $room->occupancy }}/{{ $room->capacity }}</span>
                        </div>

                        <div class="students">
                            @foreach ($room->students as $student)
                                <div class="student status-{{ $student->status }}">
                                    @if ($student->photo_path)
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
                                        @switch($student->status)
                                            @case('present') CI {{ $student->ci_time?->format('H:i') }} @break
                                            @case('sakit') SICK @break
                                            @case('izin') LEAVE @break
                                            @default &mdash;
                                        @endswitch
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($room->students_with_condition->isNotEmpty())
                            <div class="conditions">
                                @foreach ($room->students_with_condition as $student)
                                    <div class="condition-row {{ $student->condition->type }}">
                                        <span class="condition-badge {{ $student->condition->type }}">
                                            {{ $student->condition->type === 'sakit' ? 'SICK' : 'LEAVE '.strtoupper($student->condition->direction ?? '') }}
                                        </span>
                                        <div class="condition-info">
                                            <div class="name">{{ $student->name }} &middot; {{ $student->student_code }}</div>
                                            @if ($student->condition->note)
                                                <div class="note">{{ \Illuminate\Support\Str::limit($student->condition->note, 32) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (filled($room->keterangan))
                            <div class="room-note"><span>Ket:</span> {{ \Illuminate\Support\Str::limit($room->keterangan, 70) }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="slide active">
        <div style="text-align:center; color:#94a3b8; font-size:18px; padding:80px 0;">
            No students assigned yet.
        </div>
    </div>
@endforelse
