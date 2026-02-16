<x-admin.page title="Dashboard" subtitle="Welcome back, {{ auth()->user()->name ?? 'Admin' }}.">
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Hospitals</p>
                    <h4 class="mb-2">{{ number_format($hospitalCount) }}</h4>
                    <div id="spark-hospitals" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Pharmacies</p>
                    <h4 class="mb-2">{{ number_format($pharmacyCount) }}</h4>
                    <div id="spark-pharmacies" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Active Requests</p>
                    <h4 class="mb-2">{{ number_format($activeRequests) }}</h4>
                    <div id="spark-active" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Delivered (30d)</p>
                    <h4 class="mb-2">{{ number_format($deliveredLast30) }}</h4>
                    <div id="spark-delivered" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Market Overview</h4>
                    <div id="requestsChart" class="apex-charts" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Request Status Breakdown</h4>
                    <div id="statusChart" class="apex-charts" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Pharmacy Activity</h4>
                    <div id="pharmacyChart" class="apex-charts" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Recent Requests</h4>
                    <x-admin.table>
                        <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                    <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide">Request</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide">Patient</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide">Hospital</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide">Created</th>
                            <th class="px-4 py-2 no-sort table-actions-column"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                        @forelse ($recentRequests as $req)
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/70">
                                <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">{{ \Illuminate\Support\Str::limit($req->id, 8, '') }}</td>
                                <td class="px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ $req->patient->name }}</td>
                                <td class="px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ $req->hospital->name }}</td>
                                <td class="px-4 py-2"><x-status-badge :status="$req->status" /></td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $req->created_at->format('M d, H:i') }}</td>
                                <td class="px-4 py-2 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary">View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-zinc-500 dark:text-zinc-400">No recent requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                    </x-admin.table>
                </div>
            </div>
        </div>
    </div>
</x-admin.page>

@push('scripts')
    <script id="dashboard-chart-data" type="application/json">
        {{ json_encode([
            'hospitalCount' => $hospitalCount,
            'pharmacyCount' => $pharmacyCount,
            'activeRequests' => $activeRequests,
            'deliveredLast30' => $deliveredLast30,
            'requestsByMonth' => $requestsByMonth,
            'statusCounts' => $statusCounts,
            'pharmacyActivity' => $pharmacyActivity,
        ]) }}
    </script>
    <script src="{{ asset('admin/js/pages/dashboard.js') }}"></script>
@endpush
