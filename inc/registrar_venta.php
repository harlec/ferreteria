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
	// Pagos multiples
	$pf = isset($_POST['pf']) ? $_POST['pf'] : array();
	$pm = isset($_POST['pm']) ? $_POST['pm'] : array();
	// Forma principal (primer pago) para ventas.forma
	$forma = !empty($pf) ? intval($pf[0]) : 1;
	$fecha_pago = isset($_POST['fecha_pago']) && $_POST['fecha_pago'] != '' ? $_POST['fecha_pago'] : null;
	$fecha_ope = date("Y-m-d H:i:s");
	$id_p = $_POST['id_pro'];
	$precio = $_POST['precio'];
	$cantidad = $_POST['cantidad'];
	$total = floatval($_POST['total']);
	$total_pre = $_POST['total_pre'];

	if ($total <= 0) {
		echo json_encode(['respuesta' => false, 'mensaje' => 'El total debe ser mayor a cero.', 'venta_id' => 0]);
		exit;
	}

	$respuestaOk = true;
	//guardamos en tabla ventas



	if (!empty($fecha) && !empty($id_p) && !empty($total_pre)) {

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

			$ventas = Sdba::table('ventas');
			$data = array('id_venta'=>'','fecha'=> $fecha,'fecha_ope'=>$fecha_ope,'total'=>$total,'cliente'=>$cliente,'usuario'=>$id_usuario,'tipo'=>$tipo,'forma'=>$forma,'fecha_pago'=>$fecha_pago,'estado'=>'0');
			$ventas->insert($data);
			$venta_id = $ventas->insert_id();
			// Guardar pagos múltiples
			for ($pi = 0; $pi < count($pf); $pi++) {
				$p_forma = intval($pf[$pi]);
				$p_monto = floatval($pm[$pi]);
				if ($p_forma > 0 && $p_monto > 0) {
					$pago = Sdba::table('pagos');
					$pago->insert(array('id_pago'=>'','venta'=>$venta_id,'forma'=>$p_forma,'monto'=>$p_monto));
				}
			}
			if ($venta_id) {
				$respuestaOk = true;
				$mensajeError = 'entro';

				//guardamos en tabla detalle de venta
				for ($i=0; $i < count($id_p) ; $i++) {
					$prod_id = $id_p[$i];
					$prod_cant = floatval($cantidad[$i]);

					//guardamos el detalle de las ventas
					$dventas = Sdba::table('detalle_ventas');
					$ddata = array('id_detalle'=>'','venta'=>$venta_id,'producto'=>$prod_id,'cantidad'=>$cantidad[$i],'precio'=>$precio[$i],'total'=>$total_pre[$i],'estado'=>'0');
					$dventas->insert($ddata);

					// Calcular nuevo stock total
					$cstock_total = isset($stock_total_map[$prod_id]) ? $stock_total_map[$prod_id] : 0;
					$stocktot = $cstock_total - $prod_cant;
					$stock_total_map[$prod_id] = $stocktot; // Actualizar mapa para siguiente iteración del mismo producto

					// Insertar registro de stock
					$motivo = 'v-'.$venta_id;
					$stock = Sdba::table('stock');
					$datas = array('id_stock'=>'','producto'=>$prod_id,'egreso'=>$cantidad[$i],'motivo'=>$motivo,'stock'=>$stocktot,'stockt'=>$stocktot,'fecha'=>$fecha, 'estado'=>'0');
					$stock->insert($datas);

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