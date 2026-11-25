<?php
/**
 * tablon.php · Tablón de misiones
 * PARTE 1 (UD3): recorrer el array de misiones y pintar cada tarjeta.
 * PARTE 2 (UD4): filtro por GET y aceptación por POST.
 * PARTE 3 (UD5): página solo para miembros; contador de visitas en sesión.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';

iniciarSesion();
$aventurero = requiereLogin();
$misiones   = require __DIR__ . '/inc/datos_misiones.php';
$_SESSION['zurron'] ??= [];

// Contador de visitas: +1 cada vez que se carga el tablón (no en el POST)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['visitas'] = ($_SESSION['visitas'] ?? 0) + 1;
}

// ---- POST: aceptar una misión --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $id = is_string($id) ? $id : '';
    $mision = buscarMision($misiones, $id);

    if ($mision === null) {
        mensajeFlash('Esa misión no existe en el tablón.');
    } elseif (in_array($id, $_SESSION['zurron'], true)) {
        mensajeFlash('Ya tienes «' . $mision['titulo'] . '» en tu zurrón.');
    } else {
        $_SESSION['zurron'][] = $id;
        mensajeFlash('Has aceptado «' . $mision['titulo'] . '». ¡Buena suerte!');
    }
    // Post/Redirect/Get: así F5 no reenvía el formulario
    header('Location: tablon.php');
    exit;
}

// ---- GET: filtros ---------------------------------------------------------
$rango     = leerRangoFiltro($_GET);
$oroMinimo = leerOroMinimo($_GET);
$visibles  = filtrarMisiones($misiones, $rango, $oroMinimo);
$flash     = leerFlash();

$titulo = 'Tablón de misiones';
require __DIR__ . '/inc/cabecera.php';
?>
<h1>Tablón de misiones</h1>
<p class="nota">Entraste a las <?= e((string) ($_SESSION['entrada'] ?? '—')) ?> · Visitas al tablón en esta sesión: <?= e((string) ($_SESSION['visitas'] ?? 0)) ?></p>
<?php if ($flash !== null): ?><p class="aviso"><?= e($flash) ?></p><?php endif; ?>

<form method="get" action="tablon.php" class="filtros">
    <label>Rango
        <select name="rango">
            <option value="">Todos</option>
            <?php foreach (RANGOS as $r): ?>
                <option value="<?= $r ?>" <?= $r === $rango ? 'selected' : '' ?>><?= $r ?> · <?= etiquetaRango($r) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Recompensa mínima
        <input type="number" name="oro" min="0" max="10000" step="10" value="<?= $oroMinimo ?>">
    </label>
    <button type="submit">Filtrar</button>
</form>

<p><?= count($visibles) ?> de <?= count($misiones) ?> misiones.</p>

<div class="misiones">
<?php foreach ($visibles as $m): ?>
    <?php
    $textoDias = $m['dias'] . ($m['dias'] === 1 ? ' día' : ' días');
    $enZurron  = in_array($m['id'], $_SESSION['zurron'], true);
    ?>
    <article class="mision <?= e(claseRango($m['rango'])) ?>">
        <span class="rango"><?= e($m['rango']) ?> · <?= e(etiquetaRango($m['rango'])) ?></span>
        <h2><?= e($m['titulo']) ?></h2>
        <p class="nota"><?= e($m['zona']) ?> · <?= e($textoDias) ?></p>
        <p><?= e($m['descripcion']) ?></p>
        <p class="oro"><?= e(formatearOro($m['recompensa'])) ?></p>
        <?php if ($enZurron): ?>
            <p class="nota">✔ En tu zurrón</p>
        <?php else: ?>
            <form method="post" action="tablon.php">
                <input type="hidden" name="id" value="<?= e($m['id']) ?>">
                <button type="submit">Aceptar misión</button>
            </form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
<?php if (count($visibles) === 0): ?>
    <p>Ninguna misión cumple el filtro.</p>
<?php endif; ?>
</div>
<?php require __DIR__ . '/inc/pie.php'; ?>