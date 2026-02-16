<x-admin.page title="{{ __('Hospitals') }}" subtitle="{{ __('Manage hospital records and coverage.') }}">
    <div class="row g-3">
        <div class="col-12">
        <x-admin.section>
            <x-admin.table>
                <table id="hospitalsTable" class="table table-bordered table-hover align-middle table-nowrap w-100">
                    <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Town') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Status') }}</th>
                            <th class="px-4 py-2 no-sort table-actions-column"></th>
                        </tr>
                    </thead>
                </table>
            </x-admin.table>
        </x-admin.section>
        </div>
    </div>
</x-admin.page>

@push('scripts')
    <script>
        (function () {
            function initHospitalsTable() {
                if (!window.jQuery || !window.jQuery.fn.DataTable) return;
                const $table = window.jQuery('#hospitalsTable');
                if (!$table.length) return;

                if (window.jQuery.fn.DataTable.isDataTable('#hospitalsTable')) {
                    $table.DataTable().destroy();
                    $table.empty().append(
                        `<thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Name') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Town') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Status') }}</th>
                                <th class="px-4 py-2 no-sort table-actions-column"></th>
                            </tr>
                        </thead>`
                    );
                }

                $table.DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ route('admin.hospitals.datatables') }}",
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[0, 'asc']],
                    columns: [
                        { data: 'name', name: 'name' },
                        { data: 'town', name: 'town' },
                        { data: 'type', name: 'type' },
                        { data: 'status', name: 'is_active', orderable: false, searchable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                    ],
                });
            }

            document.addEventListener('DOMContentLoaded', initHospitalsTable);
            window.addEventListener('livewire:navigated', initHospitalsTable);
        })();
    </script>
@endpush
