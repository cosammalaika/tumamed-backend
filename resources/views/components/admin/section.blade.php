@props([
    'class' => '',
    'padding' => '',
])

<div {{ $attributes->merge(['class' => "card {$class}"]) }}>
    <div class="card-body {{ $padding }}">
        {{ $slot }}
    </div>
</div>
