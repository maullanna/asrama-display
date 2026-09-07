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
                                        <img class="avatar" src="{{ asset('storage/'.$student->photo_path) }}" alt="{{ $student->name }}">
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
                                        {{ $student->condition->type === 'sakit' ? 'SICK' : 'LEAVE '.strtoupper($student->condition->direction ?? '') }}
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
