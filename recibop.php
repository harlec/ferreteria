<?php
ini_set('display_errors', 0);
require 'inc/vendor/autoload.php';
use Luecano\NumeroALetras\NumeroALetras;

include('inc/control.php');
include('inc/sdba/sdba.php');

$id = intval($_GET['id']);

$venta = Sdba::table('proforma');
$venta->where('id_venta', $id);
$venta_l = $venta->get_one();

$cliente = Sdba::table('clientes');
$cliente->where('id_cliente', $venta_l['cliente']);
$cls = $cliente->get_one();
$clsn = $cls['cliente'] ?? 'VARIOS';

$fechita = date("d-m-Y", strtotime($venta_l['fecha']));
$tipo = $venta_l['tipo'] == '1' ? 'Contado' : 'Crédito';

$ventas = Sdba::table('detalle_proforma');
$ventas->where('venta', $id);
$ventas->left_join('producto', 'productos', 'id_producto');
$ventas_list = $ventas->get();

$tot = 0;
$filas = '';
foreach ($ventas_list as $key) {
    $tot += floatval($key['total']);
    $filas .= '<tr>
        <td>[' . $key['cantidad'] . '] ' . htmlspecialchars($key['nom_prod']) . '</td>
        <td class="der">' . number_format($key['precio'], 2, '.', ',') . '</td>
        <td class="der">' . number_format($key['total'], 2, '.', ',') . '</td>
    </tr>';
}

$formatter = new NumeroALetras();
$letras = $formatter->toInvoice($tot, 2) . ' SOLES';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Proforma N° <?php echo $id; ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; width: 78mm; }
    h4, h5, h6 { font-weight: bold; text-align: center; margin: 3px 0; }
    h4 { font-size: 12px; }
    h5 { font-size: 11px; }
    h6 { font-size: 9px; font-weight: normal; }
    hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; }
    th { font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding: 2px 0; }
    td { font-size: 9px; padding: 1px 0; vertical-align: top; }
    .der { text-align: right; }
    .total-row td { font-size: 11px; font-weight: bold; border-top: 1px dashed #000; padding-top: 3px; }
    .letras { font-size: 8px; padding-top: 3px; }
    .gracias { text-align: center; margin-top: 6px; font-size: 9px; }
    @media print {
        @page { margin: 0.4cm; size: 80mm auto; }
        body { width: 100%; }
    }
</style>
</head>
<body>
    <h5>★ PROFORMA ★</h5>
    <h4>Ferreteros y Constructores<br>"TORITO DE ORO"</h4>
    <h6><b>ENVIROMENTAL SENSE CONSULTING S.R.L. - ENSCO S.R.L.</b><br>
        Mz-A sublote-01 Urb San José - Espaldas del Grifo Repsol - Barranca<br>
        986362380 - 992770595 - 986165174<br>
        RUC 20600064879
    </h6>
    <h5>PROFORMA N° <?php echo $id; ?></h5>
    <h6>FECHA: <?php echo $fechita; ?> &nbsp;|&nbsp; <?php echo $tipo; ?><br><?php echo htmlspecialchars($clsn); ?></h6>
    <hr>
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">[CANT.] DESCRIPCIÓN</th>
                <th class="der">P/U</th>
                <th class="der">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $filas; ?>
            <tr class="total-row">
                <td colspan="2" class="der">TOTAL: S/</td>
                <td class="der"><?php echo number_format($tot, 2, '.', ','); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="letras"><b>SON: </b><?php echo $letras; ?></td>
            </tr>
        </tbody>
    </table>
    <p class="gracias">— GRACIAS POR SU PREFERENCIA —</p>
<script>
    window.onload = function() { window.print(); };
    window.onafterprint = function() { window.close(); };
</script>
</body>
</html>
