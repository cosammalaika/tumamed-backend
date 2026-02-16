<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('admin/minia/assets/images/logo-sm.svg') }}" alt="" width="200">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('admin/minia/assets/images/logo-sm.svg') }}" alt="" width="200">
                    </span>
                </a>

                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('admin/minia/assets/images/logo-sm.svg') }}" alt="" width="200">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('admin/minia/assets/images/logo-sm.svg') }}" alt="" width="200">
                        <span class="logo-txt"></span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light"
                data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="fa fa-fw fa-bars"></i>
            </button>
        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item noti-icon position-relative"
                    id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i data-feather="bell" style="width: 24px; height: 24px;"></i>

                    <span class="badge bg-danger rounded-pill">5</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ..."
                                    aria-label="Search Result">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i class="mdi mdi-moon-waning-crescent fs-4 layout-mode-dark"></i>
                    <i class="mdi mdi-white-balance-sunny fs-4 layout-mode-light"></i>
                </button>
            </div>

            {{-- @livewire('notification.notification-bell') --}}


            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                    @php
                        $auth = auth()->user();
                        $photo = $auth?->profile_photo_path;
                        $photoPath = $photo ? ltrim($photo, '/') : null;
                        $hasPhoto = $photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath);

                        $nameSeed = trim(($auth->first_name ?? '') . ' ' . ($auth->last_name ?? ''));
                        $parts = preg_split('/\s+/u', $nameSeed) ?: [];
                        $initials = '';
                        foreach ($parts as $part) {
                            if ($part === '') {
                                continue;
                            }
                            $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
                            if (mb_strlen($initials, 'UTF-8') >= 2) {
                                break;
                            }
                        }
                        if ($initials === '' && !empty($auth?->email)) {
                            $initials = mb_strtoupper(mb_substr($auth->email, 0, 1, 'UTF-8'), 'UTF-8');
                        }
                        if ($initials === '') {
                            $initials = 'U';
                        }
                    @endphp

                    @if ($hasPhoto)
                        <img src="{{ asset('storage/' . $photoPath) }}" alt="Profile Photo"
                            class="rounded-circle header-profile-user" />
                    @else
                        <span class="rounded-circle header-profile-user d-inline-flex align-items-center justify-content-center bg-primary text-white fw-semibold"
                            style="width: 40px; height: 40px;">
                            {{ $initials }}
                        </span>
                    @endif

                    {{-- Always display the name --}}
                    <span class="d-none d-xl-inline-block ms-1 fw-medium">
                        {{ trim(($auth->first_name ?? '') . ' ' . ($auth->last_name ?? '')) ?: ($auth->name ?? 'User') }}
                    </span>

                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                        <i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="mdi mdi-logout font-size-16 align-middle me-1"></i> Logout
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                </div>
            </div>

        </div>
    </div>
</header>
