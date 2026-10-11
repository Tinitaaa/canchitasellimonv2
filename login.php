<?php
require __DIR__ . '/includes/init.php';

if (usuario_actual() !== null) {
    redirigir('reservar.php');
}

$pdo = conectar();
$error = '';
$identificador = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $error = 'El formulario expiró. Volvé a intentarlo.';
    } else {
        $identificador = trim(post('identificador'));
        $password = post('password');

        if ($identificador === '' || $password === '') {
            $error = 'Completá el correo (o usuario) y la contraseña.';
        } else {
            // Se puede ingresar con el correo o con el nombre de usuario
            $st = $pdo->prepare(
                'SELECT id, nombre, apellido, password_hash
                   FROM usuarios
                  WHERE email = ? OR usuario = ?
                  LIMIT 1'
            );
            $st->execute([strtolower($identificador), $identificador]);
            $fila = $st->fetch();

            if ($fila && password_verify($password, $fila['password_hash'])) {
                session_regenerate_id(true);   // evita el robo de sesión
                $_SESSION['usuario'] = [
                    'id'       => (int)$fila['id'],
                    'nombre'   => $fila['nombre'],
                    'apellido' => $fila['apellido'],
                ];

                // Si venía de una página protegida, se lo devuelve ahí
                $destino = $_SESSION['volver_a'] ?? '';
                unset($_SESSION['volver_a']);
                if (!in_array($destino, ['reservar.php', 'mis_reservas.php'], true)) {
                    $destino = 'reservar.php';
                }
                redirigir($destino);
            }
            // El mensaje es el mismo si falla el usuario o la contraseña (no da pistas)
            $error = 'El correo/usuario o la contraseña son incorrectos.';
        }
    }
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/includes/header.php';
?>

<section class="form-card">
    <h1>Iniciar sesión</h1>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?= csrf_campo() ?>

        <label for="identificador">Correo electrónico o usuario</label>
        <input type="text" id="identificador" name="identificador" value="<?= e($identificador) ?>" autocomplete="username" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>

        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
    </form>

    <p class="center muted">¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
