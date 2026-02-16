@props([
    'class' => '',
])

<div data-admin-table {{ $attributes->merge(['class' => "{$class}"]) }}>
    <div class="table-responsive">
        {{ $slot }}
    </div>
</div>
