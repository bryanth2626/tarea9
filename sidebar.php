<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

    <div class="logo">
        <img src="img/ishume.png" alt="Logo Ishume">
    </div>

    <a href="index.php" class="<?php echo ($pagina_actual == 'index.php') ? 'activo' : ''; ?>">
        <span class="icono">🏠</span>
        Dashboard
    </a>

    <a href="proveedores.php" class="<?php echo ($pagina_actual == 'proveedores.php') ? 'activo' : ''; ?>">
        <span class="icono">🏭</span>
        Proveedores
    </a>

    <a href="clientes.php" class="<?php echo ($pagina_actual == 'clientes.php') ? 'activo' : ''; ?>">
        <span class="icono">👥</span>
        Clientes
    </a>

</div>