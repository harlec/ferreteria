<?php
session_start();
$usuario = $_SESSION['usuario'];
$tienda = $_SESSION['tienda'];

include('inc/control.php');
$fecha = date('d-m-Y');
$newDate = date("Y-m-d", strtotime($fecha));

include('inc/sdba/sdba.php'); // include main file

//obtnemos colaboradores
$clientes = Sdba::table('clientes');
$el = $clientes->get();
foreach ($el as $value) {
	$emplel.='<option value="'.$value['id_cliente'].'">'.$value['cliente'].'</option>';
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
    <link rel="stylesheet" href="/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.0.5/sweetalert2.min.css">
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
	      		<li class="active">
	      			<a href="venta.php">Registrar venta</a>
	      		</li>
	      		<li >
	      			<a href="ventas.php">Listar ventas</a>
	      		</li>
	      		<li>
	      			<a href="ventap.php">Proforma</a>
	      		</li>
	      		<li>
	      			<a href="venta_comprobantes.php">Comprobantes</a>
	      		</li>
	      	</ul>
	      </div>
	    </nav>
		<div class="kbg">
			<div class="cuerpo">
				<div class="titulo">
					<h3>Registrar Venta</h3>
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
															    <label for="exampleInputPassword1">Fecha</label>
															    <input type="date" class="form-control" name="fecha" id="fecha" value="<?php echo $newDate; ?>" placeholder="monto">
															 </div>
											    		</div>
											    		<div class="col-md-3">
											    			<label for="exampleInputPassword1">Cliente</label>
															    <select class="form-control" name="cliente">
															    	<?php echo $emplel; ?>
															    </select>
											    		</div>
											    		<div class="col-md-3">
											    			<label for="exampleInputPassword1">Tipo</label>
															    <select class="form-control" name="tipo">
															    	<option value="1">Contado</option>
															    	<option value="2">Crédito</option>
															    </select>
											    		</div>
											    		<div class="col-md-3">
											    			<label for="exampleInputPassword1">Forma</label>
															    <select class="form-control" name="forma">
															    	<option value="1">Efectivo</option>
															    	<option value="2">Tar. Debito</option>
															    	<option value="3">Tar. Credito</option>
															    	<option value="4">Crédito</option>
															    </select>
											    		</div>
											    		<div class="col-md-12">
											    			<h3 class="text-center">Items</h3>
											    			
											    		</div>
											    	</div class="row">
											    	<div class="row">
											    		<div class="col-sm-2"></div>
											    		<div class="col-md-12">	
													    	<table id="items" class="table table-striped table-condensed">
																<thead>
																	<tr >
																		<th>Cantidad</th>
																		<th>Descripción</th>
																		<th>Unidad</th>
																		<th>Lote</th>
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
																<strong>Total: S/ </strong><input id="total" name="total" type="text" id="total">
															</div>
														</div>
														<div class="col-sm-2"></div>
											    	
												    </div>
												  <button type="button" id="guardar_venta" class="btn btn-success btn-block btn-lg">Registrar</button>
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
					    			<th>Lote</th>
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
	 	<!-- Tab panes -->
		

	  
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="/assets/js/jquery-ui.min.js"></script> 
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.0.5/sweetalert2.min.js"></script>
	<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
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

		$('#datos').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: '/inc/venta_ajax.php',
				type: 'GET'
			},
			columns: [
				{ title: "Producto" },
				{ title: "Unidad" },
				{ title: "Lote" },
				{ title: "Stock" },
				{ title: "Precio" },
				{ title: "", orderable: false, searchable: false }
			]
		});


		var total = 0;

		$('#datos').on('click', '#agregar', function(){
		    var nombre = $(this).closest('tr').find('.nom_prod').text();
		    var precio = $(this).closest('tr').find('.precio_venta').text();
		    var fv = $(this).closest('tr').find('.fv').text();
		    var unidad = $(this).closest('tr').find('.unidad').text();
		    var stock = $(this).closest('tr').find('.stock').text();
		    var cantidad = 1;
		    var id_p = $(this).val();
		    var monto = precio;

			$('input[type=search]').val('');
		    total = monto*1 + total*1;

		    //alert(stock);
		    console.log(stock);
		    if (stock <=0) {
		    	swal('Advertencia','No puedes agregar, no tienes stock.','warning');
		    	
		    }
		    else{
		    	$('#items tr:last').after('<tr class="child"><input type="hidden" class="stocki" value="'+stock+'" name="stock[]" ><input type="hidden" value="'+id_p+'" name="id_pro[]" ><td><input class="cantidad" type="number" max="'+stock+'" value="'+cantidad+'" name="cantidad[]"></td><td style="text-transform:uppercase;">'+nombre+'</td><td style="text-transform:uppercase;">'+unidad+'</td><td><input name="fv[]" type="text" class="fv" value="'+fv+'" ></td><td><input type="number" class="pre" value="'+precio+'" name="precio[]"></td><td ><input class="mon" type="text" value="'+monto+'" name="total_pre[]" ></td><td><button value="'+monto+'" class="borrar">x</button></td></tr>');
		    	$("#total").val(total);

		    }


		});
	    //borrar item
	    $("#items").on('click', '.borrar', function () {
		    //$(this).closest('tr').remove();
		    
		    var resta = $(this).val();
		    console.log(resta)
		    $(this).parents("tr").remove();
		    total = (total - resta*1).toFixed(2);

		    $("#total").val(total);
		});
		//actualizar item
		var monto1 = 0;
		$('body').on('change paste keyup',".cantidad", function(){
			var stock = $(this).closest('tr').find('.stocki').val();
			var cantidad = $(this).closest('tr').find('.cantidad').val();
			console.log(stock);
			console.log(cantidad);
			//if (cantidad <= stock ) {
				//$('.cantidad').on('change paste keyup', function(){
				var anterior = $(this).closest('tr').find('.mon').val();
				var precio = $(this).closest('tr').find('.pre').val();
				
				var monto1 =  precio*cantidad;


				total = (total - anterior + monto1).toFixed(2);
				monto1 = monto1.toFixed(2);
				$("#total").val(total);
				
				//alert(monto1);
				$(this).closest('tr').find('.mon').val(monto1);
				$(this).closest('tr').find('.borrar').val(monto1);
			//}
			//else{
				//alert('No cuenta con esa cantidad');
				console.log('no cuenta');
			//}
		});

		$('body').on('change paste keyup',".pre", function(){
		//$('.cantidad').on('change paste keyup', function(){
			var anterior = $(this).closest('tr').find('.mon').val();
			var precio = $(this).closest('tr').find('.pre').val();
			var cantidad = $(this).closest('tr').find('.cantidad').val();
			var monto1 =  precio*cantidad;

			total = (total - anterior + monto1).toFixed(2);
			$("#total").val(total);
			
			//alert(monto1);
			monto1 = monto1.toFixed(2);
			$(this).closest('tr').find('.mon').val(monto1);
			$(this).closest('tr').find('.borrar').val(monto1);
		});


	   		//autocompletamos el producto
		    $('#basics').autocomplete({
		      	source: function(request,response){
					var str = 'term='+request.term;
					//alert('entro');
					$.ajax({
							type:'GET',
							dataType: 'json',
							url: '/inc/autocomplete-producto.php',
							data: str,
							success: function(data){
								response(data);
								//$("#precio").val('12');
							}
					});
				}
				//minLength: 2
		    });

		    //obtenemos el precio
		    $('#basics').on('change paste keyup', function(){
		    	var str1 = 'producto='+$('#basics').val();
		    	//alert (str1);
		    	$.ajax({	
			    	type:'GET',
					dataType: 'json',
				  	url: '/inc/autocomplete-precio.php',
				  	data: str1,
				  	success: function(response) {
				   	 	$('#precio').val(response.precio);
				   	 	$('#id_p').val(response.id_p);
				   	 	
				  	}
				});
			});
		$('body').on('click',"#guardar_venta", function(e){
          e.preventDefault();

				
				//var tipoVenta = $('input:radio[name=pregunta]:checked').val();
				//DNI = $('#dni_ruc').val();

				var str2 = $('#venta').serialize();
				alert(str2);
				
				$.ajax({
					cache: false,
					type: "POST",
					dataType: "json",
					url: "/inc/registrar_venta.php",
					data: str2,
					success: function(response){

						if(response.respuesta == false){
							swal('Advertencia',response.mensaje,'warning');
							


						}else{

							swal('Perfecto', response.venta_id,'success');
							//var id_venta = response.id_venta;
							console.log(response.mesa);
							//$('#mostrarmesa').load('inc/mobile/ver_mesa.php?mesa='+ response.mesa);
							document.location.href = "ver_venta.php?id="+response.venta_id;
						
						}
					
					},
					error: function(){
						swal('Advertencia','Error General del Sistema','warning');
					}
				});
				
				$(this ).hide();
				//return false;

			
		});

		
	    console.log( "ready!" );
	});
		
	</script>
</body>
</html>