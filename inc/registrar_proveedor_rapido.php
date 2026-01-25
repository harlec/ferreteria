<?php
session_start();
include('sdba/sdba.php');

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombre)) {
	echo json_encode(array('success' => false, 'mensaje' => 'Nombre requerido'));
	exit;
}

$proveedores = Sdba::table('proveedores');
$data = array(
	'id_proveedor' => '',
	'proveedor' => $nombre,
	'ruc' => '',
	'direccion' => '',
	'telefono' => '',
	'email' => '',
	'estado' => '1'
);
$proveedores->insert($data);
$nuevo_id = $proveedores->insert_id();

if ($nuevo_id) {
	echo json_encode(array('success' => true, 'id' => $nuevo_id, 'nombre' => $nombre));
} else {
	echo json_encode(array('success' => false, 'mensaje' => 'Error al registrar'));
}
