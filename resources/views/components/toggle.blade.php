@props([
    'label' => null,
    'checked' => false,
    'disabled' => false,
])

@php
    $isDisabled = $disabled || $attributes->get('disabled');
@endphp

<label class="flex items-center gap-3 select-none {{ $isDisabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }}">
    <span class="relative inline-flex h-5 w-9 items-center">
        <input
            type="checkbox"
            {{ $attributes->class('peer sr-only')->merge([
                'aria-checked' => $checked ? 'true' : 'false',
            ]) }}
            @checked($checked)
            @disabled($isDisabled)
        />
        <span class="h-5 w-9 rounded-full bg-zinc-300 transition peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-300 dark:bg-zinc-700 dark:peer-focus:ring-blue-800 peer-focus:outline-none"></span>
        <span class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-4"></span>
    </span>

    @if ($label)
        <span class="text-sm text-neutral-800 dark:text-neutral-100">{{ $label }}</span>
    @endif
</label>
