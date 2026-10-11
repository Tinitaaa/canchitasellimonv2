<?php
require __DIR__ . '/includes/init.php';
$pdo = conectar();

$hoy = date('Y-m-d');
$max = date('Y-m-d', strtotime('+' . DIAS_ANTICIPACION . ' days'));

$fecha = param('fecha', $hoy);
if (!fecha_valida($fecha) || $fecha < $hoy || $fecha > $max) {
    $fecha = $hoy;
}
$ocupados = horarios_ocupados($pdo, $fecha);

$titulo = 'Horarios disponibles';
require __DIR__ . '/includes/header.php';
?>

<h1>Horarios disponibles</h1>

<form method="get" action="horarios.php" class="card fecha-form">
    <label for="fecha">Elegí una fecha</label>
    <input type="date" id="fecha" name="fecha" value="<?= e($fecha) ?>" min="<?= e($hoy) ?>" max="<?= e($max) ?>">
    <button type="submit" class="btn btn-primary">Ver horarios</button>
</form>

<h2 class="fecha-titulo"><?= e(fecha_larga($fecha)) ?></h2>

<div class="slots">
    <?php foreach (HORARIOS as $hora):
        $estado = estado_turno($fecha, $hora, $ocupados); ?>
        <div class="slot slot-<?= e($estado) ?>">
            <span class="slot-hora"><?= e($hora) ?> hs</span>
            <?php if ($estado === 'disponible'): ?>
                <span class="slot-estado">Disponible</span>
                <a class="btn btn-primary btn-sm" href="reservar.php?fecha=<?= e($fecha) ?>&amp;hora=<?= e($hora) ?>">Reservar</a>
            <?php elseif ($estado === 'ocupado'): ?>
                <span class="slot-estado">Ocupado</span>
            <?php else: ?>
                <span class="slot-estado">Ya pasó</span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if (usuario_actual() === null): ?>
    <p class="muted">Para reservar necesitás <a href="login.php">iniciar sesión</a> o <a href="registro.php">crear una cuenta</a>.</p>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
