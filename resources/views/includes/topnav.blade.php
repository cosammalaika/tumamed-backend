@php
    $isAdmin = auth()->user()?->hasRole(\App\Models\User::ROLE_ADMIN);
    $isDashboard = request()->routeIs('dashboard') || request()->routeIs('admin.dashboard');
    $isTransactions = request()->routeIs('admin.requests');
    $isReports = request()->routeIs('admin.reports*');
    $isHospitals = request()->routeIs('admin.hospitals');
    $isPharmacies = request()->routeIs('admin.pharmacies');
    $isUsers = request()->routeIs('admin.users');
    $isSettings = request()->routeIs('admin.audit-logs') || request()->routeIs('admin.access.*');
@endphp

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">
                    @if($isAdmin || auth()->user()?->can('view_dashboard'))
                        <li class="nav-item {{ $isDashboard ? 'active' : '' }}">
                            <a class="nav-link arrow-none" href="{{ route('admin.dashboard') }}" id="topnav-dashboard">
                                <span data-key="t-dashboards">Dashboard</span>
                            </a>
                        </li>
                    @endif

                    @if ($isAdmin || auth()->user()?->can('manage_users'))
                        <li class="nav-item {{ $isUsers ? 'active' : '' }}">
                            <a class="nav-link arrow-none" href="{{ route('admin.users') }}">
                                <span>{{ __('Users') }}</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || auth()->user()?->can('view_requests'))
                        <li class="nav-item {{ $isTransactions ? 'active' : '' }}">
                            <a class="nav-link arrow-none" href="{{ route('admin.requests') }}">
                                <span>{{ __('Requests') }}</span>
                            </a>
                        </li>
                    @endif


                    @if($isAdmin || auth()->user()?->can('manage_hospitals'))
                        <li class="nav-item {{ $isHospitals ? 'active' : '' }}">
                            <a class="nav-link arrow-none" href="{{ route('admin.hospitals') }}">
                                <span>{{ __('Hospitals') }}</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || auth()->user()?->can('manage_pharmacies'))
                        <li class="nav-item {{ $isPharmacies ? 'active' : '' }}">
                            <a class="nav-link arrow-none" href="{{ route('admin.pharmacies') }}">
                                <span>{{ __('Pharmacies') }}</span>
                            </a>
                        </li>
                    @endif
                    @if($isAdmin || auth()->user()?->can('view_reports'))
                        <li class="nav-item {{ $isReports ? 'active' : '' }}">
                            <a class="nav-link arrow-none" href="{{ route('admin.reports') }}">
                                <span>{{ __('Reports') }}</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || auth()->user()?->canAny(['view_audit_logs', 'manage_roles_permissions']))
                        <li class="nav-item dropdown {{ $isSettings ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-settings" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span data-key="t-settings">Settings</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-settings">
                                @if($isAdmin || auth()->user()?->can('view_audit_logs'))
                                    <a href="{{ route('admin.audit-logs') }}" class="dropdown-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">
                                        Audit Logs
                                    </a>
                                @endif
                                @if ($isAdmin || auth()->user()?->can('manage_roles_permissions'))
                                    <a href="{{ route('admin.access.index') }}" class="dropdown-item {{ request()->routeIs('admin.access.*') ? 'active' : '' }}">
                                        Access Control
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endif

                </ul>
            </div>
        </nav>
    </div>
</div>
