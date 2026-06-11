<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/conexion.php';

// ── GUARDAR TODO DE UNA VEZ ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {

    // 1. Guardamos el testigo primero
    $testigo_nombre    = $_POST['testigo_nombre'];
    $testigo_apellidos = $_POST['testigo_apellidos'];
    $testigo_dni       = $_POST['testigo_dni'];

    $sql = "INSERT INTO testigo (nombre, apellidos, DNI) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $testigo_nombre, $testigo_apellidos, $testigo_dni);
    $stmt->execute();
    $idtestigo = $conexion->insert_id;
    $stmt->close();

    // 2. Guardamos el cliente
    $nombre    = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $dni       = $_POST['dni'];
    $telefono  = $_POST['telefono'];
    $correo    = $_POST['correo'];
    $direccion = $_POST['direccion'];

    $sql2 = "INSERT INTO cliente (nombre, apellidos, DNI, telefono, correo, direccion)
             VALUES (?, ?, ?, ?, ?, ?)";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param("ssssss", $nombre, $apellidos, $dni, $telefono, $correo, $direccion);
    $stmt2->execute();
    $idcliente = $conexion->insert_id;
    $stmt2->close();

    // 3. Guardamos el contrato
    $fecha_contrato = $_POST['fecha_contrato'];
    $fecha_entrega  = $_POST['fecha_entrega'] != '' ? $_POST['fecha_entrega'] : NULL;

    $sql3 = "INSERT INTO contratos (fecha_contrato, fecha_entrega, idcliente, idtestigo)
             VALUES (?, ?, ?, ?)";
    $stmt3 = $conexion->prepare($sql3);
    $stmt3->bind_param("ssii", $fecha_contrato, $fecha_entrega, $idcliente, $idtestigo);
    $stmt3->execute();
    $idcontrato = $conexion->insert_id;
    $stmt3->close();

    // 4. Guardamos el detalle del contrato
    $idproducto = $_POST['idproducto'];
    $cantidad   = $_POST['cantidad'];
    $adelanto   = $_POST['adelanto'];
    $total      = $_POST['total'];
    $subtotal   = $total - $adelanto;

    $sql4 = "INSERT INTO detallecontratos (cantidad, adelanto, subtotal, total, idcontrato, idproducto)
             VALUES (?, ?, ?, ?, ?, ?)";
    $stmt4 = $conexion->prepare($sql4);
    $stmt4->bind_param("idddii", $cantidad, $adelanto, $subtotal, $total, $idcontrato, $idproducto);
    $stmt4->execute();
    $stmt4->close();

    header("Location: contratos.php");
    exit();
}

// ── ELIMINAR CONTRATO ────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    // Primero el detalle, luego el contrato (por las FK)
    $stmt = $conexion->prepare("DELETE FROM detallecontratos WHERE idcontrato = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $conexion->prepare("DELETE FROM contratos WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: contratos.php");
    exit();
}

