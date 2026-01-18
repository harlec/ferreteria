<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

//if (isset($_POST) && !empty($_POST)) {
	$id = $_POST['id'];
	$nombre = $_POST['nombre'];
	$precio_v = $_POST['precio_v'];
	$precio_c = $_POST['precio_c'];
	$unidad = $_POST['unidad'];
	$categoria = $_POST['categoria'];
	$marca = $_POST['marca'];
	$color = $_POST['color'];
	//$serie = $_POST['serie'];
	$exonerada = $_POST['exonerada'];
	$codigo = $_POST['codigo'];
	$respuestaOk = true;
	//guardamos en tabla ventas
			
			$ventas = Sdba::table('productos');
			$ventas->where('id_producto', $id);
			$data = array('nom_prod'=>$nombre,'exonerada'=>$exonerada,'codigo_producto'=>$codigo,'precio_venta'=>$precio_v,'precio_compra'=>$precio_c,'unidad_prod'=>$unidad,'categoria'=>$categoria,'marca'=>$marca,'color'=>$color);
			//$data = array('id_producto'=>'','cod_sunat'=> '','nom_prod'=>$nombre,'unidad_prod'=>'1','categoria'=>'1','precio_compra'=>'','precio_venta'=>$precio,'proveedor'=>'1','estado'=>'1');
			$ventas->update($data);
			
				$respuestaOk = true;
				$mensajeError = 'entro';


		

//}		

		$salidaJson = array('respuesta' => $respuestaOk,
							'mensaje' => $mensajeError);

		echo json_encode($salidaJson);


?>