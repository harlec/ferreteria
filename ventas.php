<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$tienda = $_SESSION['tienda'];
 // include main file
$ventas = Sdba::table('ventas');
$ventas->where('usuario',$_SESSION['id_usr'])->and_where('estado !=','2'); // creating table object
if ($_SESSION['type'] =='admin') {
	$ventas->reset();
	$ventas->where('estado !=','2');
}
// Filtro por mes si viene del dashboard
$filtro_comp = isset($_GET['tipo_comp']) ? $_GET['tipo_comp'] : '';
if ($filtro_comp) {
	$mes_inicio = date("Y-m-01");
	$ventas->and_where('fecha >=', $mes_inicio);
}
$ventas_list = $ventas->get();

// Obtener todos los IDs de ventas para consultar comprobantes en una sola query
$venta_ids = array();
foreach ($ventas_list as $v) {
	$venta_ids[] = $v['id_venta'];
}

// Consulta única de comprobantes para todas las ventas
$comprobantes_map = array();
if (!empty($venta_ids)) {
	$comp = Sdba::table('comprobantes');
	$comp->where_in('venta', $venta_ids);
	$comp->order_by('id_comprobante','desc');
	$comp_list = $comp->get();
	foreach ($comp_list as $c) {
		// Solo guardamos el primero (más reciente) por venta
		if (!isset($comprobantes_map[$c['venta']])) {
			$comprobantes_map[$c['venta']] = $c;
		}
	}
}

// Filtrar por tipo de comprobante si se solicita
if ($filtro_comp) {
	$ventas_filtradas = array();
	foreach ($ventas_list as $v) {
		$id_v = $v['id_venta'];
		$tiene_comp = isset($comprobantes_map[$id_v]) ? $comprobantes_map[$id_v]['tipo'] : '';
		if ($filtro_comp == 'B' && $tiene_comp == 'B') {
			$ventas_filtradas[] = $v;
		} elseif ($filtro_comp == 'F' && $tiene_comp == 'F') {
			$ventas_filtradas[] = $v;
		} elseif ($filtro_comp == 'NV' && ($tiene_comp != 'B' && $tiene_comp != 'F')) {
			$ventas_filtradas[] = $v;
		}
	}
	$ventas_list = $ventas_filtradas;
}

$filas = array();
$i = 1;
foreach ($ventas_list as $value) {
	$ocultar = '';
	$comprobante = '';
	$id = $value['id_venta'];
	$total_dventa = $value['total'];

	$ventas_list1 = isset($comprobantes_map[$id]) ? $comprobantes_map[$id] : null;

	if ($value['estado']=='1' && $ventas_list1) {
		$ocultar = 'ocultar';
		$comprobante = '<a title="Ver comprobante" target="_BLANK" href="'.$ventas_list1['url'].'">'.$ventas_list1['tipo'].''.$ventas_list1['numero'].'</a>';
	}

	$tipo = ($value['tipo']=='1') ? 'Contado' : 'Credito';

	switch ($value['forma']) {
		case '1': $forma = 'Efectivo'; break;
		case '2': $forma = 'Tar. Debito'; break;
		case '3': $forma = 'Tar. Crédito'; break;
		case '4': $forma = 'Crédito'; break;
		case '5': $forma = 'Yape'; break;
		case '6': $forma = 'Tranferencia'; break;
		default: $forma = ''; break;
	}

	$filas[] = '<tr>
    			<th scope="row">'.$i.'</th>
    			<td>v-'.$id.'</td>
    			<td>'.$tipo.'</td>
    			<td>'.$forma.'</td>
    			<td>'.$value['fecha'].'</td>
    			<td>'.$total_dventa.'</td>
    			<td>'.$comprobante.'</td>
    			<td><a title="Ver venta" class="btn btn-primary" href="ver_venta.php?id='.$id.'"><i class="fas fa-eye"></i></a><a class="btn btn-success '.$ocultar.'" href="factura.php?id='.$id.'" title="factura electrónica"><i class="fas fa-file-invoice-dollar"></i></a><a class="btn btn-danger '.$ocultar.'" href="boleta.php?id='.$id.'" title="boleta electrónica"><i class="fab fa-bitcoin"></i></a><button class="btn-custom" id="borrar" value="'.$id.'" title="borrar"><img src="assets/img/trash.png" /></button></td>
    		  </tr>';
	$i++;
}
$datos = implode('', $filas);


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
	      		<li >
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
											    			<th>Opciones</th>

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
	 	<!-- Tab panes -->
		

	  
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
	<script src="assets/js/sweetalert2.all.min.js"></script>
	<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/10.5.0/sweetalert2.min.js" integrity="sha512-V9JHp52ZkrbVVjJqNz/XXYMUOyUfzaGKEGrcD2Ual7n39+UR1yJK0numAHZqkhhGTAH/Klj0KUe4btAZXccw9w==" crossorigin="anonymous"></script>
	<script >
	// A $( document ).ready() block.
	$(document ).ready(function() {
		$.extend( true, $.fn.dataTable.defaults, {
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
		            "last": "Último",
		            "next": "Siguiente",
		            "previous": "Anterior"
		        },
		        "processing": "Procesando...",
		        "search": "Buscar:",
		        "searchPlaceholder": "Término de búsqueda",
		        "zeroRecords": "No se encontraron resultados",
		        "emptyTable": "Ningún dato disponible en esta tabla",
		        "aria": {
		            "sortAscending":  ": Activar para ordenar la columna de manera ascendente",
		            "sortDescending": ": Activar para ordenar la columna de manera descendente"
		        },
		        //only works for built-in buttons, not for custom buttons
		        "buttons": {
		            "create": "Nuevo",
		            "edit": "Cambiar",
		            "remove": "Borrar",
		            "copy": "Copiar",
		            "csv": "fichero CSV",
		            "excel": "tabla Excel",
		            "pdf": "documento PDF",
		            "print": "Imprimir",
		            "colvis": "Visibilidad columnas",
		            "collection": "Colección",
		            "upload": "Seleccione fichero...."
		        },
		        "select": {
		            "rows": {
		                _: '%d filas seleccionadas',
		                0: 'clic fila para seleccionar',
		                1: 'una fila seleccionada'
		            }
		        }
		    }           
		} ); 
		$('#datos').DataTable();

		$('body').on('click',"#borrar", function() {
	    	Swal.fire({
			  title: 'Seguro de borrar?',
			  text: "Tu no puedes revertir esto!",
			  icon: 'warning',
			  showCancelButton: true,
			  confirmButtonColor: '#3085d6',
			  cancelButtonColor: '#d33',
			  confirmButtonText: 'Si, borrar!'
			}).then((result) => {
			  if (result.isConfirmed) {
			  	var id = $(this).val();
				var str1 = 'id=' + id;
			  	$.ajax({	
			    	type:'GET',
					dataType: 'json',
				  	url: '/inc/borrar_venta.php',
				  	data: str1,
				  	success: function(data1) {
				   	 	console.log('borrado');
				   	 	document.location.href = "ventas.php"; 	
				  	}
				});
			    Swal.fire(
			      'Borrado!',
			      'El registro fue borrado correctamente.',
			      'success'
			    )
			  }
			})
 
		});



		

		
	    console.log( "ready!" );
	});
		
	</script>
</body>
</html>