<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marmotón - Acceso</title>
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
            font-style: normal; /* Quité oblique para un look más formal */
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
            width: 100px; /* Ajusta el tamaño según prefieras */
            height: auto;
            margin-right: 20px;
        }

        .login-container {
            padding: 180px 20px 100px 20px; /* Ajustado para el banner */
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 350px; /* Mantengo el ancho original */
            margin: 0 auto;
        }

        .login-box {
            background-color: #1a1a1a; /* Gris oscuro como el banner */
            width: 100%;
            padding: 20px;
            border-radius: 6px; /* Bordes menos redondeados para formalidad */
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Sombra sutil */
            color: #ed8b35; /* Naranja */
            border: 1px solid #ed8b35; /* Borde naranja */
            text-align: center;
        }

        h2 {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #ed8b35; /* Naranja */
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            text-align: left;
            color: #ed8b35; /* Naranja */
            font-size: 12px;
        }

        input[type="number"] {
            width: 100%;
            padding: 6px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ed8b35; /* Borde naranja */
            font-size: 12px;
            background-color: #ffffff; /* Blanco */
            color: #000000; /* Negro */
            box-sizing: border-box;
        }

        input[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #ed8b35; /* Naranja */
            color: #000000; /* Negro */
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Sombra sutil */
        }

        input[type="submit"]:hover {
            background-color: #ffaa5c; /* Naranja más claro al hover */
        }

        .num-buttons {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            width: 100%;
            justify-content: center;
        }

        .num-buttons button {
            padding: 10px;
            background-color: #ed8b35; /* Naranja */
            color: #000000; /* Negro */
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Sombra sutil */
        }

        .num-buttons button:hover {
            background-color: #ffaa5c; /* Naranja más claro al hover */
        }

        .num-buttons .clear {
            background-color: #ed8b35; /* Naranja */
            width: 100%; /* Ocupa el 100% del ancho disponible */
            grid-column: span 2; /* Hace que ocupe 2 columnas en la cuadrícula */
        }

        .num-buttons .clear:hover {
            background-color: #ffaa5c; /* Naranja más claro al hover */
        }
    </style>
</head>
<body>
    <div class="banner">
        <span>Marmotón</span>
    </div>
    <div class="login-container">
        <div class="login-box">
            <h2>Bienvenido a Marmotón</h2>
            <form action="ValidaAcceso.php" method="post">
                <label for="Password">Contraseña</label>
                <input type="number" name="Password" id="Password" required>
                <input type="submit" value="Ingresar">
            </form> 

            <!-- Panel de botones numéricos -->
            <div class="num-buttons">
                <button type="button" onclick="agregarNumero(1)">1</button>
                <button type="button" onclick="agregarNumero(2)">2</button>
                <button type="button" onclick="agregarNumero(3)">3</button>
                <button type="button" onclick="agregarNumero(4)">4</button>
                <button type="button" onclick="agregarNumero(5)">5</button>
                <button type="button" onclick="agregarNumero(6)">6</button>
                <button type="button" onclick="agregarNumero(7)">7</button>
                <button type="button" onclick="agregarNumero(8)">8</button>
                <button type="button" onclick="agregarNumero(9)">9</button>
                <button type="button" onclick="agregarNumero(0)">0</button>
                <button type="button" class="clear" onclick="borrarNumero()">Borrar</button>
            </div>
        </div>
    </div>

    <script>
        function agregarNumero(numero) {
            const inputPassword = document.getElementById('Password');
            inputPassword.value += numero;
        }

        function borrarNumero() {
            const inputPassword = document.getElementById('Password');
            inputPassword.value = inputPassword.value.slice(0, -1); // Elimina el último dígito
        }
    </script>
</body>
</html>