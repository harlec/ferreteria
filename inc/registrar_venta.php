<?php

session_start();
$id_usuario = $_SESSION['id_usr']; 
//$tienda = $_SESSION['tienda'];

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

if (isset($_POST) && !empty($_POST)) {

	$fecha = $_POST['fecha'];
	$cliente = $_POST['cliente'];
	$tipo = $_POST['tipo'];
	$forma = $_POST['forma'];
	$fecha_ope = date("Y-m-d H:i:s");
	$id_p = $_POST['id_pro'];
	$fv= $_POST['fv'];
	$precio = $_POST['precio'];
	$cantidad = $_POST['cantidad'];
	//$monto = $_POST['monto'];
	$total = $_POST['total'];
	$total_pre = $_POST['total_pre'];
	$respuestaOk = true;
	//guardamos en tabla ventas



	if (!empty($fecha) && !empty($id_p) && !empty($total_pre)) {

			// Normalizar fv antes de las consultas batch
			for ($i=0; $i < count($id_p); $i++) {
				if($fv[$i]=='-'){
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
			$stock_q2 = Sdba::table('stock');
			$stock_q2->where_in('producto', $id_p);
			$stock_q2->order_by('id_stock','desc');
			$stock_list2 = $stock_q2->get();
			foreach ($stock_list2 as $s) {
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

			$ventas = Sdba::table('ventas');
			$data = array('id_venta'=>'','fecha'=> $fecha,'fecha_ope'=>$fecha_ope,'total'=>$total,'cliente'=>$cliente,'usuario'=>$id_usuario,'tipo'=>$tipo,'forma'=>$forma,'estado'=>'0');
			$ventas->insert($data);
			$venta_id = $ventas->insert_id();
			if ($venta_id) {
				$respuestaOk = true;
				$mensajeError = 'entro';

				//guardamos en tabla detalle de venta
				for ($i=0; $i < count($id_p) ; $i++) {
					$prod_id = $id_p[$i];
					$prod_fv = $fv[$i];
					$prod_cant = floatval($cantidad[$i]);

					//guardamos el detalle de las ventas
					$dventas = Sdba::table('detalle_ventas');
					$ddata = array('id_detalle'=>'','venta'=>$venta_id,'producto'=>$prod_id,'cantidad'=>$cantidad[$i],'precio'=>$precio[$i],'total'=>$total_pre[$i],'estado'=>'0');
					$dventas->insert($ddata);

					// Calcular nuevo stock total
					$cstock_total = isset($stock_total_map[$prod_id]) ? $stock_total_map[$prod_id] : 0;
					$stocktot = $cstock_total - $prod_cant;
					$stock_total_map[$prod_id] = $stocktot; // Actualizar mapa para siguiente iteración del mismo producto

					// Calcular nuevo stock por lote
					$key_lote = $prod_id . '_' . $prod_fv;
					$cstock_lote = isset($stock_lote_map[$key_lote]) ? $stock_lote_map[$key_lote] : 0;
					$nstock = $cstock_lote - $prod_cant;
					$stock_lote_map[$key_lote] = $nstock; // Actualizar mapa

					// Insertar registro de stock
					$motivo = 'v-'.$venta_id;
					$stock = Sdba::table('stock');
					$datas = array('id_stock'=>'','producto'=>$prod_id,'egreso'=>$cantidad[$i],'motivo'=>$motivo,'stock'=>$nstock,'fv'=>$prod_fv,'stockt'=>$stocktot,'fecha'=>$fecha, 'estado'=>'0');
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


		

}

$venta_id = isset($venta_id) ? $venta_id : 0;
$salidaJson = array('respuesta' => $respuestaOk,
					'mensaje' => $mensajeError,
					'venta_id' => $venta_id);

echo json_encode($salidaJson);


?>