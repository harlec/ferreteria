<?php
/* ===================================
   Modern UI Components Library
   Bootstrap 5 Compatible
   =================================== */

/**
 * Renderiza el head HTML con Bootstrap 5 y estilos modernos
 * @param string $title - Título de la página
 */
function renderModernHead($title = "Sistema Ferretería") {
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>' . $title . '</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
    <!-- Modern Styles -->
    <link rel="stylesheet" href="/assets/css/modern-styles.css">
</head>';
}

/**
 * Renderiza el sidebar moderno
 * @param string $activeMenu - Número del menú activo (1-7)
 */
function renderModernSidebar($activeMenu = '1') {
    $menuType = $_SESSION['type'];

    // Menú items según tipo de usuario
    $adminMenu = [
        ['url' => 'dashboard.php', 'icon' => 'fa-home', 'label' => 'Escritorio', 'id' => '1'],
        ['url' => 'ver_usuarios.php', 'icon' => 'fa-users', 'label' => 'Usuarios', 'id' => '2'],
        ['url' => 'ver_clientes.php', 'icon' => 'fa-user-tie', 'label' => 'Clientes', 'id' => '7'],
        ['url' => 'ver_productos.php', 'icon' => 'fa-box', 'label' => 'Productos', 'id' => '3'],
        ['url' => 'venta.php', 'icon' => 'fa-shopping-cart', 'label' => 'Ventas', 'id' => '4'],
        ['url' => 'compra.php', 'icon' => 'fa-truck', 'label' => 'Compras', 'id' => '6'],
        ['url' => 'reportes.php', 'icon' => 'fa-chart-bar', 'label' => 'Reportes', 'id' => '5']
    ];

    $operatorMenu = [
        ['url' => 'dashboard.php', 'icon' => 'fa-home', 'label' => 'Escritorio', 'id' => '1'],
        ['url' => 'venta.php', 'icon' => 'fa-shopping-cart', 'label' => 'Ventas', 'id' => '4']
    ];

    $menuItems = ($menuType == 'admin') ? $adminMenu : $operatorMenu;

    echo '<div class="sidebar" id="sidebar" style="
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 260px;
        background: linear-gradient(180deg, #1a1d29 0%, #13151f 100%);
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        z-index: 1000;
    ">
        <div class="sidebar-header" style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
            <img src="/assets/img/harlec-sistema.png" alt="Logo" class="img-fluid" style="max-height: 50px;">
            <h4 style="color: white; margin-top: 10px; font-size: 1rem; font-weight: 600;">Ferretería</h4>
        </div>
        <div class="toggle-btn" onclick="toggleSidebar()" style="
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
        ">
            <i class="fas fa-chevron-left" id="toggle-icon"></i>
        </div>
        <ul class="sidebar-menu" style="list-style: none; padding: 20px 0; margin: 0;">';

    foreach ($menuItems as $item) {
        $activeClass = ($item['id'] == $activeMenu) ? 'active' : '';
        $activeStyle = ($item['id'] == $activeMenu) ? 'background: linear-gradient(90deg, #667eea, #764ba2); border-left: 4px solid white;' : '';

        echo '<li style="margin-bottom: 5px;">
            <a href="' . $item['url'] . '" class="' . $activeClass . '" style="
                display: flex;
                align-items: center;
                padding: 15px 25px;
                color: #e0e0e0;
                text-decoration: none;
                transition: all 0.3s ease;
                ' . $activeStyle . '
            ">
                <i class="fas ' . $item['icon'] . '" style="width: 25px; font-size: 1.2rem; margin-right: 15px;"></i>
                <span>' . $item['label'] . '</span>
            </a>
        </li>';
    }

    echo '<li style="margin-bottom: 5px;">
            <a href="salir.php" style="
                display: flex;
                align-items: center;
                padding: 15px 25px;
                color: #e0e0e0;
                text-decoration: none;
                transition: all 0.3s ease;
            ">
                <i class="fas fa-sign-out-alt" style="width: 25px; font-size: 1.2rem; margin-right: 15px;"></i>
                <span>Salir</span>
            </a>
        </li>
    </ul>
    </div>';
}

/**
 * Renderiza la barra superior
 * @param string $pageTitle - Título de la página actual
 */
function renderTopBar($pageTitle = "Panel de Control") {
    echo '<div class="top-bar" style="
        background: white;
        padding: 20px 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">
        <h1 style="font-size: 1.5rem; font-weight: 600; color: #2d3436; margin: 0;">
            ' . $pageTitle . '
        </h1>
        <div class="user-info" style="display: flex; align-items: center; gap: 15px;">
            <span>Bienvenido, <strong>' . strtoupper($_SESSION['usuario']) . '</strong></span>
            <div class="avatar" style="
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea, #764ba2);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 600;
            ">
                ' . strtoupper(substr($_SESSION['usuario'], 0, 1)) . '
            </div>
        </div>
    </div>';
}

/**
 * Renderiza scripts necesarios al final del body
 */
function renderModernScripts() {
    echo '
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery Validate -->
    <script src="/assets/js/jquery.validate.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="/assets/js/sweetalert2.all.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const icon = document.getElementById("toggle-icon");
            const mainContent = document.querySelector(".main-content");

            if (sidebar.style.width === "80px") {
                sidebar.style.width = "260px";
                icon.classList.remove("fa-chevron-right");
                icon.classList.add("fa-chevron-left");
                if (mainContent) mainContent.style.marginLeft = "260px";
            } else {
                sidebar.style.width = "80px";
                icon.classList.remove("fa-chevron-left");
                icon.classList.add("fa-chevron-right");
                if (mainContent) mainContent.style.marginLeft = "80px";
            }
        }
    </script>';
}

