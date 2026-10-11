<?php
// Encabezado común. Cada página define $titulo antes de incluirlo.
$paginaActual = basename($_SERVER['SCRIPT_NAME']);
$usuarioSesion = usuario_actual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Inicio') ?> | <?= e(NOMBRE_SITIO) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php"><span class="brand-mark">⚽</span> <?= e(NOMBRE_SITIO) ?></a>

        <button class="menu-toggle" id="menuToggle" type="button" aria-label="Abrir menú" aria-expanded="false">☰</button>

        <nav class="nav" id="mainNav">
            <?= nav_link('index.php', 'Inicio', $paginaActual) ?>
            <?= nav_link('horarios.php', 'Horarios', $paginaActual) ?>
            <?= nav_link('reservar.php', 'Reservar', $paginaActual) ?>
            <?php if ($usuarioSesion): ?>
                <?= nav_link('mis_reservas.php', 'Mis reservas', $paginaActual) ?>
            <?php endif; ?>
            <?= nav_link('contacto.php', 'Contacto', $paginaActual) ?>

            <?php if ($usuarioSesion): ?>
                <span class="saludo">Hola, <?= e($usuarioSesion['nombre']) ?></span>
                <form action="logout.php" method="post" class="form-inline">
                    <?= csrf_campo() ?>
                    <button type="submit" class="btn btn-ghost btn-sm">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <?= nav_link('login.php', 'Ingresar', $paginaActual) ?>
                <a class="btn btn-accent btn-sm" href="registro.php">Crear cuenta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
<?php foreach (flash_get() as $mensaje): ?>
    <div class="alert alert-<?= e($mensaje['tipo']) ?>"><?= e($mensaje['mensaje']) ?></div>
<?php endforeach; ?>
