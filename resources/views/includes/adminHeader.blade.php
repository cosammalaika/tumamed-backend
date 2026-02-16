 <meta charset="utf-8" />
 @php
     $pageHeading = trim($__env->yieldContent('page-title'));
     $explicitTitle = trim($__env->yieldContent('title'));
     $resolvedTitle = $explicitTitle !== '' ? $explicitTitle : ($pageHeading !== '' ? $pageHeading . ' | ' . config('app.name') : config('app.name'));
 @endphp
 <title>{{ $resolvedTitle }}</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
 <meta content="Themesbrand" name="author" />
 <meta name="csrf-token" content="{{ csrf_token() }}" />
 <!-- App favicon -->
 @if (file_exists(public_path('admin/minia/assets/images/favicon.ico')))
     <link rel="icon" type="image/x-icon" href="{{ asset('admin/minia/assets/images/favicon.ico') }}">
     <link rel="shortcut icon" type="image/x-icon" href="{{ asset('admin/minia/assets/images/favicon.ico') }}">
 @endif
 <!-- plugin css -->
 <link href="{{ asset('admin/minia/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet"
     type="text/css" />

 <!-- preloader css -->
 <link rel="stylesheet" href="{{ asset('admin/minia/assets/css/preloader.min.css') }}" type="text/css" />

 <!-- choices css -->
 <link href="{{ asset('admin/minia/assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet"
     type="text/css" />



 <!-- color picker css -->
 <link rel="stylesheet" href="{{ asset('admin/minia/assets/libs/@simonwep/pickr/themes/classic.min.css') }}" />
 <link rel="stylesheet" href="{{ asset('admin/minia/assets/libs/@simonwep/pickr/themes/monolith.min.css') }}" />
 <!-- 'monolith' theme -->
 <link rel="stylesheet" href="{{ asset('admin/minia/assets/libs/@simonwep/pickr/themes/nano.min.css') }}" />

 <!-- datepicker css -->
 <link rel="stylesheet" href="{{ asset('admin/minia/assets/libs/flatpickr/flatpickr.min.css') }}">


 <!-- Bootstrap Css -->
 <link href="{{ asset('admin/minia/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
 <!-- Icons Css -->
 <link href="{{ asset('admin/minia/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
 <!-- App Css-->
 <link href="{{ asset('admin/minia/assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
 <!-- Custom overrides -->
 <link href="{{ asset('admin/minia/assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
 <link href="{{ asset('admin/minia/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
     type="text/css" />
 <link href="{{ asset('admin/minia/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet"
     type="text/css" />

 <!-- Responsive datatable examples -->
 <link href="{{ asset('admin/minia/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
     rel="stylesheet" type="text/css" />
 <link href="{{ asset('admin/css/datatables-tumamed.css') }}" rel="stylesheet" type="text/css" />
 <link href="{{ asset('admin/css/tumamed-theme.css') }}" rel="stylesheet" type="text/css" />

 {{-- Livewire styles --}}
 @livewireStyles
 @stack('styles')
