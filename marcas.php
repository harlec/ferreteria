<?php
include('inc/control.php');
if ($_SESSION['type']=='operador') {
	header("Location: dashboard.php");
}

include('inc/sdba/sdba.php');
$marcas = Sdba::table('marca');
$marcas_list = $marcas->get();
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Marcas</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
        .content-container { padding: 30px; display: flex; gap: 30px; }
        .main-panel { flex: 1; }
        .side-panel { width: 350px; }
        .content-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .content-card .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .content-card .card-header-custom h5 { margin: 0; font-weight: 600; color: #2d3436; }
        .sub-nav { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .sub-nav .nav-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sub-nav .nav-btn.active { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }
        .modern-table thead { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .modern-table thead th { color: white; font-weight: 600; padding: 12px 15px; font-size: 0.85rem; text-transform: uppercase; border: none; }
        .modern-table tbody tr { transition: all 0.3s ease; border-bottom: 1px solid #e0e0e0; }
        .modern-table tbody tr:hover { background: #f8f9fa; }
        .modern-table tbody td { padding: 12px 15px; vertical-align: middle; }
        .btn-action { width: 35px; height: 35px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-action.btn-edit { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
        .btn-action:hover { transform: translateY(-2px); }
        .form-label { font-weight: 600; color: #2d3436; margin-bottom: 8px; }
        .form-control { border: 2px solid #e0e0e0; border-radius: 10px; padding: 12px 15px; transition: all 0.3s ease; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn-submit { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 12px 30px; font-weight: 600; border-radius: 10px; color: white; width: 100%; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102,126,234,0.4); color: white; }
        .btn-cancel { background: #f0f0f0; border: none; padding: 12px 30px; font-weight: 600; border-radius: 10px; color: #636e72; width: 100%; }
        .btn-cancel:hover { background: #e0e0e0; }
        .dataTables_wrapper .dataTables_filter input { border: 2px solid #e0e0e0; border-radius: 8px; padding: 8px 15px; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important; color: white !important; border: none !important; border-radius: 8px; }
        @media (max-width: 992px) { .content-container { flex-direction: column; } .side-panel { width: 100%; } }
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
            <?php if ($_SESSION['type'] == 'admin') { echo '
                <li><a href="dashboard.php"><i class="fas fa-home"></i><span>Escritorio</span></a></li>
                <li><a href="ver_usuarios.php"><i class="fas fa-users"></i><span>Usuarios</span></a></li>
                <li><a href="ver_clientes.php"><i class="fas fa-user-tie"></i><span>Clientes</span></a></li>
                <li><a href="ver_productos.php" class="active"><i class="fas fa-box"></i><span>Productos</span></a></li>
                <li><a href="venta.php"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a></li>
                <li><a href="compra.php"><i class="fas fa-truck"></i><span>Compras</span></a></li>
                <li><a href="reportes.php"><i class="fas fa-chart-bar"></i><span>Reportes</span></a></li>
                <li><a href="salir.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>'; } ?>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-industry me-2"></i>Marcas</h1>
            <div class="user-info">
                <span>Bienvenido, <strong><?php echo strtoupper($_SESSION['usuario']); ?></strong></span>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
            </div>
        </div>

        <div class="content-container">
            <div class="main-panel">
                <div class="sub-nav">
                    <a href="agregar_producto.php" class="nav-btn"><i class="fas fa-plus me-2"></i>Registrar Producto</a>
                    <a href="ver_productos.php" class="nav-btn"><i class="fas fa-list me-2"></i>Listar Productos</a>
                    <a href="categorias.php" class="nav-btn"><i class="fas fa-tags me-2"></i>Categorías</a>
                    <a href="marcas.php" class="nav-btn active"><i class="fas fa-industry me-2"></i>Marcas</a>
                    <a href="colores.php" class="nav-btn"><i class="fas fa-palette me-2"></i>Colores</a>
                </div>
                <div class="content-card">
                    <div class="card-header-custom">
                        <h5><i class="fas fa-industry me-2"></i>Lista de Marcas</h5>
                        <span class="badge bg-primary"><?php echo count($marcas_list); ?> marcas</span>
                    </div>
                    <div class="table-responsive">
                        <table id="datos" class="table modern-table">
                            <thead><tr><th>ID</th><th>Marca</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php foreach ($marcas_list as $value) {
                                    echo '<tr>
                                        <td><strong>#' . $value['id_marca'] . '</strong></td>
                                        <td id="' . $value['id_marca'] . '" class="nom_cat">' . htmlspecialchars($value['marca']) . '</td>
                                        <td><button class="btn-action btn-edit editar_c" value="' . $value['id_marca'] . '" title="Editar"><i class="fas fa-edit"></i></button></td>
                                    </tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="side-panel">
                <div class="content-card">
                    <div class="card-header-custom">
                        <h5 id="tituc"><i class="fas fa-plus-circle me-2"></i>Nueva Marca</h5>
                    </div>
                    <form>
                        <div class="mb-4">
                            <label class="form-label">Nombre de la Marca</label>
                            <input class="form-control" type="text" name="categoria" id="categoria" placeholder="Ej: Stanley">
                            <input type="hidden" id="id" name="id">
                        </div>
                        <div class="row">
                            <div class="col-6"><button id="cancel" class="btn btn-cancel" type="button"><i class="fas fa-times me-2"></i>Cancelar</button></div>
                            <div class="col-6"><button id="guardar_cate" class="btn btn-submit" type="button" value="nuevo"><i class="fas fa-save me-2"></i>Guardar</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('collapsed'); document.getElementById('toggle-icon').classList.toggle('fa-chevron-left'); document.getElementById('toggle-icon').classList.toggle('fa-chevron-right'); }

        $(document).ready(function() {
            $.extend(true, $.fn.dataTable.defaults, { "language": { "search": "Buscar:", "lengthMenu": "Mostrar _MENU_", "info": "Mostrando _START_ a _END_ de _TOTAL_", "paginate": { "first": "«", "last": "»", "next": "›", "previous": "‹" }, "zeroRecords": "Sin resultados" } });
            var table1 = $('#datos').DataTable({ "pageLength": 10, "order": [[0, "desc"]] });

            $('body').on('click', "#guardar_cate", function() {
                var categoria = $('#categoria').val();
                var opcion = $(this).val();
                var id_ca = $('#id').val();
                var str1 = 'categoria=' + categoria + '&accion=' + opcion + '&id=' + id_ca;
                $.ajax({
                    type: "POST", dataType: 'json', url: '/inc/registrar_marca.php', data: str1,
                    success: function(data1) {
                        $('#categoria').val('');
                        if (data1.accion == 'nuevo') { table1.rows.add([[data1.id, data1.categoria, "<button class='btn-action btn-edit editar_c' value='" + data1.id + "'><i class='fas fa-edit'></i></button>"]]).draw(); }
                        else { table1.cell($('#' + data1.id)).data(data1.categoria).draw(); }
                        resetForm();
                    }
                });
            });

            $('body').on('click', ".editar_c", function() {
                var id = $(this).val();
                var nombre = $(this).closest('tr').find('.nom_cat').text();
                $('#tituc').html('<i class="fas fa-edit me-2"></i>Editar Marca');
                $('#categoria').val(nombre);
                $('#guardar_cate').html('<i class="fas fa-save me-2"></i>Editar').val('editar');
                $('#id').val(id);
            });

            function resetForm() { $('#tituc').html('<i class="fas fa-plus-circle me-2"></i>Nueva Marca'); $('#guardar_cate').html('<i class="fas fa-save me-2"></i>Guardar').val('nuevo'); $('#categoria').val(''); $('#id').val(''); }
            $('body').on('click', "#addnew, #cancel", resetForm);
        });
    </script>
</body>
</html>
