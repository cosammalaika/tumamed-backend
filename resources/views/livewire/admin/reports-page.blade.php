<x-admin.page title="{{ __('Reports') }}" subtitle="{{ __('Analyze request activity, trends, and outcomes.') }}">
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">{{ __('Total Requests') }}</p>
                    <h4 class="mb-0 text-primary">{{ number_format($summary['totalRequests']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">{{ __('Delivered (30d)') }}</p>
                    <h4 class="mb-0 text-success">{{ number_format($summary['delivered30']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">{{ __('Active Requests') }}</p>
                    <h4 class="mb-0">{{ number_format($summary['activeRequests']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">{{ __('Failed / Expired') }}</p>
                    <h4 class="mb-0 text-danger">{{ number_format($summary['failedExpired']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 position-sticky" style="top: 84px; z-index: 2;">
        <div class="card-body">
            <form wire:submit.prevent="applyFilters" class="row g-3 align-items-end">
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">{{ __('From') }}</label>
                    <input type="date" wire:model="dateFrom" class="form-control">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">{{ __('To') }}</label>
                    <input type="date" wire:model="dateTo" class="form-control">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">{{ __('Hospital') }}</label>
                    <select wire:model="hospitalId" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($hospitals as $hospital)
                            <option value="{{ $hospital->id }}">{{ $hospital->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">{{ __('Pharmacy') }}</label>
                    <select wire:model="pharmacyId" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select wire:model="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Apply') }}</button>
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">{{ __('Reset') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('Requests per Day') }}</h5>
                    <div id="reportsRequestsChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('Status Breakdown') }}</h5>
                    <div id="reportsStatusChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <x-admin.section>
        <div class="d-flex justify-content-end gap-2 mb-3">
            <a href="{{ route('admin.reports.export', array_filter(request()->query() + ['format' => 'csv'])) }}" class="btn btn-sm btn-outline-primary">{{ __('Export CSV') }}</a>
            <a href="{{ route('admin.reports.export', array_filter(request()->query() + ['format' => 'xlsx'])) }}" class="btn btn-sm btn-outline-primary">{{ __('Export XLSX') }}</a>
            <a href="{{ route('admin.reports.export', array_filter(request()->query() + ['format' => 'pdf'])) }}" class="btn btn-sm btn-outline-primary">{{ __('Export PDF') }}</a>
        </div>
        <x-admin.table>
            <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                <thead>
                    <tr>
                        <th>{{ __('Request ID') }}</th>
                        <th>{{ __('Patient') }}</th>
                        <th>{{ __('Hospital') }}</th>
                        <th>{{ __('Pharmacy') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created at') }}</th>
                        <th>{{ __('Updated at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $request)
                        @php
                            $phone = (string) ($request->patient->phone ?? '');
                            $maskedPhone = strlen($phone) > 4 ? str_repeat('*', max(strlen($phone) - 4, 0)) . substr($phone, -4) : $phone;
                        @endphp
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $request->patient->name }}</div>
                                <div class="text-muted small">{{ $maskedPhone }}</div>
                            </td>
                            <td>{{ $request->hospital->name ?? '—' }}</td>
                            <td>{{ $request->currentPharmacy->name ?? '—' }}</td>
                            <td><x-status-badge :status="$request->status" /></td>
                            <td>{{ optional($request->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($request->updated_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>
    </x-admin.section>
</x-admin.page>

@push('scripts')
    <script id="reports-chart-data" type="application/json">{{ json_encode($chartData) }}</script>
    <script src="{{ asset('admin/js/pages/reports.js') }}"></script>
@endpush

