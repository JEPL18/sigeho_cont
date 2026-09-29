# SIGEHO-CONT (MVC)

Migración a arquitectura **MVC** del sistema SIGEHO-CONT (Sistema Integrado de Gestión
de Horarios — Coordinación de Contaduría Pública, UPTAG). Se conserva toda la lógica,
textos, roles, consultas SQL y reglas de negocio del código original en PHP plano.

## Estructura de directorios

```
sigeho_cont_mvc/
├── public/                     <- Document root de Apache
│   ├── index.php               <- Front controller (rutas, sesión endurecida)
│   ├── .htaccess               <- Reescritura a URLs amigables
│   └── assets/
│       ├── css/estilos.css     <- Copia sin cambios del original
│       ├── js/main.js          <- Copia sin cambios del original
│       └── img/                <- Logos (copias sin cambios)
├── app/
│   ├── config/database.php     <- Conexión PDO + cifrado AES-256 (MASTER_KEY intacta)
│   ├── core/
│   │   ├── Controller.php      <- render(), url(), redirect(), requireLogin(), requireAdmin()
│   │   └── Router.php          <- Despacho controlador/acción
│   ├── controllers/            <- Auth, Dashboard, Profesor, Materia, Seccion,
│   │                              Aula, Horario, Usuario, Configuracion
│   ├── models/                 <- Acceso a datos (PDO) por tabla
│   ├── views/
│   │   ├── layouts/            <- header.php / footer.php (sidebar original)
│   │   ├── auth/               <- login, verificar_2fa, recuperar_clave, configurar_2fa
│   │   ├── dashboard/          <- KPIs + cuadrícula SIACE + modal de log de choques
│   │   ├── profesores/         <- index + form (nuevo/editar comparten form.php)
│   │   ├── materias/           <- index + form
│   │   ├── secciones/          <- index + form
│   │   ├── aulas/              <- index + form
│   │   ├── horarios/           <- index + form
│   │   ├── usuarios/           <- index + form + eliminar (confirmación con 2FA)
│   │   └── configuracion/      <- lapso académico, 2FA, respaldo cifrado
│   └── libs/GoogleAuthenticator.php  <- Copia sin cambios del original
└── sql/sigeho_cont_db.sql      <- Copia sin cambios del esquema original (9 tablas)
```

## Instalación

1. Apuntar el **document root** de Apache a `public/`
   (o copiar el contenido del proyecto y usar `public/` como raíz web).
2. Habilitar `mod_rewrite` y permitir `AllowOverride All` para que
   `public/.htaccess` traduzca las URLs amigables a `index.php?r=...`.
3. Crear la base de datos e importar `sql/sigeho_cont_db.sql`:

   ```sql
   CREATE DATABASE sigeho_cont_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   -- luego importar sql/sigeho_cont_db.sql
   ```
4. Revisar las credenciales en `app/config/database.php`
   (por defecto: host `localhost`, base `sigeho_cont_db`, usuario `root`, sin contraseña,
   igual que el `config/db.php` original). La `MASTER_KEY` del cifrado AES-256
   se mantuvo **exactamente** como en el original.
5. Requisitos PHP: PDO MySQL, `ZipArchive` y `OpenSSL`
   (usados por el respaldo cifrado AES-256 y el 2FA).

## Rutas

Formato principal: **URLs amigables** (`/horarios/nuevo`, `/usuarios/editar?id=5`).
También se acepta manualmente `index.php?r=controlador/accion`.

| Ruta | Descripción |
|---|---|
| `/auth/login` | Inicio de sesión (bloqueo tras 3 intentos) |
| `/auth/verificar2fa` | Segundo paso del 2FA |
| `/auth/recuperar` | Recuperación de clave en 3 pasos |
| `/auth/configurar2fa` | Activar/desactivar 2FA propio |
| `/auth/logout` | Cierre de sesión (registra bitácora) |
| `/dashboard` | KPIs, cuadrícula SIACE, log de choques |
| `/profesores`, `/profesores/nuevo`, `/profesores/editar?id=`, `/profesores/eliminar?id=` | Nómina docente |
| `/materias`, ... | Malla curricular |
| `/secciones`, ... | Gestión de secciones |
| `/aulas`, ... | Aulas y espacios |
| `/horarios`, ... | Gestión de bloques (anti-choques) |
| `/usuarios`, ... `/usuarios/desbloquear?id=` | Cuentas de acceso |
| `/configuracion` | Lapso académico, 2FA, respaldo |
| `/configuracion/backup` | Genera el ZIP AES-256 (solo POST) |

## Decisiones de migración (ambigüedades documentadas)

1. **Formularios compartidos**: cada módulo usa un único `form.php` para
   `nuevo` y `editar`, con variable `$modo` (`'nuevo'`/`'editar'`). Se conservan
   títulos, iconos, placeholders, `aria-label` y botones de cada versión original.
2. **Backup**: `backup.php` solo respondía a POST (un GET redirigía a
   `configuracion.php`). En MVC vive en `ConfiguracionController::backup()`
   (ruta `/configuracion/backup`, solo POST) y el modal apunta allí. Las imágenes
   del ZIP se toman de `public/assets/img` (nueva ubicación de `assets/img`).
   El menú lateral original enlazaba a `backup.php` por GET (lo que redirigía);
   ahora ese enlace apunta a la página de configuración, que contiene el modal.
3. **Recuperación de clave**: el original compara las respuestas de seguridad
   en texto plano (aunque al crear/editar se guardan con `password_hash`).
   Se preservó el comportamiento original sin "corregirlo".
4. **`$nueva_password` en `usuarios/editar.php`**: la línea de lectura llega como
   `$nueva_password=<redacted>`; el formulario usa `name="password"` y el resto del
   archivo trata esa variable como el valor de `$_POST['password']`. Se migró como
   `$nueva_password = $_POST['password'];` (lectura mínima coherente).
5. **Días sin tilde**: la base de datos guarda `Miercoles`/`Sabado` (los valores
   de los `<option>` originales). La cuadrícula SIACE y el ordenamiento usan esas
   mismas claves sin tilde, como el original.
6. **Turnos del dashboard**: se conservan `MATUTINO` / `VESPERTINO` / `NOCTURNO`
   con sus filtros de hora exactos (`< 12:00`, `12:00–18:00`, `>= 18:00`).
7. **Bitácora**: `Usuario::limpiarDependencias()` borra bitácora y log_choques del
   usuario antes de eliminarlo, como el original.
8. **Sin dependencias nuevas**: autoload propio para `core`, `models` y
   `controllers`; no se usa Composer ni frameworks.
