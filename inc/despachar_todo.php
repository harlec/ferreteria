<?php
session_start();
include('sdba/sdba.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id_usr'])) {
	echo json_encode(array('success' => false, 'mensaje' => 'Sesion no valida'));
	exit;
}

$venta = isset($_POST['venta']) ? intval($_POST['venta']) : 0;

if ($venta <= 0) {
	echo json_encode(array('success' => false, 'mensaje' => 'Datos invalidos'));
	exit;
}

// Obtener todos los detalles de la venta
$det_query = Sdba::table('detalle_ventas');
$det_query->where('venta', $venta);
$detalles = $det_query->get();

if (empty($detalles)) {
	echo json_encode(array('success' => false, 'mensaje' => 'No hay items en esta venta'));
	exit;
}

// Para cada detalle, calcular pendiente y despachar si hay algo pendiente
$nuevos_ids = array();

foreach ($detalles as $det) {
	$id_det    = $det['id_detalle'];
	$cant_total = floatval($det['cantidad']);

	// Calcular ya despachado
	$desp_q = Sdba::table('despachos');
	$desp_q->where('detalle', $id_det);
	$desp_list = $desp_q->get();
	$ya_despachado = 0;
	foreach ($desp_list as $d) {
		$ya_despachado += floatval($d['cantidad']);
	}
	$pendiente = $cant_total - $ya_despachado;

	if ($pendiente > 0) {
		$nuevo = Sdba::table('despachos');
		$nuevo->insert(array(
			'id_despacho' => '',
			'venta'       => $venta,
			'detalle'     => $id_det,
			'cantidad'    => $pendiente,
			'fecha'       => date('Y-m-d H:i:s'),
			'usuario'     => $_SESSION['id_usr']
		));
		$nid = $nuevo->insert_id();
		if ($nid) {
			$nuevos_ids[] = $nid;
		}
	}
}

if (empty($nuevos_ids)) {
	echo json_encode(array('success' => false, 'mensaje' => 'No hay pendientes por despachar'));
} else {
	echo json_encode(array('success' => true, 'ids' => $nuevos_ids));
}
