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
$clsn = $cls['cliente'] ?? 'varios';

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
        <td>' . htmlspecialchars('[' . $key['cantidad'] . ']' . $key['nom_prod']) . '</td>
        <td class="num">' . number_format($key['precio'], 2, '.', ',') . '</td>
        <td class="num">' . number_format($key['total'], 2, '.', ',') . '</td>
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

    .nom { font-size: 20px; font-weight: bold; text-align: center; line-height: 1.3; margin-bottom: 10px; }
    .emp { font-size: 12px; font-weight: bold; text-align: center; line-height: 1.5; margin-bottom: 10px; }
    .nventa { font-size: 18px; font-weight: bold; text-align: center; margin: 10px 0; }
    .info { font-size: 13px; font-weight: bold; margin: 10px 0; line-height: 1.6; }

    hr { border: none; border-top: 2px solid #000; margin: 8px 0; }

    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    thead th { font-size: 12px; font-weight: bold; padding: 3px 2px; text-align: left; }
    thead th.num { text-align: right; }
    tbody td { font-size: 12px; padding: 2px 2px; vertical-align: top; }
    .num { text-align: right; white-space: nowrap; }

    .total-row td { font-size: 14px; font-weight: bold; padding-top: 6px; }
    .letras { font-size: 12px; padding-top: 8px; line-height: 1.5; }
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
    <p class="nventa">PROFORMA</p>
    <p class="nom">Ferreteros y Constructores<br>"TORITO DE ORO"</p>
    <p class="emp">
        ENVIROMENTAL SENSE CONSULTING<br>S.R.L. - ENSCO S.R.L.<br>
        Mz-A sublote-01 Urb San José - Espaldas del<br>Grifo Repsol - Barranca<br>
        986362380 - 992770595 - 986165174<br>
        RUC 20600064879
    </p>
    <p class="nventa">PROFORMA N° <?php echo $id; ?></p>
    <p class="info">
        FECHA: <?php echo $fechita; ?><br>
        <?php echo $tipo; ?><br>
        <?php echo htmlspecialchars($clsn); ?>
    </p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>[CANT.] DESCRIPCIÓN</th>
                <th class="num">P/U</th>
                <th class="num">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $filas; ?>
            <tr class="total-row">
                <td colspan="2" class="num">TOTAL: S/</td>
                <td class="num"><?php echo number_format($tot, 2, '.', ','); ?></td>
            </tr>
        </tbody>
    </table>
    <p class="letras"><b>IMPORTE EN LETRAS:</b> <?php echo $letras; ?></p>
    <p class="gracias">GRACIAS X SU PREFERENCIA</p>
</div>
<script>
    window.onload = function() { window.print(); };
    window.onafterprint = function() { window.close(); };
</script>
</body>
</html>
