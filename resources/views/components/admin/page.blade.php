@props([
    'title',
    'subtitle' => null,
])

<div>
    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>
            @if($subtitle)
                <p class="text-muted mb-0 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="page-title-right">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </div>
    </div>

    @isset($actions)
        <div class="mb-3 text-end">
            {{ $actions }}
        </div>
    @endisset

    <div class="row g-3">
        <div class="col-12">
            {{ $slot }}
        </div>
    </div>
</div>
