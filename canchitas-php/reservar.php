<?php
require __DIR__ . '/includes/init.php';
requerir_login();                       // página protegida

$pdo = conectar();
$usuario = usuario_actual();

$hoy = date('Y-m-d');
$max = date('Y-m-d', strtotime('+' . DIAS_ANTICIPACION . ' days'));

$fecha = param('fecha', $hoy);
$horaSeleccionada = param('horario', param('hora'));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $error = 'El formulario expiró. Volvé a intentarlo.';
    } else {
        // Toda la validación se repite en el servidor (no se confía en el navegador)
        $resultado = crear_reserva($pdo, $usuario['id'], $fecha, $horaSeleccionada);
        if ($resultado === null) {
            flash_set('ok', '¡Reserva confirmada para el ' . fecha_larga($fecha) . ' a las ' . $horaSeleccionada . ' hs!');
            redirigir('mis_reservas.php');
        }
        $error = $resultado;
    }
}

if (!fecha_valida($fecha) || $fecha < $hoy || $fecha > $max) {
    $fecha = $hoy;
}
$ocupados = horarios_ocupados($pdo, $fecha);
if (estado_turno($fecha, $horaSeleccionada, $ocupados) !== 'disponible') {
    $horaSeleccionada = '';             // no se deja marcado un turno que no se puede reservar
}

$titulo = 'Reservar turno';
require __DIR__ . '/includes/header.php';
?>

<h1>Reservar turno</h1>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<!-- Paso 1: elegir la fecha (al cambiarla se actualizan los horarios) -->
<form method="get" action="reservar.php" class="card fecha-form">
    <label for="fecha">1. Elegí la fecha</label>
    <input type="date" id="fecha" name="fecha" value="<?= e($fecha) ?>" min="<?= e($hoy) ?>" max="<?= e($max) ?>">
    <button type="submit" class="btn btn-primary">Ver horarios</button>
</form>

<!-- Paso 2: elegir el horario y confirmar -->
<form method="post" action="reservar.php" id="formReserva" class="card" data-confirm="¿Confirmás esta reserva?">
    <?= csrf_campo() ?>
    <input type="hidden" name="fecha" value="<?= e($fecha) ?>">

    <h2>2. Elegí el horario para el <?= e(fecha_larga($fecha)) ?></h2>

    <div class="slots">
        <?php foreach (HORARIOS as $hora):
            $estado = estado_turno($fecha, $hora, $ocupados);
            $libre = ($estado === 'disponible'); ?>
            <label class="slot slot-<?= e($estado) ?>">
                <input type="radio" name="horario" value="<?= e($hora) ?>"
                       <?= $libre ? '' : 'disabled' ?>
                       <?= ($libre && $hora === $horaSeleccionada) ? 'checked' : '' ?>>
                <span class="slot-hora"><?= e($hora) ?> hs</span>
                <span class="slot-estado">
                    <?= $estado === 'disponible' ? 'Disponible' : ($estado === 'ocupado' ? 'Ocupado' : 'Ya pasó') ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>

    <p class="muted">Reserva a nombre de <strong><?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?></strong>.</p>

    <button type="submit" class="btn btn-accent btn-block" id="btnConfirmar">Confirmar reserva</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
