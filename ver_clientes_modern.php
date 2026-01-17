<?php
include('inc/control.php');
if ($_SESSION['type']=='operador') {
	header("Location: dashboard.php");
}

include('inc/sdba/sdba.php');
include('inc/modern-components.php');

$ventas = Sdba::table('clientes');
$ventas_list = $ventas->get();

$datos = '';
$i = 1;
foreach ($ventas_list as $value) {
	$vc = Sdba::table('ventas');
	$vc->where('cliente',$value['id_cliente']);
	$cantidad = $vc->total();

	$datos .='<tr>
				<td>' . $value['id_cliente'] . '</td>
    			<td><strong>' . $value['cliente'] . '</strong></td>
    			<td><span class="badge bg-primary">' . $cantidad . '</span></td>
    			<td>' . $value['doc_identidad'] . '</td>
    			<td><i class="fas fa-phone text-success me-1"></i>' . $value['telefono'] . '</td>
    			<td><i class="fas fa-envelope text-info me-1"></i>' . $value['email'] . '</td>
    			<td>';

    $datos .= renderTableActions(
        'editar_cliente.php?id=' . $value['id_cliente'],
        'eliminarCliente(' . $value['id_cliente'] . ')',
        'ver_cliente.php?id=' . $value['id_cliente']
    );

    $datos .= '</td></tr>';
    $i++;
}

renderModernHead("Clientes - Sistema Ferretería");
?>

<body>
    <?php renderModernSidebar('7'); ?>

    <?php startMainContent(); ?>
        <?php renderTopBar('<i class="fas fa-user-tie me-2"></i>Gestión de Clientes'); ?>

        <div class="dashboard-container" style="padding: 30px;">
            <!-- Action Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="gradient-text mb-0">Listado de Clientes</h3>
                    <p class="text-muted mb-0">Administra todos tus clientes registrados</p>
                </div>
                <a href="agregar_cliente.php" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i>
                    Nuevo Cliente
                </a>
            </div>

            <!-- Search & Filter Bar -->
            <div class="search-filter-bar">
                <div class="search-input-wrapper">
                    <input type="text" id="searchInput" placeholder="Buscar por nombre, documento o email...">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <button class="btn-modern btn-modern-info">
                    <i class="fas fa-filter"></i>
                    Filtros
                </button>
                <button class="btn-modern btn-modern-success">
                    <i class="fas fa-file-excel"></i>
                    Exportar
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6>Total Clientes</h6>
                        <h3><?php echo $i - 1; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h6>Clientes Activos</h6>
                        <h3><?php echo $i - 1; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h6>Con Compras</h6>
                        <h3>0</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info">
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h6>Nuevos este Mes</h6>
                        <h3>0</h3>
                    </div>
                </div>
            </div>

            <!-- Modern Table -->
            <?php
            renderModernTableHeader([
                'ID',
                'Cliente',
                'Ventas',
                'Documento',
                'Teléfono',
                'Email',
                'Acciones'
            ], 'clientesTable');

            echo $datos;

            renderModernTableFooter();
            ?>
        </div>

    <?php endMainContent(); ?>

    <?php renderModernScripts(); ?>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with modern styling
            $('#clientesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                },
                pageLength: 10,
                order: [[0, 'desc']],
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            });

            // Custom search functionality
            $('#searchInput').on('keyup', function() {
                $('#clientesTable').DataTable().search(this.value).draw();
            });
        });

        function eliminarCliente(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esta acción",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f5576c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí iría la lógica de eliminación
                    Swal.fire(
                        'Eliminado!',
                        'El cliente ha sido eliminado.',
                        'success'
                    );
                }
            });
        }
    </script>

    <style>
        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-bottom: 15px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px;
            padding: 8px 15px;
            margin: 0 2px;
            border: none;
            background: transparent;
            color: #2d3436 !important;
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            color: white !important;
            border: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            color: white !important;
            border: none;
        }
    </style>
</body>
</html>
