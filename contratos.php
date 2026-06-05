<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/conexion.php';

// ── GUARDAR NUEVO CONTRATO ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {
    $idcliente      = $_POST['idcliente'];
    $idtestigo      = $_POST['idtestigo'];
    $fecha_contrato = $_POST['fecha_contrato'];
    $fecha_entrega  = $_POST['fecha_entrega'] != '' ? $_POST['fecha_entrega'] : NULL;

    $sql = "INSERT INTO contratos (fecha_contrato, fecha_entrega, idcliente, idtestigo)
            VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssii", $fecha_contrato, $fecha_entrega, $idcliente, $idtestigo);
    $stmt->execute();

    // Guardamos también el detalle (producto, cantidad, adelanto, total)
    $idcontrato = $conexion->insert_id;
    $idproducto = $_POST['idproducto'];
    $cantidad   = $_POST['cantidad'];
    $adelanto   = $_POST['adelanto'];
    $total      = $_POST['total'];
    $subtotal   = $total - $adelanto;

    $sql2 = "INSERT INTO detallecontratos (cantidad, adelanto, subtotal, total, idcontrato, idproducto)
             VALUES (?, ?, ?, ?, ?, ?)";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param("idddii", $cantidad, $adelanto, $subtotal, $total, $idcontrato, $idproducto);
    $stmt2->execute();
    $stmt2->close();
    $stmt->close();

    header("Location: contratos.php");
    exit();
}

// ── ELIMINAR CONTRATO ────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    // Primero eliminamos el detalle (por la FK)
    $sql = "DELETE FROM detallecontratos WHERE idcontrato = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Luego eliminamos el contrato
    $sql2 = "DELETE FROM contratos WHERE id = ?";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: contratos.php");
    exit();
}

// ── OBTENER CONTRATOS (con nombre del cliente) ───────────────
$contratos = $conexion->query("
    SELECT
        c.id,
        c.fecha_contrato,
        c.fecha_entrega,
        cl.nombre AS nombre_cliente,
        cl.apellidos AS apellidos_cliente,
        t.nombre AS nombre_testigo,
        t.apellidos AS apellidos_testigo
    FROM contratos c
    INNER JOIN cliente cl ON c.idcliente = cl.id
    LEFT  JOIN testigo t  ON c.idtestigo = t.id
    ORDER BY c.id DESC
");

// ── LISTAS PARA EL FORMULARIO ────────────────────────────────
$clientes  = $conexion->query("SELECT id, nombre, apellidos FROM cliente ORDER BY nombre");
$testigos  = $conexion->query("SELECT id, nombre, apellidos FROM testigo ORDER BY nombre");
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
          <td>
            <?php
              if ($fila['nombre_testigo']) {
                  echo htmlspecialchars($fila['nombre_testigo'] . ' ' . $fila['apellidos_testigo']);
              } else {
                  echo '—';
              }
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
          <td colspan="6" style="text-align:center; color:#a0aec0; padding: 30px;">
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

      <!-- Cliente -->
      <div class="campo">
        <label>Cliente *</label>
        <select name="idcliente" required>
          <option value="">-- Selecciona un cliente --</option>
          <?php
          // Reiniciamos el puntero de la consulta
          $clientes->data_seek(0);
          while ($c = $clientes->fetch_assoc()):
          ?>
          <option value="<?php echo $c['id']; ?>">
            <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellidos']); ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Testigo -->
      <div class="campo">
        <label>Testigo</label>
        <select name="idtestigo">
          <option value="">-- Sin testigo --</option>
          <?php
          $testigos->data_seek(0);
          while ($t = $testigos->fetch_assoc()):
          ?>
          <option value="<?php echo $t['id']; ?>">
            <?php echo htmlspecialchars($t['nombre'] . ' ' . $t['apellidos']); ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Fechas en la misma fila -->
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

      <!-- Producto -->
      <div class="campo">
        <label>Producto *</label>
        <select name="idproducto" required>
          <option value="">-- Selecciona un producto --</option>
          <?php
          $productos->data_seek(0);
          while ($p = $productos->fetch_assoc()):
          ?>
          <option value="<?php echo $p['id']; ?>">
            <?php echo htmlspecialchars($p['nombre']); ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Cantidad y adelanto en la misma fila -->
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

      <!-- Total -->
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