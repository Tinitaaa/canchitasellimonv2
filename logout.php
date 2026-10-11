<?php
require __DIR__ . '/includes/init.php';

// Solo se cierra la sesión si viene del botón (POST con token)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $_SESSION = [];                 // se borran los datos del usuario
    session_regenerate_id(true);    // se invalida el identificador de sesión anterior
    flash_set('ok', 'Cerraste sesión correctamente.');
}

redirigir('index.php');
