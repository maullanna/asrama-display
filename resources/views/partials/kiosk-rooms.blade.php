<script type="application/json" id="abnormal-json">@json($abnormal ?? [])</script>
@php
    // Susun "slide": ruang isolasi (bila ada) jadi slide pertama, lalu tiap lantai
    // (maksimal 10 kamar per slide, 5 x 2).
    $slides = collect();
    if (! empty($isolation)) {
        $slides->push(['isolation' => $isolation]);
    }
    foreach ($floors as $floor) {
        foreach ($floor->rooms->chunk(10) as $chunk) {
            $slides->push(['floor' => $floor, 'rooms' => $chunk]);
        }
    }
    // Slide terakhir: VOKASI (mahasiswa A10 di luar), bila ada.
    if (! empty($vocations)) {
        $slides->push(['vocations' => $vocations]);
    }
@endphp
@forelse ($slides as $i => $slide)
    <div class="slide{{ $i === 0 ? ' active' : '' }}">
        @if (isset($slide['vocations']))
            <div class="vok-page">
                <div class="vok-title">VOKASI</div>
                <div class="vok-groups">
                    @foreach ($slide['vocations'] as $group)
                        <div class="vok-group">
                            <div class="vok-loc">{{ strtoupper($group['label']) }}</div>
                            <div class="vok-grid">
                                @foreach ($group['students'] as $v)
                                    <div class="vok-item">
                                        @if ($v->photo_path)
                                            <img class="vok-photo" src="{{ asset('storage/'.$v->photo_path) }}" alt="{{ $v->name }}">
                                        @else
                                            <div class="vok-photo vok-empty">
                                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.34 0-10 1.67-10 5v2h20v-2c0-3.33-6.66-5-10-5z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="vok-name">{{ $v->name }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif (isset($slide['isolation']))
            <div class="floor-title">Ruang Isolasi</div>
            <div class="room-grid">
                @include('partials.kiosk-room-card', ['room' => $slide['isolation'], 'isolation' => true])
            </div>
        @else
            <div class="floor-title">{{ str_replace('Lantai', 'Floor', $slide['floor']->name) }}</div>
            <div class="room-grid">
                @foreach ($slide['rooms'] as $room)
                    @include('partials.kiosk-room-card', ['room' => $room])
                @endforeach
            </div>
        @endif
    </div>
@empty
    <div class="slide active">
        <div style="text-align:center; color:#94a3b8; font-size:18px; padding:80px 0;">
            No students assigned yet.
        </div>
    </div>
@endforelse
