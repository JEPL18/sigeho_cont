# Suite de pruebas SIGEHO-CONT

Pruebas automatizadas de la lógica del sistema (modelos), sin navegador.
Se ejecutan contra una base de datos de prueba que la suite crea y
recrea en cada corrida. Nunca apunta a la base de datos real.

## Requisitos

- PHP 8 con extensión PDO MySQL.
- Un servidor MySQL/MariaDB accesible con un usuario que pueda crear
  y borrar bases de datos.
- El cliente `mysql` en el PATH (opcional: sin él, la suite importa
  el esquema desde PHP).

## Cómo correrla

```bash
php tests/suite.php
```

La conexión se configura con variables de entorno:

| Variable | Por defecto | Ejemplo XAMPP (Windows) |
|---|---|---|
| `SIGEHO_TEST_HOST` | `127.0.0.1` | `localhost` |
| `SIGEHO_TEST_DB` | `sigeho_tests` | `sigeho_tests` |
| `SIGEHO_TEST_USER` | `root` | `root` |
| `SIGEHO_TEST_PASS` | (vacía) | (vacía) |

Ejemplo en Windows (cmd, desde la carpeta del proyecto):

```cmd
set SIGEHO_TEST_HOST=localhost
set SIGEHO_TEST_USER=root
php tests\suite.php
```

La salida lista cada prueba como `PASS` o `FAIL` y termina con el
resumen `N/M pruebas pasaron`. El código de salida es `0` si todas
pasan y `1` si alguna falla.

## Qué cubre

- `Seccion::sugerirCantidad`: reparto parejo (85/30, 60/30, 31/30)
  y matrícula cero.
- `Seccion::crearSugeridas`: códigos correlativos saltando los que
  ya existen.
- `Horario::detectarChoque`: choque de aula, de profesor y de
  sección; bloque contiguo que no choca; autoexclusión al editar.
- `Horario::validarRecursos`: estados y capacidad, profesor
  inactivo y capacidad insuficiente.
- `Horario::huecosLibres`: huecos sin opt-in y días saltados cuando
  la disponibilidad no los cubre.
- `Horario::aplicarHueco`: crea con revalidación y rechaza con
  choque.
- `Profesor::tieneHorarios`: con y sin clases (protege borrados).

## Pruebas de navegador (E2E)

Los flujos completos en navegador (botones de sugerencias,
arrastre RF-06, bitácora, control 403) se probaron con navegador
real en 2026-10-28 y quedaron documentados en las notas
`ACTUALIZACION_BOTONES.txt`, `ACTUALIZACION_ARRASTRE.txt` y
`ACTUALIZACION_BITACORA.txt`, con videos de evidencia. Esos
recorridos son la segunda pata de la suite; la automatización
de navegador queda como trabajo futuro.

## Agregar una prueba

Escribir una llamada a `prueba('nombre', $condicion)` al final
del archivo, antes del resumen. Cada prueba debe crear sus
propios datos y no depender del orden de las demás, salvo que
el nombre lo diga.
