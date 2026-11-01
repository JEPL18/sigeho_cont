# SIGEHO-CONT — Manual del Programador

## 1. Portada y control

| Campo | Detalle |
|---|---|
| Sistema | SIGEHO-CONT — Sistema Integrado de Gestión de Horarios |
| Documento | Manual del Programador |
| Institución | Universidad Politécnica Territorial de Falcón "Alonso Gamero" (UPTAG), Departamento de Contaduría |
| Fecha | 2026-10-31 |
| Autores | Juan Montañez, Andrés Moreno y Jesús Peña |
| Tutor | Dr. Andys Corona |
| Licencia | Creative Commons Atribución-NoComercial-CompartirIgual 4.0 Internacional (CC BY-NC-SA 4.0) |
| Stack | PHP 8.3, PDO, MySQL/MariaDB, Apache, Bootstrap 5.3 |
| Público | Desarrolladores que deban mantener o continuar el sistema |

> Este manual describe el sistema tal como está implementado en el código. No incluye
> valores de claves, secretos, contraseñas, tokens ni credenciales de ningún tipo.
> Los archivos de configuración se describen por su estructura, nunca por su contenido.

## 2. Índice

1. Portada y control
2. Índice
3. Introducción y objetivos del sistema
4. Arquitectura
5. Requisitos y stack
6. Estructura de directorios
7. Componentes
8. Rutas
9. Base de datos
10. Reglas de negocio
11. Seguridad
12. Configuración
13. Instalación
14. Scripts de consola
15. Pruebas
16. Mantenimiento y despliegue
17. Solución de problemas técnicos frecuentes
18. Glosario y anexos

## 3. Introducción y objetivos del sistema

SIGEHO-CONT es un sistema web para la gestión de horarios académicos del
Departamento de Contaduría de la UPTAG. Su propósito es facilitar la construcción
de horarios evitando conflictos, no sustituir el criterio del personal académico:
el sistema propone, valida y registra; la decisión final de crear, mover o
eliminar un horario es siempre humana y queda registrada en bitácora.

Funciones principales:

- Gestión (CRUD) de profesores, materias, secciones, aulas, horarios y usuarios.
- Detección de choques de aula, profesor y sección al crear, editar y mover
  horarios, con registro de los intentos fallidos en un log de choques.
- Sugerencias supervisadas: cantidad de secciones según matrícula proyectada y
  huecos libres de horario según disponibilidad, capacidad y malla. Las
  sugerencias nunca se aplican solas: el administrador las revisa y las aplica.
- Arrastrar y soltar bloques en la cuadrícula semanal del panel principal
  (solo con mouse, solo para administradores), con revalidación completa en el
  servidor antes de mover.
- Exportación de horarios por profesor, con impresión/PDF y Excel generados en
  el navegador.
- Autenticación con contraseñas robustas, bloqueo por intentos, verificación en
  dos pasos (TOTP) opcional, recuperación por preguntas de seguridad o por
  correo con token, protección contra bots y bitácora de acciones.

## 4. Arquitectura

### 4.1. Patrón MVC

El sistema usa MVC sin frameworks:

- **Modelo** (`app/models/`): acceso a datos con PDO. Cada modelo corresponde
  a una tabla o a un concepto (ver capítulo 7). Los modelos nunca leen
  superglobales (`$_GET`, `$_POST`, `$_SESSION`); reciben parámetros.
- **Vista** (`app/views/`): plantillas PHP con HTML y Bootstrap. Las vistas
  reciben variables extraídas desde el controlador y escapan la salida con
  `htmlspecialchars`.
- **Controlador** (`app/controllers/`): recibe la petición, aplica las reglas
  (permisos, validaciones, opt-in, choques), llama al modelo y elige la vista.

No hay capa de servicios ni ORM. La lógica de negocio vive en los modelos
(consultas y reglas reutilizables) y en los controladores (flujo de cada
acción).

### 4.2. Front controller (`public/index.php`)

Toda petición web entra por `public/index.php`, que en orden:

1. Endurece la sesión: cookie `HttpOnly`, `SameSite=Strict` y
   `gc_maxlifetime` de 900 segundos; luego `session_start()`.
2. Define constantes: `BASE_PATH` (raíz del proyecto), `APP_PATH` (detecta
   `public/app/` si existe y si no usa `BASE_PATH/app/`, lo que permite las
   dos disposiciones de archivos), `PUBLIC_PATH`, `BASE_URL` (calculada desde
   `dirname($_SERVER['SCRIPT_NAME'])`, con `/` como valor cuando el proyecto
   está en la raíz web) y `SESION_MAX_INACTIVIDAD` (900 segundos).
3. Si hay sesión iniciada y pasaron más de 900 segundos desde
   `$_SESSION['ultima_actividad']`, destruye la sesión, borra la cookie y
   redirige a `auth/login?msg=sesion_expirada`. Si no expiró, actualiza
   `ultima_actividad` y envía cabeceras `Cache-Control: no-store` (más
   `Pragma: no-cache` y `Expires: 0`) en respuestas autenticadas.
4. Carga `app/config/database.php`, que crea la conexión PDO `$conn` y define
   las funciones de CSRF y de cifrado (ver capítulos 11 y 12).
5. Genera el token CSRF de sesión si no existe.
6. En toda petición POST, compara `$_POST['csrf_token']` con el token de
   sesión usando `hash_equals`. Si no coincide, responde 403 con un mensaje
   fijo y detiene la ejecución. Este control es central: ningún controlador
   necesita repetirlo.
7. Registra un autocargador que busca cada clase, en orden, en
   `app/core/`, `app/models/` y `app/controllers/`. Las librerías
   (`app/libs/`) no se autocargan: los controladores que las usan las
   incorporan con `require_once` (ver capítulo 7).
8. Crea el `Router` con `$conn` y despacha.

### 4.3. Router de despacho dinámico (`app/core/Router.php`)

El router lee **únicamente** `$_GET['r']` (la reescritura de
`public/.htaccess` convierte las URLs amigables a ese parámetro; sin
`mod_rewrite` se usa `?r=controlador/accion` directamente).

Reglas de despacho:

- Ruta vacía: redirige a `dashboard` si hay sesión, si no a `auth/login`.
- El primer segmento es la clave del controlador y se pasa a minúsculas. El
  mapa es fijo (no se instancia ninguna clase fuera de él):

| Segmento | Controlador |
|---|---|
| `auth` | `AuthController` |
| `dashboard` | `DashboardController` |
| `profesores` | `ProfesorController` |
| `materias` | `MateriaController` |
| `secciones` | `SeccionController` |
| `aulas` | `AulaController` |
| `horarios` | `HorarioController` |
| `usuarios` | `UsuarioController` |
| `configuracion` | `ConfiguracionController` |
| `ayuda` | `AyudaController` |
| `bitacora` | `BitacoraController` |

- El segundo segmento es el método. Si está ausente o vacío, se usa `index`.
  Debe cumplir la expresión `/^[a-zA-Z][a-zA-Z0-9_]*$/`; cualquier otro valor
  produce 404.
- Si la clave no está en el mapa, la clase no existe o el método no existe o
  no es invocable, produce 404.
- El 404 es un mensaje fijo: `Página no encontrada.` La entrada del usuario
  nunca se refleja en la respuesta.

Consecuencias para el mantenedor:

- Una acción nueva sobre un controlador existente **no** requiere tocar el
  router: basta con añadir el método público al controlador.
- Un controlador nuevo **sí** requiere añadir su entrada al mapa de
  `$controladores` en `Router.php`.
- Los métodos del controlador no reciben parámetros de la URL: todo llega por
  `$_GET`/`$_POST` y lo lee el propio método.

### 4.4. Ciclo de una petición

1. El navegador pide una URL amigable (por ejemplo, `horarios/nuevo`).
2. `public/.htaccess` la reescribe a `index.php?r=horarios/nuevo` (si el
   recurso no existe físicamente; los archivos de `assets/` se sirven
   directamente).
3. `public/index.php` aplica sesión, expiración, CSRF y autocarga.
4. El router instancia el controlador y ejecuta el método.
5. El método verifica permisos (`requireLogin()` o `requireAdmin()`),
   procesa el POST si lo hay, consulta los modelos y renderiza.
6. La respuesta sale como HTML (o JSON en el caso de `horarios/mover`).

### 4.5. `render()` vs `renderPage()` (`app/core/Controller.php`)

- `render($vista, $datos)`: compone la página completa con
  `layouts/header.php` + vista + `layouts/footer.php`. Expone a la vista las
  variables `$conn`, `$baseUrl` y `$es_admin`, más los datos extraídos con
  `EXTR_SKIP`. Es el método de todas las páginas internas. Si la vista no
  existe, responde 404.
- `renderPage($vista, $datos)`: renderiza solo la vista, sin encabezado ni
  pie. La usan las páginas de autenticación y recuperación (`login`,
  `verificar_2fa`, `recuperar_clave`, `solicitar_recuperacion`,
  `restablecer`), que son documentos HTML completos por sí mismos.
- `requireLogin()`: si no hay `$_SESSION['usuario_id']`, redirige a
  `auth/login`.
- `requireAdmin($fallback)`: si no hay sesión, redirige a `auth/login`; si
  el rol (comparado en minúsculas) no es `administrador`, responde 403 y
  renderiza el fragmento `errores/403` (que no tiene `<head>` propio: vive
  dentro del layout).
- `bitacora($accion)`: registra la acción en bitácora dentro de un
  `try/catch` silencioso: si el registro falla, la operación principal no se
  interrumpe.
- `url($ruta, $params)` y `asset($ruta)`: construyen URLs con el prefijo
  `BASE_URL`. `redirect($ruta)` redirige y termina la ejecución.

## 5. Requisitos y stack

