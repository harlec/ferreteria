<?php
include('inc/control.php');
if ($_SESSION['type']=='operador') {
	header("Location: dashboard.php");
}

include('inc/sdba/sdba.php');
$empleados = Sdba::table('empleados');
$empleados_list = $empleados->get();

?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Colaboradores</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f6fa; overflow-x: hidden; }
        .sidebar { position: fixed; left: 0; top: 0; height: 100vh; width: var(--sidebar-width); background: linear-gradient(180deg, var(--dark-bg) 0%, var(--darker-bg) 100%); box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; z-index: 1000; }
        .sidebar.collapsed { width: var(--sidebar-collapsed-width); }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-header img { max-height: 50px; transition: all 0.3s ease; }
        .sidebar.collapsed .sidebar-header img { max-height: 35px; }
        .sidebar-header h4 { color: white; margin-top: 10px; font-size: 1rem; font-weight: 600; transition: all 0.3s ease; }
        .sidebar.collapsed .sidebar-header h4 { opacity: 0; font-size: 0; }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 25px; color: var(--text-light); text-decoration: none; transition: all 0.3s ease; }
        .sidebar-menu a:hover { background: rgba(255, 255, 255, 0.1); padding-left: 30px; }
        .sidebar-menu a.active { background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); border-left: 4px solid white; }
        .sidebar-menu i { width: 25px; font-size: 1.2rem; margin-right: 15px; }
        .sidebar.collapsed .sidebar-menu span { opacity: 0; display: none; }
        .sidebar.collapsed .sidebar-menu a { justify-content: center; padding: 15px; }
        .sidebar.collapsed .sidebar-menu i { margin-right: 0; }
        .toggle-btn { position: absolute; right: -15px; top: 20px; width: 30px; height: 30px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); }
        .toggle-btn:hover { transform: scale(1.1); }
        .main-content { margin-left: var(--sidebar-width); transition: all 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed-width); }
        .top-bar { background: white; padding: 20px 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { font-size: 1.5rem; font-weight: 600; color: #2d3436; margin: 0; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info .avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
        .content-container { padding: 30px; }
        .content-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05); }
        .content-card .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .content-card .card-header-custom h5 { margin: 0; font-weight: 600; color: #2d3436; }
        .sub-nav { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .sub-nav .nav-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sub-nav .nav-btn.active { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }
        .modern-table thead { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .modern-table thead th { color: white; font-weight: 600; padding: 12px 10px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; }
        .modern-table tbody tr { transition: all 0.3s ease; border-bottom: 1px solid #e0e0e0; }
        .modern-table tbody tr:hover { background: #f8f9fa; }
        .modern-table tbody td { padding: 12px 10px; vertical-align: middle; font-size: 0.9rem; }
        .btn-action { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; margin: 0 2px; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-action:hover { transform: translateY(-2px); }
        .btn-action.btn-edit { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
        .btn-action.btn-delete { background: linear-gradient(135deg, #ff416c, #ff4b2b); color: white; }
        .badge-ubicacion { padding: 5px 10px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
        .badge-puntos { padding: 5px 10px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; background: linear-gradient(135deg, #56ab2f, #a8e063); color: white; }
        .dataTables_wrapper .dataTables_filter input { border: 2px solid #e0e0e0; border-radius: 8px; padding: 8px 15px; }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: var(--primary-color); outline: none; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important; color: white !important; border: none !important; border-radius: 8px; }
        @media (max-width: 768px) { .sidebar { width: var(--sidebar-collapsed-width); } .main-content { margin-left: var(--sidebar-collapsed-width); } .sidebar-menu span { display: none; } }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/assets/img/harlec-sistema.png" alt="Logo" class="img-fluid">
            <h4>Ferretería</h4>
        </div>
        <div class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-chevron-left" id="toggle-icon"></i></div>
        <ul class="sidebar-menu">
            <?php
            if ($_SESSION['type'] == 'admin') {
                echo '<li><a href="dashboard.php"><i class="fas fa-home"></i><span>Escritorio</span></a></li>
                    <li><a href="ver_usuarios.php" class="active"><i class="fas fa-users"></i><span>Usuarios</span></a></li>
                    <li><a href="ver_clientes.php"><i class="fas fa-user-tie"></i><span>Clientes</span></a></li>
                    <li><a href="ver_productos.php"><i class="fas fa-box"></i><span>Productos</span></a></li>
                    <li><a href="venta.php"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                    <li><a href="compra.php"><i class="fas fa-truck"></i><span>Compras</span></a></li>
                    <li><a href="reportes.php"><i class="fas fa-chart-bar"></i><span>Reportes</span></a></li>
                    <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>';
            } else {
                echo '<li><a href="dashboard.php"><i class="fas fa-home"></i><span>Escritorio</span></a></li>
                    <li><a href="venta.php"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                    <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>';
            }
            ?>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-id-badge me-2"></i>Gestión de Colaboradores</h1>
            <div class="user-info">
                <span>Bienvenido, <strong><?php echo strtoupper($_SESSION['usuario']); ?></strong></span>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
            </div>
        </div>

        <div class="content-container">
            <div class="sub-nav">
                <a href="agregar_usuario.php" class="nav-btn"><i class="fas fa-user-plus me-2"></i>Registrar Usuario</a>
                <a href="ver_usuarios.php" class="nav-btn"><i class="fas fa-list me-2"></i>Listar Usuarios</a>
                <a href="agregar_empleado.php" class="nav-btn"><i class="fas fa-id-badge me-2"></i>Agregar Colaborador</a>
                <a href="ver_empleados.php" class="nav-btn active"><i class="fas fa-id-card me-2"></i>Listar Colaboradores</a>
            </div>

            <div class="content-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-users me-2"></i>Lista de Colaboradores</h5>
                    <span class="badge bg-primary"><?php echo count($empleados_list); ?> colaboradores</span>
                </div>
                <div class="table-responsive">
                    <table id="datos" class="table modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DNI</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Email</th>
                                <th>Celular</th>
                                <th>Ubicación</th>
                                <th>Puntos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (is_array($empleados_list) && count($empleados_list) > 0) {
                                foreach ($empleados_list as $value) {
                                    $ubicaciones = ['1' => 'Chimbote 1', '2' => 'Chimbote 2', '3' => 'Trujillo'];
                                    $ubicacion = $ubicaciones[$value['ubicacion']] ?? 'Sin asignar';

                                    echo '<tr>
                                        <td><strong>#' . $value['id_empleado'] . '</strong></td>
                                        <td>' . htmlspecialchars($value['dni']) . '</td>
                                        <td>' . htmlspecialchars($value['nombres']) . '</td>
                                        <td>' . htmlspecialchars($value['apellidos']) . '</td>
                                        <td><i class="fas fa-envelope text-muted me-1"></i>' . htmlspecialchars($value['email']) . '</td>
                                        <td><i class="fas fa-phone text-muted me-1"></i>' . htmlspecialchars($value['celular']) . '</td>
                                        <td><span class="badge-ubicacion">' . $ubicacion . '</span></td>
                                        <td><span class="badge-puntos">' . $value['puntos'] . '</span></td>
                                        <td>
                                            <a href="editar_empleado.php?id=' . $value['id_empleado'] . '" class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                            <button class="btn-action btn-delete" id="borrar" value="' . $value['id_empleado'] . '" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-users fa-3x mb-3 d-block"></i>No hay colaboradores registrados</td></tr>';
                            }
                            ?>
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
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const icon = document.getElementById('toggle-icon');
            sidebar.classList.toggle('collapsed');
            icon.classList.toggle('fa-chevron-left');
            icon.classList.toggle('fa-chevron-right');
        }

        $(document).ready(function() {
            $.extend(true, $.fn.dataTable.defaults, {
                "language": { "search": "Buscar:", "lengthMenu": "Mostrar _MENU_", "info": "Mostrando _START_ a _END_ de _TOTAL_", "paginate": { "first": "«", "last": "»", "next": "›", "previous": "‹" }, "zeroRecords": "Sin resultados", "emptyTable": "Tabla vacía" }
            });
            $('#datos').DataTable({ "pageLength": 10, "order": [[0, "desc"]] });

            $('body').on('click', "#borrar", function() {
                var btn = $(this);
                Swal.fire({
                    title: '¿Eliminar colaborador?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'GET', dataType: 'json',
                            url: '/inc/borrar_empleado.php',
                            data: 'id=' + btn.val(),
                            success: function() {
                                Swal.fire('Eliminado', 'Colaborador eliminado', 'success').then(() => location.reload());
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
