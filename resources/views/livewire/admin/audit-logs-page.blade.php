<x-admin.page title="{{ __('Audit Logs') }}" subtitle="{{ __('Review critical actions across the platform.') }}">
    <x-admin.section>
    <x-admin.table>
        <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
            <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Time') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Actor') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Action') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Subject') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('IP') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Meta') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                @foreach ($logs as $log)
                    <tr class="align-top hover:bg-zinc-50/80 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $log->actor_name ?? 'N/A' }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $log->actor_type }}</div>
                        </td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $log->action }}</td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                            {{ $log->subject_type ?? '—' }} @if($log->subject_id) (#{{ $log->subject_id }}) @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $log->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $log->meta ? \Illuminate\Support\Str::limit(json_encode($log->meta), 80) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.table>
    </x-admin.section>
</x-admin.page>
