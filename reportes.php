<?php
include('inc/control.php');
if ($_SESSION['type']=='operador') {
	header("Location: dashboard.php");
}

$fecha = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Reportes</title>
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
        .content-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .content-card .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .content-card .card-header-custom h5 { margin: 0; font-weight: 600; color: #2d3436; }
        .sub-nav { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .sub-nav .nav-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sub-nav .nav-btn.active { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }
        .form-label { font-weight: 600; color: #2d3436; margin-bottom: 8px; }
        .form-control { border: 2px solid #e0e0e0; border-radius: 10px; padding: 12px 15px; transition: all 0.3s ease; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn-submit { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 12px 30px; font-weight: 600; border-radius: 10px; color: white; transition: all 0.3s ease; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102,126,234,0.4); color: white; }
        .report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .report-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: all 0.3s ease; border: 2px solid transparent; }
        .report-card:hover { transform: translateY(-5px); border-color: var(--primary-color); }
        .report-card .icon-wrapper { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; margin-bottom: 15px; }
        .report-card .icon-wrapper.stock { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .report-card .icon-wrapper.ventas { background: linear-gradient(135deg, #56ab2f, #a8e063); }
        .report-card .icon-wrapper.compras { background: linear-gradient(135deg, #ff416c, #ff4b2b); }
        .report-card .icon-wrapper.kardex { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .report-card .icon-wrapper.mas-vendido { background: linear-gradient(135deg, #667eea, #764ba2); }
        .report-card .icon-wrapper.diarias { background: linear-gradient(135deg, #ffecd2, #fcb69f); color: #2d3436; }
        .report-card h5 { font-weight: 600; color: #2d3436; margin-bottom: 10px; }
        .report-card p { color: #636e72; font-size: 0.9rem; margin-bottom: 15px; }
        .report-card .btn-report { width: 100%; padding: 10px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; text-align: center; transition: all 0.3s ease; }
        .report-card .btn-report.primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .report-card .btn-report.primary:hover { box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
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
                <li><a href="venta.php"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                <li><a href="compra.php"><i class="fas fa-truck"></i><span>Compras</span></a></li>
                <li><a href="reportes.php" class="active"><i class="fas fa-chart-bar"></i><span>Reportes</span></a></li>
                <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>'; } ?>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-chart-bar me-2"></i>Centro de Reportes</h1>
            <div class="user-info">
                <span>Bienvenido, <strong><?php echo strtoupper($_SESSION['usuario']); ?></strong></span>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
            </div>
        </div>

        <div class="content-container">
            <!-- Reporte Stock Form -->
            <div class="content-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-boxes me-2"></i>Generar Reporte de Stock</h5>
                </div>
                <form action="reportes_f.php" method="post" class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Corte</label>
                        <input type="date" class="form-control" name="fechaini" id="fechaini" value="<?php echo $fecha; ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-submit"><i class="fas fa-file-alt me-2"></i>Generar Reporte</button>
                    </div>
                </form>
            </div>

            <!-- Report Cards Grid -->
            <h5 class="mb-4" style="color: #2d3436; font-weight: 600;"><i class="fas fa-folder-open me-2"></i>Todos los Reportes</h5>
            <div class="report-grid">
                <div class="report-card">
                    <div class="icon-wrapper stock"><i class="fas fa-boxes"></i></div>
                    <h5>Reporte de Stock</h5>
                    <p>Consulta el inventario actual de todos los productos con sus cantidades disponibles.</p>
                    <a href="reportes.php" class="btn-report primary"><i class="fas fa-eye me-2"></i>Ver Reporte</a>
                </div>

                <div class="report-card">
                    <div class="icon-wrapper diarias"><i class="fas fa-calendar-day"></i></div>
                    <h5>Ventas Diarias</h5>
                    <p>Revisa las ventas realizadas en el dia con el detalle de cada transaccion.</p>
                    <a href="reportes_vd.php" class="btn-report primary"><i class="fas fa-eye me-2"></i>Ver Reporte</a>
                </div>

                <div class="report-card">
                    <div class="icon-wrapper ventas"><i class="fas fa-shopping-cart"></i></div>
                    <h5>Reporte de Ventas</h5>
                    <p>Analiza todas las ventas por periodo, cliente o producto vendido.</p>
                    <a href="reporte_ventas.php" class="btn-report primary"><i class="fas fa-eye me-2"></i>Ver Reporte</a>
                </div>

                <div class="report-card">
                    <div class="icon-wrapper compras"><i class="fas fa-truck-loading"></i></div>
                    <h5>Reporte de Compras</h5>
                    <p>Visualiza todas las compras realizadas a proveedores por periodo.</p>
                    <a href="reporte_compras.php" class="btn-report primary"><i class="fas fa-eye me-2"></i>Ver Reporte</a>
                </div>

                <div class="report-card">
                    <div class="icon-wrapper kardex"><i class="fas fa-clipboard-list"></i></div>
                    <h5>Reporte Kardex</h5>
                    <p>Seguimiento detallado de entradas y salidas de cada producto.</p>
                    <a href="reporte_kardex.php" class="btn-report primary"><i class="fas fa-eye me-2"></i>Ver Reporte</a>
                </div>

                <div class="report-card">
                    <div class="icon-wrapper mas-vendido"><i class="fas fa-trophy"></i></div>
                    <h5>Productos Mas Vendidos</h5>
                    <p>Descubre cuales son los productos con mayor rotacion en tu negocio.</p>
                    <a href="reporte_mv.php" class="btn-report primary"><i class="fas fa-eye me-2"></i>Ver Reporte</a>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
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
                    "buttons": { "excel": "Excel", "pdf": "PDF", "print": "Imprimir" }
                }
            });

            $('#datos').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excel', className: 'btn btn-success' },
                    { extend: 'pdf', className: 'btn btn-danger' },
                    { extend: 'print', className: 'btn btn-info' }
                ]
            });
        });
    </script>
</body>
</html>
