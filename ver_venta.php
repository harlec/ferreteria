<?php
include('inc/control.php');
include('inc/sdba/sdba.php'); // include main file

$id = $_GET['id'];
$ventas = Sdba::table('detalle_ventas'); // creating table object
$ventas->where('venta', $id);
$ventas->left_join('producto','productos','id_producto');
$ventas_list = $ventas->get();

// Obtener todos los despachos de esta venta
$despachos_map = array(); // id_detalle => cantidad_despachada
$detalle_ids = array();
foreach ($ventas_list as $v) {
	$detalle_ids[] = $v['id_detalle'];
}
if (!empty($detalle_ids)) {
	$desp = Sdba::table('despachos');
	$desp->where_in('detalle', $detalle_ids);
	$desp_list = $desp->get();
	foreach ($desp_list as $d) {
		$det_id = $d['detalle'];
		if (!isset($despachos_map[$det_id])) {
			$despachos_map[$det_id] = 0;
		}
		$despachos_map[$det_id] += floatval($d['cantidad']);
	}
}

$ocultar = '';
$hay_despachos = !empty($desp_list);
$ventas1 = Sdba::table('ventas');
$ventas1->where('id_venta', $id);
$ventas_list1 = $ventas1->get_one();
if ($ventas_list1['estado']=='1') {
	$ocultar = 'ocultar';
} 


// Pagos de esta venta
$formas_nombre = array('1'=>'Efectivo','2'=>'Tar. Débito','3'=>'Tar. Crédito','4'=>'Crédito','5'=>'Yape','6'=>'Transferencia');
$pagos_q = Sdba::table('pagos');
$pagos_q->where('venta', $id);
$pagos_list = $pagos_q->get();

