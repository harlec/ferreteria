<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$hoy = date("Y-m-d");
$mes_inicio = date("Y-m-01");

// Ventas del día (estado != 2 = no anuladas)
$v_dia = Sdba::table('ventas');
$v_dia->where('fecha', $hoy)->and_where('estado !=', '2');
$ventas_dia_count = $v_dia->total();
$ventas_dia_total = $v_dia->sum('total');

// Ventas del mes
$v_mes = Sdba::table('ventas');
$v_mes->where('fecha >=', $mes_inicio)->and_where('estado !=', '2');
$ventas_mes_count = $v_mes->total();
$ventas_mes_total = $v_mes->sum('total');

// Total productos activos
$prod = Sdba::table('productos');
$prod->where('estado', '1');
$total_productos = $prod->total();

// Productos con stock bajo (stockp <= 5)
$prod_bajo = Sdba::table('productos');
$prod_bajo->where('estado', '1')->and_where('stockp <=', '5');
$stock_bajo = $prod_bajo->total();

// Ventas últimos 7 días para el gráfico
$chart_labels = array();
$chart_data = array();
for ($d = 6; $d >= 0; $d--) {
	$fecha_d = date("Y-m-d", strtotime("-$d days"));
	$chart_labels[] = date("d/m", strtotime($fecha_d));
	$v_chart = Sdba::table('ventas');
	$v_chart->where('fecha', $fecha_d)->and_where('estado !=', '2');
	$total_d = $v_chart->sum('total');
	$chart_data[] = $total_d ? $total_d : 0;
}

// Desglose por tipo de comprobante (mes actual)
$v_mes_q = Sdba::table('ventas');
$v_mes_q->where('fecha >=', $mes_inicio)->and_where('estado !=', '2');
$ventas_mes_list = $v_mes_q->get();

$venta_ids_mes = array();
$ventas_por_id = array();
foreach ($ventas_mes_list as $v) {
	$venta_ids_mes[] = $v['id_venta'];
	$ventas_por_id[$v['id_venta']] = $v;
}

// Comprobantes del mes en una sola query
$comp_tipo_map = array(); // id_venta => tipo
if (!empty($venta_ids_mes)) {
	$comp_q = Sdba::table('comprobantes');
	$comp_q->where_in('venta', $venta_ids_mes);
	$comp_list = $comp_q->get();
	foreach ($comp_list as $c) {
		// Solo el tipo principal (F o B), ignorar notas de crédito
		if (($c['tipo'] == 'F' || $c['tipo'] == 'B') && !isset($comp_tipo_map[$c['venta']])) {
			$comp_tipo_map[$c['venta']] = $c['tipo'];
		}
	}
}

// Calcular totales por tipo
$boleta_count = 0; $boleta_total = 0;
$factura_count = 0; $factura_total = 0;
$nota_count = 0; $nota_total = 0;

