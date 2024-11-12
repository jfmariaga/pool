<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Asegúrate de tener tus estilos -->
    <style>
        body {
            background-color: #f8fafc;
            /* Color de fondo */
            font-family: Arial, sans-serif;
            /* Fuente */
            color: #333;
            /* Color del texto */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            /* Altura mínima para centrar verticalmente */
            margin: 0;
            padding: 0;
        }

        .container {
            text-align: center;
            /* Centramos el texto */
            background-color: #fff;
            /* Fondo blanco para el contenedor */
            padding: 40px;
            /* Espaciado interior */
            border-radius: 8px;
            /* Bordes redondeados */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            /* Sombra */
        }

        h1 {
            font-size: 4rem;
            /* Tamaño grande para el código de error */
            color: #e3342f;
            /* Color rojo para el error */
        }

        h2 {
            font-size: 2rem;
            /* Tamaño para el subtítulo */
            margin-top: 10px;
            /* Margen superior */
        }

        p {
            margin: 20px 0;
            /* Espaciado superior e inferior */
            font-size: 1.2rem;
            /* Tamaño del texto */
        }

        a {
            display: inline-block;
            /* Convertimos el enlace en un bloque */
            margin-top: 20px;
            /* Margen superior */
            padding: 10px 20px;
            /* Espaciado interior */
            background-color: #3490dc;
            /* Color de fondo del botón */
            color: white;
            /* Color del texto del botón */
            text-decoration: none;
            /* Sin subrayado */
            border-radius: 5px;
            /* Bordes redondeados */
            transition: background-color 0.3s;
            /* Transición suave para el hover */
        }

        a:hover {
            background-color: #2779bd;
            /* Color del botón en hover */
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>404</h1>
        <h2>La pagina que buscas ya no esta disponible</h2>
        <a href="{{ route('dashboard') }}">Volver al inicio</a>
    </div>
</body>

</html>
