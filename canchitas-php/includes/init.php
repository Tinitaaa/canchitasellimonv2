<?php
// ============================================================
//  init.php - se incluye al principio de TODAS las páginas.
//  Inicia la sesión y define las funciones comunes del sistema.
// ============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,          // la sesión dura mientras no se cierre el navegador
        'path'     => '/',
        'httponly' => true,       // JavaScript no puede leer la cookie de sesión
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ------------------------------------------------------------
   Utilidades generales
   ------------------------------------------------------------ */

// Escapa texto antes de mostrarlo en HTML (evita ataques XSS)
function e($texto): string
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

// Lee un dato de POST (o GET) siempre como texto; si llega otra cosa (ej. un arreglo), usa el valor por defecto
function post(string $clave): string
{
    $valor = $_POST[$clave] ?? '';
    return is_string($valor) ? $valor : '';
}

function param(string $clave, string $porDefecto = ''): string
{
    $valor = $_POST[$clave] ?? $_GET[$clave] ?? $porDefecto;
    return is_string($valor) ? $valor : $porDefecto;
}

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// Mensajes que se muestran una sola vez (ej: "Reserva confirmada")
function flash_set(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function flash_get(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

/* ------------------------------------------------------------
   Protección CSRF: cada formulario POST lleva un token secreto
   ------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valido(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

/* ------------------------------------------------------------
   Sesión de usuario
   ------------------------------------------------------------ */

function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

// Se usa en las páginas protegidas: si no hay sesión, manda al login
function requerir_login(): void
{
    if (usuario_actual() === null) {
        flash_set('error', 'Tenés que iniciar sesión para continuar.');
        $_SESSION['volver_a'] = basename($_SERVER['SCRIPT_NAME']);
        redirigir('login.php');
    }
}

function nav_link(string $archivo, string $texto, string $paginaActual): string
{
    $clase = ($archivo === $paginaActual) ? ' class="activo"' : '';
    return '<a href="' . e($archivo) . '"' . $clase . '>' . e($texto) . '</a>';
}

/* ------------------------------------------------------------
   Fechas y turnos
   ------------------------------------------------------------ */

function fecha_valida(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d !== false && $d->format('Y-m-d') === $fecha;
}

function fecha_larga(string $fecha): string
{
    $dias  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $t = strtotime($fecha);
    return $dias[(int)date('w', $t)] . ' ' . date('j', $t) . ' de '
         . $meses[(int)date('n', $t) - 1] . ' de ' . date('Y', $t);
}

// ¿El turno ya empezó o pasó?
function turno_pasado(string $fecha, string $hora): bool
{
    return strtotime($fecha . ' ' . $hora . ':00') <= time();
}

// Devuelve un mensaje de error si la fecha/hora no son válidas, o null si están bien
function validar_turno(string $fecha, string $hora): ?string
{
    if (!fecha_valida($fecha)) {
        return 'La fecha elegida no es válida.';
    }
    if (!in_array($hora, HORARIOS, true)) {
        return 'El horario elegido no es válido.';
    }
    if ($fecha < date('Y-m-d')) {
        return 'No se pueden reservar fechas pasadas.';
    }
    if ($fecha > date('Y-m-d', strtotime('+' . DIAS_ANTICIPACION . ' days'))) {
        return 'Solo se puede reservar con hasta ' . DIAS_ANTICIPACION . ' días de anticipación.';
    }
    if (turno_pasado($fecha, $hora)) {
        return 'Ese horario ya pasó. Elegí otro turno.';
    }
    return null;
}

// Lista de horarios ocupados (reservas activas) de una fecha. Ej: ['20:00', '22:00']
function horarios_ocupados(PDO $pdo, string $fecha): array
{
    $st = $pdo->prepare(
        "SELECT TIME_FORMAT(horario, '%H:%i') AS hora
           FROM reservas
          WHERE fecha = ? AND estado = 'activa'"
    );
    $st->execute([$fecha]);
    return array_column($st->fetchAll(), 'hora');
}

// 'ocupado' | 'pasado' | 'disponible'
function estado_turno(string $fecha, string $hora, array $ocupados): string
{
    if (in_array($hora, $ocupados, true)) {
        return 'ocupado';
    }
    if (turno_pasado($fecha, $hora)) {
        return 'pasado';
    }
    return 'disponible';
}

/*
   Crea una reserva. Devuelve null si salió bien o un mensaje de error.

   CONTROL DE DUPLICADOS (doble barrera):
   1) PHP: antes de guardar se consulta si el turno ya está ocupado.
   2) MySQL: el índice UNIQUE (fecha, horario, activa) rechaza el INSERT si otro
      usuario reservó el mismo turno justo en el mismo instante (error 1062).
*/
function crear_reserva(PDO $pdo, int $usuarioId, string $fecha, string $hora): ?string
{
    $error = validar_turno($fecha, $hora);
    if ($error !== null) {
        return $error;
    }

    if (in_array($hora, horarios_ocupados($pdo, $fecha), true)) {
        return MSG_OCUPADO;
    }

    try {
        $st = $pdo->prepare(
            "INSERT INTO reservas (usuario_id, fecha, horario, estado, activa)
             VALUES (?, ?, ?, 'activa', 1)"
        );
        $st->execute([$usuarioId, $fecha, $hora . ':00']);
    } catch (PDOException $ex) {
        if ((int)($ex->errorInfo[1] ?? 0) === 1062) {
            return MSG_OCUPADO;   // otro usuario ganó el turno por una fracción de segundo
        }
        throw $ex;
    }
    return null;
}