| Requisito | Detalle |
|---|---|
| PHP | 8.3 |
| Servidor web | Apache con `mod_rewrite` (URLs amigables). `mod_expires` y `mod_deflate` son opcionales: las reglas de `public/.htaccess` están dentro de `<IfModule>` |
| Base de datos | MySQL o MariaDB |
| Extensiones PHP | `pdo_mysql` (obligatoria), `openssl` (obligatoria, cifrado de secretos), `curl` (recomendada; Turnstile tiene respaldo con flujos si no está), `mbstring` (recomendada por PHPMailer), `json`, `session`, `hash` (núcleo) |
| Frontend | Bootstrap 5.3 y Bootstrap Icons servidos desde CDN (sin archivos locales de Bootstrap) |
| JavaScript | Vanilla JS (sin frameworks). El arrastre de la cuadrícula y la exportación a Excel son JavaScript del lado del cliente |

Notas:

- No hay `composer.json`: PHPMailer está vendorizado en `app/libs/PHPMailer/`
  y el resto de librerías son archivos propios. No hay proceso de build.
- El sistema no sube archivos, no ejecuta comandos del sistema desde la web
  y no usa `eval` ni includes con variables.
- El código nuevo del proyecto se escribe sin comentarios, por convención del
  repositorio. Las vistas conservan algunos comentarios HTML/CSS originales.

## 6. Estructura de directorios

```
sigeho_cont_mvc/
├── public/                  <- Raíz web (document root recomendado)
│   ├── index.php            <- Front controller
│   ├── .htaccess            <- Reescritura a URLs amigables + caché + compresión
│   └── assets/
│       ├── css/estilos.css  <- Estilos propios
│       ├── js/main.js       <- Sidebar, menú móvil y utilidades
│       └── img/             <- Logos (Contaduría y UPTAG)
├── app/
│   ├── config/
│   │   └── database.php     <- Conexión PDO + MASTER_KEY + CSRF + cifrado
│   ├── core/
│   │   ├── Controller.php   <- Clase base: render, permisos, bitácora
│   │   └── Router.php       <- Despacho controlador/acción
│   ├── controllers/         <- Un controlador por módulo (ver capítulo 7)
│   ├── models/              <- Un modelo por tabla o concepto
│   ├── views/
│   │   ├── layouts/         <- header.php (sidebar) y footer.php
│   │   ├── auth/            <- login, verificar_2fa, configurar_2fa,
│   │   │                      recuperar_clave, solicitar_recuperacion, restablecer
│   │   ├── dashboard/       <- KPIs, cuadrícula semanal, modal de log de choques
│   │   ├── profesores/      <- index + form (nuevo/editar comparten form.php)
│   │   ├── materias/        <- index + form
│   │   ├── secciones/       <- index + form + sugerir
│   │   ├── aulas/           <- index + form
│   │   ├── horarios/        <- index + form + por_profesor + sugerir
│   │   ├── usuarios/        <- index + form + eliminar
│   │   ├── configuracion/   <- Fecha de inicio del lapso y estado 2FA
│   │   ├── ayuda/           <- Acordeón de ayuda
│   │   ├── bitacora/        <- Listado y vaciado (solo administrador)
│   │   └── errores/         <- Fragmento 403 (sin <head> propio)
│   └── libs/
│       ├── Turnstile.php        <- Verificación Cloudflare Turnstile
│       ├── Correo.php           <- Envío SMTP con PHPMailer
│       ├── GoogleAuthenticator.php <- TOTP (generar secreto, verificar código)
│       └── PHPMailer/           <- Librería vendorizada (Exception, SMTP, PHPMailer)
├── scripts/                 <- Utilidades CLI (ver capítulo 14)
├── sql/                     <- Esquema fresco (ver capítulos 9 y 13)
└── tests/                   <- Suite de pruebas de modelos (ver capítulo 15)
```

Notas sobre la raíz del repositorio:

- `README.md` describe la estructura original de la migración a MVC. Está
  desactualizado en algunos puntos (ver anexo A): no debe usarse como fuente
  de verdad sobre el esquema ni sobre funciones. Este manual lo sustituye.
- Los archivos `ACTUALIZACION_*.txt` de la raíz son notas históricas de
  entrega. No forman parte del sistema en ejecución y no deben copiarse al
  servidor.
- Los archivos de configuración de producción (`master_key.php`,
  `turnstile.php`, `correo.php`) **no** están en el repositorio por diseño
  (ver capítulo 12). Solo `database.php` vive en el repositorio, con valores
  de desarrollo local que deben adaptarse a cada entorno.

## 7. Componentes

### 7.1. Controladores

Todos extienden `Controller` y reciben `$conn` (PDO) en el constructor. Las
acciones de lectura exigen sesión (`requireLogin()`); las de escritura exigen
rol administrador (`requireAdmin()`), que responde 403 en caso contrario. La
única excepción es `UsuarioController`, donde incluso el listado exige
administrador.

| Controlador | Acciones | Descripción |
|---|---|---|
| `AuthController` | `login`, `verificar2fa`, `logout`, `configurar2fa`, `recuperar`, `solicitarRecuperacion`, `restablecer` | Autenticación, 2FA y las dos recuperaciones |
| `DashboardController` | `index` | KPIs, cuadrícula semanal y log de choques; el POST `action=clear_logs` (solo administrador) vacía el log |
| `ProfesorController` | `index`, `nuevo`, `editar`, `eliminar` | CRUD de profesores |
| `MateriaController` | `index`, `nuevo`, `editar`, `eliminar` | CRUD de materias y sus prelaciones |
| `SeccionController` | `index`, `nuevo`, `editar`, `eliminar`, `sugerir` | CRUD de secciones y sugerencia de cantidad |
| `AulaController` | `index`, `nuevo`, `editar`, `eliminar` | CRUD de aulas y sus recursos en texto libre |
| `HorarioController` | `index`, `porProfesor`, `nuevo`, `editar`, `eliminar`, `sugerir`, `mover` | CRUD de horarios, vista por profesor, sugerencia de huecos y arrastre (JSON) |
| `UsuarioController` | `index`, `nuevo`, `editar`, `eliminar`, `desbloquear` | CRUD de usuarios, desbloqueo de cuentas y confirmación de borrado con contraseña del administrador |
| `ConfiguracionController` | `index` | Fecha de inicio del lapso (solo administrador) y estado 2FA del usuario actual |
| `AyudaController` | `index` | Página de ayuda |
| `BitacoraController` | `index` | Listado de las últimas 100 entradas y vaciado (POST `accion=vaciar`, solo administrador) |

Detalles por controlador:

- `AuthController::login()` aplica, en orden: espera por IP, Turnstile
  configurado, verificación Turnstile, campos no vacíos y credenciales (ver
  capítulo 11). Si la cuenta tiene 2FA activo, guarda datos temporales en
  sesión (`temp_user_id`, `temp_nombre`, `temp_rol`, `temp_2fa_secret`) y
  redirige a `auth/verificar2fa`; si no, regenera el identificador de sesión
  y crea la sesión completa.
- `AuthController::verificar2fa()` exige la sesión temporal; verifica el
  código con tolerancia de 2 pasos de tiempo, regenera el identificador de
  sesión, promueve los datos temporales a sesión completa, registra el inicio
  en bitácora y borra los datos temporales.
- `AuthController::configurar2fa()` genera el secreto con
  `createSecret()` si no existe uno guardado ni uno temporal, muestra el QR
  (URI `otpauth` con emisor y logo) y la clave manual, y exige el código para
  activar y para desactivar el 2FA propio.
- `AuthController::recuperar()` es el flujo por preguntas de seguridad, en
  3 pasos (correo, respuestas, nueva clave). Turnstile se verifica en cada
  POST del flujo y, si falla, se conserva el paso actual.
- `AuthController::solicitarRecuperacion()` es el flujo por correo: si el
  usuario existe, invalida sus tokens anteriores, crea un token nuevo, lo
  envía y muestra siempre el mismo mensaje genérico. Si el envío falla, el
  token creado se elimina.
- `AuthController::restablecer()` valida el token en GET y lo vuelve a
  validar en POST antes de cambiar la clave.
- `HorarioController::mover()` es la única acción JSON: exige administrador
  y POST, valida día y hora, conserva la duración original del bloque,
  rechaza si el bloque no cabe en el día (fin a las 24:00 o después),
  revalida recursos, opt-in y choques, registra el intento fallido en
  `log_choques` y solo entonces actualiza. Ante cualquier fallo no mueve
  nada y devuelve `{"ok": false, "error": "..."}`.
- `HorarioController::sugerir()` y `SeccionController::sugerir()` exigen
  administrador. La sugerencia web de huecos exige sección; la de secciones
  exige matrícula y capacidad (o aula operativa).
- `DashboardController::index()` calcula los KPIs (profesores activos e
  inactivos, secciones, aulas operativas e inoperativas, choques registrados),
  la semana del lapso y, si se eligió una sección, los segmentos atómicos de
  la cuadrícula (todos los cortes de inicio y fin de los bloques, ordenados,
  con `rowspan` por bloque).

### 7.2. Modelos

Cada modelo recibe `$conn` en el constructor y usa consultas preparadas. Los
métodos `all()`/`filtrar()` devuelven listados; `find($id)` un registro;
`create()`/`update()`/`delete()` escriben; `existeCodigo()`/`existeCorreo()`/
`existeCedula()` evitan duplicados con exclusión opcional del propio id al
editar.

