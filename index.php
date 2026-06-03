<?php
// Detectamos en qué página estamos para marcarla como activa
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Ishume</title>
  <style>

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      display: flex;
      min-height: 100vh;
      background-color: #f0f2f5;
    }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 210px;
      background-color: #1a1f36;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding: 20px 0;
    }

    /* Logo arriba */
    .sidebar .logo {
      text-align: center;
      padding: 10px 20px 30px 20px;
    }

    .sidebar .logo img {
      width: 80px;
    }

    /* Cada item del menú */
    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      color: #a0aec0;
      text-decoration: none;
      font-size: 14px;
      border-radius: 8px;
      margin: 2px 10px;
      transition: background 0.2s, color 0.2s;
    }

    .sidebar a:hover {
      background-color: #2d3561;
      color: #ffffff;
    }

    /* Item activo (la página donde estamos) */
    .sidebar a.activo {
      background-color: #4f46e5;
      color: #ffffff;
    }

    .sidebar a .icono {
      font-size: 18px;
    }

    /* ── CONTENIDO PRINCIPAL ── */
    .contenido {
      flex: 1;
      padding: 40px;
    }

    .contenido h1 {
      font-size: 28px;
      color: #1a1f36;
      margin-bottom: 6px;
    }

    .contenido p {
      color: #718096;
      font-size: 14px;
    }

  </style>
</head>
<body>

  <!-- ══════════════ SIDEBAR ══════════════ -->
  <div class="sidebar">

    <!-- Logo -->
    <div class="logo">
        <img src="img/ishume.png" alt="Logo Ishume">
    </div>

    <!-- Menú -->
    <a href="proveedores.php" class="<?php echo ($pagina_actual == 'proveedores.php') ? 'activo' : ''; ?>">
      <span class="icono">🏭</span>
      Proveedores
    </a>

    <a href="clientes.php" class="<?php echo ($pagina_actual == 'clientes.php') ? 'activo' : ''; ?>">
      <span class="icono">👥</span>
      Clientes
    </a>

  </div>
  <!-- ══════════════ FIN SIDEBAR ══════════════ -->


  <!-- ══════════════ CONTENIDO ══════════════ -->
  <div class="contenido">
    <h1>Dashboard</h1>
    <p>Bienvenido al sistema Ishume</p>

    <!-- Aquí adentro va el contenido de cada página -->
  </div>

</body>
</html>