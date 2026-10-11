<?php
// ============================================================
//  Configuración general del sitio
//  Editá estos datos con la información real del establecimiento.
// ============================================================

date_default_timezone_set('America/Argentina/Mendoza');

define('NOMBRE_SITIO', 'Canchitas El Limón - Turnos');

// Datos de contacto (completar)
define('DIRECCION', 'Completar con la dirección del establecimiento');
define('TELEFONO', 'Completar con el teléfono');
define('EMAIL_CONTACTO', 'completar@ejemplo.com');
define('WHATSAPP', ''); // solo números con código de país. Ej: 5492611234567

// Turnos fijos (una sola cancha)
define('HORARIOS', ['19:00', '20:00', '21:00', '22:00', '23:00']);

// Cuántos días hacia adelante se puede reservar
define('DIAS_ANTICIPACION', 60);

// Mensaje cuando el turno ya está tomado
define('MSG_OCUPADO', 'Este horario ya está reservado. Por favor, elegí otro turno.');
