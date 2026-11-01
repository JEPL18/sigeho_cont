**SIGEHO-CONT — Manual de Usuario**

**Sistema Integrado de Gestión de Horarios del Departamento de Contaduría**

## 1. Portada y datos

- **Sistema:** SIGEHO-CONT (Sistema Integrado de Gestión de Horarios).
- **Institución:** Universidad Politécnica Territorial de Falcón "Alonso Gamero" (UPTAG), Departamento de Contaduría.
- **Documento:** Manual de Usuario.
- **Fecha:** 31 de octubre de 2026.
- **Autores:** Juan Montañez, Andrés Moreno y Jesús Peña.
- **Tutor:** Dr. Andys Corona.
- **Licencia:** Esta obra está bajo una [Licencia Creative Commons Atribución-NoComercial-CompartirIgual 4.0 Internacional](https://creativecommons.org/licenses/by-nc-sa/4.0/deed.es) (CC BY-NC-SA 4.0).
- **Dirigido a:** personal de la Coordinación de Contaduría, con roles de Administrador y Asistente. No se requieren conocimientos técnicos para usar el sistema.

## 2. Índice

1. Portada y datos
2. Índice
3. Introducción
    - 3.1 Qué es SIGEHO-CONT
    - 3.2 A quién va dirigido
    - 3.3 Alcance: qué hace y qué no hace
    - 3.4 Cómo leer este manual
4. Requisitos y acceso
    - 4.1 Qué necesita para entrar
    - 4.2 La dirección del sistema y sus credenciales
    - 4.3 Primer acceso y cuenta inicial
5. Inicio de sesión y seguridad
    - 5.1 Iniciar sesión
    - 5.2 La verificación "Verifique que es un ser humano"
    - 5.3 Intentos fallidos, bloqueo y espera
    - 5.4 Autenticación en dos pasos (2FA)
    - 5.5 Recuperar la contraseña por correo
    - 5.6 Recuperar la contraseña con preguntas de seguridad
    - 5.7 La sesión se cierra tras 15 minutos sin actividad
    - 5.8 Cerrar sesión
6. Panel principal
    - 6.1 Indicadores
    - 6.2 Consultar el horario de una sección
    - 6.3 El registro de choques bloqueados
7. Módulos
    - 7.1 Profesores
    - 7.2 Materias (Malla Curricular)
    - 7.3 Secciones
    - 7.4 Aulas y Espacios
    - 7.5 Horarios
    - 7.6 Usuarios
    - 7.7 Configuración
    - 7.8 Bitácora (solo administradores)
    - 7.9 Ayuda
8. Automatización supervisada: las sugerencias
9. Filtros y búsquedas en los listados
10. Solución de problemas frecuentes
11. Buenas prácticas de seguridad para el usuario
12. Glosario y anexos
    - 12.1 Glosario
    - 12.2 Tabla de roles y permisos
    - 12.3 Mapa de menús
    - 12.4 Datos que debe entregarle el administrador

## 3. Introducción

### 3.1 Qué es SIGEHO-CONT

SIGEHO-CONT es el sistema que usa el Departamento de Contaduría para armar y consultar los horarios de clases. En un solo lugar reúne a los profesores, las materias de la malla curricular, las secciones, las aulas y los bloques de horario, y revisa que no existan choques: que un mismo profesor, un aula o una sección no queden asignados a dos clases a la misma hora.

El sistema no decide por usted: cuando algo no se puede hacer, lo explica con un mensaje y no guarda nada. Usted revisa, corrige y vuelve a intentar.

### 3.2 A quién va dirigido

Este manual es para el personal de la Coordinación de Contaduría que trabaja con los horarios:

- **Administrador:** gestiona todo el sistema (crea, edita y elimina registros, configura el sistema y revisa la bitácora).
- **Asistente:** consulta toda la información (listados, panel principal y parrillas de horarios), pero no crea, edita ni elimina registros.

### 3.3 Alcance: qué hace y qué no hace

El sistema sí hace lo siguiente:

- Registrar profesores, materias, secciones, aulas, horarios y usuarios.
- Detectar choques de horario antes de guardar un bloque.
- Respetar la disponibilidad de los profesores y las materias que cada uno dicta, cuando esa información está registrada.
- Sugerir secciones y huecos libres para horarios, siempre con revisión y aprobación de una persona.
- Mostrar la parrilla semanal de horarios por sección y por profesor, e imprimirla o exportarla a Excel.
- Registrar en la bitácora quién hizo cada cambio.

El sistema no hace lo siguiente:

- No gestiona estudiantes individuales ni calificaciones: trabaja con la matrícula (cantidad de alumnos) de cada sección.
- Las prelaciones son un registro del orden del pensum (qué materias deben aprobarse antes que otras). Registrar una prelación no impide crear un horario.
- No asigna aulas automáticamente al sugerir secciones: solo usa la capacidad del aula como referencia para calcular cuántas secciones hacen falta.

### 3.4 Cómo leer este manual

- Los pasos están numerados, en orden. Siga los pasos tal como aparecen.
- Los nombres de botones, menús y campos aparecen en **negrita**.
- Los mensajes que muestra el sistema aparecen entre comillas.
- Las palabras propias del sistema se explican en el glosario (capítulo 12).
- Este manual describe pantallas con texto y pasos. No incluye capturas de pantalla.

## 4. Requisitos y acceso

### 4.1 Qué necesita para entrar

- Una computadora o teléfono con un navegador moderno actualizado: Chrome, Microsoft Edge o Firefox.
- JavaScript activado en el navegador (es necesario para la verificación de seguridad del inicio de sesión).
- Conexión a la red donde está publicado el sistema.
- Su correo institucional y su contraseña, entregados por el administrador.

Para mover bloques de horario arrastrando (capítulo 7.5) necesita una computadora con mouse. En pantallas táctiles, use siempre el formulario de horarios.

### 4.2 La dirección del sistema y sus credenciales

El administrador le entregará la dirección web del sistema, su correo institucional de acceso y su contraseña inicial. Guarde esa dirección en sus favoritos para entrar siempre por el mismo lugar y no confundirla con otras páginas.

Su contraseña debe tener al menos 16 caracteres. Si el administrador le entrega una contraseña inicial, cámbiela cuanto antes (capítulo 7.6).

### 4.3 Primer acceso y cuenta inicial

La instalación del sistema incluye una cuenta administradora inicial, definida en el archivo de datos iniciales de la instalación. Esa cuenta existe solo para poner el sistema en marcha.

Después de crear a los administradores reales, el administrador debe cambiar la clave de esa cuenta inicial o eliminarla. No la use como cuenta de trabajo diario: cada persona debe entrar con su propia cuenta, para que la bitácora registre quién hizo cada cambio.

## 5. Inicio de sesión y seguridad

### 5.1 Iniciar sesión

1. Abra la dirección del sistema en su navegador. Verá la página de acceso con el título **Acceso al Sistema**.
2. Escriba su **Correo Institucional**.
3. Escriba su **Contraseña**.
4. Complete la verificación de seguridad (capítulo 5.2).
5. Pulse el botón **INGRESAR**.

Si los datos son correctos, entrará al Panel Principal. Si algo falla, la página muestra el motivo en un aviso rojo y no entra.

Desde la página de acceso también puede recuperar su contraseña: abajo del botón INGRESAR están los enlaces **¿Olvidaste tu contraseña? (por correo)** y **Recuperar con preguntas de seguridad**.

### 5.2 La verificación "Verifique que es un ser humano"

Antes del botón INGRESAR hay una casilla de verificación de seguridad. Debe completarla en cada inicio de sesión y en cada recuperación de contraseña.

1. Pulse la casilla o siga la instrucción que muestre el recuadro.
2. Espere a que muestre que la verificación fue exitosa.
3. Luego pulse **INGRESAR**.

Si la verificación falla o no se completa, el sistema muestra el mensaje "Verificación de seguridad fallida. Inténtalo de nuevo." y no revisa su contraseña. En ese caso, recargue la página y complete la verificación de nuevo. Esta verificación se hace antes de revisar la contraseña, así que un intento fallido de verificación no consume sus intentos de inicio de sesión.

### 5.3 Intentos fallidos, bloqueo y espera

El sistema protege las cuentas contra intentos repetidos de adivinar la contraseña:

- Si escribe mal la contraseña, verá el mensaje "Contraseña incorrecta. Le quedan X intento(s).", donde X indica cuántos intentos le quedan.
- Al tercer intento fallido, la cuenta se bloquea y verá el mensaje "¡Cuenta bloqueada! Ha excedido los 3 intentos permitidos." o "Cuenta bloqueada por seguridad tras 3 intentos fallidos. Contacte al soporte técnico."
- Una cuenta bloqueada no puede entrar aunque después escriba la contraseña correcta. Solo un administrador puede desbloquearla: en **Gestión de Usuarios**, busca al usuario bloqueado (aparece marcado como **Bloqueado**) y pulsa **Desbloquear**, confirmando la acción. El sistema confirma con el mensaje "El usuario ha sido desbloqueado y sus intentos reiniciados."
- Además, si se hacen muchos intentos fallidos desde la misma conexión, el sistema pedirá esperar unos segundos antes de intentar de nuevo ("Demasiados intentos desde esta dirección. Espera X segundos e inténtalo de nuevo."). Esa espera crece con cada grupo de intentos fallidos, hasta un máximo de 15 minutos. Espere el tiempo indicado y vuelva a intentar.

Consejo: si no recuerda su contraseña, no la adivine varias veces. Use la recuperación por correo o por preguntas (capítulos 5.5 y 5.6) antes de agotar los intentos.

### 5.4 Autenticación en dos pasos (2FA)

La autenticación en dos pasos (2FA) es una capa extra de seguridad: además de su contraseña, el sistema le pedirá un código de 6 dígitos que genera una aplicación de autenticación en su teléfono (por ejemplo Google Authenticator, Authy o Proton Pass). Ese código cambia cada 30 segundos. El 2FA es opcional para cada cuenta, pero se recomienda activarlo.

**Activar el 2FA:**

1. Entre a **Configuración** (menú SISTEMA) y abra la tarjeta **Doble Factor (2FA)**.
2. Pulse **Configurar 2FA**.
3. Abra su aplicación de autenticación y escanee el **Código QR** que muestra la página. Si no puede escanearlo, pulse la opción de clave manual e ingrese en la aplicación la **clave manual** que muestra la página.
4. Escriba en la página el código de 6 dígitos que muestra su aplicación.
5. Pulse el botón para verificar y activar.

Desde ese momento, cada inicio de sesión le pedirá su contraseña y luego el código de 6 dígitos de su aplicación.

**Usar el 2FA al entrar:**

1. Inicie sesión con su correo y contraseña como siempre.
2. En la página de verificación, escriba los 6 dígitos que muestra su aplicación en este momento.
3. Si el código es incorrecto o ya venció, verá el mensaje "Código incorrecto o expirado. Verifique su aplicación." Escriba el código nuevo que muestra la aplicación.

**Desactivar el 2FA:**

1. Entre a **Configuración** y abra la tarjeta **Doble Factor (2FA)**.
2. Pulse **Administrar 2FA**.
3. Escriba su código 2FA actual y confirme la desactivación cuando el sistema lo pregunte.

Nota: la página de configuración del 2FA solo exige haber iniciado sesión. Si su cuenta es de Asistente y no ve **Configuración** en su menú, puede abrir la página directamente en la dirección del sistema seguida de `/auth/configurar2fa`, o pedirle al administrador que le indique el acceso.

### 5.5 Recuperar la contraseña por correo

Use esta opción si tiene acceso al correo institucional registrado en su cuenta.

1. En la página de acceso, pulse **¿Olvidaste tu contraseña? (por correo)**.
2. Escriba su **Correo Institucional** y complete la verificación de seguridad.
3. Pulse **Enviar Enlace**.
4. El sistema muestra siempre el mismo mensaje: "Si el correo está registrado, recibirás un enlace para restablecer tu contraseña." Este mensaje es igual exista o no la cuenta, para no revelar qué correos están registrados.
5. Revise su correo: llegará un mensaje de SIGEHO-CONT con el asunto de recuperación, cabecera roja del sistema y un botón **Restablecer contraseña**. Si el botón no funciona, el mensaje también incluye el enlace en texto para copiarlo y pegarlo en el navegador.
6. El enlace es válido por **60 minutos** y solo puede usarse **una vez**. Si ya venció o ya se usó, verá el mensaje "El enlace es inválido o ha vencido." y deberá pedir un enlace nuevo.
7. En la página **Restablecer Contraseña**, escriba su **Nueva Contraseña** y su **Confirmar Contraseña**. Ambas deben tener al menos 16 caracteres y ser iguales.
8. Pulse **Guardar Nueva Contraseña**. Volverá a la página de acceso con el mensaje "¡Clave restablecida! Por favor, inicia sesión con tu nueva contraseña."

Si el correo no llega: revise la carpeta de correo no deseado (spam), espere unos minutos y confirme que escribió el correo registrado en el sistema. Si sigue sin llegar, pida ayuda al administrador.

Importante: la recuperación por correo no pide el código 2FA. Por eso la seguridad de su correo es fundamental: quien controle ese correo podría restablecer su contraseña (vea el capítulo 11).

### 5.6 Recuperar la contraseña con preguntas de seguridad

Use esta opción si registró sus respuestas de seguridad al crear su cuenta. Son tres preguntas fijas:

- ¿Fecha de cumpleaños?
- ¿Nombre del colegio donde estudiaste?
- ¿Ciudad donde naciste?

Pasos:

1. En la página de acceso, pulse **Recuperar con preguntas de seguridad**.
2. **Paso 1:** escriba su **Correo Institucional** registrado, complete la verificación de seguridad y pulse **Verificar Correo**.
3. **Paso 2:** responda las tres preguntas de seguridad y pulse **Validar Respuestas**. Las respuestas deben escribirse exactamente como se registraron (revise la ortografía). Si son incorrectas, verá el mensaje "Respuestas incorrectas. Verifique la ortografía e intente nuevamente."
4. **Paso 3:** escriba su **Nueva Contraseña** (mínimo 16 caracteres) y guárdela.
5. Vuelva a la página de acceso e inicie sesión con su nueva contraseña.

Si no recuerda sus respuestas de seguridad, pida al administrador que le restablezca la contraseña desde **Gestión de Usuarios**.

### 5.7 La sesión se cierra tras 15 minutos sin actividad

Por seguridad, si usted no usa el sistema durante 15 minutos, su sesión se cierra sola y deberá iniciar sesión de nuevo. Verá un aviso de sesión expirada en la página de acceso.

Consejos:

- Guarde su trabajo antes de ausentarse.
- Si está llenando un formulario largo y pasa más de 15 minutos sin guardar, al guardar se le pedirá iniciar sesión de nuevo. En ese caso, inicie sesión y vuelva a cargar el formulario.

### 5.8 Cerrar sesión

Para terminar, pulse **Cerrar Sesión** en el menú lateral o en el menú de su nombre, en la parte superior. No basta con cerrar la pestaña del navegador: si solo cierra la pestaña, la sesión queda abierta hasta que pasen los 15 minutos sin actividad. Use siempre el botón **Cerrar Sesión**, sobre todo en computadoras compartidas.

## 6. Panel principal

Al iniciar sesión verá el **Panel Principal**. Es la pantalla de consulta de horarios y el punto de partida del sistema.

### 6.1 Indicadores

En la parte superior hay cuatro tarjetas con los números actuales del sistema:

- **Docentes activos:** cantidad de profesores con estado ACTIVO.
- **Secciones registradas:** cantidad de secciones, con su matrícula.
- **Aulas operativas:** cantidad de aulas con estado OPERATIVA.
- **Choques detectados:** cantidad de intentos de horario bloqueados por choque, con el botón **Ver Log** (capítulo 6.3).

### 6.2 Consultar el horario de una sección

1. En el Panel Principal, busque el cuadro **Consultar Horario de la Sección**.
2. Seleccione la sección en la lista.
3. Seleccione el **Turno** (Matutino, Vespertino o Nocturno).
4. Se mostrará la parrilla semanal de esa sección, de lunes a sábado, con los bloques de cada día: materia, profesor, aula y hora.

Los turnos se definen por la hora de inicio del bloque:

- **Matutino:** bloques que inician antes de las 12:00.
- **Vespertino:** bloques que inician desde las 12:00 y antes de las 18:00.
- **Nocturno:** bloques que inician desde las 18:00.

Encima de la parrilla están los botones **Imprimir / Guardar PDF** y **Exportar Excel**:

- **Imprimir / Guardar PDF** abre el diálogo de impresión del navegador. Para guardar en PDF, elija la opción de guardar como PDF que ofrece su navegador.
- **Exportar Excel** descarga la parrilla como un archivo de Excel.

El sistema también calcula la semana actual del trimestre a partir de la fecha de inicio del lapso que el administrador registra en **Configuración**. El trimestre tiene 12 semanas; pasada la semana 12, el sistema indica "Trimestre Finalizado".

### 6.3 El registro de choques bloqueados

Cuando alguien intenta guardar un horario que choca con otro, el sistema no lo guarda y anota el intento en el registro de choques. Para revisarlo:

1. En la tarjeta **Choques detectados**, pulse **Ver Log**.
2. Se abre la ventana **Auditoría: Log de Choques Bloqueados**, con los intentos bloqueados y su detalle.
3. Para cerrar, pulse **Cerrar Registro**.

El botón **Vaciar Historial** borra todo el registro y solo está disponible para el administrador. Antes de borrar, el sistema pide confirmación porque la acción no se puede deshacer.

## 7. Módulos

Todos los módulos se usan de la misma forma: una lista con los registros, filtros en la parte superior, botones para agregar y, en cada fila, acciones para editar o eliminar (solo para administradores). Los asistentes pueden ver las listas y las parrillas, pero no ven ni pueden usar los botones de crear, editar o eliminar. Si un asistente intenta abrir una acción de escritura, el sistema muestra la página **Acceso denegado (403)**, que indica que esa función es solo para administradores.

En todos los formularios, los campos marcados son obligatorios: si falta alguno, el sistema muestra el mensaje "Todos los campos son obligatorios." y no guarda.

### 7.1 Profesores

El menú **Profesores** muestra la lista de docentes con su cédula, nombre completo, estado, disponibilidad y materias que dictan.

**Registrar un profesor (administrador):**

1. Pulse **Nuevo Profesor**.
2. Escriba la **Cédula de Identidad** (solo números).
3. Escriba el **Nombre Completo**.
4. Seleccione el **Estado en la Institución**: ACTIVO, o INACTIVO (Reposo/Jubilado).
5. Si lo desea, marque la **Disponibilidad**: una tabla de días (lunes a sábado) por turnos (Matutino, Vespertino, Nocturno). Si no marca nada, no se restringe: el profesor podrá recibir horarios en cualquier día y turno.
6. Si lo desea, marque las **Materias que dicta**. Si no marca nada, no se restringe: el profesor podrá recibir horarios de cualquier materia.
7. Pulse **Guardar**.

**Editar un profesor (administrador):** en la lista, pulse **Editar** en la fila del profesor, cambie los datos y pulse **Guardar Cambios**.

**Eliminar un profesor (administrador):** en la lista, pulse **Eliminar** en la fila y confirme. No se puede eliminar un profesor que tenga bloques de horario asignados: primero deben eliminarse esos bloques.

**Reglas que aplican al crear horarios:**

- Solo los profesores con estado ACTIVO pueden recibir bloques nuevos. Si el profesor no está ACTIVO, verá el mensaje "El docente seleccionado no está ACTIVO."
- Si el profesor tiene disponibilidad registrada y el bloque cae en un día o turno no marcado, verá el mensaje "El docente no tiene disponibilidad el [día] en el turno [turno]."
- Si el profesor tiene materias registradas y el bloque es de una materia que no dicta, verá el mensaje "El docente no dicta esa materia."
- Si el profesor no tiene disponibilidad ni materias registradas, esas dos reglas no se aplican.

### 7.2 Materias (Malla Curricular)

El menú **Malla Curricular** muestra las materias con su código, nombre, trayecto, horas semanales y prelaciones.

**Registrar una materia (administrador):**

1. Pulse **Registrar Materia**.
2. Escriba el **Código Oficial** (por ejemplo, CON-221). El código no puede repetirse: si ya existe, verá el mensaje "Ya existe una materia registrada con ese código."
3. Escriba el **Nombre de la Unidad Curricular**.
4. Seleccione el **Trayecto**.
5. Escriba las **Horas Semanales**.
6. Si corresponde, marque las **Prelaciones**: las materias que deben aprobarse antes que esta. Al editar, la propia materia no aparece en la lista, así que no puede ser prelación de sí misma.
7. Pulse **Guardar**.

**Editar una materia (administrador):** en la lista, pulse **Editar**, cambie los datos y pulse **Guardar Cambios**.

**Eliminar una materia (administrador):** en la lista, pulse **Eliminar** y confirme. No se puede eliminar una materia que tenga bloques de horario asignados.

**Reglas de las prelaciones:**

- El sistema no permite que las prelaciones formen un ciclo (por ejemplo, que A pida B y B pida A). Si intenta guardar un ciclo, verá el mensaje "Las prelaciones forman un ciclo. Revisa la cadena de materias."
- Las prelaciones son solo un registro del orden del pensum: no impiden crear un horario. El sistema no bloquea un bloque porque la prelación no esté aprobada.

### 7.3 Secciones

El menú **Secciones** muestra las secciones con su código, trayecto, trimestre y matrícula (cantidad de alumnos).

**Registrar una sección (administrador):**

1. Pulse **Registrar Sección**.
2. Escriba el **Código de Sección** (por ejemplo, SECC-34). El código no puede repetirse.
3. Seleccione el **Trayecto**.
4. Escriba el **Trimestre**.
5. Escriba la **Matrícula** (cantidad de alumnos).
6. Pulse **Guardar**.

**Editar una sección (administrador):** pulse **Editar** en la fila, cambie los datos y pulse **Guardar Cambios**.

**Eliminar una sección (administrador):** pulse **Eliminar** y confirme. Si la sección tiene bloques de horario asignados, primero deben eliminarse esos bloques.

**Sugerir la cantidad de secciones (administrador):**

Si conoce la matrícula proyectada (cuántos estudiantes se esperan en total) y la capacidad por sección, el sistema le sugiere cuántas secciones hacen falta:

1. En **Secciones**, pulse **Sugerir Secciones**.
2. Escriba la **Matrícula proyectada (alumnos)**.
3. Escriba la **Capacidad por sección (alumnos)**, o elija un aula en **O usa la capacidad de un aula** para tomar su capacidad como referencia. Elegir un aula aquí no asigna esa aula a las secciones: solo presta su capacidad para el cálculo.
4. Pulse **Sugerir Secciones**. El sistema calcula cuántas secciones hacen falta dividiendo la matrícula proyectada entre la capacidad por sección.
5. Revise la sugerencia. Si está de acuerdo, complete el **Trayecto**, el **Trimestre** y el **Prefijo de los códigos** (máximo 10 caracteres, solo letras y números; por ejemplo, SECC).
6. Pulse **Crear las Secciones Sugeridas**. El sistema crea las secciones con códigos formados por el prefijo y un número.
7. Si quiere otro cálculo, pulse **Calcular otra sugerencia**.

La sugerencia no crea nada por sí sola: solo se crean secciones cuando usted pulsa **Crear las Secciones Sugeridas** (vea el capítulo 8).

### 7.4 Aulas y Espacios

El menú **Aulas y Espacios** muestra las aulas con su código, capacidad, recursos y estado.

**Registrar un aula (administrador):**

1. Pulse **Registrar Aula**.
2. Escriba el **Código del Espacio** (por ejemplo, M-110).
3. Escriba la **Capacidad (Número de Alumnos)**.
4. Seleccione el **Estado**: OPERATIVA, o INOPERATIVA (en mantenimiento).
5. Si lo desea, abra **Opciones avanzadas** y escriba los **Recursos** del aula: una lista de texto libre, separada por comas, con un máximo de 15 recursos (por ejemplo: proyector, pizarra, aire acondicionado). Los recursos son solo información descriptiva: no cambian la capacidad del aula ni se usan para asignar horarios.
6. Pulse **Guardar**.

**Editar un aula (administrador):** pulse **Editar** en la fila, cambie los datos y pulse **Guardar Cambios**.

**Eliminar un aula (administrador):** pulse **Eliminar** y confirme. Si el aula tiene bloques de horario asignados, primero deben eliminarse esos bloques.

**Reglas que aplican al crear horarios:**

- Solo las aulas con estado OPERATIVA pueden recibir bloques nuevos. Si el aula no está OPERATIVA, verá el mensaje "El aula seleccionada no está OPERATIVA."
- La matrícula de la sección no puede superar la capacidad del aula. Si la supera, verá el mensaje "Capacidad excedida: El aula (X alumnos) no soporta la matrícula (Y alumnos)."

### 7.5 Horarios

El menú **Horarios** es el centro del trabajo diario. Muestra los bloques de horario en una tabla, con filtros por sección, día y turno, y los botones **Por Profesor**, **Sugerir Huecos** y **Asignar Nuevo Bloque**. Encima de la tabla están los botones **Imprimir / Guardar PDF** y **Exportar Excel**, que funcionan igual que en el Panel Principal.

**Crear un bloque de horario (administrador):**

1. Pulse **Asignar Nuevo Bloque**.
2. Seleccione la **Sección**.
3. Seleccione la **Materia**.
4. Seleccione el **Profesor**. Al crear, la lista solo muestra profesores ACTIVOS.
5. Seleccione el **Aula**. Al crear, la lista solo muestra aulas OPERATIVAS.
6. Seleccione el **Día** (lunes a sábado).
7. Escriba la **Hora Inicio** y la **Hora Fin**.
8. Pulse **Asignar Bloque**.

Antes de guardar, el sistema revisa todo, en este orden:

1. Que todos los campos estén completos.
2. Que la hora de inicio sea anterior a la hora de fin. Si no, verá el mensaje "La hora de inicio debe ser anterior a la hora de fin." La página también le avisa en el navegador si la hora de fin no es posterior a la de inicio, pero el servidor siempre tiene la última palabra.
3. Que el profesor esté ACTIVO y el aula esté OPERATIVA.
4. Que la matrícula de la sección quepa en la capacidad del aula.
5. Que el profesor tenga disponibilidad ese día y turno, si tiene disponibilidad registrada.
6. Que el profesor dicte esa materia, si tiene materias registradas.
7. Que no exista un choque: que el profesor, el aula o la sección no tengan otro bloque que se solape ese día a esa hora. Dos bloques seguidos, sin solaparse, sí se permiten.

Si alguna revisión falla, el sistema muestra el motivo, no guarda nada y, si fue un choque, lo anota en el registro de choques (capítulo 6.3).

**Editar un bloque (administrador):** en la lista, pulse **Editar**, cambie los datos y pulse **Actualizar Bloque**. Se aplican las mismas revisiones, sin contar el propio bloque como choque de sí mismo.

**Eliminar un bloque (administrador):** en la lista, pulse **Eliminar** y confirme la pregunta "¿Borrar este bloque?".

**Ver horarios por profesor:**

1. En **Horarios**, pulse **Por Profesor**.
2. Seleccione el **Profesor** y, si lo desea, el **Turno** (Todos, Matutino, Vespertino o Nocturno).
3. Se mostrará la parrilla semanal de ese profesor, con los botones **Imprimir / Guardar PDF** y **Exportar Excel**.

**Sugerir huecos libres (administrador):**

Si necesita ubicar una clase y no sabe dónde hay espacio, el sistema busca huecos libres:

1. En **Horarios**, pulse **Sugerir Huecos**.
2. Seleccione el **Profesor**, la **Materia** y la **Sección** (los tres son obligatorios).
3. Escriba la **Duración (minutos)** del bloque.
4. Si lo desea, filtre por **Día** y por un rango **Desde** y **Hasta**.
5. Pulse **Sugerir Huecos**. El sistema muestra los huecos libres que cumplen con la duración, la disponibilidad del profesor y las reglas de choques, con su día, hora y aula propuesta.
6. En el hueco que le convenga, pulse **Crear Horario**. El sistema vuelve a validar todo antes de crear el bloque. Si algo cambió y ya no es válido, no lo crea y muestra el motivo.

**Arrastrar y soltar un bloque (administrador, solo con mouse):**

En la parrilla del Panel Principal, el administrador puede mover un bloque arrastrándolo con el mouse a otra celda (otro día u otra hora). El movimiento solo cambia el día y la hora: la materia, el profesor, el aula y la sección quedan iguales.

1. Busque el bloque en la parrilla.
2. Arrástrelo con el mouse hasta la celda de destino y suéltelo.
3. El sistema vuelve a validar disponibilidad, capacidad y choques. Si todo está bien, el bloque queda movido. Si hay un choque o alguna regla falla, el bloque no se mueve y verá el motivo.

Esta función solo está disponible para administradores y solo con mouse en computadora. En pantallas táctiles, o si prefiere el control total, use el formulario de editar el bloque.

### 7.6 Usuarios

El menú **Gestión de Usuarios** (menú SISTEMA, solo administradores) muestra los usuarios con su nombre, correo de acceso, nivel de privilegios y estado (incluye la marca **Bloqueado** cuando la cuenta está bloqueada por intentos fallidos).

**Registrar un usuario (administrador):**

1. Pulse **Registrar Usuario**.
2. Escriba el **Nombre del Personal**.
3. Escriba el **Correo Institucional (Acceso)**. No puede repetirse: si ya está en otra cuenta, verá el mensaje "Este correo ya está registrado en otra cuenta."
4. Seleccione el **Nivel de Permisos**: **Asistente (Solo Lectura)** o **Administrador (Control Total)**.
5. Escriba la **Contraseña de Acceso** (mínimo 16 caracteres).
6. Complete las tres **respuestas de seguridad** (fecha de cumpleaños, nombre del colegio donde estudió y ciudad donde nació). Son las que usará la recuperación por preguntas (capítulo 5.6). Deben ser respuestas que el usuario recuerde y escriba siempre igual.
7. Pulse **Guardar Usuario**.

**Editar un usuario (administrador):**

1. En la lista, pulse **Editar**.
2. Cambie el nombre, el correo o el nivel de permisos. Para cambiar la contraseña, escriba la **Nueva Contraseña** (mínimo 16 caracteres); si la deja en blanco, la contraseña no cambia. Para cambiar las respuestas de seguridad, escriba las nuevas.
3. Pulse **Guardar Cambios**.

Reglas al editar:

- No puede quitarse los permisos de administrador a usted mismo: verá el mensaje "No puedes quitarte los permisos de administrador a ti mismo."
- El correo no puede quedar en uso por otra persona: verá el mensaje "El correo ya está en uso por otra persona."
- Si el administrador tiene el 2FA activo, los cambios sensibles (contraseña y respuestas de seguridad) le pedirán su código 2FA para confirmarse.

**Eliminar un usuario (administrador):**

1. En la lista, pulse **Eliminar** en la fila del usuario.
2. Escriba su propia contraseña de administrador y, si tiene el 2FA activo, su código 2FA. Sin esa confirmación, la operación se cancela ("Contraseña de administrador incorrecta. Operación cancelada." o "Código 2FA incorrecto o expirado.").
3. Confirme la eliminación.

No puede eliminarse a usted mismo, y no se puede eliminar un usuario con registros asociados en el sistema.

**Desbloquear un usuario (administrador):**

Si un usuario aparece marcado como **Bloqueado** por intentos fallidos, pulse **Desbloquear** en su fila y confirme la pregunta. El sistema confirma con el mensaje "El usuario ha sido desbloqueado y sus intentos reiniciados."

### 7.7 Configuración

El menú **Configuración** (menú SISTEMA, solo administradores) tiene dos tarjetas:

- **Lapso Académico:** permite registrar la **Fecha de Inicio del Lapso Actual**. El sistema calcula automáticamente la semana actual del trimestre a partir de esa fecha (semana 1 a 12; después muestra "Trimestre Finalizado"). Si la fecha está mal, la semana mostrada estará mal: corríjala aquí.
- **Doble Factor (2FA):** abre la administración del 2FA de su propia cuenta (capítulo 5.4). El botón dice **Configurar 2FA** si aún no lo activó, o **Administrar 2FA** si ya está activado.

### 7.8 Bitácora (solo administradores)

El menú **Bitácora** (menú SISTEMA, solo administradores) muestra los últimos 100 registros del sistema, en una tabla con **Fecha y Hora**, **Usuario** y **Acción**.

La bitácora registra quién hizo qué: creaciones, ediciones y eliminaciones en los seis módulos (profesores, materias, secciones, aulas, horarios y usuarios), sugerencias aplicadas, bloques movidos con arrastrar y soltar, e inicios de sesión. No registra contraseñas, códigos 2FA ni secretos de ningún tipo.

El botón **Vaciar Bitácora** borra todos los registros. Antes de borrar, el sistema pide confirmación ("¿Vaciar toda la bitácora? Esta acción no se puede deshacer.") porque no hay forma de recuperarlos.

### 7.9 Ayuda

El menú **Ayuda** abre una página con explicaciones organizadas en secciones plegables. Pulse cada título para abrir o cerrar su contenido:

- Panel Principal
- Profesores
- Malla Curricular y prelaciones
- Aulas y Espacios
- Secciones
- Horarios
- Usuarios, roles y seguridad
- Mensajes del sistema

La página de Ayuda está enlazada en el menú SISTEMA (administradores). Si su cuenta es de Asistente, puede abrirla directamente en la dirección del sistema seguida de `/ayuda`.

## 8. Automatización supervisada: las sugerencias

Las sugerencias son la automatización del sistema, y funcionan con una regla fija: **el humano decide, el sistema ejecuta**.

Hay dos sugerencias:

- **Sugerir Secciones** (capítulo 7.3): propone cuántas secciones hacen falta según la matrícula proyectada y la capacidad por sección.
- **Sugerir Huecos** (capítulo 7.5): propone espacios libres para ubicar una clase, según el profesor, la materia, la sección y la duración.

En ambos casos, el proceso es el mismo:

1. Usted escribe los datos y pide la sugerencia.
2. El sistema muestra la propuesta, sin crear nada todavía.
3. Usted la revisa. Si no le convence, puede pedir otro cálculo o no hacer nada.
4. Solo cuando usted pulsa el botón de crear (**Crear las Secciones Sugeridas** o **Crear Horario**), el sistema vuelve a validar todo (disponibilidad, capacidad, choques y demás reglas) y, si todo está bien, crea los registros.
5. Si en esa última validación algo ya no es válido, el sistema no crea nada y muestra el motivo.

Ninguna sugerencia crea registros por sí sola, y ninguna pasa por encima de las reglas de choques, capacidad o disponibilidad. Usted siempre tiene la última palabra.

## 9. Filtros y búsquedas en los listados

Cada listado tiene filtros en la parte superior, en forma de listas desplegables. Al cambiar un filtro, la lista se actualiza de inmediato. El botón **Limpiar** o **Limpiar filtros** quita los filtros y vuelve a mostrar todos los registros.

Los filtros disponibles en cada módulo son:

| Módulo | Filtros |
|---|---|
| Profesores | Estado (ACTIVO / INACTIVO) |
| Materias (Malla Curricular) | Trayecto |
| Secciones | Trayecto, Trimestre |
| Aulas y Espacios | Estado (OPERATIVA / INOPERATIVA) |
| Horarios | Sección, Día, Turno |
| Usuarios | Rol (Administrador / Asistente) |

No hay búsqueda por texto libre: para encontrar un registro, use los filtros de su módulo. Si no aparece lo que busca, pulse **Limpiar filtros** y revise la lista completa: puede estar viendo un filtro aplicado de antes.

## 10. Solución de problemas frecuentes

**La sesión expiró y me pide entrar de nuevo.**

Es el comportamiento normal: la sesión se cierra tras 15 minutos sin actividad (capítulo 5.7). Inicie sesión de nuevo. Si estaba llenando un formulario, deberá cargarlo otra vez. Guarde con frecuencia y no deje formularios abiertos sin usar.

**El código 2FA me dice incorrecto o expirado.**

El código cambia cada 30 segundos: use el código que muestra su aplicación en este momento, no uno anterior. Escriba solo los 6 dígitos, sin espacios. Si el problema persiste, revise que la hora de su teléfono esté en automático (si la hora del teléfono está mal, los códigos no coinciden) y vuelva a intentar con el código nuevo. Si perdió el acceso a su aplicación, pida al administrador que le ayude a recuperar su cuenta.

**El correo de recuperación no llega.**

Revise la carpeta de correo no deseado (spam) y espere unos minutos. Confirme que escribió el correo institucional registrado en el sistema. Recuerde que el sistema muestra siempre el mismo mensaje, exista o no la cuenta, así que ese mensaje no confirma que el correo se haya enviado. Si después de revisar sigue sin llegar, avise al administrador.

**Mi cuenta está bloqueada.**

Después de 3 intentos fallidos con la contraseña incorrecta, la cuenta se bloquea (capítulo 5.3). No siga intentando: pida a un administrador que la desbloquee desde **Gestión de Usuarios**. Mientras esté bloqueada, ni la contraseña correcta ni la recuperación por preguntas la desbloquearán por sí solas.

**La verificación "Verifique que es un ser humano" falla o no carga.**

Complete la casilla antes de pulsar INGRESAR o Enviar. Si el recuadro no aparece, revise que JavaScript esté activado, recargue la página y vuelva a intentar. Si el sistema indica que la verificación no está configurada en el servidor, avise al administrador: esa parte solo la puede resolver quien administra el sistema.

**Quiero guardar un horario y me dice que hay un choque.**

Lea el mensaje completo: indica qué choca (profesor, aula o sección) y en qué día y hora. Cambie el día, la hora, el aula o el profesor, y vuelva a intentar. Recuerde que dos bloques seguidos sin solaparse sí se permiten; el problema es solo cuando se solapan.

**Me dice "Capacidad excedida".**

La matrícula de la sección es mayor que la capacidad del aula elegida. Elija un aula con más capacidad, o divida la sección en secciones más pequeñas (capítulo 7.3).

**Me dice que el docente no tiene disponibilidad o no dicta esa materia.**

El profesor tiene disponibilidad o materias registradas, y el bloque cae fuera de ellas. Revise los datos del profesor en **Profesores** (disponibilidad y materias que dicta) o cambie el profesor, el día o el turno del bloque. Si el profesor no tiene nada registrado en esos campos, esas reglas no se aplican.

**No veo los botones para crear, editar o eliminar.**

Su cuenta es de Asistente (solo lectura). Los botones de escritura solo están disponibles para administradores. Si necesita hacer un cambio, pídaselo a un administrador.

**Veo la página "Acceso denegado (403)".**

Intentó abrir una función solo para administradores con una cuenta de Asistente. Pulse **Volver al dashboard** y siga consultando, o pida a un administrador que realice la acción.

## 11. Buenas prácticas de seguridad para el usuario

- Use una contraseña larga y única para el sistema (mínimo 16 caracteres). No la comparta con nadie, ni siquiera con compañeros de trabajo: cada persona debe tener su propia cuenta.
- Active el 2FA en su cuenta (capítulo 5.4). Es la protección más fuerte si alguien llegara a conocer su contraseña.
- Cierre sesión siempre con el botón **Cerrar Sesión**, sobre todo en computadoras compartidas.
- Cuide su correo institucional: la recuperación por correo no pide el código 2FA, así que quien controle su correo podría restablecer su contraseña.
- No comparta sus respuestas de seguridad: son la llave de la recuperación por preguntas.
- Si recibe un correo de recuperación que usted no pidió, ignórelo: su contraseña no se cambia si usted no abre el enlace ni completa el proceso. Avise al administrador si le preocupa.
- Entre siempre por la dirección oficial que le entregó la coordinación. Desconfíe de enlaces que lleguen por mensajes no solicitados y que pidan su contraseña.
- No deje su sesión abierta ni su computadora desbloqueada si se ausenta: recuerde que la sesión se cierra sola a los 15 minutos sin actividad, pero en ese lapso cualquiera podría usarla.

## 12. Glosario y anexos

### 12.1 Glosario

- **Aula (o espacio):** salón o espacio físico donde se dicta una clase. Tiene un código, una capacidad y un estado (OPERATIVA o INOPERATIVA).
- **Bitácora:** registro de quién hizo qué en el sistema y cuándo. Solo la consultan los administradores.
- **Bloque:** una clase en la parrilla: una sección, una materia, un profesor, un aula, un día y un rango de horas.
- **Capacidad:** cantidad de alumnos que caben en un aula.
- **Choque:** cuando un mismo profesor, aula o sección queda asignado a dos bloques que se solapan en día y hora. El sistema bloquea los choques antes de guardar.
- **Disponibilidad:** días y turnos en los que un profesor puede dictar clases. Si no se registra nada, no restringe.
- **Horario:** conjunto de bloques que forman la programación de clases.
- **Lapso:** el trimestre académico en curso. El sistema calcula la semana actual a partir de su fecha de inicio.
- **Malla curricular:** el conjunto de materias del plan de estudios, organizadas por trayecto.
- **Matrícula:** cantidad de alumnos de una sección (proyectada o real).
- **Parrilla:** la tabla semanal de horarios, de lunes a sábado, por sección o por profesor.
- **Prelación:** materia que debe aprobarse antes que otra, según el orden del pensum. Es solo un registro: no bloquea la creación de horarios.
- **Recursos (de aula):** texto libre que describe con qué cuenta un aula (por ejemplo, proyector). No afecta la capacidad ni la asignación de horarios.
- **Rol:** nivel de permisos de una cuenta: Administrador o Asistente.
- **Sección:** grupo de alumnos que cursa junto (por ejemplo, SECC-34), con su trayecto, trimestre y matrícula.
- **Trayecto:** etapa del plan de estudios a la que pertenece una materia o sección.
- **Trimestre:** período académico al que pertenece una sección. El trimestre tiene 12 semanas.
- **Turno:** parte del día según la hora de inicio del bloque: Matutino (antes de las 12:00), Vespertino (de 12:00 a 18:00) y Nocturno (desde las 18:00).
- **2FA (autenticación en dos pasos):** código de 6 dígitos de una aplicación de autenticación, que se pide además de la contraseña.

### 12.2 Tabla de roles y permisos

| Función | Administrador | Asistente |
|---|---|---|
| Ver listados, panel principal y parrillas | Sí | Sí |
| Imprimir parrillas y exportar a Excel | Sí | Sí |
| Consultar la página de Ayuda | Sí | Sí |
| Crear, editar y eliminar profesores | Sí | No |
| Crear, editar y eliminar materias | Sí | No |
| Crear, editar y eliminar secciones | Sí | No |
| Crear, editar y eliminar aulas | Sí | No |
| Crear, editar y eliminar horarios | Sí | No |
| Usar sugerencias (secciones y huecos) | Sí | No |
| Mover bloques arrastrando | Sí | No |
| Crear, editar, eliminar y desbloquear usuarios | Sí | No |
| Cambiar la fecha de inicio del lapso | Sí | No |
| Administrar su propio 2FA | Sí | Sí |
| Ver y vaciar la Bitácora | Sí | No |

Notas:

- Las opciones de **Gestión de Usuarios**, **Configuración** y **Bitácora** aparecen en el menú SISTEMA solo para administradores. Un asistente que intente abrirlas verá la página de acceso denegado (403).
- El 2FA se administra por cuenta: cada usuario gestiona el suyo desde **Configuración** (capítulo 5.4).
- Los asistentes ven los mismos listados y parrillas que los administradores; la única diferencia es que no pueden modificar nada.

### 12.3 Mapa de menús

Menú lateral (siempre visible al iniciar sesión):

- **Panel Principal:** indicadores, consulta de horario por sección y registro de choques.
- **Horarios:** tabla de bloques, filtros, vista por profesor, sugerir huecos y asignar bloques.
- **GESTIÓN ACADÉMICA**
  - **Profesores:** lista y registro de docentes.
  - **Malla Curricular:** lista y registro de materias y prelaciones.
  - **Aulas y Espacios:** lista y registro de aulas y recursos.
  - **Secciones:** lista y registro de secciones, y sugerir cantidad.
- **SISTEMA** (solo administradores)
  - **Gestión de Usuarios:** cuentas, roles, bloqueos y eliminaciones.
  - **Configuración:** fecha de inicio del lapso y 2FA.
  - **Bitácora:** registro de acciones del sistema.
  - **Ayuda:** explicaciones por tema.
- **Cerrar Sesión:** cierra su sesión.

Menú de usuario (parte superior, al pulsar su nombre):

- **Configuración:** abre la página de Configuración.
- **Cerrar Sesión:** cierra su sesión.

Páginas sin enlace en el menú (se abren desde enlaces de otras pantallas):

- **Recuperar por Correo** y **Recuperar con preguntas de seguridad:** desde la página de acceso.
- **Restablecer Contraseña:** desde el enlace que llega por correo.
- **Verificación de 2 Pasos:** después de iniciar sesión, si su cuenta tiene el 2FA activo.
- **Horarios por Profesor:** desde el botón **Por Profesor** en Horarios.
- **Sugerir Huecos:** desde el botón **Sugerir Huecos** en Horarios.
- **Sugerir Secciones:** desde el botón **Sugerir Secciones** en Secciones.

### 12.4 Datos que debe entregarle el administrador

Al entregarle acceso al sistema, el administrador debe darle:

- La dirección web del sistema.
- Su correo institucional de acceso.
- Su contraseña inicial (que usted debe cambiar cuanto antes).
- La confirmación de si su cuenta es de Administrador o de Asistente.
- La fecha de inicio del lapso, si usted es administrador y debe registrarla en **Configuración**.

Si le falta alguno de estos datos, pídalo al administrador antes de intentar entrar: el sistema no puede crearle acceso por sí solo.

---

*Fin del Manual de Usuario de SIGEHO-CONT. Documento verificado contra el código del sistema el 31 de octubre de 2026.*
