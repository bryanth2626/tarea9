<?php
require_once 'config/conexion.php';

// ── GUARDAR NUEVO PROVEEDOR ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {
    $nombre    = $_POST['nombre'];
    $telefono  = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    $sql = "INSERT INTO proveedores (nombre, telefono, direccion) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $nombre, $telefono, $direccion);
    $stmt->execute();
    $stmt->close();

    // Recargamos la página para ver el nuevo registro
    header("Location: proveedores.php");
    exit();
}

// ── ELIMINAR PROVEEDOR ───────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $sql = "DELETE FROM proveedores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: proveedores.php");
    exit();
}

// ── OBTENER TODOS LOS PROVEEDORES ───────────────────────────
$resultado = $conexion->query("SELECT * FROM proveedores ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proveedores - Ishume</title>
  <style>
    /* ── PÁGINA ── */
    .cabecera {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 6px;
    }

    .cabecera h1 {
      font-size: 28px;
      color: #1a1f36;
    }

    .subtitulo {
      color: #718096;
      font-size: 14px;
      margin-bottom: 30px;
    }

    /* Botón azul principal */
    .btn-nuevo {
      background-color: #4f46e5;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
    }

    .btn-nuevo:hover {
      background-color: #3730a3;
    }

    /* ── TABLA ── */
    .tabla-container {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    table thead {
      background-color: #f7f8fa;
    }

    table thead th {
      padding: 14px 18px;
      text-align: left;
      color: #718096;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    table tbody tr {
      border-top: 1px solid #f0f2f5;
    }

    table tbody tr:hover {
      background-color: #fafafa;
    }

    table tbody td {
      padding: 14px 18px;
      color: #2d3748;
    }

    /* Botones de acciones */
    .btn-editar, .btn-eliminar {
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }

    .btn-editar {
      background-color: #ebf8ff;
      color: #2b6cb0;
      margin-right: 4px;
    }

    .btn-editar:hover {
      background-color: #bee3f8;
    }

    .btn-eliminar {
      background-color: #fff5f5;
      color: #c53030;
    }

    .btn-eliminar:hover {
      background-color: #fed7d7;
    }

    /* ── MODAL (ventana flotante del formulario) ── */
    .modal-fondo {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.4);
      z-index: 999;
      justify-content: center;
      align-items: center;
    }

    .modal-fondo.visible {
      display: flex;
    }

    .modal {
      background: white;
      border-radius: 12px;
      padding: 32px;
      width: 420px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .modal h2 {
      font-size: 20px;
      color: #1a1f36;
      margin-bottom: 20px;
    }

    /* Campos del formulario */
    .campo {
      margin-bottom: 16px;
    }

    .campo label {
      display: block;
      font-size: 13px;
      color: #4a5568;
      margin-bottom: 6px;
      font-weight: 600;
    }

    .campo input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 14px;
      color: #2d3748;
      outline: none;
    }

    .campo input:focus {
      border-color: #4f46e5;
    }

    /* Botones del formulario */
    .modal-botones {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 24px;
    }

    .btn-cancelar {
      background: #f0f2f5;
      color: #4a5568;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
    }

    .btn-cancelar:hover {
      background: #e2e8f0;
    }

    .btn-guardar {
      background-color: #4f46e5;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
    }

    .btn-guardar:hover {
      background-color: #3730a3;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- ══════════════ CONTENIDO ══════════════ -->
<div class="contenido">

  <div class="cabecera">
    <div>
      <h1>Proveedores</h1>
      <p class="subtitulo">Administra todos tus proveedores</p>
    </div>
    <button class="btn-nuevo" onclick="abrirModal()">+ Nuevo Proveedor</button>
  </div>

  <!-- TABLA DE PROVEEDORES -->
  <div class="tabla-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Dirección</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $numero = 1;
        while ($fila = $resultado->fetch_assoc()):
        ?>
        <tr>
          <td><?php echo str_pad($numero++, 2, '0', STR_PAD_LEFT); ?></td>
          <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
          <td><?php echo $fila['telefono'] ? htmlspecialchars($fila['telefono']) : '—'; ?></td>
          <td><?php echo $fila['direccion'] ? htmlspecialchars($fila['direccion']) : '—'; ?></td>
          <td>
            <a class="btn-editar" href="#">✏️ Editar</a>
            <a class="btn-eliminar"
               href="proveedores.php?eliminar=<?php echo $fila['id']; ?>"
               onclick="return confirm('¿Seguro que quieres eliminar este proveedor?')">
              🗑️ Eliminar
            </a>
          </td>
        </tr>
        <?php endwhile; ?>

        <?php if ($numero == 1): ?>
        <tr>
          <td colspan="5" style="text-align:center; color:#a0aec0; padding: 30px;">
            No hay proveedores registrados aún.
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
    <h2>Nuevo Proveedor</h2>

    <form method="POST" action="proveedores.php">

      <div class="campo">
        <label>Nombre *</label>
        <input type="text" name="nombre" placeholder="Ej: Imprenta López" required>
      </div>

      <div class="campo">
        <label>Teléfono</label>
        <input type="text" name="telefono" placeholder="Ej: 999 000 111">
      </div>

      <div class="campo">
        <label>Dirección</label>
        <input type="text" name="direccion" placeholder="Ej: Av. Principal 123">
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

  // Cerrar si hacen clic fuera del modal
  document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
  });
</script>

</body>
</html>