foreach ($ventas_mes_list as $v) {
	$id = $v['id_venta'];
	$total = floatval($v['total']);
	if (isset($comp_tipo_map[$id])) {
		if ($comp_tipo_map[$id] == 'B') {
			$boleta_count++;
			$boleta_total += $total;
		} else {
			$factura_count++;
			$factura_total += $total;
		}
	} else {
		$nota_count++;
		$nota_total += $total;
	}
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

<body class="mobile dashboard escritorio">
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
	        <?php menu('1'); ?>

	      </div>
	    </nav>
		<div class="kbg">
			<div class="container-fluid" style="padding-top: 15px;">
				<div class="row">
					<div class="col-md-3 col-sm-6">
						<div class="panel panel-default">
							<div class="panel-body text-center">
								<i class="fas fa-cash-register fa-2x" style="color:#5cb85c;"></i>
								<h4 style="margin:10px 0 5px;">S/ <?php echo number_format($ventas_dia_total, 2); ?></h4>
								<p style="color:#888; margin:0;">Ventas hoy (<?php echo $ventas_dia_count; ?>)</p>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6">
						<div class="panel panel-default">
							<div class="panel-body text-center">
								<i class="fas fa-calendar-alt fa-2x" style="color:#337ab7;"></i>
								<h4 style="margin:10px 0 5px;">S/ <?php echo number_format($ventas_mes_total, 2); ?></h4>
								<p style="color:#888; margin:0;">Ventas del mes (<?php echo $ventas_mes_count; ?>)</p>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6">
						<div class="panel panel-default">
							<div class="panel-body text-center">
								<i class="fas fa-boxes fa-2x" style="color:#f0ad4e;"></i>
								<h4 style="margin:10px 0 5px;"><?php echo $total_productos; ?></h4>
								<p style="color:#888; margin:0;">Productos activos</p>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6">
						<div class="panel panel-default">
							<div class="panel-body text-center">
								<i class="fas fa-exclamation-triangle fa-2x" style="color:#d9534f;"></i>
								<h4 style="margin:10px 0 5px;"><?php echo $stock_bajo; ?></h4>
								<p style="color:#888; margin:0;">Stock bajo (&le; 5)</p>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<a href="ventas.php?tipo_comp=B" style="text-decoration:none; color:inherit;">
							<div class="panel panel-default" style="cursor:pointer;">
								<div class="panel-body text-center">
									<i class="fas fa-file-invoice fa-2x" style="color:#5bc0de;"></i>
									<h4 style="margin:10px 0 5px;">S/ <?php echo number_format($boleta_total, 2); ?></h4>
									<p style="color:#888; margin:0;">Boletas del mes (<?php echo $boleta_count; ?>)</p>
								</div>
							</div>
						</a>
					</div>
					<div class="col-md-4">
						<a href="ventas.php?tipo_comp=F" style="text-decoration:none; color:inherit;">
							<div class="panel panel-default" style="cursor:pointer;">
								<div class="panel-body text-center">
									<i class="fas fa-file-invoice-dollar fa-2x" style="color:#5cb85c;"></i>
									<h4 style="margin:10px 0 5px;">S/ <?php echo number_format($factura_total, 2); ?></h4>
									<p style="color:#888; margin:0;">Facturas del mes (<?php echo $factura_count; ?>)</p>
								</div>
							</div>
						</a>
					</div>
					<div class="col-md-4">
						<a href="ventas.php?tipo_comp=NV" style="text-decoration:none; color:inherit;">
							<div class="panel panel-default" style="cursor:pointer;">
								<div class="panel-body text-center">
									<i class="fas fa-receipt fa-2x" style="color:#f0ad4e;"></i>
									<h4 style="margin:10px 0 5px;">S/ <?php echo number_format($nota_total, 2); ?></h4>
									<p style="color:#888; margin:0;">Nota de venta del mes (<?php echo $nota_count; ?>)</p>
								</div>
							</div>
						</a>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-body">
								<h5>Ventas - Últimos 7 días</h5>
								<canvas id="chartVentas" height="80"></canvas>
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
	<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
	<script src="assets/js/sweetalert2.all.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
	<script>
	$(document).ready(function() {
		var ctx = document.getElementById('chartVentas').getContext('2d');
		new Chart(ctx, {
			type: 'bar',
			data: {
				labels: <?php echo json_encode($chart_labels); ?>,
				datasets: [{
					label: 'Ventas (S/)',
					data: <?php echo json_encode($chart_data); ?>,
					backgroundColor: 'rgba(51, 122, 183, 0.7)',
					borderColor: 'rgba(51, 122, 183, 1)',
					borderWidth: 1
				}]
			},
			options: {
				responsive: true,
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							callback: function(value) {
								return 'S/ ' + value.toLocaleString();
							}
						}
					}
				},
				plugins: {
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'S/ ' + context.parsed.y.toLocaleString();
							}
						}
					}
				}
			}
		});
	});
	</script>
</body>
</html>