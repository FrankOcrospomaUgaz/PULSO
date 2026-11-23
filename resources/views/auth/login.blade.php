<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Hub - Login</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo_h.png') }}">
    <style>
        #loading-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgb(233, 234, 243);
    z-index: 99999;
    display: flex;
    justify-content: center;
    align-items: center;
}
.spinner {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: inline-block;
    position: absolute;
    background: linear-gradient(0deg, rgb(76, 163, 175) 33%, #363d3f 100%);
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: auto
}


.spinner:after {
    content: "";
    box-sizing: border-box;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #ffffff
}


@keyframes rotation {
    0% {
        transform: rotate(0)
    }

    to {
        transform: rotate(360deg)
    }
}
.loader {
    position: relative;
    width: 150px;
    height: 150px;
}

.img-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    /* Ajusta el tamaño de la imagen */

    z-index: 10;
    animation: none;
    /* Sin animación */
}


    </style>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        archivo: "Archivo",
                        manrope: "Manrope",
                    },
                    colors: {
                        primary: "#00796b",
                        dark: "#004d40",
                    }
                }
            }
        };
    </script>

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
      <div id="loading-screen" style="display: none;">
            <div class="loader">
                <img class="img-loading" src="{{ asset('img/logo_h.png') }}" alt="Cargando...">
                <span class="spinner"></span>
            </div>
        </div>

<body class="bg-gray-100 flex items-center justify-center h-screen"
    style="background-image: url('{{ asset('img/fondo4.jpg') }}'); background-position: center; background-size: cover;">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
        <div class="flex justify-center">
            <img src="{{ asset('img/Logo_HotelHub_tr.png') }}" alt="Hotel Hub Logo" class="w-70">
        </div>

        <h2 class="text-xl font-bold text-primary text-center mt-4">Iniciar Sesión</h2>
        <p class="text-gray-600 text-center mb-6">Ingresa tus credenciales para acceder</p>

        <!-- Formulario -->
        <form id="login-form" method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Usuario -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium">Nombre de Usuario</label>
                <div class="relative">
                    <input id="name" type="text" name="name"
                        class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none"
                        value="{{ old('name') }}" required placeholder="Ingresa tu usuario">
                    <i class="fa-solid fa-user absolute right-3 top-4 text-gray-400"></i>
                </div>
            </div>

            <!-- Contraseña -->
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium">Contraseña</label>
                <div class="relative">
                    <input id="password" type="password" name="password"
                        class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none"
                        required placeholder="Ingresa tu contraseña">
                    <i class="fa-solid fa-eye absolute right-3 top-4 text-gray-400 cursor-pointer"
                        id="togglePassword"></i>
                </div>
            </div>

            <!-- Recordarme -->
            <div class="mb-4 flex items-center">
                <input type="checkbox" name="remember" id="remember" class="mr-2">
                <label for="remember" class="text-gray-700">Recuérdame</label>
            </div>

            <!-- Botón de inicio de sesión -->
            <button id="login-button" type="submit"
                class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-dark transition">
                <i class="fa-solid fa-sign-in-alt mr-2"></i> Iniciar Sesión
            </button>
        </form>
    </div>

    <script>
        // Mostrar u ocultar contraseña
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                this.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                this.classList.replace("fa-eye-slash", "fa-eye");
            }
        });
        // Mostrar pantalla de carga
        function mostrarPantallaDeCarga() {
            const loadingScreen = document.getElementById('loading-screen');

            if (loadingScreen) {
                loadingScreen.style.display = 'flex';
                // Reiniciar opacidad para que el fade vuelva a funcionar
                setTimeout(() => {
                    loadingScreen.style.opacity = '1';
                }, 10); // pequeño delay para que el navegador detecte el cambio
            }
        }


        // Ocultar pantalla de carga
        function ocultarPantallaDeCarga() {
            const loadingScreen = document.getElementById('loading-screen');

            if (loadingScreen) {
                loadingScreen.style.transition = 'opacity 0.5s ease';
                loadingScreen.style.opacity = '0';

                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                    // Restaurar opacidad para el siguiente "mostrar"
                    loadingScreen.style.opacity = '1';
                }, 500); // mismo tiempo que la transición
            }
        }

        const loginForm = document.getElementById('login-form');
        const loginButton = document.getElementById('login-button');

        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
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
                    const validationMessage = data.errors
                        ? Object.values(data.errors).flat()[0]
                        : null;

                    throw new Error(validationMessage || data.message || 'No se pudo iniciar sesión. Inténtalo nuevamente.');
                }

                window.location.assign(data.redirect_url || @json(\App\Providers\RouteServiceProvider::HOME));
            } catch (error) {
                ocultarPantallaDeCarga();
                loginButton.disabled = false;

                await Swal.fire({
                    icon: 'error',
                    title: 'Acceso denegado',
                    text: error.message,
                    confirmButtonText: 'Intentar nuevamente',
                    confirmButtonColor: '#00796b'
                });

                document.getElementById('password').value = '';
                document.getElementById('password').focus();
            }
        });

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Acceso denegado',
                text: @json($errors->first()),
                confirmButtonText: 'Intentar nuevamente',
                confirmButtonColor: '#00796b'
            });
        @endif
    </script>

</body>

</html>
