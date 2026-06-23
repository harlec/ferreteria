<?php
session_start();
include('sdba/sdba.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$respuestaOk = false;
$mensajeError = '';

if ($id > 0) {
	$producto = Sdba::table('productos');
	$producto->where('id_producto', $id);
	$producto->update(array('estado' => '0'));
	$respuestaOk = true;
}

echo json_encode(array('respuesta' => $respuestaOk, 'mensaje' => $mensajeError));
?>