$datos = '';
$i = 1;
$tot = 0;
foreach ($ventas_list as $value) {
	$tot = $tot + $value['total'];
	$id_det = $value['id_detalle'];
	$cant = floatval($value['cantidad']);
	$despachado = isset($despachos_map[$id_det]) ? $despachos_map[$id_det] : 0;
	$pendiente = $cant - $despachado;

	// Color de fila según estado
	$row_class = '';
	if ($pendiente <= 0) {
		$row_class = 'success'; // Verde - todo despachado
	} elseif ($despachado > 0) {
		$row_class = 'warning'; // Amarillo - parcial
	}

	// Botón despachar solo si hay pendiente
	$btn_despachar = '';
	if ($pendiente > 0) {
		$btn_despachar = '<button class="btn btn-sm btn-info btn-despachar" data-detalle="'.$id_det.'" data-pendiente="'.$pendiente.'" data-nombre="'.htmlspecialchars($value['nom_prod']).'"><i class="fas fa-truck"></i></button>';
	}

	$datos .='<tr id="row-'.$id_det.'" class="'.$row_class.'">
    			<th scope="row">'.$i.'</th>
    			<td>'.$value['nom_prod'].'</td>
    			<td>'.$value['cantidad'].'</td>
    			<td class="td-despachado">'.$despachado.'</td>
    			<td class="td-pendiente">'.$pendiente.'</td>
    			<td>'.$value['precio'].'</td>
    			<td>'.$value['total'].'</td>
    			<td>'.$btn_despachar.'</td>
    		  </tr>';
    $i++;
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Menu Principal</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/custom.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/assets/css/sweetalert2.min.css">
</head>

<body class="mobile dashboard">
	<div class="">
		<nav class="navbar navbar-inverse navbar-fixed-top">
	      <div class="">
	        <div class="navbar-header">
	          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
	            <span class="sr-only">Toggle navigation</span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	          </button>
	          <a class="navbar-brand" href="#"><img class="img-responsive logo" src="/assets/img/harlec-sistema.png"></a>
	        </div>
	        <?php menu('4'); ?>
	      </div>
	      <div class="submenu">
	      	<ul class="subtop-tabs">
	      		<li >
	      			<a href="venta.php">Registrar venta</a>
	      		</li>
	      		<li >
	      			<a href="ventas.php">Listar ventas</a>
	      		</li>
	      	</ul>
	      </div>
	    </nav>
		<div class="kbg">
			<div class="cuerpofull">
				<div class="titulo">
					<h3>Venta <?php echo $id; ?></h3>
				</div>
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<div class="kdashboard">
								<div class="row">
									<div class="col-md-6">
										<div class="panel panel-default pa">
											<div class="panel-body">
												<p><strong>Venta id: <?php echo $id; ?></strong></p>
											    <table id="datos" class="table table-hover">
											    	<thead>
											    		<tr>
											    			<th>#</th>
											    			<th>Nombre</th>
											    			<th>Cant.</th>
											    			<th>Despachado</th>
											    			<th>Pendiente</th>
											    			<th>Precio</th>
											    			<th>Total</th>
											    			<th>Despacho</th>
											    		</tr>
											    	</thead> 
											    	<tbody> 
											    		<?php echo $datos; ?>

											    	</tbody> 
											    </table>
											    <p class="text-right"><strong>Total: S/ <?php echo $tot; ?></strong></p>

											    <?php if (!empty($pagos_list)): ?>
											    <div style="margin-bottom:10px;">
											    	<strong>Forma<?php echo count($pagos_list) > 1 ? 's' : ''; ?> de pago:</strong>
											    	<table class="table table-condensed" style="margin-top:5px;margin-bottom:0;">
											    		<tbody>
											    		<?php foreach ($pagos_list as $pg): ?>
											    			<tr>
											    				<td><?php echo isset($formas_nombre[$pg['forma']]) ? $formas_nombre[$pg['forma']] : 'Otro'; ?></td>
											    				<td class="text-right"><strong>S/ <?php echo number_format(floatval($pg['monto']), 2); ?></strong></td>
											    			</tr>
											    		<?php endforeach; ?>
											    		</tbody>
											    	</table>
											    </div>
											    <?php endif; ?>

											    <center>
											    	<a class="btn btn-success btn-lg <?php echo $ocultar;?>" href="factura.php?id=<?php echo $id; ?>">Factura</a>
												    <a class="btn btn-primary btn-lg <?php echo $ocultar;?>" href="boleta.php?id=<?php echo $id; ?>" target="_blank">Boleta</a>
												    <a class="btn btn-primary btn-lg <?php echo $ocultar;?>" href="recibo.php?id=<?php echo $id; ?>" target="_blank">Recibo</a>
												    <button type="button" id="btn_despachar_todo" class="btn btn-warning btn-lg">Despachar TODO y Generar Guía</button>
												    <?php if ($hay_despachos): ?>
												    <a href="guia_entrega.php?venta=<?php echo $id; ?>&despachado=1" target="_blank" class="btn btn-default btn-lg"><i class="fas fa-file-alt"></i> Ver Guía de Entrega</a>
												    <?php endif; ?>
												</center>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	 	<!-- Tab panes -->
		

	  
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="assets/js/sweetalert2.all.min.js"></script>
	<!-- Flotante Generar Guía -->
	<div id="panel-guia" style="display:none;position:fixed;bottom:20px;right:20px;z-index:9999;">
		<button id="btn_generar_guia" class="btn btn-success btn-lg">
			<i class="fas fa-file-alt"></i> Generar Guía (<span id="guia-count">0</span>)
		</button>
	</div>

	<script>
	$(document).ready(function() {
		var despachados_ids = [];
		var venta_id = <?php echo $id; ?>;

		function actualizarPanelGuia() {
			if (despachados_ids.length > 0) {
				$('#guia-count').text(despachados_ids.length);
				$('#panel-guia').show();
			} else {
				$('#panel-guia').hide();
			}
		}

		$(document).on('click', '.btn-despachar', function() {
			var $btn = $(this);
			var detalle  = $btn.data('detalle');
			var pendiente = parseFloat($btn.data('pendiente'));
			var nombre   = $btn.data('nombre');

			Swal.fire({
				title: 'Despachar: ' + nombre,
				input: 'number',
				inputLabel: 'Cantidad a despachar (max: ' + pendiente + ')',
				inputValue: pendiente,
				inputAttributes: { min: 0.01, max: pendiente, step: 0.01 },
				showCancelButton: true,
				confirmButtonText: 'Despachar',
				cancelButtonText: 'Cancelar',
				inputValidator: (value) => {
					if (!value || value <= 0) return 'Ingrese una cantidad mayor a 0';
					if (parseFloat(value) > pendiente) return 'No puede despachar mas de ' + pendiente;
				}
			}).then((result) => {
				if (!result.isConfirmed) return;
				var cantidad = parseFloat(result.value);
				$.ajax({
					type: 'POST',
					url: '/inc/registrar_despacho.php',
					data: { detalle: detalle, cantidad: cantidad, venta: venta_id },
					dataType: 'json',
					success: function(data) {
						if (data.success) {
							despachados_ids.push(data.id_despacho);
							actualizarPanelGuia();
							// Actualizar fila sin recargar
							var $row = $('#row-' + detalle);
							var despAnt = parseFloat($row.find('.td-despachado').text()) || 0;
							var nuevoDespachado = despAnt + cantidad;
							var cantTotal = parseFloat($row.find('td:nth-child(3)').text());
							var nuevoPendiente = cantTotal - nuevoDespachado;
							$row.find('.td-despachado').text(nuevoDespachado);
							$row.find('.td-pendiente').text(nuevoPendiente);
							if (nuevoPendiente <= 0) {
								$row.removeClass('warning').addClass('success');
								$btn.remove();
							} else {
								$row.addClass('warning');
								$btn.data('pendiente', nuevoPendiente);
							}
						} else {
							Swal.fire('Error', data.mensaje, 'error');
						}
					},
					error: function() { Swal.fire('Error', 'Error de conexion', 'error'); }
				});
			});
		});

		$('#btn_generar_guia').on('click', function() {
			window.open('guia_entrega.php?venta=' + venta_id + '&despachos=' + despachados_ids.join(','), '_blank');
		});

		$('#btn_despachar_todo').on('click', function() {
			Swal.fire({
				title: 'Despachar TODO',
				text: 'Se despachará todo lo pendiente y se generará la Guía de Entrega.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Sí, despachar todo',
				cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (!result.isConfirmed) return;
				$.ajax({
					type: 'POST',
					url: '/inc/despachar_todo.php',
					data: { venta: venta_id },
					dataType: 'json',
					success: function(data) {
						if (data.success) {
							// Usar modo todo=1: muestra todos los items del pedido completo
							window.open('guia_entrega.php?venta=' + venta_id + '&todo=1', '_blank');
							location.reload();
						} else {
							Swal.fire('Aviso', data.mensaje, 'info');
						}
					},
					error: function() { Swal.fire('Error', 'Error de conexion', 'error'); }
				});
			});
		});
	});
	</script>
</body>
</html>