# Memoria · Gremio de Aventureros

**Código de alumno/a:** TU_CODIGO

## 1. Mi aventurero
- Usuario que he añadido a la tabla: (Entrega 2)
- Comando o pasos que he usado para generar el hash: (Entrega 2)

## 2. Parte 1 · match
Sí, he puesto `default => 'Desconocido'` en `etiquetaRango()`. Si llega un rango como `'Z'`, devuelve «Desconocido». Sin `default`, `match` lanza un `UnhandledMatchError` y la página se rompería. Lo prefiero así porque la web sigue funcionando aunque llegue un valor inesperado.

## 3. Parte 2 · Validación de la URL
Al escribir `tablon.php?rango=Z&oro=hola`, la página muestra las 10 misiones sin errores. `leerRangoFiltro()` convierte el valor a mayúsculas y comprueba con `in_array($valor, RANGOS, true)` que esté en `RANGOS`; como `Z` no está, devuelve `null` y no se filtra por rango. `leerOroMinimo()` usa `filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000]])`; con `hola` devuelve `false` y la función devuelve `0`.

## 4. Parte 3 · Sesión frente a cookie
(Entrega 2)

## 5. Parte 3 · Seguridad del login
(Entrega 2)

## 6. Pruebas que he hecho
| Prueba | Resultado esperado | Resultado obtenido |
|---|---|---|
| Abrir tablon.php | 10 tarjetas con borde verde, naranja o rojo | |
| Filtrar por rango B y oro mínimo 500 | 2 misiones | |
| tablon.php?rango=Z&oro=hola | Muestra las 10 misiones sin errores | |
| Aceptar «Bandidos del desfiladero» y «El dragón de Obsidiana» | El zurrón muestra ambas y un total de 5.650 mo | |
| Aceptar la misma misión dos veces / pulsar F5 | No se duplica; F5 no reenvía el formulario | |
| Pulsar Abandonar en una misión del zurrón | Desaparece del zurrón y el total se recalcula | |

## 7. Dificultades y cómo las he resuelto
Al principio no encontraba el docker-lamp porque no lo tenía clonado. Lo descargué desde GitHub, configuré el archivo `.env` y arranqué los contenedores con `docker compose up -d --build`.

## Uso de IA
He usado un poco de ia notebook y ayudas para poder entender el proceso para ayudarme a montar el entorno Docker y para escribir y revisar las funciones de `funciones.php`, `tablon.php` y `zurron.php` de las Partes 1 y 2.