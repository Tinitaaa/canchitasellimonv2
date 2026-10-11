-- ============================================================
--  Canchitas El Limón - Turnos
--  Base de datos MySQL / MariaDB (XAMPP)
--  Importar desde phpMyAdmin: pestaña "Importar" -> elegir este archivo
-- ============================================================

CREATE DATABASE IF NOT EXISTS canchitas_limon
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE canchitas_limon;

-- ------------------------------------------------------------
--  Tabla de usuarios
--  La contraseña NUNCA se guarda en texto plano: se guarda el
--  hash generado por password_hash() de PHP.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(60)  NOT NULL,
  apellido      VARCHAR(60)  NOT NULL,
  email         VARCHAR(120) NOT NULL,
  usuario       VARCHAR(20)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  creado_en     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),      -- no se permiten correos duplicados
  UNIQUE KEY uq_usuarios_usuario (usuario)   -- ni nombres de usuario duplicados
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla de reservas (hay una sola cancha)
--
--  CONTROL DE TURNOS DUPLICADOS EN LA BASE DE DATOS:
--  La columna "activa" vale 1 mientras la reserva está activa y
--  NULL cuando se cancela. El índice UNIQUE (fecha, horario, activa)
--  impide que existan dos reservas ACTIVAS en la misma fecha y hora.
--  Como MySQL permite varios NULL en un índice UNIQUE, las reservas
--  canceladas no molestan y el turno puede volver a reservarse.
--  Si dos usuarios intentan reservar a la vez, MySQL acepta un
--  INSERT y rechaza el otro con el error 1062 (Duplicate entry).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservas (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id  INT UNSIGNED NOT NULL,
  fecha       DATE         NOT NULL,
  horario     TIME         NOT NULL,
  estado      ENUM('activa','cancelada') NOT NULL DEFAULT 'activa',
  activa      TINYINT(1)   NULL DEFAULT 1,
  creada_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_turno_activo (fecha, horario, activa),
  KEY idx_reservas_usuario (usuario_id),
  CONSTRAINT fk_reservas_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