| Modelo | Tabla(s) | Métodos clave |
|---|---|---|
| `Aula` | `aulas` | `all`, `filtrar`, `find`, `catalogoOperativas`, `catalogoTodas`, `existeCodigo`, `create`, `update`, `delete`, `contarOperativas`, `contarInoperativas` |
| `Bitacora` | `bitacora` | `registrar`, `recientes` (100 por defecto), `vaciar` |
| `Configuracion` | `configuracion` | `get`, `set`, `fechaInicioLapso`, `guardarFechaInicioLapso` |
| `Horario` | `horarios` | `allConDetalle`, `filtrarConDetalle`, `ordenarDias`, `find`, `create`, `update`, `delete`, `validarRecursos`, `detectarChoque`, `bloquesPorSeccion`, `bloquesPorProfesor`, `huecosLibres`, `aplicarHueco` |
| `IntentoIp` | `intentos_ip` | `datos`, `esperaRestante`, `registrarFallo`, `limpiar` |
| `LogChoque` | `log_choques` | `registrar`, `recientes` (50 por defecto), `contar`, `vaciar` |
| `Materia` | `materias`, `materia_prelacion` | `all`, `filtrar`, `find`, `catalogo`, `existeCodigo`, `create`, `update`, `delete`, `prelacionesDe`, `mapaPrelaciones`, `sincronizarPrelaciones`, `esPrelacionDe`, `tieneHorarios`, `formaCiclo` |
| `Profesor` | `profesores`, `profesor_disponibilidad`, `profesor_materia` | `all`, `filtrar`, `find`, `catalogoActivos`, `catalogoTodos`, `existeCedula`, `create`, `update`, `delete`, `disponibilidadDe`, `materiasDe`, `mapaDisponibilidad`, `mapaMaterias`, `sincronizarDisponibilidad`, `sincronizarMaterias`, `tieneDisponibilidad`, `tieneMaterias`, `disponibilidadCubre`, `dictaMateria`, `tieneHorarios`, `contarActivos`, `contarInactivos` |
| `Seccion` | `secciones` | `all`, `filtrar`, `find`, `catalogo`, `listaParaDashboard`, `existeCodigo`, `create`, `update`, `delete`, `contar`, `sugerirCantidad`, `crearSugeridas` |
| `TokenRecuperacion` | `recuperacion_tokens` | `crear`, `buscarValido`, `marcarUsado`, `invalidarDeUsuario`, `eliminar` |
| `Usuario` | `usuarios` | `all`, `filtrar`, `find`, `findParaLogin`, `existeCorreo`, `existeIdPorCorreo`, `findIdPorCorreo`, `actualizarPasswordPorId`, `actualizarPasswordPorCorreo`, `verificarRespuestasSeguridad`, `create`, `update`, `delete`, `desbloquear`, `resetearIntentos`, `registrarIntentoFallido`, `datosSeguridad`, `estado2fa`, `datos2fa`, `activar2fa`, `desactivar2fa`, `limpiarDependencias` |

Reglas de borrado que el mantenedor debe conocer:

- `Materia::esPrelacionDe($id)` impide borrar una materia que es prelación
  de otra. `Materia::tieneHorarios($id)` y `Profesor::tieneHorarios($id)`
  protegen los borrados con horarios asociados.
- `Materia::formaCiclo($materia_id, $nuevas)` detecta ciclos por búsqueda en
  profundidad (DFS) antes de guardar prelaciones.
- `Usuario::limpiarDependencias($id)` gestiona los registros asociados al
  borrar un usuario (bitácora y log de choques referencian usuarios; ver
  capítulo 9 sobre las claves foráneas).

### 7.3. Vistas y layouts

- `layouts/header.php`: documento completo hasta el contenido. Calcula la
  semana del lapso (ver capítulo 10), define la barra lateral (sidebar) con
  el bloque SISTEMA solo para administradores, el enlace de ayuda y el
  favicon (logo de Contaduría). Incluye Bootstrap 5.3.0 y Bootstrap Icons
  1.10.5 desde CDN, el enlace de salto al contenido (`#contenido`) y las
  clases de accesibilidad.
- `layouts/footer.php`: cierra los contenedores, carga el JS de Bootstrap y
  `assets/js/main.js`, y muestra el pie con la licencia CC BY-NC-SA 4.0 y
  los autores.
- Las vistas de cada módulo usan el patrón `index.php` (listado con filtros
  y contador, más botón de limpiar) y `form.php` (compartido por nuevo y
  editar; el modo edición se distingue por la presencia de datos).
- Las vistas de autenticación y recuperación son documentos completos
  (usan `renderPage`) y cargan Bootstrap 5.3.3 y Bootstrap Icons 1.11.3
  desde CDN. Existe, por tanto, una diferencia de versiones de Bootstrap
  entre el login/flujo de autenticación (5.3.3) y las páginas internas
  (5.3.0). No es un error funcional, pero el mantenedor debe conocerla
  antes de unificar versiones: cambiar una sin probar la otra puede alterar
  la presentación.
- `horarios/por_profesor.php`: vista por profesor con botones de imprimir /
  guardar PDF (`window.print()`) y exportar a Excel (tabla convertida a
  HTML y descargada como Blob desde el navegador). No hay generación de
  PDF ni Excel en el servidor.
- `dashboard/index.php`: KPIs, selector de sección y turno, cuadrícula
  semanal con segmentos atómicos y `rowspan`, modal con el log de choques y
  el JavaScript de arrastrar y soltar (bloques `draggable` solo para
  administradores, celdas `data-dia`, token CSRF leído dentro del
  manejador de soltar).
- `horarios/form.php`: validación en el navegador (hora de fin posterior a
  la de inicio; matrícula de la sección dentro de la capacidad del aula)
  con alertas. El servidor es la autoridad: rechaza ambos casos sin crear
  nada aunque el JavaScript se omita.

### 7.4. Librerías

- `app/libs/Turnstile.php` (clase `Turnstile`, estática):
  `config()` lee `app/config/turnstile.php` y exige la clave secreta;
  `configurado()` indica si la configuración es válida; `verificar($token,
  $ip)` envía el token a `siteverify` de Cloudflare con cURL (5 segundos de
  tiempo de espera, 2 intentos solo ante fallo de transporte) o con flujos
  si cURL no existe, y exige `success === true`. No verifica el nombre de
  host: los tokens están ligados a la clave del sitio. Todo fallo (sin
  configuración, sin token, sin respuesta, respuesta inválida o
  `success !== true`) devuelve falso y se registra en `error_log`. Nunca
  lanza excepciones hacia el controlador.
- `app/libs/Correo.php` (clase `Correo`, estática): `config()` lee
  `app/config/correo.php` y exige host, puerto y remitente;
  `enviar($destino, $asunto, $cuerpo, $cuerpoHtml = null)` envía por SMTP
  con PHPMailer, juego de caracteres UTF-8 y remitente con nombre
  configurable. Si se pasa versión HTML, la envía como cuerpo y usa el
  texto como `AltBody`; si no, envía texto plano. Si la configuración falta
  o es inválida, devuelve falso sin interrumpir el flujo; los errores van a
  `error_log`. La única llamada actual es la recuperación por correo.
- `app/libs/GoogleAuthenticator.php` (clase `PHPGangsta_GoogleAuthenticator`):
  `createSecret($secretLength = 16)` genera el secreto; `getCode($secret)`
  calcula el código TOTP; `verifyCode($secret, $code, $discrepancy = 1)`
  lo verifica con tolerancia en pasos de tiempo (el controlador usa
  tolerancia 2); `getQRCodeGoogleUrl()` construye URLs de QR (el
  controlador, en cambio, arma la URI `otpauth` a mano y pide la imagen a
  un servicio externo de generación de QR: el secreto viaja en esa URL).
- `app/libs/PHPMailer/`: PHPMailer vendorizado (clases `Exception`, `SMTP`
  y `PHPMailer`). No se actualiza con Composer: para actualizarlo hay que
  sustituir el directorio completo y volver a probar el envío.

## 8. Rutas

Formato: `BASE_URL + controlador/acción`. La acción por defecto es `index`.
`P` = pública (sin sesión), `S` = exige sesión, `A` = solo administrador.

| Ruta | Controlador / acción | Acceso | Notas |
|---|---|---|---|
| `/` | Redirección | — | A `dashboard` con sesión, si no a `auth/login` |
| `auth/login` | `AuthController::login` | P | Con Turnstile y límite por IP |
| `auth/verificar2fa` | `AuthController::verificar2fa` | P* | *Exige la sesión temporal de un login con 2FA; sin ella redirige a `auth/login` |
| `auth/logout` | `AuthController::logout` | — | Destruye la sesión y su cookie (si la hay) y redirige a `auth/login` |
| `auth/configurar2fa` | `AuthController::configurar2fa` | S | Activa/desactiva el 2FA propio |
| `auth/recuperar` | `AuthController::recuperar` | P | Por preguntas de seguridad, 3 pasos, con Turnstile |
| `auth/solicitarRecuperacion` | `AuthController::solicitarRecuperacion` | P | Por correo, con Turnstile, mensaje siempre genérico |
| `auth/restablecer` | `AuthController::restablecer` | P | Requiere `?token=` válido de 64 hexadecimales |
| `dashboard` | `DashboardController::index` | S | KPIs y cuadrícula; `clear_logs` por POST solo administrador |
| `profesores`, `materias`, `secciones`, `aulas` | `index` de cada controlador | S | Listados con filtros |
| `profesores/nuevo`, `profesores/editar`, `profesores/eliminar` | `ProfesorController` | A | Igual patrón en `materias`, `secciones`, `aulas` |
| `horarios` | `HorarioController::index` | S | Listado con filtros por sección, día y turno |
| `horarios/porProfesor` | `HorarioController::porProfesor` | S | Vista por profesor con impresión y Excel |
| `horarios/nuevo`, `horarios/editar`, `horarios/eliminar` | `HorarioController` | A | Con validación completa (capítulo 10) |
| `horarios/sugerir` | `HorarioController::sugerir` | A | Huecos libres; exige sección en la web |
| `horarios/mover` | `HorarioController::mover` | A | Solo POST; responde JSON |
| `secciones/sugerir` | `SeccionController::sugerir` | A | Cantidad de secciones y creación supervisada |
| `usuarios` | `UsuarioController::index` | A | Incluso el listado es solo administrador |
| `usuarios/nuevo`, `usuarios/editar`, `usuarios/eliminar` | `UsuarioController` | A | El borrado exige contraseña del administrador y código 2FA si lo tiene activo |
| `usuarios/desbloquear` | `UsuarioController::desbloquear` | A | Desbloquea cuentas tras los 3 intentos |
| `configuracion` | `ConfiguracionController::index` | A | Fecha de inicio del lapso |
| `ayuda` | `AyudaController::index` | S | Acordeón de ayuda |
| `bitacora` | `BitacoraController::index` | A | Últimas 100 entradas; vaciado por POST |

