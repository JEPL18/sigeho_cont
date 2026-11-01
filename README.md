# SIGEHO-CONT

Sistema de gestión de horarios para el departamento de Contaduría de la
UPTAG. Facilita la construcción de horarios académicos previniendo
choques de profesor, aula y hora.

**Stack:** PHP 8 + PDO + MySQL/MariaDB + Bootstrap 5.3, arquitectura MVC
con controlador frontal (`public/index.php`).

**Autores:** Juan Montañez, Andrés Moreno y Jesús Peña (UPTAG).
**Licencia:** [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/deed.es).

## Características

- **6 módulos con CRUD completo:** profesores, materias, secciones,
  aulas, horarios y usuarios.
- **Detección de choques:** al crear o editar un horario, el sistema
  detecta choques de profesor y aula en el mismo día y hora, y los
  registra en un log consultable desde el panel principal.
- **Cuadrícula semanal estilo SIACE:** vista por sección y por
  profesor, con bloques atómicos y turnos (matutino, vespertino,
  nocturno) derivados de la hora de inicio.
- **Arrastrar y soltar (borrador):** mueve un bloque a otro día y hora
  desde la cuadrícula del panel principal (solo con mouse, solo
  administradores). Revalida choques, capacidad y disponibilidad en
  el servidor; ante un choque no mueve nada y muestra el motivo.
- **Sugerencias supervisadas:** el administrador sugiere y decide, el
  sistema ejecuta tras revalidar. Secciones necesarias según
  matrícula proyectada y capacidad, y huecos libres para un profesor.
  Disponibles como botones en la interfaz y como scripts de consola.
- **Exportación:** impresión y PDF de las cuadrículas (solo la
  cuadrícula, sin botones ni pie de página) y Excel por profesor y
  por departamento.
- **Recursos de aula** como texto libre (máximo 15) y **prelaciones**
  como registro de orden del pensum (no bloquean la creación de
  horarios: el sistema no tiene estudiantes ni notas).
- **Disponibilidad opt-in:** si un profesor no tiene filas de
  disponibilidad o materias asociadas, no hay restricción; con datos,
  la creación de horario que los viole se bloquea con mensaje.
- **Verificación en dos pasos (TOTP) opcional** por usuario, con
  secretos cifrados en AES-256 bajo una clave maestra externa.
- **Recuperación de contraseña** por preguntas de seguridad y por
  correo (token aleatorio, solo se guarda su hash SHA-256, 60 minutos
  de vigencia, un solo uso, respuesta genérica sin enumerar usuarios).
- **Protección contra bots:** widget Cloudflare Turnstile en el login
  y en ambas recuperaciones (falla cerrado: sin verificación válida
  no hay acceso) más límite progresivo por IP en el login.
- **Bitácora** de crear, editar y eliminar en los 6 módulos, más
  sugerencias y arrastres. Solo para administradores.
- **Seguridad:** consultas preparadas PDO, `htmlspecialchars` en
  salidas, tokens CSRF en todos los POST, contraseñas con bcrypt,
  sesiones con cookie `HttpOnly` y `SameSite=Strict`, expiración por
  15 minutos de inactividad y cierre de sesión que destruye la sesión
  y su cookie.
- **Roles:** Administrador (todo) y Asistente (solo lectura; las
  acciones de administrador devuelven 403).
- **Ayuda integrada** y diseño responsive.

## Requisitos

- PHP 8.1 o superior con extensiones PDO-MySQL, OpenSSL y cURL.
- MySQL o MariaDB.
- Apache con `mod_rewrite` (o XAMPP). Las URL bonitas se resuelven
  con `public/.htaccess`.

## Instalación

1. Clona el repositorio en tu servidor o en `htdocs`.
2. Crea la base de datos e importa
   `sql/sigeho_cont_db.sql` (instalación fresca, 14 tablas). El repo
   distribuye solo el esquema completo; no reimportes el esquema
   sobre una base con datos reales.
3. Ajusta `app/config/database.php` con tu host, base, usuario y
   clave.
4. **Crea `app/config/master_key.php` (obligatorio).** Son dos
   líneas: `<?php` y `return '...';` con una clave aleatoria larga
   (por ejemplo, 64 caracteres hexadecimales). Sin este archivo (o
   sin la variable de entorno `SIGEHO_MASTER_KEY`) el sistema se
   detiene con un mensaje de configuración: no hay clave de repuesto
   escrita en el código. Guarda la clave fuera del proyecto: si se
   pierde, los usuarios con 2FA no podrán entrar hasta que un
   administrador les desactive el 2FA. **Nunca subas este archivo a
   Git ni lo compartas.**
