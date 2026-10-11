<?php
// ============================================================
//  Conexión a MySQL con PDO
//  Valores por defecto de XAMPP: usuario "root" y sin contraseña.
// ============================================================

const DB_HOST = 'localhost';
const DB_NAME = 'canchitas_limon';
const DB_USER = 'root';
const DB_PASS = '';

function conectar(): PDO
{
    static $pdo = null;   // la conexión se crea una sola vez por pedido

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,   // consultas preparadas reales
            ]);
        } catch (PDOException $ex) {
            http_response_code(500);
            // No se muestra el error técnico al usuario
            die('No se pudo conectar con la base de datos. Verificá que MySQL esté encendido en XAMPP y que hayas importado database.sql.');
        }
    }
    return $pdo;
}
