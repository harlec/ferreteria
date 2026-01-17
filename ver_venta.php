<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$id = $_GET['id'];
$ventas = Sdba::table('detalle_ventas');
$ventas->where('venta', $id);
$ventas->left_join('producto','productos','id_producto');
$ventas_list = $ventas->get();

$ocultar = '';
$ventas1 = Sdba::table('ventas');
$ventas1->where('id_venta', $id);
$ventas_list1 = $ventas1->get_one();
if ($ventas_list1['estado']=='1') {
	$ocultar = 'd-none';
}

$datos = '';
$i = 1;
$tot = 0;
foreach ($ventas_list as $value) {
	$tot = $tot + $value['total'];
	$datos .='<tr>
		<td><strong>'.$i.'</strong></td>
		<td>'.htmlspecialchars($value['nom_prod']).'</td>
		<td class="text-center">'.$value['cantidad'].'</td>
		<td class="text-end">S/ '.number_format($value['precio'], 2).'</td>
		<td class="text-end"><strong>S/ '.number_format($value['total'], 2).'</strong></td>
	</tr>';
    $i++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Ver Venta #<?php echo $id; ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }
        .modern-table thead { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .modern-table thead th { color: white; font-weight: 600; padding: 12px 15px; font-size: 0.85rem; text-transform: uppercase; border: none; }
        .modern-table tbody tr { transition: all 0.3s ease; border-bottom: 1px solid #e0e0e0; }
        .modern-table tbody tr:hover { background: #f8f9fa; }
        .modern-table tbody td { padding: 12px 15px; vertical-align: middle; }
        .total-box { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 20px; border-radius: 10px; text-align: right; margin-top: 20px; }
        .total-box h4 { margin: 0; font-size: 1.5rem; }
        .btn-comprobante { padding: 15px 30px; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-comprobante.btn-factura { background: linear-gradient(135deg, #56ab2f, #a8e063); color: white; }
        .btn-comprobante.btn-boleta { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-comprobante.btn-recibo { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
        .btn-comprobante:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); color: white; }
        .comprobantes-section { display: flex; gap: 15px; justify-content: center; margin-top: 25px; flex-wrap: wrap; }
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
            <h1><i class="fas fa-receipt me-2"></i>Detalle de Venta #<?php echo $id; ?></h1>
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
                <a href="venta_comprobantes.php" class="nav-btn"><i class="fas fa-receipt me-2"></i>Comprobantes</a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-shopping-cart me-2"></i>Venta #<?php echo $id; ?></h5>
                            <span class="badge bg-primary"><?php echo count($ventas_list); ?> items</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo $datos; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="total-box">
                            <h4><i class="fas fa-calculator me-2"></i>TOTAL: S/ <?php echo number_format($tot, 2); ?></h4>
                        </div>

                        <div class="comprobantes-section <?php echo $ocultar; ?>">
                            <a href="factura.php?id=<?php echo $id; ?>" class="btn-comprobante btn-factura">
                                <i class="fas fa-file-invoice-dollar"></i> Generar Factura
                            </a>
                            <a href="boleta.php?id=<?php echo $id; ?>" class="btn-comprobante btn-boleta">
                                <i class="fas fa-receipt"></i> Generar Boleta
                            </a>
                            <a href="recibo.php?id=<?php echo $id; ?>" class="btn-comprobante btn-recibo">
                                <i class="fas fa-file-alt"></i> Generar Recibo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-left');
            document.getElementById('toggle-icon').classList.toggle('fa-chevron-right');
        }
    </script>
</body>
</html>