5. Si vas a usar el login con protección antibots, crea un widget
   de Cloudflare Turnstile y pon su clave del sitio en las vistas
   de login y recuperación y su secreto en
   `app/config/turnstile.php` (debe devolver
   `['clave_secreta' => '...']`). Sin Turnstile configurado, el
   login y las recuperaciones niegan el acceso por diseño.
6. Si vas a usar la recuperación por correo, crea
   `app/config/correo.php` con tu SMTP externo (host, puerto,
   remitente; usuario y clave solo si tu SMTP los exige). Sin este
   archivo, la recuperación por correo no envía; la recuperación por
   preguntas de seguridad sigue funcionando.
7. Si `app/` queda bajo alcance web, protege la carpeta con el
   `app/.htaccess` incluido (`Require all denied`). Las rutas
   `app/config/*` deben devolver 403 desde el navegador.
8. Entra con el usuario semilla y **cambia su contraseña
   inmediatamente**, además de sus respuestas de seguridad.

### Primer ingreso (instalación fresca)

- Usuario: `admin@uptag.edu.ve`
- Contraseña: `admin12345678901`

⚠️ **Esta es una credencial semilla pública.** Cambia la contraseña
en cuanto entres, borra o desactiva las cuentas de prueba y pon tus
propias respuestas de seguridad. Una instalación que conserva la
cuenta semilla está abierta a cualquiera que lea este README.

## Configuración

| Archivo | En el repo | Qué contiene |
|---|---|---|
| `app/config/database.php` | Sí (ejemplo localhost) | Host, base, usuario y clave de la BD |
| `app/config/master_key.php` | No (ignorado por Git) | La clave maestra del 2FA |
| `app/config/turnstile.php` | No (ignorado por Git) | El secreto del widget Turnstile |
| `app/config/correo.php` | No (ignorado por Git) | SMTP del correo de recuperación |

Los tres archivos ignorados deben crearse en cada servidor; el
`.gitignore` impide que se cuelen en un commit.

## Estructura

```
app/
  config/       database.php (los secretos se crean aparte, ver arriba)
  controllers/  auth, dashboard, profesores, materias, secciones,
                aulas, horarios, usuarios, configuracion, ayuda, bitacora
  core/         Router (despacho dinámico) y Controller base
  libs/         Turnstile, Correo (PHPMailer), GoogleAuthenticator
  models/       12 modelos (incluye IntentoIp y TokenRecuperacion)
  views/        31 vistas (incluye ayuda, bitácora, por-profesor)
public/
  index.php     Controlador frontal
  .htaccess     URL bonitas + caché de estáticos + compresión
  assets/       css, js, imágenes
scripts/        Sugerencias y rotación de clave (solo consola)
sql/            Esquema fresco (14 tablas)
tests/          Suite de pruebas de consola (20 pruebas)
docs/           Manual de usuario y manual del programador
```

## Pruebas

La suite de consola (`tests/suite.php`) corre 20 pruebas contra una
base de datos de prueba propia, configurada por variables de entorno
(ver `tests/README.md`). No corre contra la base real.

## Scripts de consola (solo CLI, la web devuelve 403)

- `scripts/sugerir_secciones.php --matricula=N [--capacidad=N | --aula=ID] [--aplicar ...]`
- `scripts/sugerir_huecos.php --profesor=ID --materia=ID [--seccion=ID] [--aplicar=N]`
- `scripts/rotar_master_key.php --probar` (simulación) o sin flags
  (migra todos los secretos 2FA a una clave nueva).

## Estado del proyecto

En proceso de validación académica. Instrumento de 40 puntos: 39,5.
Queda pendiente que el departamento confirme dónde debe aplicarse el
arrastrar y soltar (RF-06), que es el único punto abierto.

## Documentación completa

- `docs/manual-de-usuario.md` — uso módulo por módulo, problemas
  frecuentes y glosario.
- `docs/manual-del-programador.md` — arquitectura, modelo de datos,
  despliegue, configuración, pruebas, mantenimiento y un anexo de
  discrepancias y pendientes verificados contra el código.
