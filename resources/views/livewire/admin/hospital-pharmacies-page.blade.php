<x-admin.page title="{{ __('Hospital ↔ Pharmacy Mappings') }}" subtitle="{{ __('Define routing priority from hospitals to pharmacies.') }}">
    <div class="row g-3">
        <div class="col-xl-4">
        <x-admin.section class="space-y-4">
            <flux:select wire:model="selectedHospitalId" :label="__('Hospital')">
                <option value="">{{ __('Select hospital') }}</option>
                @foreach ($hospitals as $hospital)
                    <option value="{{ $hospital->id }}">{{ $hospital->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="pharmacyId" :label="__('Pharmacy')">
                <option value="">{{ __('Select pharmacy') }}</option>
                @foreach ($pharmacies as $pharmacy)
                    <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                @endforeach
            </flux:select>

            <flux:input wire:model="priority" :label="__('Priority')" type="number" min="0" />
            <x-toggle wire:model="is_active" :label="__('Active')" wire:loading.attr="disabled" wire:target="save" />

            <button type="button" class="btn btn-primary btn-sm w-100" wire:click="save">{{ __('Save Mapping') }}</button>
        </x-admin.section>
        </div>

        <div class="col-xl-8">
        <x-admin.section>
            <x-admin.table>
                <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                    <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Pharmacy') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Priority') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Active') }}</th>
                            <th class="px-4 py-2 no-sort table-actions-column"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                        @forelse ($mappings as $mapping)
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/70">
                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $mapping->pharmacy->name ?? __('Unknown') }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $mapping->priority }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                    {{ $mapping->is_active ? __('Yes') : __('No') }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="remove('{{ $mapping->id }}')" wire:confirm="{{ __('Remove mapping?') }}">
                                        {{ __('Delete') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-sm text-neutral-500">
                                    {{ __('No mappings yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table>
        </x-admin.section>
        </div>
    </div>
</x-admin.page>
