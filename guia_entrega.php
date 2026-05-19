<?php
ini_set('display_errors', 0);
ob_start();
require_once 'inc/dompdf/autoload.inc.php';
require 'inc/vendor/autoload.php';

include('inc/control.php');
include('inc/sdba/sdba.php');

$venta_id      = isset($_GET['venta'])      ? intval($_GET['venta']) : 0;
$modo_todo     = isset($_GET['todo'])      && $_GET['todo']      == '1';
$modo_despachado = isset($_GET['despachado']) && $_GET['despachado'] == '1';

if ($venta_id <= 0) die('Parametros invalidos');

// Validar parámetros según modo
if (!$modo_todo && !$modo_despachado) {
    if (isset($_GET['despachos']) && $_GET['despachos'] != '') {
        $despacho_ids = array_filter(array_map('intval', explode(',', $_GET['despachos'])));
    } elseif (isset($_GET['despacho'])) {
        $despacho_ids = array(intval($_GET['despacho']));
    } else {
        die('Parametros invalidos');
    }
    if (empty($despacho_ids)) die('Parametros invalidos');
}

// Datos del cliente
$db = Sdba::db();
$venta_row = $db->query("
    SELECT v.*, IFNULL(cl.cliente,'VARIOS') as nombre_cliente,
           IFNULL(cl.telefono,'') as telefono,
           IFNULL(cl.telefono2,'') as telefono2
    FROM ventas v
    LEFT JOIN clientes cl ON v.cliente = cl.id_cliente
    WHERE v.id_venta = {$venta_id}
")->row();

$cliente_nombre = $venta_row ? $venta_row['nombre_cliente'] : 'VARIOS';
$cliente_tel1   = $venta_row ? $venta_row['telefono']       : '';
$cliente_tel2   = $venta_row ? $venta_row['telefono2']      : '';
$fecha_guia     = date('d-m-Y');

// Obtener items según modo
if ($modo_todo) {
    // Modo "Despachar TODO": todos los items del pedido con cantidad original
    $rows = $db->query("
        SELECT dv.cantidad, p.nom_prod
        FROM detalle_ventas dv
        LEFT JOIN productos p ON dv.producto = p.id_producto
        WHERE dv.venta = {$venta_id}
        ORDER BY p.nom_prod
    ")->result();
} elseif ($modo_despachado) {
    // Modo "copia": suma de todo lo despachado real por producto
    $rows = $db->query("
        SELECT SUM(d.cantidad) as cantidad, p.nom_prod
        FROM despachos d
        LEFT JOIN detalle_ventas dv ON d.detalle = dv.id_detalle
        LEFT JOIN productos p ON dv.producto = p.id_producto
        WHERE d.venta = {$venta_id}
        GROUP BY dv.producto
        ORDER BY p.nom_prod
    ")->result();
} else {
    // Modo despacho individual: mostrar solo lo despachado en esos IDs
    $ids_str = implode(',', $despacho_ids);
    $rows = $db->query("
        SELECT d.cantidad, p.nom_prod
        FROM despachos d
        LEFT JOIN detalle_ventas dv ON d.detalle = dv.id_detalle
        LEFT JOIN productos p ON dv.producto = p.id_producto
        WHERE d.id_despacho IN ({$ids_str}) AND d.venta = {$venta_id}
        ORDER BY p.nom_prod
    ")->result();
}

$filas_html = '';
foreach ($rows as $r) {
    $filas_html .= '<tr>
        <td>' . htmlspecialchars($r['nom_prod']) . '</td>
        <td style="text-align:center;">' . $r['cantidad'] . '</td>
    </tr>';
}
?>

<style>
body { font-family: Helvetica, Sans-Serif; }
thead th { font-size: 10px; font-weight: bold; border-bottom: 1px solid #000; }
tbody td { font-size: 10px; }
@page { margin-left: 0.4cm; margin-right: 0.4cm; margin-top: 0.4cm; margin-bottom: 0.4cm; }
</style>

<h5 style="text-align:center;"><b>GUIA DE ENTREGA</b></h5>
<h5 style="text-align:center;">Ferreteros y Constructores<br>"TORITO DE ORO"</h5>
<h6 style="text-align:center;"><b>ENVIROMENTAL SENSE CONSULTING S.R.L. - ENSCO S.R.L.</b><br>
    Mz-A sublote-01 Urb San José - Espaldas del Grifo Repsol - Barranca<br>
    986362380 - 992770595 - 986165174<br>
    RUC 20600064879
</h6>
<h6>
    FECHA: <?php echo $fecha_guia; ?><br>
    VENTA: v-<?php echo $venta_id; ?><br>
    CLIENTE: <?php echo htmlspecialchars($cliente_nombre); ?><br>
    <?php if ($cliente_tel1): ?>TEL: <?php echo htmlspecialchars($cliente_tel1); ?><br><?php endif; ?>
    <?php if ($cliente_tel2): ?>TEL2: <?php echo htmlspecialchars($cliente_tel2); ?><br><?php endif; ?>
</h6>
<hr>
<table width="100%">
    <thead>
        <tr>
            <th>DESCRIPCIÓN</th>
            <th style="text-align:center; width:60px;">CANT.</th>
        </tr>
    </thead>
    <tbody>
        <?php echo $filas_html; ?>
    </tbody>
</table>
<hr>
<h6 style="text-align:center;">GRACIAS X SU PREFERENCIA</h6>

<?php
use Dompdf\Dompdf;
$dompdf = new DOMPDF();
$dompdf->load_html(ob_get_clean());
// Formato ticket 80mm de ancho, altura suficiente para muchos items
$dompdf->set_paper(array(0, 0, 226, 2000));
$dompdf->render();
$dompdf->stream('guia_entrega.pdf');
?>
