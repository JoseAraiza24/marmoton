<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marmotón - Gestión de Comandas</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #000000; /* Negro */
      margin: 0;
      min-height: 100vh;
      position: relative;
    }

    .banner {
      width: 100%;
      background-color: #1a1a1a; /* Gris oscuro */
      padding: 25px;
      text-align: left;
      font-size: 40px;
      font-weight: bold;
      font-style: normal; /* Más formal, sin oblique */
      color: #ed8b35; /* Naranja */
      position: fixed;
      top: 0;
      left: 0;
      z-index: 10;
    }

    .salir-btn {
      position: fixed;
      top: 120px; /* Más abajo, ajustado para coincidir con el otro menú */
      right: 20px;
      padding: 12px 24px;
      background-color: #ff0000; /* Rojo */
      color: #ffffff; /* Blanco */
      border: none; /* Quité el borde para un look más limpio */
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      z-index: 11;
    }

    .salir-btn:hover {
      background-color: #cc0000; /* Rojo más oscuro al hover */
    }

    .crear-btn {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 14px 28px;
      background-color: #ed8b35; /* Naranja */
      color: #000000; /* Negro */
      border: none;
      border-radius: 6px;
      font-size: 18px;
      cursor: pointer;
      z-index: 11;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Sombra sutil */
    }

    .crear-btn:hover {
      background-color: #ffaa5c; /* Naranja más claro al hover */
    }

    .mesas-container {
      padding: 180px 20px 100px 20px; /* Ajustado para coincidir con el otro menú */
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: flex-start;
    }

    .mesa-box {
      background-color: #ed8b35; /* Naranja */
      width: 80px;
      height: 80px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 6px; /* Menos redondeado para formalidad */
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Sombra sutil */
      cursor: pointer;
      color: #000000; /* Negro */
      font-weight: bold;
      font-size: 18px;
      text-align: center;
    }

    .mesa-box:hover {
      background-color: #ffaa5c; /* Naranja más claro al hover */
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6); /* Fondo negro semitransparente */
      justify-content: center;
      align-items: center;
      z-index: 10;
    }

    .modal-content {
      background-color: #1a1a1a; /* Gris oscuro */
      padding: 15px;
      border-radius: 6px; /* Menos redondeado */
      width: 220px;
      text-align: center;
      border: 1px solid #ed8b35; /* Borde naranja */
    }

    .modal-content h2 {
      margin: 0 0 10px 0;
      font-size: 18px;
      color: #ed8b35; /* Naranja */
    }

    .modal-content label {
      display: block;
      margin: 8px 0 4px 0;
      font-weight: bold;
      text-align: left;
      color: #ed8b35; /* Naranja */
      font-size: 12px;
    }

    .modal-content input {
      width: 90%;
      padding: 6px;
      margin: 0 auto;
      border-radius: 4px; /* Bordes más rectos */
      border: 1px solid #ed8b35; /* Borde naranja */
      font-size: 12px;
      display: block;
      background-color: #ffffff; /* Blanco */
      color: #000000; /* Negro */
    }

    .modal-content button {
      margin-top: 10px;
      width: 100%;
      padding: 8px;
      background-color: #ed8b35; /* Naranja */
      color: #000000; /* Negro */
      border: none;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
    }

    .modal-content button:hover {
      background-color: #ffaa5c; /* Naranja más claro al hover */
    }

    .modal-content .comandar-btn {
      padding: 12px;
      font-size: 18px;
      font-weight: bold;
      background-color: #ed8b35; /* Naranja */
      color: #000000; /* Negro */
    }

    .modal-content .comandar-btn:hover {
      background-color: #ffaa5c; /* Naranja más claro al hover */
    }
  </style>
</head>
<body>
<div class="banner">Marmotón - Gestión de Comandas</div>
<button class="salir-btn" onclick="window.location.href='http://localhost/DSI30/Hackathon/FAcceso.php'">Salir</button>
<div class="mesas-container" id="mesasContainer"></div>
<button class="crear-btn" onclick="abrirModalCrear()">Crear Mesa</button>

<!-- Modal para crear mesa -->
<div id="modalCrear" class="modal">
  <div class="modal-content">
    <h2>Crear Mesa</h2>
    <form>
      <label for="idMesa">Nombre de la Mesa (ID)</label>
      <input type="text" id="idMesa" name="idMesa" required>
      <label for="comensales">Número de Comensales</label>
      <input type="number" id="comensales" name="comensales" min="1" required>
      <button type="button" onclick="crearMesa()">Guardar</button>
      <button type="button" onclick="cerrarModalCrear()">Cancelar</button>
    </form>
  </div>
</div>

<!-- Modal para opciones de mesa -->
<div id="modalMesa" class="modal">
  <div class="modal-content">
    <h2 id="mesaSeleccionada">Mesa Seleccionada</h2>
    <button class="comandar-btn" onclick="window.location.href='http://localhost/DSI30/Hackathon/Comandera.php'">Comandar</button>
    <button onclick="abrirModalModificarComensales()">Modificar Comensales</button>
    <button onclick="abrirModalModificarNombre()">Modificar Nombre</button>
    <button onclick="mostrarResumenCuenta()">Resumen de Cuenta</button>
    <button onclick="cerrarModalMesa()">Cerrar</button>
  </div>
