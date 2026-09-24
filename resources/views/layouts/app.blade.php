<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Street Fighter 2')</title>
    <link rel="shortcut icon" href="{{ asset('velzon/images/favicon.ico') }}">

    <script>
        try {
            if (localStorage.getItem('sf2-layout-mode') === 'dark') {
                document.documentElement.setAttribute('data-layout-mode', 'dark');
            }
        } catch (e) {}
    </script>
    <script src="{{ asset('velzon/js/layout.js') }}"></script>
    <link href="{{ asset('velzon/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/app.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/custom.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <div id="layout-wrapper">
        @include('layouts.partials.topbar')
        @include('layouts.partials.sidebar')

        <div class="vertical-overlay"></div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">@yield('page-title', 'Registro de Matches')</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Street Fighter 2</a></li>
                                        <li class="breadcrumb-item active">@yield('page-title', 'Registro de Matches')</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    @yield('content')
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">{{ date('Y') }} &copy; Street Fighter 2</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('velzon/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('velzon/js/plugins.js') }}"></script>
    <script src="{{ asset('velzon/js/app.js') }}"></script>
    <script>
        document.querySelector('.light-dark-mode')?.addEventListener('click', function () {
            try {
                localStorage.setItem('sf2-layout-mode', document.documentElement.getAttribute('data-layout-mode') === 'dark' ? 'dark' : 'light');
            } catch (e) {}
        });

        document.querySelectorAll('#navbar-nav [data-bs-toggle="modal"]').forEach(function (link) {
            link.addEventListener('click', function () {
                document.body.classList.remove('vertical-sidebar-enable');
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