/**
 * Renderiza el contenedor principal con sidebar
 */
function startMainContent() {
    echo '<div class="main-content" style="
        margin-left: 260px;
        transition: all 0.3s ease;
        min-height: 100vh;
    ">';
}

/**
 * Cierra el contenedor principal
 */
function endMainContent() {
    echo '</div>';
}

/**
 * Renderiza una tabla moderna con DataTables
 * @param array $headers - Array de encabezados
 * @param string $tableId - ID de la tabla
 */
function renderModernTableHeader($headers, $tableId = "modernTable") {
    echo '<div class="modern-table-wrapper">
        <table class="table table-hover modern-table" id="' . $tableId . '">
            <thead>
                <tr>';

    foreach ($headers as $header) {
        echo '<th>' . $header . '</th>';
    }

    echo '</tr>
            </thead>
            <tbody>';
}

/**
 * Cierra una tabla moderna
 */
function renderModernTableFooter() {
    echo '</tbody>
        </table>
    </div>';
}

/**
 * Renderiza botones de acción para tablas
 * @param string $editUrl - URL de edición
 * @param string $deleteAction - Acción de eliminación
 * @param string $viewUrl - URL de vista (opcional)
 */
function renderTableActions($editUrl = "#", $deleteAction = "", $viewUrl = "") {
    echo '<div class="table-actions">';

    if (!empty($viewUrl)) {
        echo '<a href="' . $viewUrl . '" class="btn-action view" title="Ver">
            <i class="fas fa-eye"></i>
        </a>';
    }

    echo '<a href="' . $editUrl . '" class="btn-action edit" title="Editar">
        <i class="fas fa-edit"></i>
    </a>';

    if (!empty($deleteAction)) {
        echo '<button onclick="' . $deleteAction . '" class="btn-action delete" title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>';
    }

    echo '</div>';
}

/**
 * Renderiza un formulario moderno
 * @param string $title - Título del formulario
 * @param string $icon - Icono del formulario
 */
function startModernForm($title = "Formulario", $icon = "fa-edit") {
    echo '<div class="modern-form-wrapper">
        <div class="modern-form-header">
            <h4><i class="fas ' . $icon . '"></i> ' . $title . '</h4>
        </div>
        <form>';
}

/**
 * Cierra un formulario moderno
 */
function endModernForm() {
    echo '</form>
    </div>';
}

/**
 * Renderiza un badge de estado
 * @param string $text - Texto del badge
 * @param string $type - Tipo: success, danger, warning, info
 */
function renderBadge($text, $type = "success") {
    echo '<span class="badge-modern ' . $type . '">' . $text . '</span>';
}

?>
