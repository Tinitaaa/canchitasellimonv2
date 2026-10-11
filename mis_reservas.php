<?php
require __DIR__ . '/includes/init.php';
requerir_login();                       // página protegida

$pdo = conectar();
$usuario = usuario_actual();

$st = $pdo->prepare(
    "SELECT id, fecha, TIME_FORMAT(horario, '%H:%i') AS hora, estado, creada_en
       FROM reservas
      WHERE usuario_id = ?
      ORDER BY fecha DESC, horario DESC"
);
$st->execute([$usuario['id']]);
$reservas = $st->fetchAll();

// Se separan las próximas (activas y futuras) del historial
$proximas = [];
$historial = [];
foreach ($reservas as $r) {
    if ($r['estado'] === 'activa' && !turno_pasado($r['fecha'], $r['hora'])) {
        $proximas[] = $r;
    } else {
        $historial[] = $r;
    }
}
$proximas = array_reverse($proximas);   // la más cercana primero

$titulo = 'Mis reservas';
require __DIR__ . '/includes/header.php';
?>

<h1>Mis reservas</h1>

<h2>Próximos turnos</h2>
<?php if (!$proximas): ?>
    <div class="card"><p class="muted">No tenés turnos próximos. <a href="reservar.php">Reservá uno</a>.</p></div>
<?php else: ?>
    <div class="lista">
        <?php foreach ($proximas as $r): ?>
            <article class="card reserva">
                <div>
                    <strong><?= e(fecha_larga($r['fecha'])) ?></strong><br>
                    <?= e($r['hora']) ?> hs · <span class="badge badge-activa">Activa</span>
                </div>
                <form method="post" action="cancelar.php" data-confirm="¿Estás seguro de que querés cancelar esta reserva?">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2>Historial</h2>
<?php if (!$historial): ?>
    <div class="card"><p class="muted">Todavía no tenés reservas anteriores.</p></div>
<?php else: ?>
    <div class="lista">
        <?php foreach ($historial as $r):
            $cancelada = ($r['estado'] === 'cancelada'); ?>
            <article class="card reserva">
                <div>
                    <strong><?= e(fecha_larga($r['fecha'])) ?></strong><br>
                    <?= e($r['hora']) ?> hs ·
                    <span class="badge <?= $cancelada ? 'badge-cancelada' : 'badge-finalizada' ?>">
                        <?= $cancelada ? 'Cancelada' : 'Realizada' ?>
                    </span>
                </div>
                <small class="muted">Reservada el <?= e(date('d/m/Y H:i', strtotime($r['creada_en']))) ?></small>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
