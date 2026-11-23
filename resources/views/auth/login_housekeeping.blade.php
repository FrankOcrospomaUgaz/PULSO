<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PULSO+') }} · Acceso de equipo</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/pulso-favicon.jpg') }}">
    <link rel="stylesheet" href="{{ asset('css/pulso-brand.css') }}">

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
                        primary: "#1e3d8a",
                        dark: "#111932",
                    }
                }
            }
        };
    </script>

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="pulso-housekeeping-page bg-gray-100 flex items-center justify-center h-screen">

    <div class="pulso-housekeeping-card bg-white p-8 w-full max-w-md">
        <div class="flex justify-center">
            <img src="{{ asset('img/pulso-logo.jpg') }}" alt="Pulso+ · Gestiona salud" class="w-70">
        </div>
        
        <h2 class="text-xl font-bold text-primary text-center mt-4">Housekeeping - Iniciar Sesión</h2>
        <p class="text-gray-600 text-center mb-6">Selecciona tu usuario y accede a tu jornada</p>

        <!-- Formulario -->
        <form method="POST" action="{{ route('housekeeping.login') }}">
            @csrf

            <!-- Selección de Usuario -->
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Selecciona tu usuario:</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($empleados as $empleado)
                        <button type="button" class="user-btn w-full p-3 rounded-lg border border-gray-300 bg-gray-200 hover:bg-primary hover:text-white transition" 
                            onclick="seleccionarUsuario('{{ $empleado->id }}', '{{ $empleado->nombres }}')">
                            {{ $empleado->nombres }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Campo oculto para el usuario seleccionado -->
            <input type="hidden" id="selectedUser" name="user_id" required>

            <!-- Contraseña -->
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium">Contraseña</label>
                <div class="relative">
                    <input id="password" type="password" name="password" class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none" required placeholder="Ingresa tu contraseña">
                    <i class="fa-solid fa-eye absolute right-3 top-4 text-gray-400 cursor-pointer" id="togglePassword"></i>
                </div>
            </div>

            <!-- Botón de inicio de sesión -->
            <button type="submit" id="loginButton" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-dark transition" disabled>
                <i class="fa-solid fa-sign-in-alt mr-2"></i> Ingresar
            </button>
        </form>
    </div>

    <script>
        // Mostrar u ocultar contraseña
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                this.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                this.classList.replace("fa-eye-slash", "fa-eye");
            }
        });

        function seleccionarUsuario(id, nombre) {
            document.getElementById('selectedUser').value = id;
            
            // Marcar el botón seleccionado
            document.querySelectorAll('.user-btn').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-gray-200');
            });

            event.target.classList.remove('bg-gray-200');
            event.target.classList.add('bg-primary', 'text-white');

            // Habilitar el botón de inicio de sesión
            document.getElementById('loginButton').disabled = false;
        }
    </script>

</body>

</html>
