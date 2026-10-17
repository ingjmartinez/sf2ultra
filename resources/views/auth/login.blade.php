<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

<head>
    <meta charset="utf-8">
    <title>Iniciar sesion | Street Fighter 2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('velzon/images/favicon.ico') }}">

    <script src="{{ asset('velzon/js/layout.js') }}"></script>
    <link href="{{ asset('velzon/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/app.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/custom.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .login-card { margin-top: 9rem; }
        @media (min-width: 768px) { .login-card { margin-top: 18rem; } }
    </style>
</head>

<body>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles" style="background-image: url({{ asset('images/logo.png') }}); background-size: contain; background-repeat: no-repeat; background-position: center top;">
            <div class="bg-overlay" style="opacity: .35;"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card login-card">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Bienvenido</h5>
                                    <p class="text-muted">Inicia sesion para continuar.</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <form method="post" action="{{ route('login.store') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Correo</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Ingresa tu correo" required autofocus autocomplete="username">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Contrasena</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror" placeholder="Ingresa tu contrasena" id="password-input" name="password" required autocomplete="current-password">
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" value="1" id="auth-remember-check">
                                            <label class="form-check-label" for="auth-remember-check">Recordarme</label>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Entrar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy; {{ date('Y') }} Street Fighter 2</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="{{ asset('velzon/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('velzon/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('velzon/js/plugins.js') }}"></script>
    <script src="{{ asset('velzon/libs/particles.js/particles.js') }}"></script>
    <script src="{{ asset('velzon/js/pages/particles.app.js') }}"></script>
    <script src="{{ asset('velzon/js/pages/password-addon.init.js') }}"></script>
</body>

</html>