Notas:

- Los formularios de creación y edición usan el método GET para mostrar el
  formulario y POST para guardar, dentro de la misma acción (`nuevo`,
  `editar`). Las eliminaciones son POST con CSRF (nunca GET).
- `horarios/mover` no es una página: responde JSON y no renderiza vista.
- El parámetro `r` también acepta la forma `?r=controlador/accion` cuando
  `mod_rewrite` no está disponible.

## 9. Base de datos

Motor: MySQL/MariaDB, InnoDB, `utf8mb4`. El esquema de instalación limpia
está en `sql/sigeho_cont_db.sql` y contiene 14 tablas. Las dos últimas
(`recuperacion_tokens`, `intentos_ip`) usan `CREATE TABLE IF NOT EXISTS`;
el resto usa `CREATE TABLE` sin esa cláusula. El archivo mezcla estructura
con INSERT de datos semilla: nunca debe reimportarse sobre una base con
datos reales (ver capítulo 13).

### 9.1. Tablas

| Tabla | Columnas clave | Claves y relaciones |
|---|---|---|
| `aulas` | `id`, `codigo` (único), `capacidad`, `estado` (por defecto `OPERATIVA`), `recursos` (TEXT, nulo) | PK `id`; UK `codigo` |
| `bitacora` | `id`, `usuario_id` (NOT NULL), `accion`, `fecha` (timestamp, por defecto actual) | PK `id`; FK `usuario_id` → `usuarios.id` (sin acción ON DELETE: borrar un usuario con entradas exige gestionar antes sus dependencias) |
| `configuracion` | `id`, `parametro`, `valor` | PK `id`. Parámetro usado: `fecha_inicio_lapso` |
| `horarios` | `id`, `seccion_id`, `materia_id`, `profesor_id`, `aula_id`, `dia_semana` (ENUM sin domingo), `hora_inicio`, `hora_fin` | PK `id`; FK a `secciones`, `materias`, `profesores` y `aulas` (sin acción ON DELETE) |
| `log_choques` | `id`, `fecha`, `usuario_id` (nulo), `intento_asignacion`, `detalle_conflicto` | PK `id`; sin FK (el usuario puede ser nulo) |
| `materias` | `id`, `codigo` (único), `nombre`, `trayecto`, `horas_semanales` | PK `id`; UK `codigo` |
| `materia_prelacion` | `materia_id`, `prelacion_id` | PK compuesta (`materia_id`, `prelacion_id`) |
| `profesores` | `id`, `cedula` (única), `titulo`, `nombre`, `max_horas` (por defecto 5), `estado` (por defecto `ACTIVO`) | PK `id`; UK `cedula`. La columna `titulo` existe en la base pero los formularios no la escriben ni la muestran |
| `profesor_disponibilidad` | `profesor_id`, `dia_semana`, `turno` | PK compuesta (`profesor_id`, `dia_semana`, `turno`) |
| `profesor_materia` | `profesor_id`, `materia_id` | PK compuesta (`profesor_id`, `materia_id`) |
| `secciones` | `id`, `codigo` (único), `trayecto`, `trimestre` (por defecto 1), `cantidad_alumnos` | PK `id`; UK `codigo` |
| `usuarios` | `id`, `nombre`, `correo` (único), `password`, `2fa_secret` (nulo), `2fa_activo`, `rol` (ENUM), `pregunta_seguridad_1..3`, `respuesta_seguridad_1..3`, `intentos`, `bloqueado` | PK `id`; UK `correo` |
| `recuperacion_tokens` | `id`, `usuario_id`, `token_hash` CHAR(64), `expira_en`, `usado`, `creado_en` | PK `id`; FK `usuario_id` → `usuarios.id` ON DELETE CASCADE |
| `intentos_ip` | `ip` VARCHAR(45), `intentos`, `ultimo_intento` | PK `ip` (admite IPv4 e IPv6) |

### 9.2. Notas de esquema que el mantenedor debe conocer

- **Roles históricos.** La columna `usuarios.rol` es un ENUM con cuatro
  valores (`administrador`, `coordinador`, `profesor`, `asistente`). El
  código, en cambio, solo ofrece dos roles en los formularios
  (`Administrador` y `Asistente`, comparados en minúsculas) y el control de
  acceso es binario: `administrador` lo puede todo; cualquier otro rol es
  solo lectura. Los valores viejos se conservan en la base únicamente por
  compatibilidad. No se debe añadir un tercer rol sin revisar
  `requireAdmin()` y todos los formularios.
- **`bitacora.usuario_id` es NOT NULL con FK.** Todo registro de bitácora
  exige un usuario existente. Por eso los scripts de consola no pueden
  registrar en bitácora: no hay usuario de sesión en CLI (ver capítulo 14).
  El ayudante `Controller::bitacora()` usa `$_SESSION['usuario_id']`; fuera
  de una sesión web no debe llamarse.
- **`horarios.dia_semana` no incluye domingo.** Los días válidos en todo el
  sistema son `Lunes`, `Martes`, `Miercoles`, `Jueves`, `Viernes` y
  `Sabado` (sin tildes, tal como están en el ENUM). Cualquier lista de días
  en código nuevo debe usar exactamente esos valores y comparación estricta.
- **Semillas.** El archivo de esquema incluye INSERT de datos de ejemplo.
  En una instalación real deben revisarse: no deben quedar cuentas ni datos
  de ejemplo en producción.
- **Contraseñas y respuestas.** `usuarios.password` guarda hashes
  `password_hash` (bcrypt). Las respuestas de seguridad también se guardan
  como hashes, nunca en texto plano. `2fa_secret` guarda el secreto
  cifrado, nunca en texto plano (ver capítulo 11).
- **Tokens.** `recuperacion_tokens.token_hash` guarda solo el hash SHA-256
  del token (CHAR(64)); el token en claro solo existe en el correo enviado.
  `intentos_ip` guarda contadores por IP, nunca credenciales.

## 10. Reglas de negocio

### 10.1. Detección de choques (`Horario::detectarChoque`)

Se ejecuta al crear, editar y mover horarios, en este orden: aula, profesor,
sección. Cada comprobación usa la condición de traslape estricto:

```sql
dia_semana = ? AND (hora_inicio < ? AND hora_fin > ?) AND <entidad>_id = ?
-- parámetros: día, hora_fin nueva, hora_inicio nueva, id de la entidad
```

Consecuencias:

- **Bloques contiguos son válidos.** Si un bloque termina a las 10:30 y
  otro empieza a las 10:30, no hay choque (las desigualdades son estrictas).
- **Autoexclusión al editar/mover.** El parámetro `$excluirId` añade
  `AND id != ?`, para que el propio horario no choque consigo mismo.
- **El parámetro `$esEdicion` solo cambia el texto del mensaje**, no la
  lógica.
- El método devuelve `null` si no hay choque, o un arreglo con `error`
  (mensaje para mostrar) y `detalle` (texto corto para el log).
- Todo intento fallido por choque en el arrastre se registra en
  `log_choques` con el usuario, el intento y el detalle. El contador del
  panel principal (`LogChoque::contar()`) y el modal "Ver Log" muestran los
  últimos 50.

### 10.2. Validación de recursos (`Horario::validarRecursos`)

Devuelve una fila con `prof_estado`, `aula_estado`, `aula_cap`
(capacidad del aula) y `sec_alumn` (matrícula de la sección). El controlador
la aplica así, en orden:

1. Profesor debe estar `ACTIVO`.
2. Aula debe estar `OPERATIVA`.
3. Matrícula de la sección dentro de la capacidad del aula
   (`sec_alumn <= aula_cap`).

La validación en el navegador del formulario de horarios (hora de fin
posterior a la de inicio; matrícula dentro de la capacidad) es solo una
ayuda: el servidor rechaza ambos casos sin crear nada.

### 10.3. Turnos

El turno se deriva de `hora_inicio`:

| Turno | Condición sobre `hora_inicio` |
|---|---|
| `MATUTINO` | menor que 12:00 |
| `VESPERTINO` | desde 12:00 y menor que 18:00 |
| `NOCTURNO` | desde 18:00 |

Se usa en los filtros de horarios, en la cuadrícula del panel y en la
comprobación de disponibilidad. La misma tabla de umbrales existe en
`Horario::turnoDe()` (modelo) y en el controlador de horarios.

### 10.4. Disponibilidad y materias: regla opt-in

Las tablas pivote `profesor_disponibilidad` (día + turno) y
`profesor_materia` son **opt-in**:

- Si el profesor **no tiene filas** en la pivote, **no hay restricción**:
  puede dictar cualquier materia y en cualquier día/turno.
- Si **tiene filas**, la restricción aplica y bloquea con mensaje: el
  horario solo se crea si la disponibilidad cubre el día y turno
  (`disponibilidadCubre`) y si el profesor dicta esa materia
  (`dictaMateria`).

Métodos de apoyo: `tieneDisponibilidad()`, `tieneMaterias()`,
`disponibilidadDe()`, `materiasDe()`, `mapaDisponibilidad()`,
`mapaMaterias()`, `sincronizarDisponibilidad()` y
`sincronizarMaterias()` (reescriben la pivote completa desde el formulario).

