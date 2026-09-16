<script type="application/json" id="abnormal-json">@json($abnormal ?? [])</script>
@php
    // Slide: tiap lantai (maks 10 kamar/slide, lantai yang lebih tetap di slide lantai
    // yang sama). Slide terakhir: VOKASI.
    $slides = collect();
    foreach ($floors as $floor) {
        foreach ($floor->rooms->chunk(10) as $chunk) {
            $slides->push(['floor' => $floor, 'rooms' => $chunk]);
        }
    }
    if (! empty($vocations)) {
        $slides->push(['vocations' => $vocations]);
    }
@endphp
@forelse ($slides as $i => $slide)
    <div class="slide{{ $i === 0 ? ' active' : '' }}">
        @if (isset($slide['vocations']))
            {{-- Summary VOKASI (disalin JS ke header saat slide ini aktif) --}}
            <div class="slide-summary">
                <span class="hs-title">VOKASI</span>
                @foreach ($vocationSummary['rows'] ?? [] as $r)
                    <span class="hs-stat"><b>{{ $r['count'] }}</b><span>{{ $r['label'] }}</span></span>
                @endforeach
                <span class="hs-stat hs-total"><b>{{ $vocationSummary['total'] ?? 0 }}</b><span>Total</span></span>
            </div>
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
        @else
            @php $fname = str_replace('Lantai', 'Floor', $slide['floor']->name); @endphp
            {{-- Summary lantai (disalin JS ke header saat slide ini aktif) --}}
            <div class="slide-summary">
                <span class="hs-title">{{ strtoupper($fname) }}</span>
                @foreach ($slide['floor']->summary['rows'] as $r)
                    <span class="hs-stat"><b>{{ $r['count'] }}</b><span>{{ $r['label'] }}</span></span>
                @endforeach
                <span class="hs-stat hs-total"><b>{{ $slide['floor']->summary['total'] }}</b><span>Total</span></span>
            </div>
            <div class="floor-title">{{ $fname }}</div>
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
