<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$tienda = $_SESSION['tienda'];

$filtro = date('Y-m');
$ventas1 = Sdba::table('comprobantes');
$ventas1->like('fecha', $filtro);
$ventas1->order_by('id_comprobante', 'asc');
$ventas_list1 = $ventas1->get();

$datos = '';
$i = 0;
foreach ($ventas_list1 as $value) {
	$anulado = '';
	$anul = '<span class="badge bg-success">No</span>';
	$muestra = 'style="display:none"';
	$muestra1 = '';
	$newDate = date("d/m/Y", strtotime($value['fecha']));

	switch ($value['tipo']) {
		case 'F':
			$tipo = '<span class="badge bg-primary">01 - Factura</span>';
			$enlacen = '<a title="Generar nota de credito" class="btn-action btn-note" href="generar_nota_credito_f.php?id='.$value['id_comprobante'].'"><i class="fas fa-file-invoice"></i></a>';
			break;
		case 'B':
			$tipo = '<span class="badge bg-info">03 - Boleta</span>';
			$enlacen = '<a title="Generar nota de credito" class="btn-action btn-note" href="generar_nota_credito_b.php?id='.$value['id_comprobante'].'"><i class="fas fa-file-invoice"></i></a>';
			break;
	}

	if ($value['state'] == '1') {
		$anulado = 'table-warning';
		$muestra = 'style="display:block"';
		$muestra1 = 'style="display:none"';
		$totalu = 0;
		$anul = '<span class="badge bg-warning">Pendiente</span>';
	}
	if ($value['state'] == '2') {
		$anulado = 'table-danger';
		$anul = '<span class="badge bg-danger">Si</span>';
		$muestra = 'style="display:none"';
		$muestra1 = 'style="display:none"';
		$totalu = 0;
	}
	if ($value['state'] == '0') {
		$totalu = $value['total'];
	}

	$datos .= '<tr class="'.$anulado.'">
		<td>'.$newDate.'</td>
		<td>'.$tipo.'</td>
		<td><strong>'.$value['serie'].'</strong></td>
		<td>'.$value['numero'].'</td>
		<td>'.$value['doc'].'</td>
		<td>'.htmlspecialchars($value['nombre']).'</td>
		<td class="text-end">S/ '.number_format($value['grabada'], 2).'</td>
		<td class="text-end">S/ '.number_format($value['igv'], 2).'</td>
		<td class="text-end"><strong>S/ '.number_format($totalu, 2).'</strong></td>
		<td class="text-center">'.$anul.'</td>
		<td class="text-center">
			<a title="Descargar PDF" class="btn-action btn-pdf" target="_blank" href="'.$value['url'].'.pdf"><i class="fas fa-file-pdf"></i></a>
		</td>
		<td class="text-center">'.$enlacen.'</td>
		<td class="text-center">
			<a title="Verificar anulacion" '.$muestra.' class="btn-action btn-check" href="verificar_anulacion.php?id='.$value['id_comprobante'].'"><i class="fas fa-check-circle"></i></a>
		</td>
	</tr>';
	$i++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Comprobantes</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
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
        .content-container { padding: 30px; }
        .content-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .content-card .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .content-card .card-header-custom h5 { margin: 0; font-weight: 600; color: #2d3436; }
        .sub-nav { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .sub-nav .nav-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sub-nav .nav-btn.active { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }
        .action-buttons { display: flex; gap: 10px; margin-bottom: 20px; }
        .action-buttons .btn-util { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; border: none; cursor: pointer; }
        .action-buttons .btn-util.btn-zip { background: linear-gradient(135deg, #f093fb, #f5576c); color: white; }
        .action-buttons .btn-util.btn-download { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
        .action-buttons .btn-util:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .modern-table thead { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .modern-table thead th { color: white; font-weight: 600; padding: 12px 15px; font-size: 0.8rem; text-transform: uppercase; border: none; }
        .modern-table tbody tr { transition: all 0.3s ease; border-bottom: 1px solid #e0e0e0; }
        .modern-table tbody tr:hover { background: #f8f9fa; }
        .modern-table tbody td { padding: 12px 15px; vertical-align: middle; font-size: 0.9rem; }
        .btn-action { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: none; cursor: pointer; text-decoration: none; }
        .btn-action.btn-pdf { background: linear-gradient(135deg, #ff416c, #ff4b2b); color: white; }
        .btn-action.btn-note { background: linear-gradient(135deg, #f093fb, #f5576c); color: white; }
        .btn-action.btn-check { background: linear-gradient(135deg, #56ab2f, #a8e063); color: white; }
        .btn-action:hover { transform: translateY(-2px); color: white; }
        .dataTables_wrapper .dataTables_filter input { border: 2px solid #e0e0e0; border-radius: 8px; padding: 8px 15px; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important; color: white !important; border: none !important; border-radius: 8px; }
        .dt-buttons .btn { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; color: white; border-radius: 8px; padding: 8px 15px; margin-right: 5px; }
        .dt-buttons .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .total-footer { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 15px 20px; border-radius: 10px; margin-top: 20px; display: flex; justify-content: flex-end; align-items: center; gap: 20px; }
        .total-footer span { font-size: 1.2rem; font-weight: 600; }
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
            <h1><i class="fas fa-receipt me-2"></i>Comprobantes Electronicos</h1>
            <div class="user-info">
                <span>Bienvenido, <strong><?php echo strtoupper($_SESSION['usuario']); ?></strong></span>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
            </div>
        </div>

        <div class="content-container">
            <div class="sub-nav">
                <a href="venta.php" class="nav-btn"><i class="fas fa-plus me-2"></i>Registrar Venta</a>
                <a href="ventas.php" class="nav-btn"><i class="fas fa-list me-2"></i>Listar Ventas</a>
                <a href="ventap.php" class="nav-btn"><i class="fas fa-file-alt me-2"></i>Proforma</a>
                <a href="venta_comprobantes.php" class="nav-btn active"><i class="fas fa-receipt me-2"></i>Comprobantes</a>
            </div>

            <div class="action-buttons">
                <a href="ju.php" class="btn-util btn-zip"><i class="fas fa-file-archive me-2"></i>Generar ZIP</a>
                <a href="descargar.php" class="btn-util btn-download"><i class="fas fa-download me-2"></i>Descargar Comprobantes</a>
            </div>

            <div class="content-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-file-invoice me-2"></i>Comprobantes del Mes - <?php echo date('F Y'); ?></h5>
                    <span class="badge bg-primary"><?php echo count($ventas_list1); ?> comprobantes</span>
                </div>
                <div class="table-responsive">
                    <table id="datos" class="table modern-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Serie</th>
                                <th>Numero</th>
                                <th>RUC/DNI</th>
                                <th>Denominacion</th>
                                <th>Gravada</th>
                                <th>IGV</th>
                                <th>Total</th>
                                <th>Anulado</th>
                                <th>PDF</th>
                                <th>N.Cred</th>
                                <th>Verif.</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="8" style="text-align:right">Total:</th>
                                <th id="total-footer"></th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php echo $datos; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-left');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-right');
        }

        $(document).ready(function() {
            $.extend(true, $.fn.dataTable.defaults, {
                "language": {
                    "search": "Buscar:",
                    "lengthMenu": "Mostrar _MENU_",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_",
                    "paginate": { "first": "«", "last": "»", "next": "›", "previous": "‹" },
                    "zeroRecords": "Sin resultados",
                    "emptyTable": "Sin comprobantes"
                }
            });

            var table = $('#datos').DataTable({
                "order": [[0, 'asc'], [1, 'asc'], [2, 'asc']],
                "pageLength": 25,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        title: 'Comprobantes - <?php echo $filtro; ?>',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        },
                        footer: true
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        },
                        footer: true
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i> Imprimir',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        }
                    }
                ],
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api();

                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[S\/\s,]/g, '') * 1 :
                            typeof i === 'number' ? i : 0;
                    };

                    var total = 0;
                    api.rows().data().each(function(el, index) {
                        total += parseFloat(intVal(el[8]));
                    });

                    $('#total-footer').html('<strong>S/ ' + total.toFixed(2) + '</strong>');
                }
            });
        });
    </script>
</body>
</html>
