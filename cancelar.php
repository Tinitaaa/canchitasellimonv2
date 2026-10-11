<?php
require __DIR__ . '/includes/init.php';
requerir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    redirigir('mis_reservas.php');
}

$pdo = conectar();
$usuario = usuario_actual();
$id = (int)($_POST['id'] ?? 0);

// Solo se puede cancelar una reserva PROPIA y ACTIVA
$st = $pdo->prepare(
    "SELECT fecha, TIME_FORMAT(horario, '%H:%i') AS hora
       FROM reservas
      WHERE id = ? AND usuario_id = ? AND estado = 'activa'"
);
$st->execute([$id, $usuario['id']]);
$reserva = $st->fetch();

if (!$reserva) {
    flash_set('error', 'No se encontró esa reserva.');
} elseif (turno_pasado($reserva['fecha'], $reserva['hora'])) {
    flash_set('error', 'No se puede cancelar un turno que ya pasó.');
} else {
    // activa = NULL libera el turno: el índice UNIQUE deja de contarla
    $st = $pdo->prepare(
        "UPDATE reservas SET estado = 'cancelada', activa = NULL
          WHERE id = ? AND usuario_id = ? AND estado = 'activa'"
    );
    $st->execute([$id, $usuario['id']]);
    flash_set('ok', 'Reserva cancelada. El horario quedó disponible nuevamente.');
}

redirigir('mis_reservas.php');
