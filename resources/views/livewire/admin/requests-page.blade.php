<x-admin.page title="{{ __('Medicine Requests') }}" subtitle="{{ __('Track request lifecycle and routing events.') }}">
    <x-admin.section>
    <x-admin.table>
        <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
            <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Patient') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Hospital') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Self') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Phone') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Matched Pharmacy') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Radius (km)') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Prescriptions') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Responses') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                @foreach ($requests as $request)
                    <tr class="align-top hover:bg-zinc-50/80 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $request->patient_name }}</div>
                            <div class="text-zinc-500 dark:text-zinc-400">{{ $request->is_self_patient ? __('Self patient') : __('Proxy order') }}</div>
                        </td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $request->hospital->name }}</td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $request->is_self_patient ? __('Yes') : __('No') }}</td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $request->patient_phone }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$request->status" /></td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $request->matchedPharmacy->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $request->search_radius_km }}</td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $request->prescriptions->count() }}</td>
                        <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $request->pharmacyRequests->count() }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $request->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.table>
    </x-admin.section>
</x-admin.page>
