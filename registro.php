<?php
require __DIR__ . '/includes/init.php';

// Si ya inició sesión no tiene sentido registrarse de nuevo
if (usuario_actual() !== null) {
    redirigir('reservar.php');
}

$pdo = conectar();
$errores = [];
$datos = ['nombre' => '', 'apellido' => '', 'email' => '', 'usuario' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores[] = 'El formulario expiró. Volvé a intentarlo.';
    } else {
        $datos['nombre']   = trim(post('nombre'));
        $datos['apellido'] = trim(post('apellido'));
        $datos['email']    = strtolower(trim(post('email')));
        $datos['usuario']  = trim(post('usuario'));
        $password          = post('password');
        $confirmacion      = post('confirmacion');

        // --- Validaciones ---
        if ($datos['nombre'] === '' || $datos['apellido'] === '' || $datos['email'] === ''
            || $datos['usuario'] === '' || $password === '' || $confirmacion === '') {
            $errores[] = 'Completá todos los campos.';
        }
        if (mb_strlen($datos['nombre']) > 60 || mb_strlen($datos['apellido']) > 60) {
            $errores[] = 'El nombre y el apellido pueden tener hasta 60 caracteres.';
        }
        if ($datos['email'] !== '' && (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['email']) > 120)) {
            $errores[] = 'El correo electrónico no es válido.';
        }
        if ($datos['usuario'] !== '' && !preg_match('/^[A-Za-z0-9_]{3,20}$/', $datos['usuario'])) {
            $errores[] = 'El nombre de usuario debe tener entre 3 y 20 caracteres (letras, números o guion bajo).';
        }
        if ($password !== '' && strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $confirmacion) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        // --- Correo y usuario no repetidos ---
        if (!$errores) {
            $st = $pdo->prepare('SELECT email, usuario FROM usuarios WHERE email = ? OR usuario = ?');
            $st->execute([$datos['email'], $datos['usuario']]);
            foreach ($st->fetchAll() as $fila) {
                if (strtolower($fila['email']) === $datos['email']) {
                    $errores[] = 'Ya existe una cuenta con ese correo electrónico.';
                }
                if (strtolower($fila['usuario']) === strtolower($datos['usuario'])) {
                    $errores[] = 'Ese nombre de usuario ya está en uso.';
                }
            }
            $errores = array_unique($errores);
        }

        // --- Guardar ---
        if (!$errores) {
            try {
                $st = $pdo->prepare(
                    'INSERT INTO usuarios (nombre, apellido, email, usuario, password_hash)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $st->execute([
                    $datos['nombre'], $datos['apellido'], $datos['email'], $datos['usuario'],
                    password_hash($password, PASSWORD_DEFAULT),   // contraseña cifrada
                ]);
                flash_set('ok', '¡Cuenta creada! Ya podés iniciar sesión.');
                redirigir('login.php');
            } catch (PDOException $ex) {
                if ((int)($ex->errorInfo[1] ?? 0) === 1062) {
                    $errores[] = 'Ya existe una cuenta con ese correo o nombre de usuario.';
                } else {
                    throw $ex;
                }
            }
        }
    }
}

$titulo = 'Crear cuenta';
require __DIR__ . '/includes/header.php';
?>

<section class="form-card">
    <h1>Crear cuenta</h1>
    <p class="muted">Registrate para poder reservar turnos.</p>

    <?php if ($errores): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="alert alert-error" id="errorJS" hidden></div>

    <form method="post" action="registro.php" id="formRegistro" novalidate>
        <?= csrf_campo() ?>

        <div class="field-row">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" maxlength="60" required>
            </div>
            <div>
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?= e($datos['apellido']) ?>" maxlength="60" required>
            </div>
        </div>

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="<?= e($datos['email']) ?>" maxlength="120" required>

        <label for="usuario">Nombre de usuario</label>
        <input type="text" id="usuario" name="usuario" value="<?= e($datos['usuario']) ?>" maxlength="20" required>
        <small class="muted">3 a 20 caracteres: letras, números o guion bajo.</small>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
        <small class="muted">Mínimo 8 caracteres.</small>

        <label for="confirmacion">Repetir contraseña</label>
        <input type="password" id="confirmacion" name="confirmacion" autocomplete="new-password" required>

        <button type="submit" class="btn btn-primary btn-block">Crear cuenta</button>
    </form>

    <p class="center muted">¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
