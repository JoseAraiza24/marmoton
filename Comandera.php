<?php
// Conexión a la base de datos MariaDB
$host = "localhost";
$usuario = "root"; // Cambia esto si tu usuario es diferente
$contrasena = ""; // Cambia esto si tienes una contraseña
$base_datos = "Marmoton";

$conexion = mysqli_connect($host, $usuario, $contrasena, $base_datos);
if (!$conexion) {
    die("Error al conectar a la base de datos: " . mysqli_connect_error());
}

// Procesar las acciones enviadas por POST (enviar comanda o borrar producto)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data)) {
        // Caso 1: Enviar comanda
        if (isset($data['action']) && $data['action'] === 'enviar' && !empty($data['comanda']) && !empty($data['ingredientes'])) {
            $comanda = $data['comanda'];
            $ingredientesPorProducto = $data['ingredientes'];

            mysqli_begin_transaction($conexion);

            try {
                foreach ($comanda as $producto) {
                    $nombreProducto = mysqli_real_escape_string($conexion, $producto['nombre']);
                    $cantidad = (int)$producto['cantidad'];
                    $ingredientes = $ingredientesPorProducto[$nombreProducto];

                    foreach ($ingredientes as $ingrediente => $cantidadPorUnidad) {
                        $cantidadTotal = $cantidadPorUnidad * $cantidad;

                        // Verificar el stock actual
                        $sql = "SELECT stock FROM insumos WHERE nombre = '$ingrediente' FOR UPDATE";
                        $result = mysqli_query($conexion, $sql);
                        if (!$result || mysqli_num_rows($result) == 0) {
                            throw new Exception("Insumo $ingrediente no encontrado.");
                        }
                        $row = mysqli_fetch_assoc($result);
                        $stockActual = (int)$row['stock'];

                        if ($stockActual < $cantidadTotal) {
                            throw new Exception("No hay suficiente $ingrediente. Stock actual: $stockActual.");
                        }

                        // Restar la cantidad del inventario
                        $sql_update = "UPDATE insumos SET stock = stock - $cantidadTotal WHERE nombre = '$ingrediente'";
                        if (!mysqli_query($conexion, $sql_update)) {
                            throw new Exception("Error al actualizar $ingrediente: " . mysqli_error($conexion));
                        }
                    }
                }

                mysqli_commit($conexion);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Comanda enviada y inventario actualizado con éxito']);
            } catch (Exception $e) {
                mysqli_rollback($conexion);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;
        }

        // Caso 2: Borrar producto
        if (isset($data['action']) && $data['action'] === 'borrar' && !empty($data['producto']) && !empty($data['ingredientes'])) {
            $producto = $data['producto'];
            $ingredientesPorProducto = $data['ingredientes'];

            $nombreProducto = mysqli_real_escape_string($conexion, $producto['nombre']);
            $cantidad = (int)$producto['cantidad'];
            $ingredientes = $ingredientesPorProducto[$nombreProducto];

            mysqli_begin_transaction($conexion);

            try {
                foreach ($ingredientes as $ingrediente => $cantidadPorUnidad) {
                    $cantidadTotal = $cantidadPorUnidad * $cantidad;

                    // Sumar la cantidad al inventario (devolver al stock)
                    $sql_update = "UPDATE insumos SET stock = stock + $cantidadTotal WHERE nombre = '$ingrediente'";
                    if (!mysqli_query($conexion, $sql_update)) {
                        throw new Exception("Error al actualizar $ingrediente: " . mysqli_error($conexion));
                    }
                }

                mysqli_commit($conexion);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Producto borrado y inventario actualizado']);
            } catch (Exception $e) {
                mysqli_rollback($conexion);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos o acción no especificada']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comandar</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #000000;
      margin: 0;
      min-height: 100vh;
      position: relative;
      color: #ffffff;
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

    .container {
      padding: 120px 20px 20px 20px;
      max-width: 800px;
      margin: 0 auto;
    }

    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .tabs button {
      flex: 1;
      background-color: #ffaa5c;
      color: #000000;
      padding: 8px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
    }

    .tabs button.active {
      background-color: #ed8b35;
    }

    .productos-tab {
      background-color: #1a1a1a;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
    }

    .productos-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      max-height: 300px;
      overflow-y: auto;
    }

    .producto-item {
      width: 120px;
      height: 120px;
      background-color: #ed8b35;
      color: #000000;
      border-radius: 6px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: center;
      padding: 10px;
      cursor: pointer;
      position: relative;
    }

    .producto-item:hover {
      background-color: #ffaa5c;
    }

    .producto-nombre {
      font-size: 14px;
      font-weight: bold;
      text-align: center;
    }

    .producto-precio {
      font-size: 12px;
      color: #000000;
    }

    .comentario-btn {
      width: 25px;
      height: 25px;
      background-color: #2e7d32;
      color: #ffffff;
      border: none;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .comentario-btn:hover {
      background-color: #4caf50;
    }

    .repetir-btn {
      width: 100%;
      padding: 10px;
      background-color: #1a1a1a;
      color: #ed8b35;
      border: 1px solid #ed8b35;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      margin-bottom: 15px;
    }

    .repetir-btn:hover {
      background-color: #ed8b35;
      color: #000000;
    }

    .repetir-tab {
      background-color: #1a1a1a;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
      display: none;
    }

    .comanda-actual {
      background-color: #1a1a1a;
      padding: 10px;
      border-radius: 6px;
      max-height: 200px;
      overflow-y: auto;
      margin-bottom: 15px;
    }

    .comanda-item {
      border: 1px solid #ed8b35;
      padding: 8px;
      margin: 5px 0;
      border-radius: 4px;
      position: relative;
      min-height: 40px;
    }

    .comanda-item input[type="number"] {
      width: 50px;
      padding: 4px;
      border-radius: 4px;
      border: 1px solid #ed8b35;
      background-color: #e0e0e0;
      color: #000000;
    }

    .comanda-item .buttons {
      display: flex;
      gap: 10px;
    }

    .comentario-text {
      background-color: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      padding: 4px;
      margin-top: 5px;
      border-radius: 4px;
      font-size: 12px;
      word-wrap: break-word;
    }

    .delete-btn {
      background-color: #ff0000;
      color: #ffffff;
      padding: 2px 6px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .delete-btn:hover {
      background-color: #cc0000;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
    }

    .action-buttons button {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
    }

    .enviar-btn {
      background-color: #ed8b35;
      color: #000000;
    }

    .enviar-btn:hover {
      background-color: #ffaa5c;
    }

    .cancelar-btn {
      background-color: #ff0000;
      color: #ffffff;
    }

    .cancelar-btn:hover {
      background-color: #cc0000;
    }

    .comentario-modal {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #1a1a1a;
      padding: 15px;
      border-radius: 6px;
      border: 1px solid #ed8b35;
      z-index: 100;
      width: 250px;
    }

    .comentario-modal h3 {
      color: #ed8b35;
      font-size: 16px;
      margin: 0 0 10px 0;
    }

    .comentario-modal textarea {
      width: 100%;
      height: 60px;
      padding: 5px;
      border-radius: 4px;
      border: 1px solid #ed8b35;
      background-color: #e0e0e0;
      color: #000000;
      resize: none;
    }

    .comentario-modal .modal-buttons {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .comentario-modal button {
      flex: 1;
      padding: 8px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .comentario-modal .guardar-btn {
      background-color: #ed8b35;
      color: #000000;
    }

    .comentario-modal .guardar-btn:hover {
      background-color: #ffaa5c;
    }

    .comentario-modal .cancelar-modal-btn {
      background-color: #ff0000;
      color: #ffffff;
    }

    .comentario-modal .cancelar-modal-btn:hover {
      background-color: #cc0000;
    }
  </style>
</head>
<body>
<div class="banner" id="mesaBanner">Comandar para Mesa</div>
<div class="container">
  <div class="tabs">
    <button id="tabAlimentos" onclick="mostrarTab('alimentos')" class="active">Alimentos</button>
    <button id="tabBebidas" onclick="mostrarTab('bebidas')">Bebidas</button>
  </div>

  <button class="repetir-btn" onclick="mostrarRepetir()">Repetir Productos Anteriores</button>

  <div id="repetirTab" class="repetir-tab">
    <h3 style="color: #ed8b35; font-size: 16px; margin: 0 0 10px 0;">Productos Anteriores</h3>
    <div id="listaRepetir" class="productos-grid"></div>
  </div>

  <div id="productosAlimentos" class="productos-tab">
    <div id="listaAlimentos" class="productos-grid"></div>
  </div>
  <div id="productosBebidas" class="productos-tab" style="display: none;">
    <div id="listaBebidas" class="productos-grid"></div>
  </div>

  <div class="comanda-actual">
    <h3 style="color: #ed8b35; font-size: 16px; margin: 0 0 10px 0;">Comanda Actual</h3>
    <div id="comandaActual"></div>
  </div>

  <div class="action-buttons">
    <button class="cancelar-btn" onclick="window.location.href='http://localhost/DSI30/Hackathon/Mesas.php'">Cancelar</button>
    <button class="enviar-btn" onclick="enviarComanda()">Enviar</button>
  </div>
</div>

<div id="comentarioModal" class="comentario-modal">
  <h3 id="comentarioProducto">Agregar Comentario</h3>
  <textarea id="comentarioInput"></textarea>
  <div class="modal-buttons">
    <button class="cancelar-modal-btn" onclick="cerrarComentarioModal()">Cancelar</button>
    <button class="guardar-btn" onclick="guardarComentario()">Guardar</button>
  </div>
</div>

<script>
  let comandaActual = [];
  let productosAnteriores = [
    { nombre: "Hamburguesa", tipo: "alimentos", precio: 10.50 },
    { nombre: "Pizza de Pepperoni", tipo: "alimentos", precio: 12.00 },
    { nombre: "Cerveza", tipo: "bebidas", precio: 5.00 },
    { nombre: "Refresco", tipo: "bebidas", precio: 2.50 }
  ];
  let catalogo = [
    { nombre: "Hamburguesa", tipo: "alimentos", precio: 10.50 },
    { nombre: "Pizza de Pepperoni", tipo: "alimentos", precio: 12.00 },
    { nombre: "Tacos", tipo: "alimentos", precio: 8.00 },
    { nombre: "Cerveza", tipo: "bebidas", precio: 5.00 },
    { nombre: "Refresco", tipo: "bebidas", precio: 2.50 },
    { nombre: "Agua", tipo: "bebidas", precio: 1.50 }
  ];

  const ingredientesPorProducto = {
    "Hamburguesa": { "Pan de Hamburguesa": 2, "Carne Molida": 1},
    "Pizza de Pepperoni": { "Base de Pizza": 1, "Queso Mozzarella": 1, "Pepperoni": 1, "Salsa de Tomate": 1 },
    "Tacos": { "Tortillas de Maíz": 3, "Carne Asada": 1},
    "Cerveza": { "Cerveza": 1 },
    "Refresco": { "Refresco": 1 },
    "Agua": { "Agua": 1 }
  };

  let comentarioIndex = null;

  window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mesaId = urlParams.get('mesa') || "Mesa Desconocida";
    document.getElementById('mesaBanner').textContent = `Comandar para ${mesaId}`;
    renderizarCatalogo();
    renderizarComandaActual();
  };

  function mostrarTab(tipo) {
    const tabAlimentos = document.getElementById('tabAlimentos');
    const tabBebidas = document.getElementById('tabBebidas');
    const productosAlimentos = document.getElementById('productosAlimentos');
    const productosBebidas = document.getElementById('productosBebidas');
    const repetirTab = document.getElementById('repetirTab');

    repetirTab.style.display = 'none';

    if (tipo === 'alimentos') {
      tabAlimentos.classList.add('active');
      tabBebidas.classList.remove('active');
      productosAlimentos.style.display = 'block';
      productosBebidas.style.display = 'none';
    } else {
      tabBebidas.classList.add('active');
      tabAlimentos.classList.remove('active');
      productosBebidas.style.display = 'block';
      productosAlimentos.style.display = 'none';
    }
  }

  function renderizarCatalogo() {
    const listaAlimentos = document.getElementById('listaAlimentos');
    const listaBebidas = document.getElementById('listaBebidas');
    listaAlimentos.innerHTML = '';
    listaBebidas.innerHTML = '';

    catalogo.forEach(producto => {
      const item = document.createElement('div');
      item.className = 'producto-item';
      item.innerHTML = `
        <div class="producto-nombre">${producto.nombre}</div>
        <div class="producto-precio">$${producto.precio.toFixed(2)}</div>
        <button class="comentario-btn" style="position: absolute; bottom: 5px; right: 5px;" onclick="abrirComentarioModal(null, '${producto.nombre}', '${producto.tipo}', ${producto.precio}); event.stopPropagation()">✎</button>
      `;
      item.onclick = () => agregarProductoAComanda(producto.nombre, producto.tipo, producto.precio, true);
      if (producto.tipo === 'alimentos') {
        listaAlimentos.appendChild(item);
      } else {
        listaBebidas.appendChild(item);
      }
    });
  }

  function renderizarRepetir() {
    const listaRepetir = document.getElementById('listaRepetir');
    listaRepetir.innerHTML = '';

    productosAnteriores.forEach(producto => {
      if (!comandaActual.some(p => p.nombre === producto.nombre)) {
        const item = document.createElement('div');
        item.className = 'producto-item';
        item.innerHTML = `
          <div class="producto-nombre">${producto.nombre}</div>
          <div class="producto-precio">$${producto.precio.toFixed(2)}</div>
          <button class="comentario-btn" style="position: absolute; bottom: 5px; right: 5px;" onclick="abrirComentarioModal(null, '${producto.nombre}', '${producto.tipo}', ${producto.precio}); event.stopPropagation()">✎</button>
        `;
        item.onclick = () => agregarProductoAComanda(producto.nombre, producto.tipo, producto.precio, true);
        listaRepetir.appendChild(item);
      }
    });
  }

  function agregarProductoAComanda(nombre, tipo, precio, sinComentario = false) {
    const producto = { nombre, tipo, precio, cantidad: 1, comentario: '' };
    if (!sinComentario) {
      abrirComentarioModal(null, nombre, tipo, precio);
    } else {
      comandaActual.push(producto);
      renderizarComandaActual();
      renderizarRepetir();
    }
  }

  function renderizarComandaActual() {
    const comandaDiv = document.getElementById('comandaActual');
    comandaDiv.innerHTML = '';
    comandaActual.forEach((producto, index) => {
      const item = document.createElement('div');
      item.className = 'comanda-item';
      item.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <span>${producto.nombre} ($${producto.precio.toFixed(2)}) x
            <input type="number" min="1" value="${producto.cantidad}" onchange="actualizarCantidad(${index}, this.value)">
          </span>
          <div class="buttons">
            <button class="comentario-btn" onclick="abrirComentarioModal(${index}); event.stopPropagation()">✎</button>
            <button class="delete-btn" onclick="borrarProducto(${index})">X</button>
          </div>
        </div>
        ${producto.comentario ? `<div class="comentario-text">${producto.comentario}</div>` : ''}
      `;
      comandaDiv.appendChild(item);
    });
  }

  function actualizarCantidad(index, nuevaCantidad) {
    if (nuevaCantidad > 0) {
      comandaActual[index].cantidad = parseInt(nuevaCantidad);
      renderizarComandaActual();
    } else {
      borrarProducto(index);
    }
  }

  function abrirComentarioModal(index, nombre = null, tipo = null, precio = null) {
    const modal = document.getElementById('comentarioModal');
    const input = document.getElementById('comentarioInput');
    const title = document.getElementById('comentarioProducto');

    comentarioIndex = index;

    if (index === null) {
      title.textContent = `Agregar Comentario para ${nombre}`;
      input.value = '';
    } else {
      const producto = comandaActual[index];
      title.textContent = `Editar Comentario para ${producto.nombre}`;
      input.value = producto.comentario || '';
    }

    modal.style.display = 'block';
  }

  function guardarComentario() {
    const input = document.getElementById('comentarioInput');
    const comentario = input.value.trim();

    if (comentarioIndex === null) {
      const nombre = document.getElementById('comentarioProducto').textContent.replace('Agregar Comentario para ', '');
      const producto = catalogo.find(p => p.nombre === nombre) || productosAnteriores.find(p => p.nombre === nombre);
      if (producto) {
        comandaActual.push({ ...producto, cantidad: 1, comentario });
        renderizarRepetir();
      }
    } else {
      comandaActual[comentarioIndex].comentario = comentario;
    }

    renderizarComandaActual();
    cerrarComentarioModal();
  }

  function cerrarComentarioModal() {
    document.getElementById('comentarioModal').style.display = 'none';
    comentarioIndex = null;
  }

  function borrarProducto(index) {
    const producto = comandaActual[index];
    fetch(window.location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'borrar', producto: producto, ingredientes: ingredientesPorProducto })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        comandaActual.splice(index, 1);
        renderizarComandaActual();
        renderizarRepetir();
        alert(data.message);
        window.location.href = 'http://localhost/DSI30/Hackathon/Mesas.php';
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Ocurrió un error al borrar el producto.');
    });
  }

  function mostrarRepetir() {
    const repetirTab = document.getElementById('repetirTab');
    if (productosAnteriores.length === 0) {
      alert('No hay productos anteriores para repetir.');
      return;
    }
    repetirTab.style.display = 'block';
    renderizarRepetir();
  }

  function enviarComanda() {
    if (comandaActual.length > 0) {
      fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'enviar', comanda: comandaActual, ingredientes: ingredientesPorProducto })
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          alert(data.message);
          comandaActual = [];
          renderizarComandaActual();
          renderizarRepetir();
          window.location.href = 'http://localhost/DSI30/Hackathon/Mesas.php';
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al enviar la comanda.');
      });
    } else {
      alert('Agregue al menos un producto a la comanda.');
    }
  }
</script>
</body>
</html>

<?php
// Cerrar la conexión
mysqli_close($conexion);
?>