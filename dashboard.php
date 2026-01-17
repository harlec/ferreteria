<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

// Obtener últimas 5 ventas (copiado de ventas.php)
$ventas = Sdba::table('ventas');
$ventas->where('usuario',$_SESSION['id_usr'])->and_where('estado !=','2');
if ($_SESSION['type'] =='admin') {
	$ventas->reset();
	$ventas->where('estado !=','2');
}
$ventas->order_by('id_venta','desc');
$ventas->limit(5);
$ventas_list = $ventas->get();

?>


<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Menu Principal</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/sweetalert2.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --dark-bg: #1a1d29;
            --darker-bg: #13151f;
            --text-light: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark-bg) 0%, var(--darker-bg) 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header img {
            max-height: 50px;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header img {
            max-height: 35px;
        }

        .sidebar-header h4 {
            color: white;
            margin-top: 10px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header h4 {
            opacity: 0;
            font-size: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            padding-left: 30px;
        }

        .sidebar-menu a.active {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-left: 4px solid white;
        }

        .sidebar-menu i {
            width: 25px;
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .sidebar.collapsed .sidebar-menu span {
            opacity: 0;
            display: none;
        }

        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
            padding: 15px;
        }

        .sidebar.collapsed .sidebar-menu i {
            margin-right: 0;
        }

        /* Toggle Button */
        .toggle-btn {
            position: absolute;
            right: -15px;
            top: 20px;
            width: 30px;
            height: 30px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3436;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Dashboard Cards */
        .dashboard-container {
            padding: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .stat-card.primary .icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .stat-card.success .icon {
            background: linear-gradient(135deg, #56ab2f, #a8e063);
            color: white;
        }

        .stat-card.warning .icon {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .stat-card.info .icon {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }

        .stat-card h6 {
            color: #636e72;
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card h3 {
            color: #2d3436;
            font-weight: 700;
            margin: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }

            .sidebar-menu span {
                display: none;
            }
        }

        /* Modern Table Styles */
        .modern-table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .modern-table thead th {
            color: white;
            font-weight: 600;
            padding: 12px 15px;
            text-align: left;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .modern-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #e0e0e0;
        }

        .modern-table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }

        .modern-table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .modern-table tbody tr:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/assets/img/harlec-sistema.png" alt="Logo" class="img-fluid">
            <h4>Ferretería</h4>
        </div>
        <div class="toggle-btn" onclick="toggleSidebar()">
            <i class="fas fa-chevron-left" id="toggle-icon"></i>
        </div>
        <ul class="sidebar-menu">
            <?php
            $menuType = $_SESSION['type'];
            $menuItems = '';

            if ($menuType == 'admin') {
                $menuItems = '
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i><span>Escritorio</span></a></li>
                    <li><a href="ver_usuarios.php"><i class="fas fa-users"></i><span>Usuarios</span></a></li>
                    <li><a href="ver_clientes.php"><i class="fas fa-user-tie"></i><span>Clientes</span></a></li>
                    <li><a href="ver_productos.php"><i class="fas fa-box"></i><span>Productos</span></a></li>
                    <li><a href="venta.php"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                    <li><a href="compra.php"><i class="fas fa-truck"></i><span>Compras</span></a></li>
                    <li><a href="reportes.php"><i class="fas fa-chart-bar"></i><span>Reportes</span></a></li>
                    <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>
                ';
            } else {
                $menuItems = '
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i><span>Escritorio</span></a></li>
                    <li><a href="venta.php"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                    <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>
                ';
            }
            echo $menuItems;
            ?>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1><i class="fas fa-chart-line me-2"></i>Panel de Control</h1>
            <div class="user-info">
                <span>Bienvenido, <strong><?php echo strtoupper($_SESSION['usuario']); ?></strong></span>
                <div class="avatar">
                    <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h6>Ventas del Día</h6>
                        <h3>S/ 0.00</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h6>Productos</h6>
                        <h3>0</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h6>Clientes</h6>
                        <h3>0</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info">
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h6>Ventas del Mes</h6>
                        <h3>S/ 0.00</h3>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-4">
                <div class="col-md-12">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Últimas Ventas</h5>
                            <a href="ventas.php" class="btn btn-sm btn-outline-primary">
                                Ver todas <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover modern-table">
                                <thead>
                                    <tr>
                                        <th>ID Venta</th>
                                        <th>Tipo</th>
                                        <th>Forma de Pago</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const icon = document.getElementById('toggle-icon');

            sidebar.classList.toggle('collapsed');

            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
        }

        $(document).ready(function() {
            console.log("Dashboard loaded!");
        });
    </script>
</body>
</html>