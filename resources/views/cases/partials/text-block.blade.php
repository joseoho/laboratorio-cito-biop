@if (filled($text))
    <div class="mb-3">
        <div class="section-title">{{ $label }}</div>
        <div class="p-2 bg-light border rounded" style="white-space:pre-wrap">{{ $text }}</div>
    </div>
@endif
