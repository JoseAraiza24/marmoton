<?php
    $Contra = $_POST['Password'];

    include('Controlador.php');
    $Conexion = Conectar();
    $mensaje = '';
    $UserName = mysqli_real_escape_string($Conexion, $Contra);

    $SQL = "SELECT * FROM usuarios WHERE Contra = '$Contra';";
    $ResultSet = Ejecutar($Conexion, $SQL);
    $Fila = mysqli_fetch_row($ResultSet);
    if (mysqli_num_rows($ResultSet) == 1) {
        if ($Fila[3] == 'G') {
            Desconectar($Conexion);
            header("Location: http://localhost/DSI30/Hackathon/GerenteDes.php");
            exit();
        } else {
            $mensaje = "✗ ACCESO DENEGADO";
        }
    } else {
        $mensaje = "✗ USUARIO NO ENCONTRADO";
    }






    Desconectar($Conexion);

    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultado del Acceso</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #000000; /* Negro */
                margin: 0;
                min-height: 100vh;
                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
            }

            .banner {
                width: 100%;
                background-color: #1a1a1a; /* Gris oscuro más claro que el negro */
                padding: 25px;
                text-align: left;
                font-size: 40px;
                font-weight: bold;
                font-style: normal;
                color: #ed8b35; /* Naranja */
                position: fixed;
                top: 0;
                left: 0;
                z-index: 10;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .banner img {
                width: 100px;
                height: auto;
                margin-right: 20px;
            }

            .message-container {
                padding: 180px 20px 100px 20px; /* Ajustado para el banner */
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                max-width: 600px; /* Ancho similar al de otras páginas */
                margin: 0 auto;
            }

            .message-box {
                background-color: #1a1a1a; /* Gris oscuro como el banner */
                width: 100%;
                padding: 20px;
                border-radius: 6px; /* Bordes menos redondeados para formalidad */
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Sombra sutil */
                color: #ed8b35; /* Naranja */
                border: 1px solid #ed8b35; /* Borde naranja */
                text-align: center;
            }

            .error {
                color: #ff0000; /* Rojo para errores */
                font-weight: bold;
                font-size: 16px;
                margin: 0 0 20px 0;
            }

            .button {
                display: inline-block;
                padding: 12px 24px;
                background-color: #ed8b35; /* Naranja */
                color: #000000; /* Negro */
                text-align: center;
                text-decoration: none;
                border-radius: 6px;
                font-size: 16px;
                cursor: pointer;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Sombra sutil */
                margin-top: 20px;
            }

            .button:hover {
                background-color: #ffaa5c; /* Naranja más claro al hover */
            }
        </style>
    </head>
    <body>
        <div class="banner">
            <span>Marmotón</span>
        </div>
        <div class="message-container">
            <div class="message-box">
                <p class="error">' . $mensaje . '</p>
                <a class="button" href="http://localhost/DSI30/Hackathon/FAcceso.html">Volver al acceso</a>
            </div>
        </div>
    </body>
    </html>';
?>