<?php
session_start();
$id_usuario = $_SESSION['id_usr']; 

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

if (isset($_POST) && !empty($_POST)) {

	//datos generales
	$fecha_ingreso = $_POST['fecha_in'];
	$fecha_despacho = $_POST['fecha_des'];
	$fecha = date("Y-m-d");
	$proveedor = $_POST['proveedor'];
	$guia = $_POST['guia'];
	$serie = $_POST['serie'];
	$numero = $_POST['numero'];
	$moneda = $_POST['moneda'];
	$observaciones = $_POST['observaciones'];
	$exonerada = $_POST['exonerada'];
	//item
	$id_p = $_POST['id_pro'];
	$unidad= $_POST['unidad'];
	$fv = $_POST['fv'];
	$precio = $_POST['precio'];
	$cantidad = $_POST['cantidad'];
	//$monto = $_POST['monto'];
	$total = $_POST['total'];
	$total_pre = $_POST['total_pre'];
	$respuestaOk = true;
	$venta_id = '';
	
	if (!empty($fecha) && !empty($id_p)) {

			// Normalizar fv antes de las consultas batch
			for ($i=0; $i < count($id_p); $i++) {
				if(empty($fv[$i])){
					$fv[$i] = '0000-00-00';
				}
			}

			// Pre-cargar stock total por producto (más reciente)
			$stock_total_map = array();
			$stock_q = Sdba::table('stock');
			$stock_q->where_in('producto', $id_p);
			$stock_q->order_by('id_stock','desc');
			$stock_list = $stock_q->get();
			foreach ($stock_list as $s) {
				if (!isset($stock_total_map[$s['producto']])) {
					$stock_total_map[$s['producto']] = floatval($s['stockt']);
				}
			}

			// Pre-cargar stock por lote (producto + fv)
			$stock_lote_map = array();
			foreach ($stock_list as $s) {
				$key = $s['producto'] . '_' . $s['fv'];
				if (!isset($stock_lote_map[$key])) {
					$stock_lote_map[$key] = floatval($s['stock']);
				}
			}

			// Pre-cargar variantes
			$variantes_map = array();
			$var_q = Sdba::table('variantes');
			$var_q->where_in('producto', $id_p);
			$var_list = $var_q->get();
			foreach ($var_list as $vr) {
				$key = $vr['producto'] . '_' . $vr['variante'];
				$variantes_map[$key] = $vr['id_variante'];
			}

			$ventas = Sdba::table('compras');
			$data = array('id_compra'=>'','fecha'=> $fecha,'fecha_ingreso'=>$fecha_ingreso,'fecha_despacho'=>$fecha_despacho,'guia'=>$guia,'serie_f'=>$serie,'numero_f'=>$numero,'total'=>$total,'moneda'=>$moneda,'proveedor'=>$proveedor,'usuario'=>$id_usuario,'observacion'=>$observaciones,'exonerada'=>$exonerada,'estado'=>'0');
			$ventas->insert($data);
			$venta_id = $ventas->insert_id();
			if ($venta_id) {
				$respuestaOk = true;
				$mensajeError = 'entro';

				//guardamos en tabla detalle de compra
				for ($i=0; $i < count($id_p) ; $i++) {
					$prod_id = $id_p[$i];
					$prod_fv = $fv[$i];

					// Convertir TNE a unidades
					if ($unidad[$i]=='TNE') {
						$cantidad1 = $cantidad[$i]*50;
					} else {
						$cantidad1 = floatval($cantidad[$i]);
					}

					$dventas = Sdba::table('detalle_compras');
					$ddata = array('id_de_compra'=>'','compra'=>$venta_id,'producto'=>$prod_id,'cantidad'=>$cantidad[$i],'precio'=>$precio[$i],'total'=>$total_pre[$i],'estado'=>'0');
					$dventas->insert($ddata);

					// Calcular nuevo stock total
					$cstock_total = isset($stock_total_map[$prod_id]) ? $stock_total_map[$prod_id] : 0;
					$stocktot = $cstock_total + $cantidad1;
					$stock_total_map[$prod_id] = $stocktot; // Actualizar mapa

					// Calcular nuevo stock por lote
					$key_lote = $prod_id . '_' . $prod_fv;
					$cstock_lote = isset($stock_lote_map[$key_lote]) ? $stock_lote_map[$key_lote] : 0;
					$nstock = $cstock_lote + $cantidad1;
					$stock_lote_map[$key_lote] = $nstock; // Actualizar mapa

					// Insertar registro de stock
					$motivo = 'c-'.$venta_id;
					$stock = Sdba::table('stock');
					$datas = array('id_stock'=>'','producto'=>$prod_id,'ingreso'=>$cantidad1,'motivo'=>$motivo,'stock'=>$nstock,'fv'=>$prod_fv,'stockt'=>$stocktot,'fecha'=>$fecha, 'estado'=>'0');
					$stock->insert($datas);

					// Actualizar variante
					$idvr = isset($variantes_map[$key_lote]) ? $variantes_map[$key_lote] : null;
					if ($idvr) {
						$variacion = Sdba::table('variantes');
						$datava = array('id_variante'=>$idvr,'producto'=>$prod_id,'variante'=>$prod_fv, 'stock'=>$nstock);
						$variacion->set($datava);
					}

					// Actualizar stockp en productos
					$productos = Sdba::table('productos');
					$productos->where('id_producto', $prod_id);
					$datap = array('stockp'=>$stocktot);
					$productos->update($datap);
				}
			}

	}
	else{
		$venta_id = 'Error';
		$mensajeError = 'Debe completar los campos de la venta';
	}


		

}

$venta_id = isset($venta_id) ? $venta_id : 0;
$salidaJson = array('respuesta' => $respuestaOk,
					'mensaje' => $mensajeError,
					'venta_id' => $venta_id);

echo json_encode($salidaJson);


?>