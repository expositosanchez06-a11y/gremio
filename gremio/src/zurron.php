<?php
/**
 * =====================================================================
 *  zurron.php · Mi zurrón: misiones aceptadas · PLANTILLA DEL ALUMNO
 * =====================================================================
 *  PARTE 2 (listar y total) · PARTE 3 (los datos viven en $_SESSION)
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';

iniciarSesion();
$aventurero = requiereLogin();
$misiones   = require __DIR__ . '/inc/datos_misiones.php';
$_SESSION['zurron'] ??= [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $id = is_string($id) ? $id : '';
    $pos = array_search($id, $_SESSION['zurron'], true);

    if ($pos === false) {
        mensajeFlash('Esa misión no está en tu zurrón.');
    } else {
        unset($_SESSION['zurron'][$pos]);
        $_SESSION['zurron'] = array_values($_SESSION['zurron']); // reindexar
        mensajeFlash('Has abandonado la misión.');
    }
    header('Location: zurron.php');
    exit;
}

$ids   = $_SESSION['zurron'];
$total = totalRecompensa($misiones, $ids);
$flash = leerFlash();

$titulo = 'Mi zurrón';
require __DIR__ . '/inc/cabecera.php';
?>
<h1>Mi zurrón</h1>
<?php if ($flash !== null): ?><p class="aviso"><?= e($flash) ?></p><?php endif; ?>

<?php if (count($ids) === 0): ?>
    <p>Tu zurrón está vacío. <a href="tablon.php">Ve al tablón</a> a buscar misiones.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Misión</th><th>Rango</th><th>Recompensa</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($ids as $id): ?>
            <?php $m = buscarMision($misiones, $id); ?>
            <?php if ($m === null) { continue; } ?>
            <tr>
                <td><?= e($m['titulo']) ?></td>
                <td><?= e($m['rango']) ?> · <?= e(etiquetaRango($m['rango'])) ?></td>
                <td><?= e(formatearOro($m['recompensa'])) ?></td>
                <td>
                    <form method="post" action="zurron.php">
                        <input type="hidden" name="id" value="<?= e($m['id']) ?>">
                        <button type="submit">Abandonar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><th colspan="2">Recompensa total</th><th colspan="2"><?= e(formatearOro($total)) ?></th></tr>
        </tfoot>
    </table>
<?php endif; ?>
<p class="nota">El zurrón vive en la sesión: si sales del gremio, se vacía.</p>
<?php require __DIR__ . '/inc/pie.php'; ?>
