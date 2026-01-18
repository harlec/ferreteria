<?php
$activo = $menuActivo ?? '1';
$uno = ($activo == '1') ? 'active' : '';
$dos = ($activo == '2') ? 'active' : '';
$tres = ($activo == '3') ? 'active' : '';
$cuatro = ($activo == '4') ? 'active' : '';
$cinco = ($activo == '5') ? 'active' : '';
$seis = ($activo == '6') ? 'active' : '';
$siete = ($activo == '7') ? 'active' : '';

$usuario = $_SESSION['usuario'] ?? '';
$tipo = $_SESSION['type'] ?? '';
?>

<div id="navbar" class="navbar-collapse collapse">
    <ul class="nav navbar-nav menu">
        <li class="<?= $uno ?> text-center">
            <a title="Escritorio" href="/dashboard">
                <img class="isvg" src="<?= $__view->asset('img/dashboard.svg') ?>"><br>
                <span>Escritorio</span>
            </a>
        </li>

        <?php if ($tipo === 'admin'): ?>
        <li class="<?= $dos ?> text-center">
            <a title="Usuarios" href="/usuarios">
                <img class="isvg" src="<?= $__view->asset('img/users.png') ?>"><br>
                <span>Usuarios</span>
            </a>
        </li>

        <li class="<?= $siete ?> text-center">
            <a title="Clientes" href="/clientes">
                <img class="isvg" src="<?= $__view->asset('img/clientes.png') ?>"><br>
                <span>Clientes</span>
            </a>
        </li>

        <li class="<?= $tres ?> text-center">
            <a title="Productos" href="/productos">
                <img class="isvg" src="<?= $__view->asset('img/products.png') ?>"><br>
                <span>Productos</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="<?= $cuatro ?> text-center">
            <a title="Ventas" href="/ventas/crear">
                <img class="isvg" src="<?= $__view->asset('img/ventas.png') ?>"><br>
                <span>Ventas</span>
            </a>
        </li>

        <?php if ($tipo === 'admin'): ?>
        <li class="<?= $seis ?> text-center">
            <a title="Compras" href="/compras/crear">
                <img class="isvg" src="<?= $__view->asset('img/compras.png') ?>"><br>
                <span>Compras</span>
            </a>
        </li>

        <li class="<?= $cinco ?> text-center">
            <a title="Reportes" href="/reportes">
                <img class="isvg" src="<?= $__view->asset('img/reports.png') ?>"><br>
                <span>Reportes</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <ul id="right-top">
        <li>
            Hola <strong style="text-transform: uppercase;"><?= $__view->e($usuario) ?></strong>
            <a href="/logout">
                <img class="isvg" src="<?= $__view->asset('img/salir.png') ?>">
            </a>
        </li>
    </ul>
</div>
