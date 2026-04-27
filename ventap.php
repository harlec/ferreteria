<?php
session_start();
$usuario = $_SESSION['usuario'];
$tienda  = $_SESSION['tienda'];

include('inc/control.php');
$fecha   = date('d-m-Y');
$newDate = date("Y-m-d", strtotime($fecha));

include('inc/sdba/sdba.php');

// ID del cliente "Varios"
$cl_varios = Sdba::table('clientes');
$cl_varios->where('cliente', 'VARIOS');
$varios_row = $cl_varios->get_one();
if ($varios_row) {
	$id_varios = $varios_row['id_cliente'];
} else {
	$cl_new = Sdba::table('clientes');
	$cl_new->insert(array('id_cliente'=>'','cliente'=>'VARIOS','doc_identidad'=>'','telefono'=>'','email'=>'','estado'=>'1'));
	$id_varios = $cl_new->insert_id();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Proforma</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.0.5/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
</head>
<body class="mobile dashboard">
<div class="">
	<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><img class="img-responsive logo" src="/assets/img/harlec-sistema.png"></a>
        </div>
        <?php menu('4'); ?>
      </div>
      <div class="submenu">
      	<ul class="subtop-tabs">
      		<li><a href="venta.php">Registrar venta</a></li>
      		<li><a href="ventas.php">Listar ventas</a></li>
      		<li class="active"><a href="ventap.php">Proforma</a></li>
      		<li><a href="venta_comprobantes.php">Comprobantes</a></li>
      		<li><a href="notas_venta.php">Notas de Venta</a></li>
      		<li><a href="ventas_credito.php">Créditos</a></li>
      	</ul>
      </div>
    </nav>
	<div class="kbg">
		<div class="cuerpo">
			<div class="titulo">
				<h3>Registrar Proforma</h3>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<div class="kdashboard">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-default pa">
										<div class="panel-body">
										    <form id="venta">
										    	<div class="row">
										    		<div class="col-md-3">
										    			<div class="form-group">
														    <label>Fecha</label>
														    <input type="date" class="form-control" name="fecha" id="fecha" value="<?php echo $newDate; ?>">
														</div>
										    		</div>
										    		<div class="col-md-3">
										    			<label>Cliente</label>
										    			<div class="input-group">
										    				<input type="text" class="form-control" id="cliente_texto" placeholder="Buscar o vacío (Varios)">
										    				<input type="hidden" name="cliente" id="cliente_id" value="<?php echo $id_varios; ?>">
										    				<span class="input-group-btn">
										    					<button type="button" class="btn btn-success" id="btn_nuevo_cliente" title="Agregar nuevo cliente"><i class="fas fa-plus"></i></button>
										    				</span>
										    			</div>
										    		</div>
										    		<input type="hidden" name="tipo" value="1">
										    		<div class="col-md-12">
										    			<h3 class="text-center">Items</h3>
										    		</div>
										    	</div>
										    	<div class="row">
										    		<div class="col-md-12">
												    	<table id="items" class="table table-striped table-condensed">
															<thead>
																<tr>
																	<th>Cantidad</th>
																	<th>Descripción</th>
																	<th>Unidad</th>
																	<th>Precio</th>
																	<th>Monto</th>
																	<th></th>
																</tr>
															</thead>
															<tbody>
																<tr></tr>
															</tbody>
														</table>
														<div class="text-right">
															<strong>Total: S/ </strong><input id="total" name="total" type="text">
														</div>
													</div>
											    </div>
											  <button type="button" id="guardar_venta" class="btn btn-success btn-block btn-lg">Registrar Proforma</button>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="detalles">
			<div class="titulo">
				<h3>Agregar productos</h3>
			</div>
			<div class="panel panel-default pa">
				<div class="panel-body">
				    <table id="datos" class="table table-hover table-responsive">
				    	<thead>
				    		<tr>
				    			<th>Producto</th>
				    			<th>Unidad</th>
				    			<th>Stock</th>
				    			<th>Precio</th>
				    			<th></th>
				    		</tr>
				    	</thead>
				    	<tbody></tbody>
				    </table>
				</div>
			</div>
		</div>
	</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="/assets/js/jquery-ui.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.0.5/sweetalert2.min.js"></script>
<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {

	$.extend(true, $.fn.dataTable.defaults, {
	    language: {
	        info: "Mostrando _START_ al _END_ de _TOTAL_ registros",
	        infoEmpty: "0 registros",
	        infoFiltered: "(filtrado de _MAX_)",
	        loadingRecords: "Cargando...",
	        lengthMenu: "Mostrar _MENU_ registros",
	        paginate: { first:"Primero", last:"Último", next:"Siguiente", previous:"Anterior" },
	        processing: "Procesando...",
	        search: "Buscar:",
	        searchPlaceholder: "Término de búsqueda",
	        zeroRecords: "No se encontraron resultados",
	        emptyTable: "Ningún producto disponible"
	    }
	});

	$('#datos').DataTable({
		processing: true,
		serverSide: true,
		ajax: { url: '/inc/venta_ajax.php', type: 'GET' },
		columns: [
			{ title: "Producto" },
			{ title: "Unidad" },
			{ title: "Stock" },
			{ title: "Precio" },
			{ title: "", orderable: false, searchable: false }
		]
	});

	var total = 0;

	$('#datos').on('click', '#agregar', function() {
	    var nombre  = $(this).closest('tr').find('.nom_prod').text();
	    var precio  = $(this).closest('tr').find('.precio_venta').text();
	    var unidad  = $(this).closest('tr').find('.unidad').text();
	    var stock   = $(this).closest('tr').find('.stock').text();
	    var id_p    = $(this).val();
	    var cantidad = 1;
	    var monto   = precio;

	    $('input[type=search]').val('');

	    if (stock <= 0) {
	    	swal('Advertencia', 'No puedes agregar, no tienes stock.', 'warning');
	    	return;
	    }

	    total = parseFloat(monto) + parseFloat(total);
	    $('#items tr:last').after(
	    	'<tr class="child">' +
	    	'<input type="hidden" value="' + id_p + '" name="id_pro[]">' +
	    	'<input type="hidden" name="fv[]" value="">' +
	    	'<td><input class="cantidad" type="number" value="' + cantidad + '" name="cantidad[]"></td>' +
	    	'<td style="text-transform:uppercase;">' + nombre + '</td>' +
	    	'<td style="text-transform:uppercase;">' + unidad + '</td>' +
	    	'<td><input type="number" class="pre" value="' + precio + '" name="precio[]"></td>' +
	    	'<td><input class="mon" type="text" value="' + monto + '" name="total_pre[]"></td>' +
	    	'<td><button value="' + monto + '" class="borrar">x</button></td>' +
	    	'</tr>'
	    );
	    $("#total").val(parseFloat(total).toFixed(2));
	});

	// Borrar item
	$("#items").on('click', '.borrar', function() {
	    var resta = $(this).val();
	    $(this).parents("tr").remove();
	    total = (parseFloat(total) - parseFloat(resta)).toFixed(2);
	    $("#total").val(total);
	});

	// Actualizar por cantidad
	$('body').on('change paste keyup', '.cantidad', function() {
		var anterior = $(this).closest('tr').find('.mon').val();
		var precio   = $(this).closest('tr').find('.pre').val();
		var cantidad = $(this).closest('tr').find('.cantidad').val();
		var monto    = precio * cantidad;
		total = (parseFloat(total) - parseFloat(anterior) + monto).toFixed(2);
		$(this).closest('tr').find('.mon').val(monto.toFixed(2));
		$(this).closest('tr').find('.borrar').val(monto.toFixed(2));
		$("#total").val(total);
	});

	// Actualizar por precio
	$('body').on('change paste keyup', '.pre', function() {
		var anterior = $(this).closest('tr').find('.mon').val();
		var precio   = $(this).closest('tr').find('.pre').val();
		var cantidad = $(this).closest('tr').find('.cantidad').val();
		var monto    = precio * cantidad;
		total = (parseFloat(total) - parseFloat(anterior) + monto).toFixed(2);
		$(this).closest('tr').find('.mon').val(monto.toFixed(2));
		$(this).closest('tr').find('.borrar').val(monto.toFixed(2));
		$("#total").val(total);
	});

	// Autocomplete cliente
	$('#cliente_texto').autocomplete({
		source: function(request, response) {
			$.ajax({
				type: 'GET', dataType: 'json',
				url: '/inc/autocomplete-cliente.php',
				data: { term: request.term },
				success: function(data) { response(data); }
			});
		},
		minLength: 1,
		select: function(event, ui) {
			$('#cliente_id').val(ui.item.id);
			$('#cliente_texto').val(ui.item.value);
			return false;
		}
	});

	$('#cliente_texto').on('input', function() {
		$('#cliente_id').val('<?php echo $id_varios; ?>');
	});

	// Nuevo cliente
	$('#btn_nuevo_cliente').on('click', function() {
		swal({
			title: 'Nuevo Cliente',
			html:
				'<input id="swal-nombre" class="swal2-input" placeholder="Nombre *">' +
				'<input id="swal-tel1" class="swal2-input" placeholder="Teléfono">' +
				'<input id="swal-tel2" class="swal2-input" placeholder="Teléfono 2">',
			showCancelButton: true,
			confirmButtonText: 'Guardar',
			cancelButtonText: 'Cancelar',
			preConfirm: function() {
				var nombre = $('#swal-nombre').val().trim();
				if (!nombre) { swal.showValidationError('El nombre es requerido'); return false; }
				return { nombre: nombre, telefono: $('#swal-tel1').val().trim(), telefono2: $('#swal-tel2').val().trim() };
			}
		}).then(function(result) {
			if (result.value) {
				$.ajax({
					type: 'POST', dataType: 'json',
					url: '/inc/registrar_cliente_rapido.php',
					data: result.value,
					success: function(resp) {
						if (resp.success) {
							$('#cliente_texto').val(resp.nombre);
							$('#cliente_id').val(resp.id);
							swal('Registrado', 'Cliente creado correctamente', 'success');
						} else {
							swal('Error', resp.mensaje, 'error');
						}
					}
				});
			}
		});
	});

	// Guardar proforma
	$('body').on('click', '#guardar_venta', function(e) {
		e.preventDefault();
		var str2 = $('#venta').serialize();
		$.ajax({
			cache: false, type: "POST", dataType: "json",
			url: "/inc/registrar_proforma.php",
			data: str2,
			success: function(response) {
				if (response.respuesta == false) {
					swal('Advertencia', response.mensaje, 'warning');
				} else {
					swal('Perfecto', 'Proforma registrada', 'success');
					document.location.href = "ver_proforma.php?id=" + response.venta_id;
				}
			},
			error: function() {
				swal('Advertencia', 'Error General del Sistema', 'warning');
			}
		});
		$(this).hide();
	});

	console.log("ready!");
});
</script>
</body>
</html>
