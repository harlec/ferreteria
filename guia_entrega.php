<?php
ini_set('display_errors', 0);
require 'inc/vendor/autoload.php';

include('inc/control.php');
include('inc/sdba/sdba.php');

$venta_id        = isset($_GET['venta'])      ? intval($_GET['venta']) : 0;
$modo_todo       = isset($_GET['todo'])       && $_GET['todo']       == '1';
$modo_despachado = isset($_GET['despachado']) && $_GET['despachado'] == '1';

if ($venta_id <= 0) die('Parametros invalidos');

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

if ($modo_todo) {
    $rows = $db->query("
        SELECT dv.cantidad, p.nom_prod
        FROM detalle_ventas dv
        LEFT JOIN productos p ON dv.producto = p.id_producto
        WHERE dv.venta = {$venta_id}
        ORDER BY p.nom_prod
    ")->result();
} elseif ($modo_despachado) {
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

$filas = '';
foreach ($rows as $r) {
    $filas .= '<tr>
        <td>' . htmlspecialchars($r['nom_prod']) . '</td>
        <td class="num">' . $r['cantidad'] . '</td>
    </tr>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Guía de Entrega - v-<?php echo $venta_id; ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Helvetica, Arial, sans-serif;
        background: #e8e8e8;
        display: flex;
        justify-content: center;
        padding: 30px 0;
    }

    .ticket {
        background: #fff;
        width: 76mm;
        padding: 10px 8px 16px 8px;
    }

    .nom   { font-size: 20px; font-weight: bold; text-align: center; line-height: 1.3; margin-bottom: 10px; }
    .emp   { font-size: 12px; font-weight: bold; text-align: center; line-height: 1.5; margin-bottom: 10px; }
    .nventa { font-size: 18px; font-weight: bold; text-align: center; margin: 10px 0; }
    .info  { font-size: 13px; font-weight: bold; margin: 10px 0; line-height: 1.6; }

    hr { border: none; border-top: 2px solid #000; margin: 8px 0; }

    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    thead th { font-size: 12px; font-weight: bold; padding: 3px 2px; text-align: left; border-bottom: 1px solid #000; }
    thead th.num { text-align: center; }
    tbody td { font-size: 12px; padding: 2px 2px; vertical-align: top; }
    .num { text-align: center; white-space: nowrap; width: 50px; }

    .gracias { font-size: 13px; font-weight: bold; text-align: center; margin-top: 14px; }

    @media print {
        body { background: none; padding: 0; display: block; }
        .ticket { width: 100%; padding: 0; }
        @page { margin: 0.4cm; size: 80mm auto; }
    }
</style>
</head>
<body>
<div class="ticket">
    <p class="nom">Ferreteros y Constructores<br>"TORITO DE ORO"</p>
    <p class="emp">
        ENVIROMENTAL SENSE CONSULTING<br>S.R.L. - ENSCO S.R.L.<br>
        Mz-A sublote-01 Urb San José - Espaldas del<br>Grifo Repsol - Barranca<br>
        986362380 - 992770595 - 986165174<br>
        RUC 20600064879
    </p>
    <p class="nventa">GUIA DE ENTREGA</p>
    <p class="info">
        FECHA: <?php echo $fecha_guia; ?><br>
        VENTA: v-<?php echo $venta_id; ?><br>
        CLIENTE: <?php echo htmlspecialchars($cliente_nombre); ?><br>
        <?php if ($cliente_tel1): ?>TEL: <?php echo htmlspecialchars($cliente_tel1); ?><br><?php endif; ?>
        <?php if ($cliente_tel2): ?>TEL2: <?php echo htmlspecialchars($cliente_tel2); ?><br><?php endif; ?>
    </p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>DESCRIPCIÓN</th>
                <th class="num">CANT.</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $filas; ?>
        </tbody>
    </table>
    <hr>
    <p class="gracias">GRACIAS X SU PREFERENCIA</p>
</div>
<script>
    window.onload = function() { window.print(); };
    window.onafterprint = function() { window.close(); };
</script>
</body>
</html>