### 10.5. Prelaciones: registro de orden, no bloqueo

`materia_prelacion` registra el orden del pensum (qué materia precede a
cuál). Reglas:

- No se permite la autoprelación ni ciclos: `formaCiclo()` detecta ciclos
  por búsqueda en profundidad antes de guardar.
- No se puede borrar una materia que es prelación de otra
  (`esPrelacionDe()`).
- **Las prelaciones NO bloquean la creación de horarios.** El sistema no
  tiene estudiantes ni notas, y un choque se define como profesor, aula u
  hora. La creación, edición y arrastre no consultan prelaciones.

### 10.6. Recursos de aula y capacidad

- `aulas.recursos` es texto libre separado por comas (máximo 15 elementos,
  500 caracteres), editado dentro de un bloque "Opciones avanzadas" del
  formulario. Es informativo: **no afecta a la capacidad ni a ninguna
  validación**.
- La capacidad es asientos/espacio real del aula (`aulas.capacidad`) frente
  a la matrícula de la sección (`secciones.cantidad_alumnos`). Solo el
  aula debe estar `OPERATIVA` para usarse en horarios nuevos; el formulario
  de secciones puede tomar la capacidad de un aula operativa como atajo,
  sin asignar el aula.

### 10.7. Trimestre de 12 semanas

La tabla `configuracion` guarda `fecha_inicio_lapso`. El encabezado calcula
la semana actual como `floor(días_desde_el_inicio / 7) + 1`:

- Sin fecha configurada: "Sin configurar".
- Antes del inicio: "Por iniciar".
- Semanas 1 a 12: "Semana N".
- Más de 12 semanas: "Trimestre Finalizado".

Solo el administrador puede cambiar la fecha (`configuracion/index`).
El cálculo vive en `layouts/header.php`, no en el modelo: si se cambia el
formato del encabezado, hay que conservar esa lógica.

### 10.8. Sugerencias supervisadas

**Secciones (`Seccion::sugerirCantidad`, `crearSugeridas`).**
`cantidad = ceil(matrícula / capacidad)`; reparto parejo: base
`floor(matrícula / cantidad)` por sección y el resto se distribuye de uno
en uno en las primeras secciones. Matrícula o capacidad no positivas
devuelven nulo. `crearSugeridas` numera los códigos como
`PREFIJO-NN` desde 01, saltando los códigos que ya existen, y crea las
secciones con su matrícula repartida. En la web, la sugerencia exige
matrícula y capacidad (o aula operativa); la creación exige además
trayecto, trimestre y prefijo (alfanumérico, máximo 10 caracteres).

**Huecos (`Horario::huecosLibres`, `aplicarHueco`).** Valores por defecto:
duración 90 minutos, todos los días, máximo 40 huecos, ventana de 07:00 a
19:00 (420 a 1140 minutos). Un hueco es válido si no choca (misma regla
del capítulo 10.1) y si cumple el opt-in de disponibilidad y materias.
`aplicarHueco` vuelve a validar todo antes de crear; si algo cambió desde
la sugerencia, no crea nada y devuelve el motivo. En la web, la sugerencia
exige profesor y materia; la aplicación exige además la sección.

### 10.9. Arrastrar y soltar (`HorarioController::mover`)

- Solo administradores, solo con mouse en computadora, solo POST con JSON.
- Mueve únicamente día y hora: aula, profesor, materia y sección no cambian.
- La duración se conserva: la hora de fin nueva se calcula desde la
  duración original del bloque. Si el bloque no cabe en el día (fin a las
  24:00 o después), se rechaza.
- Revalida en el servidor, en orden: bloque existe, día y hora válidos,
  recursos (estado del profesor, estado del aula, capacidad), opt-in de
  disponibilidad, opt-in de materias y choque con autoexclusión.
- Ante un choque no mueve nada, registra el intento en `log_choques` y
  devuelve el motivo. El formulario de horarios sigue siendo la alternativa
  para cualquier caso que el arrastre no cubra.

### 10.10. Orden completo de validación al crear/editar un horario

1. Campos obligatorios.
2. Hora de inicio anterior a la hora de fin.
3. Profesor `ACTIVO`, aula `OPERATIVA`, capacidad suficiente.
4. Opt-in de disponibilidad (si el profesor tiene filas).
5. Opt-in de materias (si el profesor tiene filas).
6. Detección de choques (aula, profesor, sección).
7. Creación/actualización y registro en bitácora.

Ningún paso se salta aunque el formulario ya haya validado en el navegador.

## 11. Seguridad

### 11.1. CSRF

- El token se genera una vez por sesión con `bin2hex(random_bytes(32))`
  (`csrf_token()` en `app/config/database.php`) y se envía en cada
  formulario con `csrf_field()` (campo oculto `csrf_token`).
- `public/index.php` verifica **todas** las peticiones POST con
  `hash_equals` antes del despacho. Si el token falta o no coincide,
  responde 403 con un mensaje fijo y detiene la ejecución.
- Las acciones que cambian estado (crear, editar, eliminar, desbloquear,
  vaciar, mover, activar/desactivar 2FA) son POST. No hay cambios de
  estado por GET.
- Nota verificada: el token es estable durante la sesión; el código no
  implementa rotación del token por petición (ver anexo A).

### 11.2. Contraseñas

- Hash con `password_hash($password, PASSWORD_DEFAULT)` (bcrypt) y
  verificación con `password_verify`. Nunca se guarda ni se registra una
  contraseña en texto plano.
- Mínimo 16 caracteres, aplicado en creación y edición de usuarios y en
  las dos recuperaciones (preguntas y correo).
- Las respuestas de seguridad también se guardan como hashes
  `password_hash`, nunca en texto plano.

### 11.3. Sesiones

- Cookie de sesión `HttpOnly` y `SameSite=Strict`.
- Expiración por inactividad de 900 segundos (`SESION_MAX_INACTIVIDAD` y
  `gc_maxlifetime`): al expirar se vacía la sesión, se borra la cookie y
  se destruye la sesión, con redirección a `auth/login?msg=sesion_expirada`.
- `session_regenerate_id(true)` al iniciar sesión y al completar el 2FA
  (prevención de fijación de sesión).
- Las respuestas autenticadas envían `Cache-Control: no-store` (más
  `Pragma: no-cache` y `Expires: 0`).
- El cierre de sesión vacía la sesión, borra la cookie y destruye la
  sesión. El cierre en un dispositivo no afecta a las sesiones de otros
  dispositivos: el sistema permite sesiones concurrentes (ver anexo A
  sobre la decisión de no implementar sesión única).
- El aviso de sesión expirada del login indica 15 minutos, coherente con este plazo.

### 11.4. Verificación en dos pasos (TOTP)

- Opcional por usuario. El secreto se genera con `createSecret()` (16
  caracteres) y se guarda **cifrado** en `usuarios.2fa_secret`, nunca en
  texto plano.
- Cifrado AES-256-CBC (`openssl_encrypt`) con IV aleatorio antepuesto al
  criptograma y codificación base64 (`cifrar_secreto()` /
  `descifrar_secreto()` en `app/config/database.php`).
- La clave de cifrado (MASTER_KEY) viene de la variable de entorno
  `SIGEHO_MASTER_KEY` o del archivo `app/config/master_key.php`. Si
  ninguna fuente entrega una cadena, la aplicación se detiene con un
  mensaje de configuración. **No existe clave de repuesto escrita en el
  código**: sin MASTER_KEY, los secretos guardados son inútiles y el 2FA
  no funciona.
- Verificación con `verifyCode()` y tolerancia de 2 pasos de tiempo en el
  controlador. El código de 6 dígitos dura 30 segundos por diseño TOTP.
- Inscripción: el QR codifica una URI `otpauth` con emisor `SIGEHO-CONT`,
  la cuenta (correo del usuario) y la imagen del logo. La imagen del QR
  la genera un servicio externo de QR: el secreto viaja en esa URL. La
  clave manual se muestra como alternativa al escaneo. El secreto solo se
  muestra en la inscripción; ninguna otra vista lo imprime.
- El 2FA protege el inicio de sesión de las cuentas que lo tienen activo.
  Es obligatorio en tres puntos cuando el usuario que ejecuta la acción
  tiene 2FA activo: cambiar la contraseña o las respuestas de seguridad de
  un usuario, borrar un usuario (además de la contraseña del
  administrador) y desactivar el propio 2FA.
- Las dos recuperaciones (preguntas y correo) **omiten el 2FA por diseño**:
  son vías alternativas de acceso. La seguridad de la cuenta de correo
  registrada pasa a ser el respaldo en el flujo por correo.

### 11.5. Bloqueo de cuentas

- Tres intentos fallidos de contraseña bloquean la cuenta de forma
  permanente (`usuarios.bloqueado = 1`, `intentos` acumulado). El mensaje
  indica cuántos intentos quedan antes del bloqueo.
- Solo un administrador puede desbloquear (`usuarios/desbloquear`, que
  además reinicia el contador). No existe desbloqueo automático por
  tiempo.
- Los intentos fallidos con contraseña incorrecta y con correo no
  registrado cuentan para el límite por IP (capítulo 11.6). Los intentos
  sobre cuentas ya bloqueadas y los fallos de Turnstile no cuentan.
- El inicio de sesión distingue en sus mensajes el correo no registrado
  de la contraseña incorrecta. La recuperación por correo, en cambio,
  responde siempre con el mismo mensaje genérico, exista o no la cuenta.

### 11.6. Límite por IP (solo login)

Tabla `intentos_ip` (`ip` clave primaria, `intentos`,
`ultimo_intento`). Política, calculada por `IntentoIp::esperaRestante()`:

