<?php
// opciones_gerente.php
// Aquí podrías verificar la sesión si implementas autenticación con sesiones
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marmotón - Opciones del Gerente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000000;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .banner {
            width: 100%;
            background-color: #1a1a1a;
            padding: 25px;
            text-align: left;
            font-size: 40px;
            font-weight: bold;
            color: #ed8b35;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
        }

        .opciones-container {
            background-color: #ed8b35;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
            margin-top: 120px;
        }

        h2 {
            margin-bottom: 30px;
            color: #000000;
            font-size: 28px;
        }

        .opcion-btn {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            background-color: #ffff00;
            color: #000000;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
            font-weight: bold;
        }

        .opcion-btn:hover {
            background-color: #ffd700;
        }

        .salir-btn {
            position: fixed;
            top: 120px;
            right: 20px;
            padding: 12px 24px;
            background-color: #333333;
            color: #ffffff;
            border: 1px solid #ed8b35;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            z-index: 11;
        }

        .salir-btn:hover {
            background-color: #4d4d4d;
        }
    </style>
</head>
<body>
    <div class="banner">
        <span>Marmotón - Área de Gerente</span>
    </div>
    <button class="salir-btn" onclick="window.location.href='FAcceso.php'">Salir</button>
    <div class="opciones-container">
        <h2>Opciones del Gerente</h2>
        <button class="opcion-btn" onclick="window.location.href='MenuG.php'">Gestionar Inventario</button>
        <button class="opcion-btn" onclick="window.location.href='Mesas.php'">Gestionar Comandas</button>
    </div>
</body>
</html>