// ── OBTENER CONTRATOS PARA LA TABLA ─────────────────────────
$contratos = $conexion->query("
    SELECT
        c.id,
        c.fecha_contrato,
        c.fecha_entrega,
        cl.nombre       AS nombre_cliente,
        cl.apellidos    AS apellidos_cliente,
        cl.DNI          AS dni_cliente,
        cl.telefono     AS telefono_cliente,
        t.nombre        AS nombre_testigo,
        t.apellidos     AS apellidos_testigo
    FROM contratos c
    INNER JOIN cliente cl ON c.idcliente = cl.id
    LEFT  JOIN testigo t  ON c.idtestigo = t.id
    ORDER BY c.id DESC
");

// ── PRODUCTOS PARA EL SELECT ─────────────────────────────────
$productos = $conexion->query("SELECT id, nombre FROM productos ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contratos - Ishume</title>
  <link rel="stylesheet" href="css/sidebar.css">
  <link rel="stylesheet" href="css/contratos.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- ══════════════ CONTENIDO ══════════════ -->
<div class="contenido">

  <div class="cabecera">
    <div>
      <h1>Contratos</h1>
      <p class="subtitulo">Administra todos los contratos con clientes</p>
    </div>
    <button class="btn-nuevo" onclick="abrirModal()">+ Nuevo Contrato</button>
  </div>

  <!-- TABLA DE CONTRATOS -->
  <div class="tabla-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Cliente</th>
          <th>DNI cliente</th>
          <th>Teléfono</th>
          <th>Testigo</th>
          <th>Fecha contrato</th>
          <th>Fecha entrega</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $numero = 1;
        while ($fila = $contratos->fetch_assoc()):
        ?>
        <tr>
          <td><?php echo str_pad($numero++, 2, '0', STR_PAD_LEFT); ?></td>
          <td><?php echo htmlspecialchars($fila['nombre_cliente'] . ' ' . $fila['apellidos_cliente']); ?></td>
          <td><?php echo htmlspecialchars($fila['dni_cliente']); ?></td>
          <td><?php echo $fila['telefono_cliente'] ? htmlspecialchars($fila['telefono_cliente']) : '—'; ?></td>
          <td>
            <?php echo $fila['nombre_testigo']
                ? htmlspecialchars($fila['nombre_testigo'] . ' ' . $fila['apellidos_testigo'])
                : '—';
            ?>
          </td>
          <td><?php echo $fila['fecha_contrato']; ?></td>
          <td><?php echo $fila['fecha_entrega'] ? $fila['fecha_entrega'] : '—'; ?></td>
          <td>
            <a class="btn-editar" href="#">✏️ Editar</a>
            <a class="btn-eliminar"
               href="contratos.php?eliminar=<?php echo $fila['id']; ?>"
               onclick="return confirm('¿Seguro que quieres eliminar este contrato?')">
              🗑️ Eliminar
            </a>
          </td>
        </tr>
        <?php endwhile; ?>

        <?php if ($numero == 1): ?>
        <tr>
          <td colspan="8" style="text-align:center; color:#a0aec0; padding: 30px;">
            No hay contratos registrados aún.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- ══════════════ MODAL FORMULARIO ══════════════ -->
<div class="modal-fondo" id="modal">
  <div class="modal">
    <h2>Nuevo Contrato</h2>

    <form method="POST" action="contratos.php">

      <!-- ── DATOS DEL CLIENTE ── -->
      <p class="seccion-titulo">Datos del cliente</p>

      <div class="campo-fila">
        <div class="campo">
          <label>Nombre *</label>
          <input type="text" name="nombre" placeholder="Ej: María" required>
        </div>
        <div class="campo">
          <label>Apellidos *</label>
          <input type="text" name="apellidos" placeholder="Ej: López Torres" required>
        </div>
      </div>

      <div class="campo-fila">
        <div class="campo">
          <label>DNI *</label>
          <input type="text" name="dni" placeholder="Ej: 12345678" maxlength="20" required>
        </div>
        <div class="campo">
          <label>Teléfono</label>
          <input type="text" name="telefono" placeholder="Ej: 999 000 111">
        </div>
      </div>

      <div class="campo-fila">
        <div class="campo">
          <label>Correo</label>
          <input type="email" name="correo" placeholder="Ej: maria@correo.com">
        </div>
        <div class="campo">
          <label>Dirección</label>
          <input type="text" name="direccion" placeholder="Ej: Av. Los Álamos 456">
        </div>
      </div>

      <!-- ── DATOS DEL TESTIGO ── -->
      <p class="seccion-titulo">Datos del testigo</p>

      <div class="campo-fila">
        <div class="campo">
          <label>Nombre *</label>
          <input type="text" name="testigo_nombre" placeholder="Ej: Carlos" required>
        </div>
        <div class="campo">
          <label>Apellidos *</label>
          <input type="text" name="testigo_apellidos" placeholder="Ej: Ramos Silva" required>
        </div>
      </div>

      <div class="campo">
        <label>DNI del testigo *</label>
        <input type="text" name="testigo_dni" placeholder="Ej: 87654321" maxlength="20" required>
      </div>

      <!-- ── DATOS DEL CONTRATO ── -->
      <p class="seccion-titulo">Datos del contrato</p>

      <div class="campo-fila">
        <div class="campo">
          <label>Fecha del contrato *</label>
          <input type="date" name="fecha_contrato" required>
        </div>
        <div class="campo">
          <label>Fecha de entrega</label>
          <input type="date" name="fecha_entrega">
        </div>
      </div>

      <div class="campo">
        <<label>Producto *</label>
            <input type="text" name="producto" placeholder="Escribe el producto" required>
      </div>

      <div class="campo-fila">
        <div class="campo">
          <label>Cantidad *</label>
          <input type="number" name="cantidad" min="1" value="1" required>
        </div>
        <div class="campo">
          <label>Adelanto (S/.)</label>
          <input type="number" name="adelanto" min="0" step="0.01" value="0.00">
        </div>
      </div>

      <div class="campo">
        <label>Total (S/.) *</label>
        <input type="number" name="total" min="0" step="0.01" value="0.00" required>
      </div>

      <div class="modal-botones">
        <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
        <button type="submit" name="guardar" class="btn-guardar">Guardar</button>
      </div>

    </form>
  </div>
</div>

<script>
  function abrirModal() {
    document.getElementById('modal').classList.add('visible');
  }

  function cerrarModal() {
    document.getElementById('modal').classList.remove('visible');
  }

  document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
  });
</script>

</body>
</html>