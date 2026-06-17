<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$filtro_comp = isset($_GET['tipo_comp']) ? $_GET['tipo_comp'] : '';
$ajax_url = '/inc/ventas_ajax.php' . ($filtro_comp ? '?tipo_comp='.htmlspecialchars($filtro_comp) : '');

$page_length = 10;
$count_v = Sdba::table('ventas')->where('estado !=', '2')->total();
$count_p = Sdba::table('proforma')->where('estado !=', '2')->total();
$display_start = max(0, (ceil(($count_v + $count_p) / $page_length) - 1) * $page_length);
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
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/10.5.0/sweetalert2.min.css" integrity="sha512-YpZXdiMhuP3woCdvg0ou2UPj6l4KQUuf3gbMXTNMgtqTakMInX7h+64CTh+UIvYdA7ctBU2BAA/h4eEhoMEmsg==" crossorigin="anonymous" />
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
	      		<li class="active">
	      			<a href="ventas.php">Listar ventas</a>
	      		</li>
	      		<li>
	      			<a href="ventap.php">Proforma</a>
	      		</li>
	      		<li>
	      			<a href="venta_comprobantes.php">Comprobantes</a>
	      		</li>
	      		<li>
	      			<a href="notas_venta.php">Notas de Venta</a>
	      		</li>
	      		<li>
	      			<a href="ventas_credito.php">Créditos</a>
	      		</li>
	      	</ul>
	      </div>
	    </nav>
		<div class="kbg">
			<div class="cuerpofull">
				<div class="titulo">
					<h3>Ventas<?php
					if ($filtro_comp == 'B') echo ' - Boletas del mes';
					elseif ($filtro_comp == 'F') echo ' - Facturas del mes';
					elseif ($filtro_comp == 'NV') echo ' - Nota de venta del mes';
					?></h3>
					<?php if ($filtro_comp): ?>
					<a href="ventas.php" class="btn btn-default btn-sm">Ver todas las ventas</a>
					<?php endif; ?>
				</div>
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<div class="kdashboard">
								<div class="row">
									<div class="col-md-12">
										<div class="panel panel-default pa">
											<div class="panel-body">
											    <table id="datos" class="table table-hover">
											    	<thead>
											    		<tr>
											    			<th>#</th>
											    			<th>Venta</th>
											    			<th>Tipo</th>
											    			<th>Forma</th>
											    			<th>Fecha</th>
											    			<th>Monto</th>
											    			<th>Comprobante</th>
											    			<th>Cliente</th>
											    			<th>Opciones</th>
											    		</tr>
											    	</thead>
											    	<tbody></tbody>
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

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/10.5.0/sweetalert2.min.js" integrity="sha512-V9JHp52ZkrbVVjJqNz/XXYMUOyUfzaGKEGrcD2Ual7n39+UR1yJK0numAHZqkhhGTAH/Klj0KUe4btAZXccw9w==" crossorigin="anonymous"></script>
	<script>
	$(document).ready(function() {
		$.extend(true, $.fn.dataTable.defaults, {
		    "language": {
		        "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
		        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
		        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
		        "loadingRecords": "Cargando...",
		        "lengthMenu": "Mostrar _MENU_ registros",
		        "paginate": {
		            "first": "Primero",
		            "last": "Último",
		            "next": "Siguiente",
		            "previous": "Anterior"
		        },
		        "processing": "Procesando...",
		        "search": "Buscar:",
		        "searchPlaceholder": "Término de búsqueda",
		        "zeroRecords": "No se encontraron resultados",
		        "emptyTable": "Ningún dato disponible en esta tabla"
		    }
		});

		$('#datos').DataTable({
			processing: true,
			serverSide: true,
			displayStart: <?php echo $display_start; ?>,
			ajax: {
				url: '<?php echo $ajax_url; ?>',
				type: 'GET'
			},
			order: [[4, 'asc']],
			columns: [
				{ title: "#",           orderable: false, searchable: false },
				{ title: "Venta" },
				{ title: "Tipo" },
				{ title: "Forma" },
				{ title: "Fecha" },
				{ title: "Monto" },
				{ title: "Comprobante",  orderable: false, searchable: false },
				{ title: "Cliente" },
				{ title: "Opciones",     orderable: false, searchable: false }
			]
		});

		$('body').on('click', '.btn-borrar', function() {
			var id = $(this).val();
			Swal.fire({
				title: '¿Seguro de borrar?',
				text: 'No puedes revertir esto!',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Si, borrar!',
				cancelButtonText: 'Cancelar'
			}).then(function(result) {
				if (result.isConfirmed) {
					$.ajax({
						type: 'GET',
						dataType: 'json',
						url: '/inc/borrar_venta.php',
						data: 'id=' + id,
						success: function() {
							$('#datos').DataTable().ajax.reload();
							Swal.fire('Borrado!', 'El registro fue borrado correctamente.', 'success');
						}
					});
				}
			});
		});
	});
	</script>
</body>
</html>
