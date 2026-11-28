<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>419 - Sesión expirada</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .left {
            flex: 1;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .right {
            flex: 1;
            background-color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 40px;
        }

        h1 {
            font-size: 4rem;
            color: #222;
        }

        p {
            font-size: 1.2rem;
            color: #666;
            margin-top: 10px;
        }

        .btn {
            margin-top: 25px;
            background-color: #00897b ;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color:rgb(0, 102, 92);
        }
    </style>
</head>
<body>
    <div class="left" style="background: linear-gradient(135deg, #111d46, #1f4da2);"></div>
    <div class="right">
        <h1>419 😅</h1>
        <p>Tu sesión ha expirado. Por favor, vuelve a ingresar.</p>
        <a href="{{ url('/') }}" class="btn">IR AL SISTEMA</a>
    </div>
</body>
</html>
