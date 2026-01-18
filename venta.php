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

	$stockClass = ($stocktt <= 0) ? 'text-danger' : (($stocktt <= 5) ? 'text-warning' : 'text-success');

	$datos .='<div class="product-item" data-id="'.$value['id_producto'].'" data-nombre="'.htmlspecialchars($value['codigo_producto'].' '.$value['nom_prod'].' '.$marcan).'" data-unidad="'.$value['codigo'].'" data-stock="'.$stocktt.'" data-precio="'.$value['precio_venta'].'">
		<div class="product-info">
			<div class="product-name">'.htmlspecialchars($value['codigo_producto'].' '.$value['nom_prod']).'</div>
			<div class="product-meta">
				<span class="badge bg-secondary">'.$value['codigo'].'</span>
				<span class="badge '.$stockClass.'">Stock: '.$stocktt.'</span>
			</div>
		</div>
		<div class="product-price">S/ '.number_format($value['precio_venta'], 2).'</div>
		<button type="button" class="btn-add-product"><i class="fas fa-plus"></i></button>
	</div>';
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
    <link rel="stylesheet" type="text/css" href="/assets/css/sweetalert2.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
            --products-panel-width: 380px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --dark-bg: #1a1d29;
            --darker-bg: #13151f;
            --text-light: #e0e0e0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f6fa; overflow-x: hidden; }

        /* Sidebar izquierdo */
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

        /* Contenido principal */
        .main-content { margin-left: var(--sidebar-width); margin-right: var(--products-panel-width); transition: all 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed-width); }

        .top-bar { background: white; padding: 15px 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { font-size: 1.3rem; font-weight: 600; color: #2d3436; margin: 0; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-info .avatar { width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem; }

        .content-container { padding: 20px; }
        .sub-nav { display: flex; gap: 8px; margin-bottom: 15px; flex-wrap: wrap; }
        .sub-nav .nav-btn { padding: 8px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; font-size: 0.85rem; }
        .sub-nav .nav-btn.active { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }

        .content-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .content-card .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .content-card .card-header-custom h5 { margin: 0; font-weight: 600; color: #2d3436; font-size: 1rem; }

        .form-label { font-weight: 600; color: #2d3436; margin-bottom: 5px; font-size: 0.85rem; }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 8px; padding: 8px 12px; transition: all 0.3s ease; font-size: 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }

        /* Tabla de items */
        .items-table { margin-top: 10px; max-height: 300px; overflow-y: auto; }
        .items-table table { font-size: 0.85rem; }
        .items-table thead th { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; font-weight: 600; padding: 8px 10px; font-size: 0.75rem; text-transform: uppercase; border: none; position: sticky; top: 0; }
        .items-table tbody td { padding: 8px 10px; vertical-align: middle; border-bottom: 1px solid #eee; }
        .items-table input { border: 1px solid #e0e0e0; border-radius: 4px; padding: 4px 6px; width: 60px; text-align: center; font-size: 0.85rem; }
        .items-table .borrar { background: linear-gradient(135deg, #ff416c, #ff4b2b); color: white; border: none; border-radius: 4px; width: 26px; height: 26px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .items-table .borrar:hover { transform: scale(1.1); }

        .total-display { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 15px 20px; border-radius: 10px; margin: 15px 0; display: flex; justify-content: space-between; align-items: center; }
        .total-display strong { font-size: 1.1rem; }
        .total-display input { background: transparent; border: none; color: white; font-size: 1.4rem; font-weight: 700; width: 120px; text-align: right; }

        .btn-submit { background: linear-gradient(135deg, #56ab2f, #a8e063); border: none; padding: 12px 25px; font-weight: 600; border-radius: 10px; color: white; width: 100%; font-size: 1rem; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(86,171,47,0.4); color: white; }

        /* Panel de productos (sidebar derecho) */
        .products-panel { position: fixed; right: 0; top: 0; width: var(--products-panel-width); height: 100vh; background: white; box-shadow: -4px 0 15px rgba(0,0,0,0.1); z-index: 999; display: flex; flex-direction: column; }
        .products-header { padding: 15px 20px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .products-header h5 { margin: 0 0 10px 0; font-size: 1rem; font-weight: 600; }
        .products-search { position: relative; }
        .products-search input { width: 100%; padding: 10px 15px 10px 40px; border: none; border-radius: 8px; font-size: 0.9rem; }
        .products-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }

        .products-list { flex: 1; overflow-y: auto; padding: 10px; }
        .product-item { display: flex; align-items: center; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border-radius: 10px; cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; }
        .product-item:hover { border-color: var(--primary-color); background: #fff; transform: translateX(-5px); }
        .product-item .product-info { flex: 1; min-width: 0; }
        .product-item .product-name { font-weight: 600; font-size: 0.85rem; color: #2d3436; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-transform: uppercase; }
        .product-item .product-meta { display: flex; gap: 5px; margin-top: 4px; }
        .product-item .product-meta .badge { font-size: 0.7rem; font-weight: 500; }
        .product-item .product-price { font-weight: 700; color: var(--primary-color); font-size: 0.95rem; margin: 0 10px; white-space: nowrap; }
        .product-item .btn-add-product { width: 32px; height: 32px; border-radius: 8px; border: none; background: linear-gradient(135deg, #56ab2f, #a8e063); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
        .product-item .btn-add-product:hover { transform: scale(1.1); }

        .products-footer { padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; }
        .products-footer .badge { font-size: 0.9rem; }

        /* Toggle panel productos */
        .toggle-products { display: none; position: fixed; right: 20px; bottom: 20px; width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 1001; cursor: pointer; }

        /* Sin items mensaje */
        .no-items { text-align: center; padding: 30px; color: #999; }
        .no-items i { font-size: 3rem; margin-bottom: 10px; opacity: 0.5; }

        @media (max-width: 1400px) {
            :root { --products-panel-width: 340px; }
        }
        @media (max-width: 1200px) {
            .main-content { margin-right: 0; }
            .products-panel { transform: translateX(100%); transition: transform 0.3s ease; }
            .products-panel.show { transform: translateX(0); }
            .toggle-products { display: flex; align-items: center; justify-content: center; }
        }
        @media (max-width: 768px) {
            .sidebar { width: var(--sidebar-collapsed-width); }
            .main-content { margin-left: var(--sidebar-collapsed-width); }
            .sidebar-menu span { display: none; }
        }
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
            <div class="sub-nav">
                <a href="venta.php" class="nav-btn active"><i class="fas fa-plus me-2"></i>Registrar Venta</a>
                <a href="ventas.php" class="nav-btn"><i class="fas fa-list me-2"></i>Listar Ventas</a>
                <a href="ventap.php" class="nav-btn"><i class="fas fa-file-alt me-2"></i>Proforma</a>
                <a href="venta_comprobantes.php" class="nav-btn"><i class="fas fa-receipt me-2"></i>Comprobantes</a>
            </div>

            <div class="content-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-shopping-basket me-2"></i>Nueva Venta</h5>
                    <span class="badge bg-primary" id="items-count">0 items</span>
                </div>
                <form id="venta">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="fecha" value="<?php echo $newDate; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente"><?php echo $emplel; ?></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <option value="1">Contado</option>
                                <option value="2">Credito</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Forma de Pago</label>
                            <select class="form-select" name="forma">
                                <option value="1">Efectivo</option>
                                <option value="2">Tar. Debito</option>
                                <option value="3">Tar. Credito</option>
                                <option value="4">Credito</option>
                            </select>
                        </div>
                    </div>

                    <div class="items-table">
                        <table class="table table-sm mb-0" id="items">
                            <thead>
                                <tr>
                                    <th style="width:60px;">Cant.</th>
                                    <th>Descripcion</th>
                                    <th style="width:70px;">Unidad</th>
                                    <th style="width:70px;">Lote</th>
                                    <th style="width:70px;">Precio</th>
                                    <th style="width:80px;">Monto</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div class="no-items" id="no-items">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Agrega productos desde el panel derecho</p>
                        </div>
                    </div>

                    <div class="total-display">
                        <strong><i class="fas fa-calculator me-2"></i>TOTAL: S/</strong>
                        <input id="total" name="total" type="text" value="0.00" readonly>
                    </div>

                    <button type="button" id="guardar_venta" class="btn btn-submit">
                        <i class="fas fa-check-circle me-2"></i>Registrar Venta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel de Productos (Sidebar Derecho) -->
    <div class="products-panel" id="productsPanel">
        <div class="products-header">
            <h5><i class="fas fa-boxes me-2"></i>Agregar Productos</h5>
            <div class="products-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchProduct" placeholder="Buscar producto...">
            </div>
        </div>
        <div class="products-list" id="productsList">
            <?php echo $datos; ?>
        </div>
        <div class="products-footer">
            <span class="badge bg-primary"><?php echo count($ventas_list); ?> productos disponibles</span>
        </div>
    </div>

    <!-- Boton flotante para mostrar productos en movil -->
    <button class="toggle-products" id="toggleProducts" onclick="toggleProductsPanel()">
        <i class="fas fa-boxes"></i>
    </button>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-left');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-right');
        }

        function toggleProductsPanel() {
            document.getElementById('productsPanel').classList.toggle('show');
        }

        $(document).ready(function() {
            var total = 0;
            var itemCount = 0;

            function updateItemCount() {
                itemCount = $('#items tbody tr').length;
                $('#items-count').text(itemCount + ' items');
                if (itemCount > 0) {
                    $('#no-items').hide();
                } else {
                    $('#no-items').show();
                }
            }

            // Busqueda de productos
            $('#searchProduct').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                $('.product-item').each(function() {
                    var productName = $(this).data('nombre').toLowerCase();
                    if (productName.indexOf(searchText) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Agregar producto al hacer clic
            $('.btn-add-product').on('click', function(e) {
                e.stopPropagation();
                var item = $(this).closest('.product-item');
                addProduct(item);
            });

            // Agregar producto al hacer clic en toda la fila
            $('.product-item').on('click', function() {
                addProduct($(this));
            });

            function addProduct(item) {
                var id = item.data('id');
                var nombre = item.data('nombre');
                var unidad = item.data('unidad');
                var stock = item.data('stock');
                var precio = parseFloat(item.data('precio'));
                var cantidad = 1;
                var monto = precio;

                if (stock <= 0) {
                    Swal.fire('Sin Stock', 'Este producto no tiene stock disponible.', 'warning');
                    return;
                }

                total = total + monto;
                $("#total").val(total.toFixed(2));

                var newRow = '<tr>' +
                    '<input type="hidden" class="stocki" value="'+stock+'" name="stock[]">' +
                    '<input type="hidden" value="'+id+'" name="id_pro[]">' +
                    '<td><input class="cantidad" type="number" max="'+stock+'" value="'+cantidad+'" name="cantidad[]" min="1"></td>' +
                    '<td style="text-transform:uppercase; font-size:0.8rem;">'+nombre+'</td>' +
                    '<td style="text-transform:uppercase;">'+unidad+'</td>' +
                    '<td><input name="fv[]" type="text" class="fv" value="-"></td>' +
                    '<td><input type="number" class="pre" value="'+precio.toFixed(2)+'" name="precio[]" step="0.01"></td>' +
                    '<td><input class="mon" type="text" value="'+monto.toFixed(2)+'" name="total_pre[]" readonly></td>' +
                    '<td><button type="button" value="'+monto.toFixed(2)+'" class="borrar"><i class="fas fa-times"></i></button></td>' +
                '</tr>';

                $('#items tbody').append(newRow);
                updateItemCount();

                // Efecto visual
                item.css('background', '#d4edda');
                setTimeout(function() {
                    item.css('background', '#f8f9fa');
                }, 300);
            }

            // Eliminar item
            $('#items').on('click', '.borrar', function() {
                var resta = parseFloat($(this).val());
                $(this).closest('tr').remove();
                total = total - resta;
                if (total < 0) total = 0;
                $("#total").val(total.toFixed(2));
                updateItemCount();
            });

            // Actualizar al cambiar cantidad
            $('#items').on('change keyup', '.cantidad', function() {
                var row = $(this).closest('tr');
                var stock = parseFloat(row.find('.stocki').val());
                var cantidad = parseFloat($(this).val()) || 0;
                var anterior = parseFloat(row.find('.mon').val()) || 0;
                var precio = parseFloat(row.find('.pre').val()) || 0;

                if (cantidad > stock) {
                    cantidad = stock;
                    $(this).val(stock);
                    Swal.fire('Atencion', 'No puedes agregar mas del stock disponible.', 'info');
                }

                var monto = precio * cantidad;
                total = total - anterior + monto;
                $("#total").val(total.toFixed(2));
                row.find('.mon').val(monto.toFixed(2));
                row.find('.borrar').val(monto.toFixed(2));
            });

            // Actualizar al cambiar precio
            $('#items').on('change keyup', '.pre', function() {
                var row = $(this).closest('tr');
                var anterior = parseFloat(row.find('.mon').val()) || 0;
                var precio = parseFloat($(this).val()) || 0;
                var cantidad = parseFloat(row.find('.cantidad').val()) || 0;

                var monto = precio * cantidad;
                total = total - anterior + monto;
                $("#total").val(total.toFixed(2));
                row.find('.mon').val(monto.toFixed(2));
                row.find('.borrar').val(monto.toFixed(2));
            });

            // Guardar venta
            $('#guardar_venta').on('click', function(e) {
                e.preventDefault();

                if ($('#items tbody tr').length === 0) {
                    Swal.fire('Atencion', 'Debes agregar al menos un producto.', 'warning');
                    return;
                }

                var str2 = $('#venta').serialize();

                $.ajax({
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    url: "/inc/registrar_venta.php",
                    data: str2,
                    success: function(response) {
                        if (response.respuesta == false) {
                            Swal.fire('Advertencia', response.mensaje, 'warning');
                        } else {
                            Swal.fire({
                                title: 'Venta Registrada',
                                text: 'La venta se registro correctamente.',
                                icon: 'success',
                                confirmButtonColor: '#667eea'
                            }).then(function() {
                                document.location.href = "ver_venta.php?id=" + response.venta_id;
                            });
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error General del Sistema', 'error');
                    }
                });
            });

            updateItemCount();
        });
    </script>
</body>
</html>
