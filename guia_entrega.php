<?php
ob_start();
require_once 'inc/dompdf/autoload.inc.php';
require 'inc/vendor/autoload.php';

include('inc/control.php');
include('inc/sdba/sdba.php');

$venta_id = isset($_GET['venta']) ? intval($_GET['venta']) : 0;

// Soportar despacho=X (uno) o despachos=1,2,3 (varios)
if (isset($_GET['despachos']) && $_GET['despachos'] != '') {
	$despacho_ids = array_filter(array_map('intval', explode(',', $_GET['despachos'])));
} elseif (isset($_GET['despacho'])) {
	$despacho_ids = array(intval($_GET['despacho']));
} else {
	die('Parametros invalidos');
}

if ($venta_id <= 0 || empty($despacho_ids)) {
	die('Parametros invalidos');
}

// Datos del cliente
$venta_q = Sdba::table('ventas');
$venta_q->where('id_venta', $venta_id);
$venta_row = $venta_q->get_one();

$cliente_q = Sdba::table('clientes');
$cliente_q->where('id_cliente', $venta_row['cliente']);
$cliente_row = $cliente_q->get_one();

$cliente_nombre = $cliente_row ? $cliente_row['cliente'] : 'VARIOS';
$cliente_tel1   = $cliente_row ? $cliente_row['telefono']  : '';
$cliente_tel2   = $cliente_row && isset($cliente_row['telefono2']) ? $cliente_row['telefono2'] : '';

$fecha_guia = date('d-m-Y');

// Obtener items despachados
$ids_str = implode(',', $despacho_ids);
$db = Sdba::db();
$rows = $db->query("
	SELECT d.cantidad, p.nom_prod
	FROM despachos d
	LEFT JOIN detalle_ventas dv ON d.detalle = dv.id_detalle
	LEFT JOIN productos p ON dv.producto = p.id_producto
	WHERE d.id_despacho IN ({$ids_str}) AND d.venta = {$venta_id}
")->result();

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
thead th { font-size: 10px; font-weight: bold; }
tbody td { font-size: 10px; }
@page { margin-left: 0.4cm; margin-right: 0.4cm; margin-top: 0.4cm; }
</style>

<h5 style="text-align:center;"><b>GUIA DE ENTREGA</b></h5>
<h5 style="text-align:center;">Ferreteros y Constructores<br>"EL TORITO DE ORO"</h5>
<h6 style="text-align:center;"><b>ENVIROMENTAL SENSE CONSULTING S.R.L. - ENSCO S.R.L.</b><br>
	Mz-A sublote-01 Urb San José - Espaldas del Grifo Repsol - Barranca<br>
	986362380 - 992770595 - 986165174<br>
	RUC 20600064879
</h6>
<h6>
	FECHA: <?php echo $fecha_guia; ?><br>
	CLIENTE: <?php echo htmlspecialchars($cliente_nombre); ?><br>
	<?php if ($cliente_tel1): ?>TEL: <?php echo htmlspecialchars($cliente_tel1); ?><br><?php endif; ?>
	<?php if ($cliente_tel2): ?>TEL2: <?php echo htmlspecialchars($cliente_tel2); ?><br><?php endif; ?>
</h6>
<hr>
<table width="100%">
	<thead>
		<tr>
			<th>DESCRIPCIÓN</th>
			<th style="text-align:center;">CANTIDAD</th>
		</tr>
	</thead>
	<tbody>
		<?php echo $filas_html; ?>
	</tbody>
</table>
<h6 style="text-align:center;">GRACIAS X SU PREFERENCIA</h6>

<?php
use Dompdf\Dompdf;
$dompdf = new DOMPDF();
$dompdf->load_html(ob_get_clean());
$dompdf->set_paper(array(0,0,200,1000));
$dompdf->render();
$dompdf->stream('guia_entrega.pdf');
?>