- Menos de 5 fallos registrados: sin espera.
- Desde el 5º fallo: espera de `30 × 2^(intentos − 5)` segundos, con tope
  de 900 segundos (15 minutos). Es decir: 30, 60, 120, 240, 480 y 900.
- Las filas sin actividad por más de 24 horas se eliminan al consultarlas
  (expiración perezosa) y la espera vuelve a cero.
- Durante la espera, cualquier intento desde esa IP se rechaza **antes**
  de Turnstile y de la contraseña, con el mensaje de espera en segundos.
- Un inicio de sesión exitoso borra la fila de esa IP.
- Solo se usa `$_SERVER['REMOTE_ADDR']`. No hay encabezados de proxy
  involucrados.
- El límite aplica únicamente al login. Los formularios de recuperación
  no lo tienen.

### 11.7. Cloudflare Turnstile

- Widget en el login y en los dos flujos de recuperación. La clave pública
  va en el HTML de esas vistas; la clave secreta vive en
  `app/config/turnstile.php` (ver capítulo 12).
- En el login, la verificación corre **antes** de revisar la contraseña y
  no consume intentos de la cuenta. En el flujo por preguntas se verifica
  en cada POST y, si falla, se conserva el paso actual.
- Falla cerrado (`fail-closed`): sin configuración, sin token, sin
  respuesta del verificador o con `success !== true`, el acceso se niega.
  Nunca se permite el paso por falta de verificación.
- La verificación usa cURL con 5 segundos de espera y 2 intentos solo
  ante fallo de transporte, o flujos si cURL no existe. Los errores van a
  `error_log`; el usuario solo ve mensajes genéricos.
- Turnstile no verifica el nombre de host: los tokens están ligados a la
  clave del sitio. Su límite real es que no detiene a quien resuelve el
  widget a mano con credenciales válidas robadas: la última barrera en
  ese caso es el 2FA de la cuenta, cuando está activo.
- Turnstile protege solo la puerta (login y recuperación). Dentro de la
  sesión mandan la sesión, los roles y la bitácora.

### 11.8. Inyección SQL y XSS

- **SQL:** todas las consultas usan PDO con consultas preparadas y los
  valores viajan como parámetros. Los fragmentos dinámicos de las
  consultas de filtrado son textos fijos o valores convertidos a entero,
  y las listas (por ejemplo, los días de semana) se validan con
  comparación estricta. No hay consultas construidas con entrada del
  usuario sin parametrizar, ni búsquedas con `LIKE` sobre entrada libre.
  Los controladores no contienen SQL: todo el acceso a datos vive en los
  modelos, y los modelos no leen superglobales.
- **XSS:** la salida se escapa con `htmlspecialchars` en tablas,
  formularios, cuadrícula, bitácora, mensajes de error y nombre de sesión
  en el encabezado. El 404 es una cadena fija (la ruta no se refleja).
  PHP no escapa por sí solo: la protección depende de escapar cada salida,
  hábito que debe mantenerse en toda vista nueva.
- No hay `eval`, ni includes con variables, ni subida de archivos.

### 11.9. Bitácora y secretos

- La bitácora registra crear, editar y eliminar en los seis módulos, más
  sugerencias, arrastres, inicios y cierres de sesión, bloqueos y
  recuperaciones. La página `bitacora/index` (solo administrador) muestra
  las últimas 100 entradas y permite vaciarla por POST.
- **La bitácora nunca registra secretos**: ni contraseñas, ni secretos
  2FA, ni tokens, ni claves. Las acciones registradas describen la
  operación (qué se creó, movió o eliminó), nunca sus credenciales.
- El ayudante `Controller::bitacora()` nunca interrumpe la operación
  principal si el registro falla.

### 11.10. Protección de `app/` y del document root

- La raíz web recomendada es `public/`. Si `app/` queda dentro del
  alcance web (como en despliegues con `htdocs/app/`), debe existir
  `app/.htaccess` con denegación total de acceso. **El repositorio no
  incluye ese archivo**: debe crearse en el despliegue (ver capítulo 13).
- `public/.htaccess` deniega además el acceso a archivos que empiezan por
  punto.
- Nunca deben quedar en una carpeta pública: copias de seguridad, archivos
  `.zip`, volcados `.sql`, archivos `info.php` ni respaldos de archivos
  de configuración. Cualquiera de ellos sería descargable por cualquiera.

## 12. Configuración

Todos los archivos de configuración viven en `app/config/`. Ninguno de
ellos debe versionarse con valores reales ni incluirse en los zip de
entrega: los valores de producción se crean en el servidor. Solo
`database.php` está en el repositorio (con valores de desarrollo local
que deben adaptarse a cada entorno).

### 12.1. `app/config/database.php` (en el repositorio)

- Variables de conexión PDO: servidor, nombre de la base de datos, usuario
  y contraseña. Conexión con `charset=utf8` y modo de errores por
  excepciones (`PDO::ERRMODE_EXCEPTION`).
- Resolución de la MASTER_KEY: primero la variable de entorno
  `SIGEHO_MASTER_KEY`; si no existe o está vacía, el archivo
  `master_key.php` del mismo directorio. Si ninguna fuente entrega una
  cadena, la aplicación se detiene con un mensaje de configuración.
- Define la constante `MASTER_KEY` y borra las variables temporales.
- Funciones `csrf_token()` y `csrf_field()` (token de sesión, ver
  capítulo 11.1).
- Funciones `cifrar_secreto()` y `descifrar_secreto()` (AES-256-CBC con
  IV aleatorio antepuesto y base64, ver capítulo 11.4).

### 12.2. `app/config/master_key.php` (NO está en el repositorio)

- Archivo PHP que devuelve la clave maestra como cadena. Es el archivo
  más delicado del sistema: junto con la base de datos permitiría generar
  códigos 2FA válidos; sin él, los secretos guardados son inútiles.
- Nunca debe subirse a GitHub, incluirse en zip, compartirse, mostrarse
  ni dejarse como respaldo (`.bak`, `.old` o similar) dentro del
  proyecto: el servidor web lo serviría como texto plano.
- Si se pierde por completo, los usuarios con 2FA no podrán entrar hasta
  que un administrador desactive su 2FA. Guárdese fuera del proyecto.
- Para rotarla existe `scripts/rotar_master_key.php` (ver capítulo 14).
  En un hosting sin consola, el mecanismo de rotación debe revisarse en el
  momento del despliegue.

### 12.3. `app/config/correo.php` (NO está en el repositorio)

Estructura (claves, sin valores):

- `host`, `puerto`, `remitente`: obligatorios. Sin ellos, `Correo::config()`
  devuelve falso y el envío no se intenta.
- `usuario`, `clave`: opcionales. Si hay usuario, se activa `SMTPAuth`.
- `seguro`: opcional (mecanismo TLS del transporte SMTP).
- `nombre_remitente`: opcional; por defecto `SIGEHO-CONT`.

Notas:

- El envío usa PHPMailer por SMTP externo. El `mail()` local del hosting
  compartido no es fiable y no debe usarse.
- Si el envío falla, la recuperación por correo elimina el token creado
  y registra el fallo en `error_log`; el usuario ve siempre el mismo
  mensaje genérico.

### 12.4. `app/config/turnstile.php` (NO está en el repositorio)

Estructura (claves, sin valores):

- `clave_secreta`: obligatoria. Sin archivo válido o sin esta clave,
  `Turnstile::configurado()` devuelve falso y el login y la recuperación
  niegan el acceso (falla cerrado).

La clave pública (site key) no vive en este archivo: va en el HTML de las
vistas de login y recuperación. Es pública por diseño y puede estar en el
repositorio.

### 12.5. Variables de entorno

| Variable | Uso |
|---|---|
| `SIGEHO_MASTER_KEY` | Clave maestra de cifrado 2FA (prioridad sobre el archivo) |
| `SIGEHO_TEST_HOST`, `SIGEHO_TEST_DB`, `SIGEHO_TEST_USER`, `SIGEHO_TEST_PASS` | Solo para la suite de pruebas (ver capítulo 15) |

## 13. Instalación

### 13.1. Instalación en XAMPP (Windows, carpeta `sigeho_cont`)

1. **Respaldo primero.** En phpMyAdmin, exportar la base de datos
   completa y guardar el archivo fuera de `htdocs`. Sin respaldo no se
   toca nada.
2. **Extraer el contenido** del zip sobre la carpeta de instalación, sin
   crear una carpeta anidada (el contenido debe quedar directamente en
   la carpeta, no en una subcarpeta con el mismo nombre).
3. **Stop/Start de Apache.** Tras extraer archivos PHP hay que reiniciar
   Apache por OPcache: de lo contrario el servidor puede seguir sirviendo
   el código viejo. Archivos sueltos individuales no exigen reinicio.
4. **Migraciones faltantes.** Revisar en phpMyAdmin qué tiene ya la base
   (tablas pivote, columna `recursos` en aulas, valor `asistente` en el
   ENUM de rol) y, si se dispone de ellos (históricos, ver capítulo 13.3),
   ejecutar **solo** los que falten. Nunca reimportar el esquema completo sobre
   una base con datos reales.
5. **Verificar.** Iniciar sesión normal y con 2FA, y revisar que el
   panel muestra los datos reales.

Notas:

- La consola de Windows no tiene `php` en el PATH por defecto: usar la
  ruta completa al ejecutable de XAMPP o la consola de XAMPP (XAMPP
  Shell).
- Los scripts de consola deben copiarse a `scripts/` antes de ejecutarse
  (ver capítulo 14).
- `database.php` debe apuntar a la base de datos real del entorno. Al
  clonar la aplicación para pruebas, cambiar servidor, usuario y clave
  **no basta**: también debe cambiar el nombre de la base de datos, y
  debe verificarse la identidad de la base (conteo de filas de las tablas
  que se van a usar) antes de cualquier prueba o demostración.

