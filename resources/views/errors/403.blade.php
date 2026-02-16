<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 | TumaMed</title>
    <link href="{{ asset('admin/minia/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/minia/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/tumamed-theme.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <h1 class="display-4 text-primary mb-2">403</h1>
                        <h4 class="mb-2">Access denied</h4>
                        <p class="text-muted mb-4">You do not have permission to view this page.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Back</a>
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

