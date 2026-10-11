<?php
require __DIR__ . '/includes/init.php';
$titulo = 'Contacto';
require __DIR__ . '/includes/header.php';
?>

<h1>Contacto e información</h1>

<div class="grid grid-2">
    <section class="card">
        <h2>Datos del establecimiento</h2>
        <p><strong>Dirección:</strong> <?= e(DIRECCION) ?></p>
        <p><strong>Teléfono:</strong> <?= e(TELEFONO) ?></p>
        <p><strong>Correo:</strong> <?= e(EMAIL_CONTACTO) ?></p>
        <?php if (WHATSAPP !== ''): ?>
            <p><a class="btn btn-primary" target="_blank" rel="noopener"
                  href="https://wa.me/<?= e(preg_replace('/\D/', '', WHATSAPP)) ?>">Escribinos por WhatsApp</a></p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Horarios de los turnos</h2>
        <ul class="lista-simple">
            <?php foreach (HORARIOS as $hora): ?>
                <li><?= e($hora) ?> hs</li>
            <?php endforeach; ?>
        </ul>
        <p class="muted">Cada turno dura una hora. Podés cancelar tu reserva desde "Mis reservas".</p>
    </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
