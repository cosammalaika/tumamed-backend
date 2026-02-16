<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 | TumaMed</title>
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
                        <h1 class="display-4 text-primary mb-2">419</h1>
                        <h4 class="mb-2">Session expired</h4>
                        <p class="text-muted mb-4">Your session expired. Please try again.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