### 13.2. Despliegue en InfinityFree (hosting compartido sin consola)

Particularidades verificadas de este tipo de hosting:

- **No se puede colocar `app/` fuera de `htdocs`**: el `open_basedir`
  del proveedor lo impide. La disposición correcta es `htdocs/app/`
  (con `htdocs/index.php` como punto de entrada, que el front controller
  resuelve gracias a la detección de `APP_PATH`).
- **Crear `app/.htaccess`** con denegación total de acceso
  (`Require all denied`), porque `app/` queda dentro del alcance web.
  Sin ese archivo, los archivos de configuración quedarían expuestos.
- No hay SSH ni consola: las migraciones se ejecutan desde phpMyAdmin y
  los scripts de consola no pueden correrse en el host.
- No hay reinicio de Apache: los cambios aplican en la siguiente
  petición.
- La base de datos se crea desde el panel del proveedor y no admite
  conexiones remotas: el esquema y las migraciones se importan desde
  phpMyAdmin.
- El plan gratuito tiene límites (espacio en disco y peticiones diarias):
  superarlos suspende la cuenta temporalmente. No dejar archivos
  innecesarios en `htdocs`.

### 13.3. Migraciones (`sql/`)

Las migraciones históricas de versiones anteriores ya no se distribuyen en el repo: el esquema fresco (`sigeho_cont_db.sql`) ya incluye todas estas tablas y columnas. La tabla siguiente queda como referencia de lo aplicado a instalaciones existentes; los archivos se conservan en el historial de Git.

| Archivo | Contenido | Idempotente |
|---|---|---|
| `migracion_recuperacion_tokens.sql` | Crea `recuperacion_tokens` | Sí (`CREATE TABLE IF NOT EXISTS`) |
| `migracion_intentos_ip.sql` | Crea `intentos_ip` | Sí (`CREATE TABLE IF NOT EXISTS`) |
| `migracion_recursos_aulas.sql` | `ALTER TABLE aulas ADD COLUMN recursos` | No: falla si la columna ya existe |
| `migracion_prelaciones.sql` | Crea `materia_prelacion` | No: `CREATE TABLE` sin `IF NOT EXISTS` |
| `migracion_disponibilidad_materias.sql` | Crea `profesor_disponibilidad` y `profesor_materia` | No: `CREATE TABLE` sin `IF NOT EXISTS` |
| `migracion_rol_asistente.sql` | Modifica el ENUM de `usuarios.rol` | Sí en la práctica (`MODIFY COLUMN` puede repetirse) |

Reglas:

- Antes de ejecutar una migración no idempotente, comprobar en
  phpMyAdmin si ya está aplicada. Ejecutarla dos veces produce un error
  (columna o tabla duplicada) y detiene el resto del lote.
- El esquema completo (`sigeho_cont_db.sql`) es solo para instalaciones
  limpias: sus `CREATE TABLE` (salvo las dos últimas tablas) no usan
  `IF NOT EXISTS` y el archivo incluye INSERT de datos semilla.
- Nunca reimportar el esquema completo sobre una base con datos reales.

## 14. Scripts de consola

Todos los scripts son **solo consola**: si se invocan desde la web,
responden 403 con el texto `Solo por consola.` y terminan. Se ejecutan
desde la raíz del proyecto con el PHP del entorno.

### 14.1. `scripts/rotar_master_key.php`

Rota la MASTER_KEY y re-cifra todos los secretos 2FA guardados.

- Lee la clave vieja de `app/config/master_key.php` y la nueva de
  `app/config/master_key_nueva.php` (archivo que el administrador crea
  antes, con la clave nueva).
- `--probar`: modo prueba. Indica cuántos usuarios se migrarían sin
  escribir nada.
- Sin `--probar`: migración real todo o nada. Si algún secreto no se
  puede descifrar con la clave vieja, la migración se cancela y no se
  escribe nada. Si la escritura falla, se revierten los cambios.
- Al terminar con éxito, imprime que se reemplace
  `app/config/master_key.php` por `app/config/master_key_nueva.php` y
  que se reinicie Apache. La clave vieja deja de abrir ningún secreto.
- Los usuarios con 2FA activo pero sin secreto guardado no se tocan y se
  avisan en la salida.
- Procedimiento completo: respaldo de la base, crear la clave nueva,
  `--probar` limpio, migración real, intercambio de archivos, reinicio de
  Apache y prueba de inicio de sesión con 2FA.

### 14.2. `scripts/sugerir_secciones.php`

Sugiere (y opcionalmente crea) secciones según matrícula proyectada.

```
php scripts/sugerir_secciones.php --matricula=N --capacidad=N [--aula=ID] [--aplicar --trayecto=T --trimestre=T --prefijo=P]
```

- `--matricula`: matrícula proyectada (alumnos). Obligatorio.
- `--capacidad`: capacidad por sección. Obligatorio si no se pasa
  `--aula`.
- `--aula`: ID de un aula OPERATIVA; su capacidad se usa como capacidad
  por sección.
- `--aplicar`: crea las secciones sugeridas. Exige `--trayecto`,
  `--trimestre` y `--prefijo` (alfanumérico, máximo 10 caracteres). Los
  códigos se numeran desde 01 saltando los existentes.
- Sin `--aplicar` solo muestra la sugerencia.

### 14.3. `scripts/sugerir_huecos.php`

Sugiere (y opcionalmente crea) huecos libres de horario.

```
php scripts/sugerir_huecos.php --profesor=ID --materia=ID [--seccion=ID] [--duracion=MIN] [--dia=DIA] [--max=N] [--desde=HH:MM] [--hasta=HH:MM] [--aplicar=N]
```

- `--profesor`, `--materia`: obligatorios.
- `--seccion`: opcional para sugerir; obligatorio con `--aplicar` (un
  horario necesita su sección).
- `--duracion`: minutos por clase; por defecto 90.
- `--dia`: día de semana (sin tildes); por defecto todos.
- `--max`: máximo de huecos a mostrar; por defecto 40.
- `--desde`, `--hasta`: ventana de búsqueda; por defecto 07:00 y 19:00.
  Deben ser horas válidas, con fin mayor que inicio y ventana mayor o
  igual que la duración.
- `--aplicar=N`: crea el hueco número N de la lista mostrada, tras
  volver a validar todo. Sin `--aplicar` solo muestra sugerencias.

### 14.4. Limitación conocida: bitácora desde consola

Los scripts no registran en bitácora: `bitacora.usuario_id` es NOT NULL
con clave foránea y en consola no existe usuario de sesión. Las acciones
ejecutadas desde consola (sugerencias aplicadas, rotación) quedan fuera
de la bitácora. Una mejora pendiente es añadir un parámetro de usuario
explícito a los scripts para registrar esas acciones.

## 15. Pruebas

### 15.1. Suite de modelos (`tests/suite.php`)

- Solo consola (web responde 403). Prueba la lógica de los modelos sin
  navegador, contra una base de datos de prueba que la suite **crea y
  recrea en cada corrida**. Nunca apunta a la base de datos real.
- Requisitos: PHP 8 con PDO MySQL, un servidor MySQL/MariaDB accesible
  con un usuario que pueda crear y borrar bases de datos, y el cliente
  `mysql` en el PATH (opcional: sin él, la suite importa el esquema
  desde PHP).
- Configuración por variables de entorno (valores por defecto entre
  paréntesis): `SIGEHO_TEST_HOST` (`127.0.0.1`), `SIGEHO_TEST_DB`
  (`sigeho_tests`), `SIGEHO_TEST_USER` (`root`), `SIGEHO_TEST_PASS`
  (vacía).
- Ejecución:

```bash
php tests/suite.php
```

- La salida lista cada prueba como `PASS` o `FAIL` y termina con el
  resumen de cuántas pasaron. El código de salida es `0` si todas pasan
  y `1` si alguna falla.
- Cobertura: reparto de secciones (matrícula y capacidad, matrícula
  cero), códigos correlativos saltando los existentes, choques de aula,
  profesor y sección, bloque contiguo que no choca, autoexclusión al
  editar, estados y capacidad en `validarRecursos`, huecos sin opt-in y
  días saltados cuando la disponibilidad no los cubre, creación de huecos
  con revalidación y rechazo con choque, y protección de borrados con
  horarios.
- Para agregar una prueba: una llamada a `prueba('nombre', $condicion)`
  al final del archivo, antes del resumen. Cada prueba debe crear sus
  propios datos y no depender del orden de las demás, salvo que el
  nombre lo diga.
- Los flujos completos en navegador (botones de sugerencias, arrastre,
  bitácora, control 403) se probaron con navegador real; la
  automatización de navegador queda como trabajo futuro.

### 15.2. Verificación antes de enviar código

Regla del proyecto: ningún zip ni archivo se entrega sin autoverificación
previa. Dos controles obligatorios:

1. **Sintaxis** de cada archivo modificado:

```bash
php -l app/controllers/EjemploController.php
```

2. **Carga de todas las clases** (detecta conflictos que `php -l` no ve,
   como choques estático/no estático entre un modelo hijo y la clase
   base):

```bash
php -r 'define("APP_PATH", __DIR__ . "/app"); define("BASE_PATH", __DIR__);
foreach (glob("app/core/*.php") as $f) { require_once $f; }
foreach (glob("app/models/*.php") as $f) { require_once $f; }
foreach (glob("app/controllers/*.php") as $f) { require_once $f; }'
```

Un error fatal en esta carga es un error 500 en producción. Las
librerías se cargan indirectamente: `AuthController` incorpora con
`require_once` a `GoogleAuthenticator`, `Correo` y `Turnstile`, y
`UsuarioController` a `GoogleAuthenticator`.

## 16. Mantenimiento y despliegue

### 16.1. Entregas por zip

