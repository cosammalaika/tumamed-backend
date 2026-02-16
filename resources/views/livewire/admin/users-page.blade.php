<x-admin.page title="{{ __('Users') }}" subtitle="{{ __('Manage user access and account activity.') }}">
    <x-admin.section>
        <x-admin.table>
            <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="no-sort table-actions-column"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ strtoupper($user->role) }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-soft-success text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-light text-muted">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ optional($user->created_at)->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.access.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="m-0">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            onclick="return confirm('{{ $user->is_active ? __('Disable this user?') : __('Activate this user?') }}')">
                                            {{ $user->is_active ? __('Disable') : __('Activate') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>
    </x-admin.section>
</x-admin.page>

