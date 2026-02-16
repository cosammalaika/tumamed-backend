<x-layouts.app :title="__('Dashboard')">
    @if(auth()->user()->isAdmin())
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg">{{ __('Hospitals') }}</flux:heading>
                <div class="mt-2 text-3xl font-semibold">{{ $hospitalsCount }}</div>
                <flux:link :href="route('admin.hospitals')" wire:navigate class="text-sm text-blue-600 dark:text-blue-300 mt-2 inline-block">
                    {{ __('Manage hospitals') }}
                </flux:link>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg">{{ __('Pharmacies') }}</flux:heading>
                <div class="mt-2 text-3xl font-semibold">{{ $pharmaciesCount }}</div>
                <flux:link :href="route('admin.pharmacies')" wire:navigate class="text-sm text-blue-600 dark:text-blue-300 mt-2 inline-block">
                    {{ __('Manage pharmacies') }}
                </flux:link>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg">{{ __('Requests') }}</flux:heading>
                <div class="mt-2 text-3xl font-semibold">{{ $requestsCount }}</div>
                <flux:link :href="route('admin.requests')" wire:navigate class="text-sm text-blue-600 dark:text-blue-300 mt-2 inline-block">
                    {{ __('View requests') }}
                </flux:link>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
            <flux:heading size="lg">{{ __('Welcome back') }}</flux:heading>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">
                {{ __('Use the pharmacy portal to respond to incoming requests in the API or mobile client.') }}
            </p>
        </div>
    @endif
</x-layouts.app>
