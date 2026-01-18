<?php
session_start();
$usuario = $_SESSION['usuario'];
$tienda = $_SESSION['tienda'];

include('inc/control.php');
$fecha = date('d-m-Y');
$newDate = date("Y-m-d", strtotime($fecha));

include('inc/sdba/sdba.php'); // include main file
$ventas = Sdba::table('productos');
$ventas->left_join('unidad_prod','unidades','id_unidad'); // creating table object
$ventas_list = $ventas->get(); 

$datos = '';
$i = 1;
foreach ($ventas_list as $value) {

	$stock = Sdba::table('stock');
	$stock->where('producto',$value['id_producto']);
	$stock->order_by('id_stock','desc');
	$stockl = $stock->get_one();
	$stocktt = $stockl['stockt'];

	$marca = Sdba::table('marca');
	$marca->where('id_marca',$value['marca']);
	//$marca->order_by('id_stock','desc');
	$marca1 = $marca->get_one();
	$marcan = $marca1['marca'];

	
		$datos .='<tr> 
    			<td style="text-transform:uppercase;" class="nom_prod">'.$value['codigo_producto'].' '.$value['nom_prod'].' '.$marcan.'</td>
    			<td style="text-transform:uppercase;" class="unidad">'.$value['codigo'].'</td>
    			<td style="text-transform:uppercase;" class="fv">-</td>
    			<td class="stock">'.$stocktt.'</td>
    			<td class="precio_venta">'.$value['precio_venta'].'</td>  
    			<td><button id="agregar" value="'.$value['id_producto'].'" class="btn btn-xs btn-success"> + </button></td>
    		  </tr>';
	

	
    $i++;
}

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/sweetalert2.min.css">
    <style>
        :root { --sidebar-width: 260px; --sidebar-collapsed-width: 80px; --primary-color: #667eea; --secondary-color: #764ba2; --dark-bg: #1a1d29; --darker-bg: #13151f; --text-light: #e0e0e0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f6fa; overflow-x: hidden; }
        .sidebar { position: fixed; left: 0; top: 0; height: 100vh; width: var(--sidebar-width); background: linear-gradient(180deg, var(--dark-bg) 0%, var(--darker-bg) 100%); box-shadow: 4px 0 15px rgba(0,0,0,0.1); transition: all 0.3s ease; z-index: 1000; }
        .sidebar.collapsed { width: var(--sidebar-collapsed-width); }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header img { max-height: 50px; }
        .sidebar.collapsed .sidebar-header img { max-height: 35px; }
        .sidebar-header h4 { color: white; margin-top: 10px; font-size: 1rem; font-weight: 600; }
        .sidebar.collapsed .sidebar-header h4 { opacity: 0; font-size: 0; }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 25px; color: var(--text-light); text-decoration: none; transition: all 0.3s ease; }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.1); padding-left: 30px; }
        .sidebar-menu a.active { background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); border-left: 4px solid white; }
        .sidebar-menu i { width: 25px; font-size: 1.2rem; margin-right: 15px; }
        .sidebar.collapsed .sidebar-menu span { display: none; }
        .sidebar.collapsed .sidebar-menu a { justify-content: center; padding: 15px; }
        .sidebar.collapsed .sidebar-menu i { margin-right: 0; }
        .toggle-btn { position: absolute; right: -15px; top: 20px; width: 30px; height: 30px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .main-content { margin-left: var(--sidebar-width); transition: all 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed-width); }
        .top-bar { background: white; padding: 20px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { font-size: 1.5rem; font-weight: 600; color: #2d3436; margin: 0; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info .avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
        .content-container { padding: 20px; display: flex; gap: 20px; }
        .main-panel { flex: 1; }
        .side-panel { width: 800px; }
        .content-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .content-card .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .content-card .card-header-custom h5 { margin: 0; font-weight: 600; color: #2d3436; }
        .sub-nav { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .sub-nav .nav-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sub-nav .nav-btn.active { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }
        .form-label { font-weight: 600; color: #2d3436; margin-bottom: 8px; }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 10px; padding: 10px 15px; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .modern-table thead { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .modern-table thead th { color: white; font-weight: 600; padding: 10px 12px; font-size: 0.8rem; text-transform: uppercase; border: none; }
        .modern-table tbody tr { transition: all 0.3s ease; border-bottom: 1px solid #e0e0e0; }
        .modern-table tbody tr:hover { background: #f8f9fa; }
        .modern-table tbody td { padding: 10px 12px; vertical-align: middle; font-size: 0.85rem; }
        .btn-action { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-action.btn-add { background: linear-gradient(135deg, #56ab2f, #a8e063); color: white; }
        .btn-action.btn-remove { background: linear-gradient(135deg, #ff416c, #ff4b2b); color: white; }
        .btn-action:hover { transform: translateY(-2px); }
        .btn-submit { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 15px 30px; font-weight: 600; border-radius: 10px; color: white; width: 100%; font-size: 1.1rem; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102,126,234,0.4); color: white; }
        .items-table { margin-top: 15px; }
        .items-table input { border: 1px solid #e0e0e0; border-radius: 5px; padding: 5px 8px; width: 70px; text-align: center; }
        .items-table .borrar { background: #ff4757; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; }
        .total-display { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 15px 20px; border-radius: 10px; margin: 15px 0; display: flex; justify-content: space-between; align-items: center; }
        .total-display strong { font-size: 1.2rem; }
        .total-display input { background: transparent; border: none; color: white; font-size: 1.5rem; font-weight: 700; width: 120px; text-align: right; }
        .dataTables_wrapper .dataTables_filter input { border: 2px solid #e0e0e0; border-radius: 8px; padding: 8px 15px; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important; color: white !important; border: none !important; border-radius: 8px; }
        @media (max-width: 1200px) { .content-container { flex-direction: column; } .side-panel { width: 100%; } }
        @media (max-width: 768px) { .sidebar { width: var(--sidebar-collapsed-width); } .main-content { margin-left: var(--sidebar-collapsed-width); } .sidebar-menu span { display: none; } }
    </style>
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
					    	<tbody> 
					    		<?php echo $datos; ?>
					    	</tbody> 
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

		$('#datos').DataTable();


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