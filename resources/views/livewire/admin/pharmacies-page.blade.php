<x-admin.page title="{{ __('Pharmacies') }}" subtitle="{{ __('Manage partner pharmacies and availability.') }}">
    <x-slot:actions>
        <button type="button" class="btn btn-primary btn-sm" wire:click="resetForm">{{ __('New Pharmacy') }}</button>
    </x-slot:actions>

    <div class="row g-3">
        <div class="col-12">
        <x-admin.section>
            <x-admin.table>
                <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                    <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Town') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Verification') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Activity') }}</th>
                            <th class="px-4 py-2 no-sort table-actions-column"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                        @foreach ($pharmacies as $pharmacy)
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/70">
                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $pharmacy->name }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $pharmacy->town ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $pharmacy->is_verified ? 'bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' }}">
                                        {{ $pharmacy->is_verified ? __('Verified') : __('Pending') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($pharmacy->is_active)
                                        <span class="badge bg-soft-success text-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit('{{ $pharmacy->id }}')">{{ __('Edit') }}</button>
                                        <form method="POST" action="{{ route('admin.pharmacies.toggle-active', $pharmacy) }}" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $pharmacy->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" onclick="return confirm('{{ $pharmacy->is_active ? __('Disable this pharmacy?') : __('Activate this pharmacy?') }}')">
                                                {{ $pharmacy->is_active ? __('Disable') : __('Activate') }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="delete('{{ $pharmacy->id }}')" wire:confirm="{{ __('Delete this pharmacy?') }}">{{ __('Delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.table>
        </x-admin.section>
        </div>
    </div>

    <div class="modal fade" id="pharmacyModal" tabindex="-1" aria-labelledby="pharmacyModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pharmacyModalLabel">
                        {{ $editingId ? __('Edit Pharmacy') : __('Create Pharmacy') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Name') }}</label>
                                <input type="text" class="form-control" wire:model.defer="form.name" required>
                                @error('form.name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" class="form-control" wire:model.defer="form.email">
                                @error('form.email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Phone') }}</label>
                                <input type="text" class="form-control" wire:model.defer="form.phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Town') }}</label>
                                <input type="text" class="form-control" wire:model.defer="form.town">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Address') }}</label>
                                <input type="text" class="form-control" wire:model.defer="form.address">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Latitude') }}</label>
                                <input type="number" step="0.0000001" class="form-control" wire:model.live="form.latitude">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Longitude') }}</label>
                                <input type="number" step="0.0000001" class="form-control" wire:model.live="form.longitude">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Radius (km)') }}</label>
                                <select class="form-select" wire:model.live="radiusKm">
                                    @foreach ([1, 2, 3, 4, 5] as $radius)
                                        <option value="{{ $radius }}">{{ $radius }} km</option>
                                    @endforeach
                                </select>
                                @error('radiusKm') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Closest Hospital') }}</label>
                                <select class="form-select" wire:model="hospitalId" required>
                                    <option value="">{{ __('Select hospital') }}</option>
                                    @foreach ($hospitals as $hospital)
                                        <option value="{{ $hospital->id }}">
                                            {{ $hospital->name }}
                                            @isset($hospital->distance_km)
                                                ({{ number_format($hospital->distance_km, 2) }} km)
                                            @endisset
                                        </option>
                                    @endforeach
                                </select>
                                @error('hospitalId') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pharmacyActive" wire:model="form.is_active">
                                    <label class="form-check-label" for="pharmacyActive">{{ __('Active') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pharmacyVerified" wire:model="form.is_verified">
                                    <label class="form-check-label" for="pharmacyVerified">{{ __('Verified') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pharmacyOpen" wire:model="form.is_open">
                                    <label class="form-check-label" for="pharmacyOpen">{{ __('Open') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-sm">{{ $editingId ? __('Update') : __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin.page>

@push('scripts')
<script>
    (function () {
        function pharmacyModalInstance() {
            const el = document.getElementById('pharmacyModal');
            if (!el || !window.bootstrap || !window.bootstrap.Modal) return null;
            return window.bootstrap.Modal.getOrCreateInstance(el);
        }

        window.addEventListener('open-pharmacy-modal', function () {
            const modal = pharmacyModalInstance();
            if (modal) modal.show();
        });

        window.addEventListener('close-pharmacy-modal', function () {
            const modal = pharmacyModalInstance();
            if (modal) modal.hide();
        });
    })();
</script>
@endpush
