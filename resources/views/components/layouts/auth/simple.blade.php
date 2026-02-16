<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? config('app.name') }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('admin/minia/assets/images/favicon.ico') }}">
        <link href="{{ asset('admin/minia/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('admin/minia/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('admin/minia/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('admin/css/tumamed-theme.css') }}" rel="stylesheet" type="text/css" />
    </head>
    <body class="authentication-bg">
        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="text-center mb-4">
                            <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                <img src="{{ asset('admin/minia/assets/images/logo-sm.svg') }}" alt="TumaMed" height="30">
                                <span class="h5 mb-0 text-dark">TumaMed</span>
                            </a>
                        </div>
                        <div class="card auth-card">
                            <div class="card-body p-4">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
