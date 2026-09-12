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
@endphp
@forelse ($slides as $i => $slide)
    <div class="slide{{ $i === 0 ? ' active' : '' }}">
        @if (isset($slide['isolation']))
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
