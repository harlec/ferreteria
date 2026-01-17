<?php
include('inc/control.php');
if ($_SESSION['type']=='operador') {
	header("Location: dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Agregar Colaborador</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .content-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05); }
        .content-card .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .content-card .card-header-custom h5 { margin: 0; font-weight: 600; color: #2d3436; }
        .sub-nav { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .sub-nav .nav-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sub-nav .nav-btn.active { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .sub-nav .nav-btn:not(.active) { background: #f0f0f0; color: #636e72; }
        .sub-nav .nav-btn:hover:not(.active) { background: #e0e0e0; }
        .form-label { font-weight: 600; color: #2d3436; margin-bottom: 8px; }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 10px; padding: 12px 15px; font-size: 1rem; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .input-group-text { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; color: white; border-radius: 10px 0 0 10px; }
        .input-group .form-control, .input-group .form-select { border-radius: 0 10px 10px 0; }
        .btn-submit { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 15px 40px; font-size: 1.1rem; font-weight: 600; border-radius: 10px; color: white; transition: all 0.3s ease; width: 100%; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); color: white; }
        .btn-cancel { background: #f0f0f0; border: none; padding: 15px 40px; font-size: 1.1rem; font-weight: 600; border-radius: 10px; color: #636e72; transition: all 0.3s ease; width: 100%; }
        .btn-cancel:hover { background: #e0e0e0; }
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
            <h1><i class="fas fa-user-plus me-2"></i>Registrar Colaborador</h1>
            <div class="user-info">
                <span>Bienvenido, <strong><?php echo strtoupper($_SESSION['usuario']); ?></strong></span>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
            </div>
        </div>

        <div class="content-container">
            <div class="sub-nav">
                <a href="agregar_usuario.php" class="nav-btn"><i class="fas fa-user-plus me-2"></i>Registrar Usuario</a>
                <a href="ver_usuarios.php" class="nav-btn"><i class="fas fa-list me-2"></i>Listar Usuarios</a>
                <a href="agregar_empleado.php" class="nav-btn active"><i class="fas fa-id-badge me-2"></i>Agregar Colaborador</a>
                <a href="ver_empleados.php" class="nav-btn"><i class="fas fa-id-card me-2"></i>Listar Colaboradores</a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-user-edit me-2"></i>Datos del Colaborador</h5>
                        </div>
                        <form id="venta">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">DNI</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control" name="dni" id="dni" placeholder="Documento de identidad" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Nombres</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="nombres" id="nombres" placeholder="Nombres" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Apellidos</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="apellidos" id="apellidos" placeholder="Apellidos" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Celular</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" class="form-control" name="celular" id="celular" placeholder="Número de celular">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Tienda</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-store"></i></span>
                                        <select name="ubicacion" class="form-select" required>
                                            <option value="1">Chimbote 1</option>
                                            <option value="2">Chimbote 2</option>
                                            <option value="3">Trujillo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Dirección</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Dirección completa">
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-6 mb-3">
                                    <a href="ver_empleados.php" class="btn btn-cancel"><i class="fas fa-times me-2"></i>Cancelar</a>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" id="guardar_venta" class="btn btn-submit"><i class="fas fa-save me-2"></i>Registrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
            $('body').on('click', "#guardar_venta", function(e) {
                e.preventDefault();
                var str2 = $('#venta').serialize();
                $.ajax({
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    url: "/inc/registrar_empleado.php",
                    data: str2,
                    success: function(response) {
                        if (response.respuesta == false) {
                            Swal.fire('Advertencia', response.mensaje, 'warning');
                        } else {
                            Swal.fire('Perfecto', 'Colaborador registrado correctamente', 'success');
                            document.location.href = "ver_empleados.php";
                        }
                    },
                    error: function() {
                        Swal.fire('Advertencia', 'Error General del Sistema', 'warning');
                    }
                });
            });
        });
    </script>
</body>
</html>
