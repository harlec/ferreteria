<?php
include('control.php');
include('sdba/sdba.php');

// Parametros de DataTables
$draw    = isset($_GET['draw'])                  ? (int)$_GET['draw']    : 1;
$start   = isset($_GET['start'])                 ? (int)$_GET['start']   : 0;
$length  = isset($_GET['length'])                ? (int)$_GET['length']  : 10;
$search  = isset($_GET['search']['value'])       ? $_GET['search']['value'] : '';
$orderColumn = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 4;
$orderDir    = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

// Filtros de sesión
$id_usr    = $_SESSION['id_usr'];
$tipo_usr  = $_SESSION['type'];
$filtro_comp = isset($_GET['tipo_comp']) ? $_GET['tipo_comp'] : '';

// Mapeo de columnas ordenables
$columnsOrder = [
    0 => 'v.id_venta',
    1 => 'v.id_venta',
    2 => 'v.tipo',
    3 => 'v.forma',
    4 => 'v.fecha',
    5 => 'v.total',
    6 => 'v.id_venta', // comprobante - order por id
    7 => 'cl.cliente',
    8 => 'v.id_venta'
];
$orderBy = isset($columnsOrder[$orderColumn]) ? $columnsOrder[$orderColumn] : 'v.fecha';

$db = Sdba::db();

// WHERE base
$whereParts = ["v.estado != '2'"];
if ($tipo_usr != 'admin') {
    $whereParts[] = "v.usuario = " . (int)$id_usr;
}
if ($filtro_comp) {
    $mes_inicio = date("Y-m-01");
    $whereParts[] = "v.fecha >= '{$mes_inicio}'";
    if ($filtro_comp == 'B') {
        $whereParts[] = "c.tipo = 'B'";
    } elseif ($filtro_comp == 'F') {
        $whereParts[] = "c.tipo = 'F'";
    } elseif ($filtro_comp == 'NV') {
        $whereParts[] = "(c.tipo IS NULL OR (c.tipo != 'B' AND c.tipo != 'F'))";
    }
}

// Busqueda
if ($search != '') {
    $s = $db->escape('%'.$search.'%', true);
    $whereParts[] = "(v.id_venta LIKE '{$s}' OR cl.cliente LIKE '{$s}' OR v.fecha LIKE '{$s}' OR v.total LIKE '{$s}')";
}

$where = 'WHERE ' . implode(' AND ', $whereParts);

// Query base con JOIN
$baseFrom = "FROM ventas v
    LEFT JOIN clientes cl ON v.cliente = cl.id_cliente
    LEFT JOIN comprobantes c ON c.venta = v.id_venta AND c.id_comprobante = (
        SELECT MAX(id_comprobante) FROM comprobantes WHERE venta = v.id_venta
    )";

// Total sin filtros del usuario (solo estado)
$whereTotal = "WHERE v.estado != '2'";
if ($tipo_usr != 'admin') {
    $whereTotal .= " AND v.usuario = " . (int)$id_usr;
}
$totalResult = $db->query("SELECT COUNT(*) as total FROM ventas v {$whereTotal}")->row();
$totalRecords = (int)$totalResult['total'];

// Total filtrado
$filteredResult = $db->query("SELECT COUNT(*) as total {$baseFrom} {$where}")->row();
$filteredRecords = (int)$filteredResult['total'];

// Query principal
$sql = "SELECT v.id_venta, v.tipo, v.forma, v.fecha, v.total, v.estado,
        cl.cliente as nombre_cliente,
        c.tipo as comp_tipo, c.numero as comp_numero, c.url as comp_url
    {$baseFrom}
    {$where}
    ORDER BY {$orderBy} {$orderDir}
    LIMIT {$start}, {$length}";

$data = $db->query($sql)->result();

// Mapas
$tipos_pago = ['1'=>'Contado','2'=>'Crédito'];
$formas_pago = ['1'=>'Efectivo','2'=>'Tar. Débito','3'=>'Tar. Crédito','4'=>'Crédito','5'=>'Yape','6'=>'Transferencia'];

$result = [];
$num = $start + 1;
foreach ($data as $row) {
    $id = $row['id_venta'];
    $tipo  = isset($tipos_pago[$row['tipo']]) ? $tipos_pago[$row['tipo']] : '';
    $forma = isset($formas_pago[$row['forma']]) ? $formas_pago[$row['forma']] : '';

    // Comprobante
    $ocultar    = '';
    $comp_tipo  = $row['comp_tipo'];
    $comp_html  = '';
    if ($row['estado'] == '1' && ($comp_tipo == 'B' || $comp_tipo == 'F')) {
        $ocultar   = 'ocultar';
        $comp_html = '<a title="Ver comprobante" target="_BLANK" href="'.$row['comp_url'].'">'.$comp_tipo.$row['comp_numero'].'</a>';
    } else {
        $comp_html = '<span class="label label-default">Nota de Venta</span>';
    }

    // Cliente: primeros 2 nombres
    $nombre_raw   = $row['nombre_cliente'] ?? 'VARIOS';
    $partes       = explode(' ', trim($nombre_raw));
    $nombre_corto = implode(' ', array_slice($partes, 0, 2));

    // Opciones
    $opciones = '<a title="Ver venta" class="btn btn-primary btn-xs" href="ver_venta.php?id='.$id.'"><i class="fas fa-eye"></i></a> '
              . '<a class="btn btn-success btn-xs '.$ocultar.'" href="factura.php?id='.$id.'" title="Factura electrónica"><i class="fas fa-file-invoice-dollar"></i></a> '
              . '<a class="btn btn-danger btn-xs '.$ocultar.'" href="boleta.php?id='.$id.'" title="Boleta electrónica"><i class="fab fa-bitcoin"></i></a> '
              . '<button class="btn-custom btn-borrar" value="'.$id.'" title="Borrar"><img src="/assets/img/trash.png" /></button>';

    $result[] = [
        $num++,
        'v-'.$id,
        $tipo,
        $forma,
        $row['fecha'],
        $row['total'],
        $comp_html,
        strtoupper($nombre_corto),
        $opciones
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data'            => $result
]);
