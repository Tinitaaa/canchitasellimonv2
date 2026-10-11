# Canchitas El Limón - Turnos (PHP + MySQL)

Sistema web de reservas de turnos para una cancha de fútbol.
Tecnologías: **HTML, CSS, JavaScript, PHP (PDO) y MySQL**. Se ejecuta en local con **XAMPP**.

## 1. Instalación (XAMPP en Windows)

1. Instalá XAMPP y abrí el **XAMPP Control Panel**. Iniciá (Start) **Apache** y **MySQL**.
2. Copiá la carpeta `canchitas-php` dentro de `C:\xampp\htdocs\`
   (queda `C:\xampp\htdocs\canchitas-php\index.php`).
3. Creá la base de datos:
   - Abrí http://localhost/phpmyadmin
   - Pestaña **Importar** → **Elegir archivo** → seleccioná `database.sql` → **Importar**.
   - Se crea la base `canchitas_limon` con las tablas `usuarios` y `reservas`.
4. Abrí el sitio: **http://localhost/canchitas-php/**

Para editarlo: abrí la carpeta `C:\xampp\htdocs\canchitas-php` con Visual Studio Code
(File → Open Folder). Cada vez que guardes, recargá el navegador.

### Si algo no funciona
- "No se pudo conectar con la base de datos": MySQL no está encendido, o falta importar `database.sql`.
- Si tu MySQL tiene contraseña para `root`, cambiala en `config/db.php`.
- Página en blanco o error de PHP: mirá el log en XAMPP → Apache → Logs → `error.log`.

## 2. Datos que tenés que completar
En `config/config.php`: dirección, teléfono, correo y WhatsApp del establecimiento
(aparecen en la página **Contacto**). Ahí también están los horarios fijos.

## 3. Estructura del proyecto

```
canchitas-php/
├─ database.sql        Base de datos (tablas usuarios y reservas)
├─ index.php           Inicio con información de la cancha
├─ horarios.php        Consulta pública de horarios disponibles por fecha
├─ registro.php        Crear cuenta
├─ login.php           Iniciar sesión (con correo o usuario)
├─ logout.php          Cerrar sesión
├─ reservar.php        Reservar un turno (requiere sesión)
├─ mis_reservas.php    Ver y cancelar mis reservas (requiere sesión)
├─ cancelar.php        Procesa la cancelación
├─ contacto.php        Información del establecimiento
├─ config/
│  ├─ config.php       Nombre, contacto, horarios y mensajes
│  └─ db.php           Conexión a MySQL con PDO
├─ includes/
│  ├─ init.php         Sesión + funciones comunes (seguridad, turnos, reservas)
│  ├─ header.php       Encabezado y menú
│  └─ footer.php       Pie de página
├─ css/style.css       Estilos responsive
└─ js/app.js           Menú, confirmaciones y validaciones del lado del cliente
```

## 4. Cómo se evita reservar el mismo turno dos veces (para explicar en la defensa)

Hay **dos barreras**:

1. **PHP** (`crear_reserva()` en `includes/init.php`): antes de guardar consulta si ese
   día y horario ya tienen una reserva activa. Si la hay, muestra
   *"Este horario ya está reservado. Por favor, elegí otro turno."*
2. **MySQL** (índice `UNIQUE (fecha, horario, activa)` en la tabla `reservas`):
   aunque dos personas pasen la verificación de PHP al mismo tiempo, la base de datos
   acepta un solo `INSERT` y rechaza el otro con el error 1062 (*Duplicate entry*),
   que PHP captura y muestra con el mismo mensaje.

La columna `activa` vale `1` mientras la reserva está activa y `NULL` al cancelarla.
MySQL permite varios `NULL` en un índice único, así que las reservas canceladas no
bloquean el turno y éste vuelve a quedar libre.

El control es **por fecha y horario**: reservar el lunes 20:00 no afecta al martes 20:00.

### Probar la barrera de la base de datos a mano
En phpMyAdmin → pestaña SQL (reemplazá el `1` por el id de un usuario real):

```sql
INSERT INTO reservas (usuario_id, fecha, horario, estado, activa) VALUES (1, '2030-01-01', '20:00:00', 'activa', 1);
INSERT INTO reservas (usuario_id, fecha, horario, estado, activa) VALUES (1, '2030-01-01', '20:00:00', 'activa', 1);
-- El segundo falla: #1062 - Duplicate entry
```

## 5. Seguridad aplicada
- Contraseñas con `password_hash()` y `password_verify()` (nunca en texto plano).
- Consultas preparadas con PDO (protege contra inyección SQL).
- Todo texto que se muestra pasa por `htmlspecialchars()` (función `e()`), contra XSS.
- Token CSRF en todos los formularios POST.
- `session_regenerate_id()` al iniciar y cerrar sesión.
- Las páginas de reserva exigen sesión (`requerir_login()`), y solo se puede cancelar una reserva propia.
- Validaciones en JavaScript **y** repetidas en PHP.

## 6. Pruebas sugeridas
1. Registrar un usuario con correo repetido → debe mostrar error.
2. Registrar con contraseñas distintas → error.
3. Entrar a `reservar.php` sin sesión → redirige al login.
4. Iniciar sesión y reservar un turno → aparece en "Mis reservas".
5. Abrir el sitio en otro navegador con otro usuario → ese turno aparece "Ocupado" y no se puede elegir.
6. Cancelar la reserva → el turno vuelve a estar "Disponible".
7. Reservar el mismo horario en otra fecha → debe permitirlo.
