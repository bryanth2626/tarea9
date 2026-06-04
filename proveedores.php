<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
  <link rel="stylesheet" href="css/sidebar.css">
  <link rel="stylesheet" href="css/proveedores.css">
  

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