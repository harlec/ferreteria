<?php
session_start();
include('sdba/sdba.php');

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombre)) {
	echo json_encode(array('success' => false, 'mensaje' => 'Nombre requerido'));
	exit;
}

$clientes = Sdba::table('clientes');
$data = array(
	'id_cliente' => '',
	'cliente' => $nombre,
	'doc_identidad' => '',
	'telefono' => '',
	'email' => '',
	'estado' => '1'
);
$clientes->insert($data);
$nuevo_id = $clientes->insert_id();

if ($nuevo_id) {
	echo json_encode(array('success' => true, 'id' => $nuevo_id, 'nombre' => $nombre));
} else {
	echo json_encode(array('success' => false, 'mensaje' => 'Error al registrar'));
}
