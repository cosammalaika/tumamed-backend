<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('admin/minia/assets/images/favicon.ico') }}">
    <link href="{{ asset('admin/minia/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/minia/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/minia/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/css/tumamed-theme.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/css/tumamed-auth.css') }}" rel="stylesheet" type="text/css" />
</head>
<body class="auth-page">
    <div class="auth-page-wrap">
        <div class="auth-shell">
            <aside class="auth-panel-left auth-hero">
                @include('auth.partials.hero')
            </aside>
            <main class="auth-panel-right auth-form">
                    @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('admin/minia/assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
