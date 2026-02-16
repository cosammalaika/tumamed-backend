@props(['icon' => null, 'label' => '', 'value' => 0, 'subtext' => null])

<div class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-300">
        @if($icon)
            @switch($icon)
                @case('heroicon-o-building-office')
                    <flux:icon.building-office class="size-5" />
                    @break
                @case('heroicon-o-building-storefront')
                    <flux:icon.building-storefront class="size-5" />
                    @break
                @case('heroicon-o-bolt')
                    <flux:icon.bolt class="size-5" />
                    @break
                @case('heroicon-o-check-circle')
                    <flux:icon.check-circle class="size-5" />
                    @break
                @default
                    <flux:icon.layout-grid class="size-5" />
            @endswitch
        @else
            <span class="text-lg">•</span>
        @endif
    </div>
    <div class="flex-1">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $label }}</p>
        <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($value) }}</p>
        @if($subtext)
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $subtext }}</p>
        @endif
    </div>
</div>
