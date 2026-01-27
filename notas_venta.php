<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

// Verificar si mostrar todas o solo del mes
$mostrar_todas = isset($_GET['todas']) && $_GET['todas'] == '1';
$mes_actual = date('Y-m');
$mes_inicio = date('Y-m-01');

// Obtener ventas activas
$ventas = Sdba::table('ventas');
$ventas->where('estado !=', '2');
if (!$mostrar_todas) {
	$ventas->and_where('fecha >=', $mes_inicio);
}
$ventas_list = $ventas->get();

// Obtener IDs de ventas
$venta_ids = array();
foreach ($ventas_list as $v) {
	$venta_ids[] = $v['id_venta'];
}

// Obtener comprobantes de todas las ventas
$comprobantes_map = array();
if (!empty($venta_ids)) {
	$comp = Sdba::table('comprobantes');
	$comp->where_in('venta', $venta_ids);
	$comp->order_by('id_comprobante', 'desc');
	$comp_list = $comp->get();
	foreach ($comp_list as $c) {
		if (!isset($comprobantes_map[$c['venta']])) {
			$comprobantes_map[$c['venta']] = $c;
		}
	}
}

// Filtrar solo notas de venta (sin comprobante F o B)
$notas_venta_ids = array();
foreach ($ventas_list as $v) {
	$id_v = $v['id_venta'];
	$tiene_comp = isset($comprobantes_map[$id_v]) ? $comprobantes_map[$id_v]['tipo'] : '';
	if ($tiene_comp != 'B' && $tiene_comp != 'F') {
		$notas_venta_ids[] = $id_v;
	}
}

// Obtener detalle de ventas de las notas de venta
$productos_agrupados = array();
if (!empty($notas_venta_ids)) {
	$detalle = Sdba::table('detalle_ventas');
	$detalle->where_in('venta', $notas_venta_ids);
	$detalle->left_join('producto', 'productos', 'id_producto');
	$detalle_list = $detalle->get();

	// Agrupar por producto
	foreach ($detalle_list as $d) {
		$prod_id = $d['producto'];
		if (!isset($productos_agrupados[$prod_id])) {
			$productos_agrupados[$prod_id] = array(
				'id' => $prod_id,
				'codigo' => $d['codigo_producto'],
				'nombre' => $d['nom_prod'],
				'cantidad' => 0,
				'total' => 0,
				'ventas' => array()
			);
		}
		$productos_agrupados[$prod_id]['cantidad'] += floatval($d['cantidad']);
		$productos_agrupados[$prod_id]['total'] += floatval($d['total']);
		$productos_agrupados[$prod_id]['ventas'][$d['venta']] = true;
	}
}

// Generar filas de la tabla
$filas = array();
$i = 1;
foreach ($productos_agrupados as $prod) {
	$num_ventas = count($prod['ventas']);
	$filas[] = '<tr>
		<td>'.$i.'</td>
		<td>'.htmlspecialchars($prod['codigo']).'</td>
		<td style="text-transform:uppercase;">'.htmlspecialchars($prod['nombre']).'</td>
		<td class="text-right">'.$prod['cantidad'].'</td>
		<td class="text-right">S/ '.number_format($prod['total'], 2).'</td>
		<td class="text-center">'.$num_ventas.'</td>
	</tr>';
	$i++;
}
$datos = implode('', $filas);

$total_notas = count($notas_venta_ids);

// Nombre del mes en español
$meses = array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
$mes_nombre = $meses[date('m')] . ' ' . date('Y');
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Notas de Venta</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/custom.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
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
	      		<li>
	      			<a href="venta.php">Registrar venta</a>
	      		</li>
	      		<li>
	      			<a href="ventas.php">Listar ventas</a>
	      		</li>
	      		<li>
	      			<a href="ventap.php">Proforma</a>
	      		</li>
	      		<li>
	      			<a href="venta_comprobantes.php">Comprobantes</a>
	      		</li>
	      		<li class="active">
	      			<a href="notas_venta.php">Notas de Venta</a>
	      		</li>
	      	</ul>
	      </div>
	    </nav>
		<div class="kbg">
			<div class="cuerpofull">
				<div class="titulo">
					<h3>Productos en Notas de Venta<?php echo $mostrar_todas ? '' : ' - '.$mes_nombre; ?></h3>
					<p class="text-muted">
						Total de notas de venta: <strong><?php echo $total_notas; ?></strong>
						<?php if ($mostrar_todas): ?>
							<a href="notas_venta.php" class="btn btn-sm btn-default" style="margin-left:15px;">Ver solo este mes</a>
						<?php else: ?>
							<a href="notas_venta.php?todas=1" class="btn btn-sm btn-primary" style="margin-left:15px;">Ver todas</a>
						<?php endif; ?>
					</p>
				</div>
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<div class="kdashboard">
								<div class="row">
									<div class="col-md-12">
										<div class="panel panel-default pa">
											<div class="panel-body">
											    <table id="datos" class="table table-hover table-striped">
											    	<thead>
											    		<tr>
											    			<th>#</th>
											    			<th>Codigo</th>
											    			<th>Producto</th>
											    			<th class="text-right">Cantidad</th>
											    			<th class="text-right">Total</th>
											    			<th class="text-center">N Ventas</th>
											    		</tr>
											    	</thead>
											    	<tbody>
											    		<?php echo $datos; ?>
											    	</tbody>
											    </table>
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

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
	<script>
	$(document).ready(function() {
		$.extend(true, $.fn.dataTable.defaults, {
		    "language": {
		        "decimal": ",",
		        "thousands": ".",
		        "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
		        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
		        "infoPostFix": "",
		        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
		        "loadingRecords": "Cargando...",
		        "lengthMenu": "Mostrar _MENU_ registros",
		        "paginate": {
		            "first": "Primero",
		            "last": "Ultimo",
		            "next": "Siguiente",
		            "previous": "Anterior"
		        },
		        "processing": "Procesando...",
		        "search": "Buscar:",
		        "searchPlaceholder": "Buscar producto...",
		        "zeroRecords": "No se encontraron resultados",
		        "emptyTable": "No hay productos en notas de venta"
		    }
		});
		$('#datos').DataTable({
			"order": [[3, "desc"]] // Ordenar por cantidad descendente
		});
	});
	</script>
</body>
</html>
