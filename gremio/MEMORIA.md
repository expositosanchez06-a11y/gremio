# Memoria · Gremio de Aventureros

**Código de alumno/a:** PON_AQUI_TU_CODIGO

## 1. Mi aventurero
- Usuario que he añadido a la tabla: `gremial01` (clase Guerrero, alias «El Novato Valiente»).
- Pasos para generar el hash: ejecuté `docker compose exec www php -r "echo password_hash('MiClave2026', PASSWORD_DEFAULT), PHP_EOL;"`, copié el hash que salió y lo pegué en el `INSERT` de `sql/mi_aventurero.sql`, que ejecuté en phpMyAdmin. En la base de datos solo se guarda el hash, nunca la contraseña.

## 2. Parte 1 · match
Sí, he puesto `default => 'Desconocido'` en `etiquetaRango()`. Si llega un rango como `'Z'`, devuelve «Desconocido». Sin `default`, `match` lanza un `UnhandledMatchError` y la página se rompería. Lo prefiero así porque la web sigue funcionando aunque llegue un valor inesperado.

## 3. Parte 2 · Validación de la URL
Al escribir `tablon.php?rango=Z&oro=hola`, la página muestra las 10 misiones sin errores. `leerRangoFiltro()` convierte el valor a mayúsculas y comprueba con `in_array($valor, RANGOS, true)` que esté en `RANGOS`; como `Z` no está, devuelve `null` y no se filtra por rango. `leerOroMinimo()` usa `filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000]])`; con `hola` devuelve `false` y la función devuelve `0`.

## 4. Parte 3 · Sesión frente a cookie
El tema y el usuario recordado sobreviven al cierre de sesión porque son cookies que se guardan en el navegador con una fecha de caducidad (un año y 30 días). `cerrarSesion()` solo vacía `$_SESSION`, caduca la cookie de sesión y llama a `session_destroy()`, pero no toca las cookies de preferencias.

El zurrón, en cambio, vive en `$_SESSION['zurron']`, es decir, en el servidor, y está ligado a la sesión. Al cerrar sesión se destruye la sesión y con ella el zurrón, que es lo que se pide: las preferencias son del navegador y el zurrón pertenece a la sesión de trabajo del aventurero.

## 5. Parte 3 · Seguridad del login
- **Consulta preparada:** `autenticar()` usa un marcador `:usuario` y `execute()`, sin concatenar variables. Por eso `' OR '1'='1` no inyecta SQL: se trata como texto.
- **Hash:** las contraseñas se guardan con `password_hash` y se comprueban con `password_verify`. La función devuelve el aventurero sin el hash.
- **Mismo mensaje de error:** si falla el usuario o falla la contraseña se muestra «Usuario o contraseña incorrectos.». Así un atacante no puede saber qué usuarios existen.
- **`session_regenerate_id(true)`:** tras un login correcto se cambia el identificador de sesión para evitar la fijación de sesión (que alguien fuerce un id conocido antes de que la víctima se identifique).
- **Errores de base de datos:** si salta una `PDOException`, el detalle va a `error_log()` y al usuario solo se le muestra un mensaje genérico.
- **Páginas protegidas:** `requiereLogin()` redirige a `login.php?motivo=privado` y hace `exit`.
- **Cookies:** todas con `path /`, `httponly` y `samesite Lax`. El tema se valida al leerlo: si la cookie está manipulada, se usa «claro». La cookie de usuario guarda solo el nombre, nunca la contraseña.

## 6. Pruebas que he hecho
| # | Prueba | Resultado esperado | Resultado obtenido |
|---|---|---|---|
| 1 | Abrir `tablon.php` sin sesión | Redirige al login con el aviso «solo para miembros» | Correcto: redirige con el aviso |
| 2 | Login con contraseña incorrecta | «Usuario o contraseña incorrectos.» | Correcto |
| 3 | Usuario `' OR '1'='1` | Mismo mensaje, no entra | Correcto: no entra |
| 4 | Login con «Recordar», salir | El login aparece con el usuario relleno | Correcto |
| 5 | Aceptar la misma misión dos veces / F5 | No se duplica; F5 no reenvía | Correcto |
| 6 | Recargar el tablón varias veces | El contador sube | Correcto |
| 7 | Cambiar tema y cerrar sesión | El tema se mantiene; el zurrón se vacía | Correcto |
| 8 | Editar a mano la cookie `gremio_tema` | Usa el tema claro | Correcto: vuelve a claro |
| 9 | `tablon.php?rango=Z&oro=hola` | Muestra las 10 misiones sin errores | Correcto |
| 10 | Entrar con mi propio aventurero | Funciona | Correcto |

También funcionan las comprobaciones de la Entrega 1: 10 tarjetas con color por rango, rango B y oro 500 dan 2 misiones, y «Bandidos del desfiladero» más «El dragón de Obsidiana» suman 5.650 mo.

## 7. Dificultades y cómo las he resuelto
Al principio no encontraba el docker-lamp porque no lo tenía clonado. Lo descargué desde GitHub, configuré el archivo `.env` y arranqué los contenedores con `docker compose up -d --build`. También me costó trabajar en la carpeta correcta: al principio edité la copia del zip en Descargas y no la de `docker-lamp\www\gremio`.

## Uso de IA
He usado IA de notebook para resolver algunos codigos qu eno podia hacerlo para resolver dudas  para montar el entorno Docker y para escribir y revisar los archivos de las Partes 1, 2 y 3. Después he probado todo en el navegador, he hecho las 10 pruebas y he repasado el código para entenderlo.