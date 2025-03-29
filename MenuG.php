<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';

    $host = "localhost";
    $usuario = "root";
    $contrasena = "";
    $base_datos = "marmoton";

    $conexion = mysqli_connect($host, $usuario, $contrasena, $base_datos);
    if (!$conexion) {
        die("Error al conectar a la base de datos: " . mysqli_connect_error());
    }

    $sql_insumos = "SELECT id, nombre, stock, es_perecedero FROM insumos";
    $result_insumos = mysqli_query($conexion, $sql_insumos);
    if (!$result_insumos) {
        die("Error al consultar insumos: " . mysqli_error($conexion));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action']) && !isset($_FILES['pdf'])) {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("Datos recibidos para pedido: " . print_r($data, true));

        if (!empty($data)) {
            mysqli_begin_transaction($conexion);
            try {
                foreach ($data as $item) {
                    $nombre = mysqli_real_escape_string($conexion, $item['nombre']);
                    $pedido = (int)$item['pedido'];

                    $sql_get_id = "SELECT id FROM insumos WHERE nombre = '$nombre'";
                    $result_id = mysqli_query($conexion, $sql_get_id);
                    if (!$result_id || mysqli_num_rows($result_id) == 0) {
                        throw new Exception("Insumo '$nombre' no encontrado en insumos.");
                    }
                    $row = mysqli_fetch_assoc($result_id);
                    $id = $row['id'];

                    if ($pedido >= 0) {
                        $sql_check_bodega = "SELECT id FROM bodega WHERE id = $id";
                        $result_check = mysqli_query($conexion, $sql_check_bodega);

                        if ($result_check && mysqli_num_rows($result_check) > 0) {
                            $sql_update_bodega = "UPDATE bodega SET pedido = $pedido, nombre = '$nombre' WHERE id = $id";
                            if (!mysqli_query($conexion, $sql_update_bodega)) {
                                throw new Exception("Error al actualizar bodega: " . mysqli_error($conexion));
                            }
                        } else {
                            $sql_insert_bodega = "INSERT INTO bodega (id, nombre, pedido) VALUES ($id, '$nombre', $pedido)";
                            if (!mysqli_query($conexion, $sql_insert_bodega)) {
                                throw new Exception("Error al insertar en bodega: " . mysqli_error($conexion));
                            }
                        }

                        $sql_update_insumos = "UPDATE insumos SET stock = stock + $pedido WHERE id = $id";
                        if (!mysqli_query($conexion, $sql_update_insumos)) {
                            throw new Exception("Error al actualizar insumos: " . mysqli_error($conexion));
                        }
                    }
                }
                mysqli_commit($conexion);
                echo json_encode(['status' => 'success', 'message' => 'Pedidos procesados y stock actualizado']);
            } catch (Exception $e) {
                mysqli_rollback($conexion);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                error_log("Error en transacción: " . $e->getMessage());
            }
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos válidos']);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
        $pdf_file = $_FILES['pdf']['tmp_name'];
        $pdf_name = $_FILES['pdf']['name'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'xdxdelchavo@gmail.com';
            $mail->Password = 'yilo giqq jpsa wfkl'; // Contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('xdxdelchavo@gmail.com', 'Marmotón - Sistema de Inventario');
            $mail->addAddress('miguelmoreno.uaq@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = 'Pedido Generado - Marmotón';
            $mail->Body = 'Adjunto encontrarás el PDF con el pedido generado para la bodega.';
            $mail->addAttachment($pdf_file, $pdf_name);

            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'PDF enviado por correo con éxito']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "Error al enviar el correo: {$mail->ErrorInfo}"]);
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'chat') {
        $message = $_POST['message'];
        $api_key = "AIzaSyDKLgmS2HfrNmpZTNh02Vv-yaRQvzw5CyM";

        $insumos_data = [];
        while ($row = mysqli_fetch_assoc($result_insumos)) {
            $insumos_data[] = $row;
        }
        mysqli_data_seek($result_insumos, 0); // Resetear puntero para otras consultas

        $context = "
Eres un asistente de gestión de inventarios para un sistema de control de insumos y productos en bodega. El sistema maneja dos tipos principales de insumos: perecederos y no perecederos. Los insumos no perecederos deben mantenerse con un stock alrededor de 50 unidades, mientras que los perecederos deben ser gestionados con una cantidad mínima de stock para evitar el desperdicio, manteniendo en cuenta su vida útil y demanda.

La información sobre los insumos que gestionas es la siguiente:
" . json_encode($insumos_data) . "

Tu tarea es asistir al gerente respondiendo preguntas sobre cantidades de pedido y sugiriendo valores adecuados de acuerdo con las reglas de inventario, considerando lo siguiente:
- Si un insumo es **perecedero**, se debe evitar tener un exceso de stock, por lo que las sugerencias de cantidad deben basarse en la demanda, la vida útil y el consumo habitual, evitando la acumulación de productos que no se van a vender antes de su fecha de caducidad.
- Los insumos **no perecederos** deben mantener un stock de alrededor de **50 unidades**, a menos que se indique lo contrario por factores específicos como demanda o estacionalidad.

Si el stock de algún insumo es inferior a la cantidad mínima sugerida o si se acerca a niveles críticos, debes indicar esto y sugerir un pedido para reponer el stock, tomando en cuenta los valores actuales y los pedidos previos.

Además, asegúrate de que las respuestas sean claras, concisas y justificadas. Si se menciona un insumo perecedero, explica por qué se debe evitar el exceso y cuáles son las mejores prácticas para mantener el stock sin desperdiciar.

Las preguntas comunes podrían incluir:
- ¿Cuántos insumos de un producto determinado deben pedirse para mantener un stock adecuado?
- ¿Qué productos están por debajo del stock recomendado?
- ¿Cuáles son los insumos perecederos y cuál es la cantidad máxima recomendada para no desperdiciar?
- ¿Qué insumos necesitan ser reabastecidos y cuánto stock adicional se debe pedir para cubrir la demanda?
";

        $prompt = $context . "\nUsuario: " . $message;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$api_key";
        $data = ["contents" => [["parts" => [["text" => $prompt]]]]];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($response, true);
        $reply = $response_data['candidates'][0]['content']['parts'][0]['text'] ?? "Error: No response from API";
        echo json_encode(['reply' => $reply]);
        exit;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marmotón - Área de Gerente</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #000000; margin: 0; min-height: 100vh; position: relative; }
        .banner { width: 100%; background-color: #1a1a1a; padding: 25px; text-align: left; font-size: 40px; font-weight: bold; color: #ed8b35; position: fixed; top: 0; left: 0; z-index: 10; display: flex; align-items: center; justify-content: space-between; }
        .salir-btn { position: fixed; top: 120px; right: 20px; padding: 12px 24px; background-color: #333333; color: #ffffff; border: 1px solid #ed8b35; border-radius: 6px; font-size: 16px; cursor: pointer; z-index: 11; }
        .salir-btn:hover { background-color: #4d4d4d; }
        .area-gerente-container { padding: 180px 20px 100px 20px; display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 800px; margin: 0 auto; }
        .insumos-box { background-color: #1a1a1a; width: 100%; padding: 20px; border-radius: 6px; box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); color: #ed8b35; margin-bottom: 20px; border: 1px solid #ed8b35; }
        h2 { margin: 0 0 20px 0; font-size: 24px; color: #ed8b35; text-align: center; }
        h3 { margin: 20px 0 10px 0; font-size: 18px; color: #ed8b35; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #ffffff; }
        th, td { padding: 10px; border: 1px solid #ed8b35; text-align: left; color: #000000; }
        th { background-color: #ed8b35; color: #000000; }
        input[type="number"] { width: 60px; padding: 6px; border-radius: 4px; border: 1px solid #ed8b35; font-size: 12px; background-color: #ffffff; color: #000000; }
        .submit-btn { padding: 12px 24px; background-color: #ed8b35; color: #000000; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); margin-top: 20px; }
        .submit-btn:hover { background-color: #ffaa5c; }
        .chatbox { background-color: #1a1a1a; width: 100%; max-width: 600px; padding: 20px; border-radius: 6px; border: 1px solid #ed8b35; color: #ffffff; margin: 20px auto; }
        .chatbox-messages { height: 300px; overflow-y: auto; margin-bottom: 10px; padding: 10px; background-color: #333333; border-radius: 4px; }
        .chatbox-input { display: flex; gap: 10px; }
        .chatbox-input input { flex-grow: 1; padding: 8px; border: 1px solid #ed8b35; border-radius: 4px; background-color: #ffffff; color: #000000; }
        .chatbox-input button { padding: 8px 16px; background-color: #ed8b35; color: #000000; border: none; border-radius: 4px; cursor: pointer; }
        .chatbox-input button:hover { background-color: #ffaa5c; }
        .message { margin: 5px 0; }
        .user-message { color: #ed8b35; }
        .bot-message { color: #ffffff; }
    </style>
</head>
<body>
    <div class="banner">
        <span>Marmotón - Área de Gerente</span>
    </div>
    <button class="salir-btn" onclick="window.location.href='FAcceso.php'">Salir</button>
    <div class="area-gerente-container">
        <div class="insumos-box">
            <h2>Gestión de Insumos</h2>
            <h3>Insumos Disponibles</h3>
            <table id="insumosTable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Stock</th>
                        <th>Pedido Sugerido</th>
                        <th>Pedido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (mysqli_num_rows($result_insumos) > 0) {
                            while ($row = mysqli_fetch_assoc($result_insumos)) {
                                $pedido_sugerido = $row['stock'] < 50 ? 50 - $row['stock'] : 0;
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
                                echo "<td>" . $pedido_sugerido . "</td>";
                                echo "<td><input type='number' min='0' value='$pedido_sugerido'></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No hay insumos disponibles.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
            <button class="submit-btn" onclick="submitToBodega()">Enviar a Bodega</button>
        </div>

        <div class="chatbox">
            <h3>Chat con Asistente de Inventario</h3>
            <div class="chatbox-messages" id="chatMessages"></div>
            <div class="chatbox-input">
                <input type="text" id="chatInput" placeholder="Pregunta sobre pedidos o stock...">
                <button onclick="sendMessage()">Enviar</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function submitToBodega() {
            const table = document.getElementById('insumosTable');
            const rows = table.getElementsByTagName('tr');
            let data = [];

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].cells;
                if (cells.length < 4) continue;
                const nombre = cells[0].textContent.trim();
                const pedido = cells[3].getElementsByTagName('input')[0].value;
                data.push({ nombre: nombre, pedido: pedido });
            }

            if (data.length > 0) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const element = document.getElementById('insumosTable');
                        const opt = {
                            margin: 0.5,
                            filename: 'Pedido_Bodega_' + new Date().toISOString().slice(0,10) + '.pdf',
                            image: { type: 'jpeg', quality: 0.98 },
                            html2canvas: { scale: 2, useCORS: true, scrollY: 0, windowHeight: document.body.scrollHeight },
                            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
                            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
                        };

                        html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
                            pdf.save(opt.filename);

                            const pdfBlob = pdf.output('blob');
                            const formData = new FormData();
                            formData.append('pdf', pdfBlob, opt.filename);

                            fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(emailResult => {
                                if (emailResult.status === 'success') {
                                    alert('Pedidos procesados, PDF descargado y enviado por correo.');
                                } else {
                                    alert('Pedidos procesados y PDF descargado, pero error al enviar correo: ' + emailResult.message);
                                }
                                window.location.reload();
                            })
                            .catch(error => {
                                console.error('Error al enviar PDF por correo:', error);
                                alert('Pedidos procesados y PDF descargado, pero error al enviar correo.');
                                window.location.reload();
                            });
                        });
                    } else {
                        alert('Error al procesar los pedidos: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error al enviar datos:', error);
                    alert('Error al enviar los datos a Bodega');
                });
            } else {
                alert('No hay datos para enviar a Bodega');
            }
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const messages = document.getElementById('chatMessages');
            const message = input.value.trim();

            if (message) {
                // Agregar mensaje del usuario al chatbox
                const userMsg = document.createElement('div');
                userMsg.className = 'message user-message';
                userMsg.textContent = 'Tú: ' + message;
                messages.appendChild(userMsg);

                // Limpiar el input
                input.value = '';

                // Enviar el mensaje al servidor
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=chat&message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(result => {
                    if (result.reply) {
                        // Agregar respuesta del bot al chatbox
                        const botMsg = document.createElement('div');
                        botMsg.className = 'message bot-message';
                        botMsg.textContent = 'Asistente: ' + result.reply;
                        messages.appendChild(botMsg);
                        
                        // Hacer scroll hasta el último mensaje
                        messages.scrollTop = messages.scrollHeight;
                    } else {
                        console.error("Error: No se recibió respuesta del servidor.");
                    }
                })
                .catch(error => console.error('Error al enviar mensaje:', error));
            }
        }

        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>

    <?php
        mysqli_close($conexion);
    ?>
</body>
</html>
