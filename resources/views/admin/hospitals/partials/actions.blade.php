<div class="d-inline-flex gap-1 table-actions">
    <form method="POST" action="{{ route('admin.hospitals.toggle-active', $row) }}" class="m-0">
        @csrf
        <button
            type="submit"
            class="btn btn-sm {{ $row->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
            onclick="return confirm('{{ $row->is_active ? __('Disable this hospital?') : __('Activate this hospital?') }}')"
        >
            {{ $row->is_active ? __('Disable') : __('Activate') }}
        </button>
    </form>
</div>

