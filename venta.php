<?php
session_start();
$usuario = $_SESSION['usuario'];
$tienda = $_SESSION['tienda'];

include('inc/control.php');
$fecha = date('d-m-Y');
$newDate = date("Y-m-d", strtotime($fecha));

include('inc/sdba/sdba.php');
$ventas = Sdba::table('productos');
$ventas->left_join('unidad_prod','unidades','id_unidad');
$ventas_list = $ventas->get();

$datos = '';
foreach ($ventas_list as $value) {
	$stock = Sdba::table('stock');
	$stock->where('producto',$value['id_producto']);
	$stock->order_by('id_stock','desc');
	$stockl = $stock->get_one();
	$stocktt = $stockl['stockt'];

	$marca = Sdba::table('marca');
	$marca->where('id_marca',$value['marca']);
	$marca1 = $marca->get_one();
	$marcan = $marca1['marca'];

	$datos .='<tr>
		<td style="text-transform:uppercase;" class="nom_prod">'.$value['codigo_producto'].' '.$value['nom_prod'].' '.$marcan.'</td>
		<td style="text-transform:uppercase;" class="unidad">'.$value['codigo'].'</td>
		<td style="text-transform:uppercase;" class="fv">-</td>
		<td class="stock">'.$stocktt.'</td>
		<td class="precio_venta">'.$value['precio_venta'].'</td>
		<td><button id="agregar" value="'.$value['id_producto'].'" class="btn-action btn-add"><i class="fas fa-plus"></i></button></td>
	</tr>';
}