Las entregas se hacen por zip con la estructura de carpetas del destino
(por ejemplo, `app/controllers/...`, `app/views/auth/...`). En el
administrador de archivos del hosting, el zip se extrae en la raíz de
`htdocs` y los archivos caen en sus carpetas por la estructura interna.
Tras extraer, el zip debe borrarse del servidor (algunos administradores
de archivos lo borran solos al extraer; si no, borrarlo a mano).

### 16.2. Qué NUNCA va en zip, en GitHub ni suelto en `htdocs`

- `app/config/master_key.php` y cualquier respaldo suyo (`.bak`, `.old`,
  copias con otro nombre). Tampoco sus valores en notas, chats o
  documentos.
- Archivos de configuración con valores reales (`correo.php`,
  `turnstile.php`, `database.php` de producción). Esos archivos se crean
  en el servidor, no se entregan.
- Archivos `.zip`, volcados `.sql`, `info.php` o respaldos sueltos en
  `htdocs`: serían descargables por cualquiera.
- La carpeta `tests/` no debe copiarse al servidor de producción: la
  suite borra y recrea su base de datos y no tiene nada que hacer fuera
  del entorno de desarrollo.

### 16.3. Cambios seguros

- Preferir cambios pequeños y reversibles: editar archivos existentes o
  sustituirlos por la misma ruta. No copiar librerías de terceros dentro
  de `htdocs` (los archivos copiados sobreviven a las actualizaciones
  por zip y quedan obsoletos). La excepción aceptada es PHPMailer, ya
  vendorizado en `app/libs/` desde el inicio del proyecto.
- Tras extraer un zip en XAMPP: Stop/Start de Apache (OPcache). Tras
  subir archivos sueltos: sin reinicio. En hosting compartido: sin
  reinicio (los cambios aplican en la siguiente petición).
- Al añadir una acción a un controlador existente no hace falta tocar el
  router; al añadir un controlador nuevo sí (ver capítulo 4.3).
- Al añadir una vista nueva, mantener el hábito de escapar toda salida
  con `htmlspecialchars` y de exigir CSRF en todo POST (el control
  central ya lo hace, pero el campo oculto debe estar en el formulario).
- Al añadir una columna o tabla, actualizar el esquema fresco `sql/sigeho_cont_db.sql` (el repo distribuye solo el esquema completo, no migraciones) y actualizar este manual.

## 17. Solución de problemas técnicos frecuentes

| Síntoma | Causa probable | Acción |
|---|---|---|
| `Error de configuración: falta la clave maestra` | No hay variable de entorno ni `master_key.php` | Crear una de las dos fuentes (ver capítulo 12.2) |
| 403 en todos los POST | Token CSRF ausente o distinto al de sesión (sesión expirada: 15 minutos) | Recargar el formulario para obtener un token vigente y reenviar |
| Redirección a `auth/login?msg=sesion_expirada` | Más de 900 segundos sin actividad | Volver a iniciar sesión; es el comportamiento diseñado |
| `Verificación de seguridad no configurada en el servidor` | Falta `turnstile.php` o su clave secreta | Crear el archivo con la clave secreta (ver capítulo 12.4) |
| `Verificación de seguridad fallida` siempre | Clave pública y secreta de sitios distintos, JavaScript bloqueado o verificador inalcanzable (falla cerrado) | Verificar que ambas claves son del mismo widget, probar con JavaScript activo y revisar `error_log` |
| `Demasiados intentos desde esta dirección. Espera N segundos` | Límite por IP activo | Esperar los segundos indicados; un inicio exitoso limpia el contador; la inactividad de 24 horas también lo limpia |
| `Cuenta bloqueada por seguridad tras 3 intentos fallidos` | Bloqueo permanente por intentos | Un administrador debe desbloquear (`usuarios/desbloquear`) |
| `El enlace es inválido o ha vencido` en restablecer | Token expirado (60 minutos), ya usado, o invalidado por una solicitud posterior | Pedir una recuperación nueva; cada solicitud nueva invalida las anteriores |
| Error de migración `Duplicate column` / `Duplicate table` | La migración ya estaba aplicada | Comprobar en phpMyAdmin antes de ejecutar; no ejecutar dos veces las no idempotentes |
| Página en blanco o 500 tras extraer un zip en XAMPP | OPcache sirve el código viejo, o archivos en carpeta anidada | Stop/Start de Apache; verificar que el contenido quedó directamente en la carpeta, sin subcarpeta duplicada |
| Cambios que no se reflejan tras subir archivos | Caché del navegador (especialmente el favicon de la pestaña) | Recarga fuerte; para el favicon, cerrar y reabrir la pestaña |
| `502 Bad Gateway` en hosting compartido | El proxy del proveedor no obtuvo respuesta del backend | Reintentar: suele ser transitorio del proveedor. Si persiste, verificar archivos, configuración y límites de la cuenta |
| La suite de pruebas falla al conectar | Variables `SIGEHO_TEST_*` incorrectas o usuario sin permiso para crear/borrar bases | Ajustar las variables; usar un usuario con esos permisos |
| El 2FA no muestra la imagen del logo en la app | La app de autenticación ignora el parámetro `image` | Comportamiento esperado y dependiente de la app (el emisor y la cuenta sí se muestran); no es un fallo del sistema |
| Un usuario no ve cambios tras editar su 2FA | Las entradas viejas conservan la etiqueta anterior | Desactivar y reactivar el 2FA para inscribir la entrada nueva |

## 18. Glosario y anexos

### 18.1. Glosario

| Término | Significado en SIGEHO-CONT |
|---|---|
| Choque | Traslape de horario en aula, profesor o sección (ver capítulo 10.1) |
| Bitácora | Registro de acciones con usuario, descripción y fecha (`bitacora`) |
| Log de choques | Registro de intentos fallidos por choque (`log_choques`) |
| Opt-in | Restricción que solo aplica si existen filas en la pivote (disponibilidad, materias) |
| Prelación | Relación de orden del pensum entre materias (`materia_prelacion`); no bloquea horarios |
| Parrilla / cuadrícula | Cuadrícula semanal del panel principal, con segmentos atómicos y `rowspan` |
| Trimestre / lapso | Periodo académico de 12 semanas contado desde `fecha_inicio_lapso` |
| Turno | Franja derivada de la hora de inicio: MATUTINO, VESPERTINO, NOCTURNO |
| MASTER_KEY | Clave de cifrado de los secretos 2FA (entorno o `master_key.php`) |
| TOTP | Contraseña de un solo uso basada en tiempo (código 2FA de 6 dígitos) |
| Turnstile | Verificación anti-bots de Cloudflare (widget en login y recuperación) |
| Front controller | Punto único de entrada (`public/index.php`) que despacha todas las rutas |
| Semilla | Datos de ejemplo incluidos en el esquema para una instalación limpia |

### 18.2. Anexo A — Discrepancias y pendientes verificados contra el código

Esta sección registra lo que el mantenedor debe saber porque difiere de
lo que podría esperarse o de documentos anteriores. Todo fue verificado
en el código el 2026-10-31:

1. **El token CSRF no rota por petición.** El código genera el token una
   vez por sesión y lo reutiliza (`csrf_token()` solo crea si está
   vacío; `public/index.php` verifica pero no regenera). Si se desea
   rotación por petición, es una mejora pendiente, no un comportamiento
   actual.
2. **`app/.htaccess` no está en el repositorio.** Solo existe
   `public/.htaccess`. La denegación total de acceso a `app/` debe
   crearse en cada despliegue donde `app/` quede bajo alcance web.
3. **`README.md` del repositorio está desactualizado.** Menciona 9
   tablas (el esquema tiene 14) y un respaldo que ya no existe en el
   sistema. Este manual es la referencia vigente, no el README.
4. **El aviso de sesión expirada del login (corregido 2026-10-31).** El texto decía 10 minutos y se corrigió ese mismo día; al cambiar el plazo real a 900 segundos (15 minutos), el texto pasó a 15 minutos. Texto y plazo real ahora coinciden.
5. **Bitácora desde consola pendiente.** Los scripts no registran en
   bitácora por la FK NOT NULL de `usuario_id` (ver capítulo 14.4).
6. **El límite por IP es solo de login.** Los formularios de
   recuperación tienen Turnstile pero no límite por IP. Añadirlo es una
   mejora pendiente, no un comportamiento actual.
7. **Diferencia de versiones de Bootstrap.** Login y flujo de
   autenticación usan 5.3.3; páginas internas usan 5.3.0 (ver capítulo
   7.3). Unificar exige probar ambas superficies.
8. **Sesión única no implementada, por decisión.** El sistema permite
   sesiones concurrentes del mismo usuario en varios dispositivos. No
   es un olvido: se evaluó y se descartó para este sistema (personal
   reducido, expiración de 15 minutos y bitácora de inicios como
   control). Si el contexto cambia (cuentas compartidas, datos más
   sensibles), debe diseñarse con una tabla de sesiones y un flujo de
   expulsión con aviso al usuario afectado.

### 18.3. Anexo B — Archivos de referencia rápida

| Necesidad | Archivo |
|---|---|
| Entrada web y sesión | `public/index.php` |
| Rutas y mapa de controladores | `app/core/Router.php` |
| Clase base, permisos, bitácora | `app/core/Controller.php` |
| Conexión, MASTER_KEY, CSRF, cifrado | `app/config/database.php` |
| Esquema completo (instalación limpia) | `sql/sigeho_cont_db.sql` |
| Migraciones históricas | Historial de Git (ver capítulo 13.3) |
| Rotación de clave maestra | `scripts/rotar_master_key.php` |
| Sugerencias por consola | `scripts/sugerir_secciones.php`, `scripts/sugerir_huecos.php` |
| Pruebas de modelos | `tests/suite.php`, `tests/README.md` |
| Verificación previa al envío | `php -l` + carga de todas las clases (capítulo 15.2) |

*Fin del manual.*
