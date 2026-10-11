<?php
require __DIR__ . '/includes/init.php';
$titulo = 'Inicio';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Reservá tu turno de fútbol</h1>
    <p>Elegí el día y el horario que más te convenga y asegurá tu cancha en pocos pasos.</p>
    <div class="hero-actions">
        <a class="btn btn-accent" href="reservar.php">Reservar turno</a>
        <a class="btn btn-light" href="horarios.php">Ver horarios disponibles</a>
    </div>
</section>

<section class="section">
    <h2>Nuestra cancha</h2>
    <div class="grid grid-3">
        <article class="card">
            <h3>Cancha de fútbol</h3>
            <p>Contamos con una cancha disponible todos los días para que juegues con tus amigos.</p>
        </article>
        <article class="card">
            <h3>Turnos de una hora</h3>
            <p>Los turnos son a las
                <?= e(implode(', ', array_slice(HORARIOS, 0, -1))) ?> y <?= e(HORARIOS[count(HORARIOS) - 1]) ?> hs.</p>
        </article>
        <article class="card">
            <h3>Reserva online</h3>
            <p>Creá tu cuenta, elegí fecha y horario, y listo. Podés ver y cancelar tus reservas cuando quieras.</p>
        </article>
    </div>
</section>

<section class="section">
    <h2>¿Cómo funciona?</h2>
    <ol class="steps">
        <li><strong>Creá tu cuenta</strong> o iniciá sesión.</li>
        <li><strong>Elegí la fecha</strong> y mirá qué horarios están libres.</li>
        <li><strong>Confirmá tu turno.</strong> Si alguien lo reserva antes que vos, te avisamos para que elijas otro.</li>
    </ol>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