$clientes = Sdba::table('clientes');
$el = $clientes->get();
$emplel = '';
foreach ($el as $value) {
	$emplel.='<option value="'.$value['id_cliente'].'">'.$value['cliente'].'</option>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Registrar Venta</title>
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
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/assets/img/harlec-sistema.png" alt="Logo" class="img-fluid">
            <h4>Ferreteria</h4>
        </div>
        <div class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-chevron-left" id="toggle-icon"></i></div>
        <ul class="sidebar-menu">
            <?php if ($_SESSION['type'] == 'admin') { echo '
                <li><a href="dashboard.php"><i class="fas fa-home"></i><span>Escritorio</span></a></li>
                <li><a href="ver_usuarios.php"><i class="fas fa-users"></i><span>Usuarios</span></a></li>
                <li><a href="ver_clientes.php"><i class="fas fa-user-tie"></i><span>Clientes</span></a></li>
                <li><a href="ver_productos.php"><i class="fas fa-box"></i><span>Productos</span></a></li>
                <li><a href="venta.php" class="active"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                <li><a href="compra.php"><i class="fas fa-truck"></i><span>Compras</span></a></li>
                <li><a href="reportes.php"><i class="fas fa-chart-bar"></i><span>Reportes</span></a></li>
                <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>';
            } else { echo '
                <li><a href="dashboard.php"><i class="fas fa-home"></i><span>Escritorio</span></a></li>
                <li><a href="venta.php" class="active"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>';
            } ?>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-cash-register me-2"></i>Registrar Venta</h1>
            <div class="user-info">
                <span>Bienvenido, <strong><?php echo strtoupper($_SESSION['usuario']); ?></strong></span>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
            </div>
        </div>

        <div class="content-container">
            <div class="side-panel">
                <div class="sub-nav">
                    <a href="venta.php" class="nav-btn active"><i class="fas fa-plus me-2"></i>Registrar Venta</a>
                    <a href="ventas.php" class="nav-btn"><i class="fas fa-list me-2"></i>Listar Ventas</a>
                    <a href="ventap.php" class="nav-btn"><i class="fas fa-file-alt me-2"></i>Proforma</a>
                    <a href="venta_comprobantes.php" class="nav-btn"><i class="fas fa-receipt me-2"></i>Comprobantes</a>
                </div>
                <div class="content-card">
                    <div class="card-header-custom">
                        <h5><i class="fas fa-shopping-basket me-2"></i>Detalle de Venta</h5>
                    </div>
                    <form id="venta">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="fecha" id="fecha" value="<?php echo $newDate; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cliente</label>
                                <select class="form-select" name="cliente"><?php echo $emplel; ?></select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="tipo">
                                    <option value="1">Contado</option>
                                    <option value="2">Credito</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Forma de Pago</label>
                                <select class="form-select" name="forma">
                                    <option value="1">Efectivo</option>
                                    <option value="2">Tar. Debito</option>
                                    <option value="3">Tar. Credito</option>
                                    <option value="4">Credito</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3"><i class="fas fa-list-ul me-2"></i>Items de la Venta</h6>
                        <div class="table-responsive items-table">
                            <table id="items" class="table table-sm">
                                <thead style="background: #f8f9fa;">
                                    <tr>
                                        <th>Cant.</th>
                                        <th>Descripcion</th>
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
                        </div>

                        <div class="total-display">
                            <strong>TOTAL: S/</strong>
                            <input id="total" name="total" type="text" value="0.00" readonly>
                        </div>

                        <button type="button" id="guardar_venta" class="btn btn-submit">
                            <i class="fas fa-save me-2"></i>Registrar Venta
                        </button>
                    </form>
                </div>
            </div>

            <div class="main-panel">
                <div class="content-card">
                    <div class="card-header-custom">
                        <h5><i class="fas fa-boxes me-2"></i>Productos Disponibles</h5>
                        <span class="badge bg-primary"><?php echo count($ventas_list); ?> productos</span>
                    </div>
                    <div class="table-responsive">
                        <table id="datos" class="table modern-table">
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
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-left');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-right');
        }

        $(document).ready(function() {
            $.extend(true, $.fn.dataTable.defaults, {
                "language": { "search": "Buscar:", "lengthMenu": "Mostrar _MENU_", "info": "Mostrando _START_ a _END_ de _TOTAL_", "paginate": { "first": "«", "last": "»", "next": "›", "previous": "‹" }, "zeroRecords": "Sin resultados" }
            });
            $('#datos').DataTable({ "pageLength": 10, "order": [[0, "asc"]] });

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

                if (stock <= 0) {
                    Swal.fire('Advertencia', 'No puedes agregar, no tienes stock.', 'warning');
                } else {
                    $('#items tr:last').after('<tr class="child"><input type="hidden" class="stocki" value="'+stock+'" name="stock[]"><input type="hidden" value="'+id_p+'" name="id_pro[]"><td><input class="cantidad form-control form-control-sm" type="number" max="'+stock+'" value="'+cantidad+'" name="cantidad[]" style="width:60px;"></td><td style="text-transform:uppercase; font-size:0.8rem;">'+nombre+'</td><td style="text-transform:uppercase;">'+unidad+'</td><td><input name="fv[]" type="text" class="fv form-control form-control-sm" value="'+fv+'" style="width:70px;"></td><td><input type="number" class="pre form-control form-control-sm" value="'+precio+'" name="precio[]" style="width:70px;"></td><td><input class="mon form-control form-control-sm" type="text" value="'+monto+'" name="total_pre[]" style="width:70px;" readonly></td><td><button type="button" value="'+monto+'" class="borrar btn-action btn-remove"><i class="fas fa-times"></i></button></td></tr>');
                    $("#total").val(total.toFixed(2));
                }
            });

            $("#items").on('click', '.borrar', function() {
                var resta = $(this).val();
                $(this).parents("tr").remove();
                total = (total - resta*1).toFixed(2);
                $("#total").val(total);
            });

            $('body').on('change paste keyup', ".cantidad", function(){
                var stock = $(this).closest('tr').find('.stocki').val();
                var cantidad = $(this).closest('tr').find('.cantidad').val();
                var anterior = $(this).closest('tr').find('.mon').val();
                var precio = $(this).closest('tr').find('.pre').val();
                var monto1 = precio * cantidad;

                total = (total - anterior + monto1).toFixed(2);
                monto1 = monto1.toFixed(2);
                $("#total").val(total);
                $(this).closest('tr').find('.mon').val(monto1);
                $(this).closest('tr').find('.borrar').val(monto1);
            });

            $('body').on('change paste keyup', ".pre", function(){
                var anterior = $(this).closest('tr').find('.mon').val();
                var precio = $(this).closest('tr').find('.pre').val();
                var cantidad = $(this).closest('tr').find('.cantidad').val();
                var monto1 = precio * cantidad;

                total = (total - anterior + monto1).toFixed(2);
                $("#total").val(total);
                monto1 = monto1.toFixed(2);
                $(this).closest('tr').find('.mon').val(monto1);
                $(this).closest('tr').find('.borrar').val(monto1);
            });

            $('body').on('click', "#guardar_venta", function(e){
                e.preventDefault();
                var str2 = $('#venta').serialize();

                $.ajax({
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    url: "/inc/registrar_venta.php",
                    data: str2,
                    success: function(response){
                        if(response.respuesta == false){
                            Swal.fire('Advertencia', response.mensaje, 'warning');
                        } else {
                            Swal.fire('Perfecto', 'Venta registrada correctamente', 'success');
                            document.location.href = "ver_venta.php?id=" + response.venta_id;
                        }
                    },
                    error: function(){
                        Swal.fire('Advertencia', 'Error General del Sistema', 'warning');
                    }
                });
                $(this).hide();
            });
        });
    </script>
</body>
</html>
