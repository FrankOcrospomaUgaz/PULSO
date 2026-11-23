<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111932">
    <meta name="description" content="Acceso a Pulso+, la plataforma para gestionar organizaciones y equipos de salud.">
    <title>{{ config('app.name', 'PULSO+') }} · Acceso</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/pulso-favicon.jpg') }}">
    <link rel="stylesheet" href="{{ asset('css/pulso-brand.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="pulso-login-page">
    @include('components.brand-loader')

    <main class="pulso-login-shell">
        <section class="pulso-login-brand-panel" aria-label="Pulso+ gestión de salud">
            <a class="pulso-login-brand-lockup" href="https://www.pulsoplus.net" target="_blank" rel="noopener noreferrer" aria-label="Visitar el sitio de PULSO+">
                <img src="{{ asset('img/pulso-logo.jpg') }}" alt="Pulso+ · Gestiona salud" width="684" height="296">
            </a>

            <div class="pulso-login-copy">
                <p class="pulso-login-eyebrow">Tecnología para cuidar mejor</p>
                <h1>Gestionar salud importa.<br><span>Hacerlo simple, también.</span></h1>
                <p>Una plataforma para que los equipos de salud organicen su operación y dediquen más energía a las personas.</p>
                <ul class="pulso-login-points">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Procesos claros para todo tu equipo</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Información conectada en un solo lugar</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Gestión pensada para empresas de salud</span></li>
                </ul>
            </div>

            <div class="pulso-login-brand-footer">
                <span>© {{ date('Y') }} Pulso+. Todos los derechos reservados.</span>
                <span><i class="fa-solid fa-heart-pulse" aria-hidden="true"></i> Salud que avanza</span>
            </div>
        </section>

        <section class="pulso-login-main" aria-label="Inicio de sesión">
            <div class="pulso-login-card">
                <a class="pulso-login-mobile-logo" href="https://www.pulsoplus.net" target="_blank" rel="noopener noreferrer" aria-label="Visitar el sitio de PULSO+">
                    <img src="{{ asset('img/pulso-logo.jpg') }}" alt="Pulso+ · Gestiona salud" width="684" height="296">
                </a>

                <p class="pulso-login-eyebrow">Acceso a la plataforma</p>
                <h2>Bienvenido de vuelta</h2>
                <p class="pulso-login-intro">Ingresa con tus credenciales para continuar en tu espacio de trabajo.</p>

                <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="pulso-login-field">
                        <label for="name">Nombre de usuario</label>
                        <div class="pulso-login-input-wrap">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                autocomplete="username" autocapitalize="none" spellcheck="false"
                                required autofocus placeholder="Ingresa tu usuario">
                        </div>
                    </div>

                    <div class="pulso-login-field">
                        <label for="password">Contraseña</label>
                        <div class="pulso-login-input-wrap">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input id="password" type="password" name="password" autocomplete="current-password"
                                required placeholder="Ingresa tu contraseña">
                            <button class="pulso-password-toggle" id="togglePassword" type="button"
                                aria-label="Mostrar contraseña" aria-pressed="false">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pulso-login-options">
                        <label class="pulso-remember" for="remember">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Recuérdame</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="pulso-login-forgot" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>

                    <button class="pulso-login-submit" id="login-button" type="submit">
                        <span>Iniciar sesión</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="pulso-login-footnote">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>Tu acceso es personal y protegido. Si necesitas ayuda, comunícate con el administrador de tu organización.</span>
                </p>
            </div>
        </section>
    </main>

    <script>
        const passwordField = document.getElementById('password');
        const passwordToggle = document.getElementById('togglePassword');
        const loadingScreen = document.getElementById('loading-screen');
        const loginForm = document.getElementById('login-form');
        const loginButton = document.getElementById('login-button');

        passwordToggle.addEventListener('click', function () {
            const showing = passwordField.type === 'password';
            passwordField.type = showing ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(showing));
            passwordToggle.setAttribute('aria-label', showing ? 'Ocultar contraseña' : 'Mostrar contraseña');
            passwordToggle.innerHTML = `<i class="fa-regular ${showing ? 'fa-eye-slash' : 'fa-eye'}" aria-hidden="true"></i>`;
        });

        function mostrarPantallaDeCarga() {
            if (!loadingScreen) return;
            loadingScreen.style.display = 'flex';
            loadingScreen.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => { loadingScreen.style.opacity = '1'; });
        }

        function ocultarPantallaDeCarga() {
            if (!loadingScreen) return;
            loadingScreen.style.opacity = '0';
            loadingScreen.setAttribute('aria-hidden', 'true');
            window.setTimeout(() => { loadingScreen.style.display = 'none'; }, 300);
        }

        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!loginForm.reportValidity()) return;

            loginButton.disabled = true;
            mostrarPantallaDeCarga();

            try {
                const response = await fetch(loginForm.action, {
                    method: 'POST',
                    body: new FormData(loginForm),
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(validationMessage || data.message || 'No se pudo iniciar sesión. Inténtalo nuevamente.');
                }

                window.location.assign(data.redirect_url || @json(\App\Providers\RouteServiceProvider::HOME));
            } catch (error) {
                ocultarPantallaDeCarga();
                loginButton.disabled = false;

                await Swal.fire({
                    icon: 'error',
                    title: 'No pudimos iniciar sesión',
                    text: error.message,
                    confirmButtonText: 'Intentar nuevamente',
                    confirmButtonColor: '#1e3d8a'
                });

                passwordField.value = '';
                passwordField.focus();
            }
        });

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'No pudimos iniciar sesión',
                text: @json($errors->first()),
                confirmButtonText: 'Intentar nuevamente',
                confirmButtonColor: '#1e3d8a'
            });
        @endif
    </script>
</body>
</html>