</div>

<!-- Modal para modificar comensales -->
<div id="modalModificarComensales" class="modal">
  <div class="modal-content">
    <h2>Modificar Comensales</h2>
    <form>
      <label for="nuevosComensales">Nuevo Número de Comensales</label>
      <input type="number" id="nuevosComensales" name="nuevosComensales" min="1" required>
      <button type="button" onclick="guardarNuevosComensales()">Guardar</button>
      <button type="button" onclick="cerrarModalModificarComensales()">Cancelar</button>
    </form>
  </div>
</div>

<!-- Modal para modificar nombre -->
<div id="modalModificarNombre" class="modal">
  <div class="modal-content">
    <h2>Modificar Nombre</h2>
    <form>
      <label for="nuevoNombre">Nuevo Nombre de la Mesa</label>
      <input type="text" id="nuevoNombre" name="nuevoNombre" required>
      <button type="button" onclick="guardarNuevoNombre()">Guardar</button>
      <button type="button" onclick="cerrarModalModificarNombre()">Cancelar</button>
    </form>
  </div>
</div>

<script>
  let mesas = []; // Array para almacenar las mesas
  let mesaActual = null; // Para rastrear la mesa seleccionada

  function abrirModalCrear() {
    document.getElementById('modalCrear').style.display = 'flex';
  }

  function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
  }

  function abrirModalMesa(mesaInfo) {
    document.getElementById('mesaSeleccionada').textContent = mesaInfo;
    const [id, comensales] = mesaInfo.split(' - ');
    mesaActual = mesas.find(m => m.id === id); // Guardar referencia a la mesa actual
    document.getElementById('modalMesa').style.display = 'flex';
  }

  function cerrarModalMesa() {
    document.getElementById('modalMesa').style.display = 'none';
    mesaActual = null; // Limpiar referencia
  }

  function crearMesa() {
    const idMesa = document.getElementById('idMesa').value.trim();
    const comensales = document.getElementById('comensales').value;
    if (idMesa && comensales) {
      mesas.push({ id: idMesa, comensales: comensales });
      ordenarYRenderizarMesas();
      cerrarModalCrear();
    } else {
      alert('Por favor, completa todos los campos.');
    }
  }

  function ordenarYRenderizarMesas() {
    // Ordenar alfabética y numéricamente
    mesas.sort((a, b) => a.id.localeCompare(b.id, undefined, { numeric: true, sensitivity: 'base' }));

    const container = document.getElementById('mesasContainer');
    container.innerHTML = ''; // Limpiar contenedor

    mesas.forEach(mesa => {
      const mesaBox = document.createElement('div');
      mesaBox.className = 'mesa-box';
      mesaBox.innerHTML = mesa.id; // Solo el ID de la mesa
      mesaBox.onclick = () => abrirModalMesa(`${mesa.id} - ${mesa.comensales} comensales`);
      container.appendChild(mesaBox);
    });
  }

  // Funciones para modificar comensales
  function abrirModalModificarComensales() {
    document.getElementById('modalModificarComensales').style.display = 'flex';
  }

  function cerrarModalModificarComensales() {
    document.getElementById('modalModificarComensales').style.display = 'none';
  }

  function guardarNuevosComensales() {
    const nuevosComensales = document.getElementById('nuevosComensales').value;
    if (nuevosComensales && mesaActual) {
      mesaActual.comensales = nuevosComensales;
      ordenarYRenderizarMesas(); // Actualizar visualmente
      cerrarModalModificarComensales();
      cerrarModalMesa(); // Cerrar ambos modales
    } else {
      alert('Por favor, ingrese un número válido.');
    }
  }

  // Funciones para modificar nombre
  function abrirModalModificarNombre() {
    document.getElementById('modalModificarNombre').style.display = 'flex';
  }

  function cerrarModalModificarNombre() {
    document.getElementById('modalModificarNombre').style.display = 'none';
  }

  function guardarNuevoNombre() {
    const nuevoNombre = document.getElementById('nuevoNombre').value.trim();
    if (nuevoNombre && mesaActual) {
      mesaActual.id = nuevoNombre;
      ordenarYRenderizarMesas(); // Actualizar visualmente
      cerrarModalModificarNombre();
      cerrarModalMesa(); // Cerrar ambos modales
    } else {
      alert('Por favor, ingrese un nombre válido.');
    }
  }

  // Función para resumen de cuenta (placeholder por ahora)
  function mostrarResumenCuenta() {
    if (mesaActual) {
      alert(`Resumen de cuenta para ${mesaActual.id} - ${mesaActual.comensales} comensales\n(Aún no hay base de datos para mostrar productos comandados)`);
    }
  }
</script>
</body>
</html>