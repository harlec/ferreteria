<?php
session_start();
include('sdba/sdba.php');

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['id_usr'])) {
	echo json_encode(array('success' => false, 'mensaje' => 'Sesion no valida'));
	exit;
}

$detalle = isset($_POST['detalle']) ? intval($_POST['detalle']) : 0;
$cantidad = isset($_POST['cantidad']) ? floatval($_POST['cantidad']) : 0;
$venta = isset($_POST['venta']) ? intval($_POST['venta']) : 0;

// Validar datos
if ($detalle <= 0 || $cantidad <= 0 || $venta <= 0) {
	echo json_encode(array('success' => false, 'mensaje' => 'Datos invalidos'));
	exit;
}

// Verificar que el detalle pertenece a la venta
$det = Sdba::table('detalle_ventas');
$det->where('id_detalle', $detalle)->and_where('venta', $venta);
$detalle_row = $det->get_one();

if (!$detalle_row) {
	echo json_encode(array('success' => false, 'mensaje' => 'Detalle no encontrado'));
	exit;
}

// Calcular cantidad pendiente
$cant_total = floatval($detalle_row['cantidad']);
$desp = Sdba::table('despachos');
$desp->where('detalle', $detalle);
$desp_list = $desp->get();
$ya_despachado = 0;
foreach ($desp_list as $d) {
	$ya_despachado += floatval($d['cantidad']);
}
$pendiente = $cant_total - $ya_despachado;

// Validar que no exceda pendiente
if ($cantidad > $pendiente) {
	echo json_encode(array('success' => false, 'mensaje' => 'Cantidad excede el pendiente (' . $pendiente . ')'));
	exit;
}

// Registrar despacho
$nuevo = Sdba::table('despachos');
$data = array(
	'id_despacho' => '',
	'venta' => $venta,
	'detalle' => $detalle,
	'cantidad' => $cantidad,
	'fecha' => date('Y-m-d H:i:s'),
	'usuario' => $_SESSION['id_usr']
);
$nuevo->insert($data);
$nuevo_id = $nuevo->insert_id();

if ($nuevo_id) {
	echo json_encode(array('success' => true, 'mensaje' => 'Despacho registrado correctamente'));
} else {
	echo json_encode(array('success' => false, 'mensaje' => 'Error al registrar despacho'));
}
