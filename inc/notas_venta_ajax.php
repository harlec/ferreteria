<?php
include('control.php');
include('sdba/sdba.php');

$draw        = isset($_GET['draw'])                   ? (int)$_GET['draw']    : 1;
$start       = isset($_GET['start'])                  ? (int)$_GET['start']   : 0;
$length      = isset($_GET['length'])                 ? (int)$_GET['length']  : 10;
$search      = isset($_GET['search']['value'])        ? $_GET['search']['value'] : '';
$orderColumn = isset($_GET['order'][0]['column'])     ? (int)$_GET['order'][0]['column'] : 1;
$orderDir    = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
$todas       = isset($_GET['todas']) && $_GET['todas'] == '1';

$columnsOrder = [
    0 => 'v.id_venta',
    1 => 'v.id_venta',
    2 => 'v.fecha',
    3 => 'cl.cliente',
    4 => 'v.total',
];
$orderBy = isset($columnsOrder[$orderColumn]) ? $columnsOrder[$orderColumn] : 'v.fecha';

$db = Sdba::db();

$whereParts = [
    "v.estado != '2'",
    "(c.tipo IS NULL OR (c.tipo != 'B' AND c.tipo != 'F'))"
];

if (!$todas) {
    $mes_inicio = date('Y-m-01');
    $whereParts[] = "v.fecha >= '{$mes_inicio}'";
}

if ($search != '') {
    $s = $db->escape('%'.$search.'%', true);
    $whereParts[] = "(v.id_venta LIKE '{$s}' OR cl.cliente LIKE '{$s}' OR v.fecha LIKE '{$s}' OR v.total LIKE '{$s}')";
}

$where = 'WHERE ' . implode(' AND ', $whereParts);

$baseFrom = "FROM ventas v
    LEFT JOIN clientes cl ON v.cliente = cl.id_cliente
    LEFT JOIN comprobantes c ON c.venta = v.id_venta AND c.id_comprobante = (
        SELECT MAX(id_comprobante) FROM comprobantes WHERE venta = v.id_venta
    )";

$totalParts = ["v.estado != '2'", "(c.tipo IS NULL OR (c.tipo != 'B' AND c.tipo != 'F'))"];
if (!$todas) {
    $totalParts[] = "v.fecha >= '" . date('Y-m-01') . "'";
}
$whereTotal = 'WHERE ' . implode(' AND ', $totalParts);

$totalResult    = $db->query("SELECT COUNT(*) as total {$baseFrom} {$whereTotal}")->row();
$totalRecords   = (int)$totalResult['total'];

$filteredResult  = $db->query("SELECT COUNT(*) as total {$baseFrom} {$where}")->row();
$filteredRecords = (int)$filteredResult['total'];

$sql = "SELECT v.id_venta, v.fecha, v.total, cl.cliente as nombre_cliente
    {$baseFrom}
    {$where}
    ORDER BY {$orderBy} {$orderDir}
    LIMIT {$start}, {$length}";

$data = $db->query($sql)->result();

$result = [];
$num = $start + 1;
foreach ($data as $row) {
    $id     = $row['id_venta'];
    $partes = explode(' ', trim($row['nombre_cliente'] ?? 'VARIOS'));
    $cliente = strtoupper(implode(' ', array_slice($partes, 0, 2)));

    $btn_detalle = '<button class="btn btn-info btn-xs btn-detalle" data-id="'.$id.'" title="Ver productos"><i class="fas fa-list"></i> Ver</button>';
    $btn_carrito = '<button class="btn btn-success btn-xs btn-carrito"
        data-id="'.$id.'"
        data-fecha="'.htmlspecialchars($row['fecha']).'"
        data-total="'.floatval($row['total']).'"
        data-cliente="'.htmlspecialchars($cliente).'"
        title="Agregar al carrito"><i class="fas fa-cart-plus"></i> Agregar</button>';

    $result[] = [
        $num++,
        'v-'.$id,
        $row['fecha'],
        $cliente,
        'S/ '.number_format(floatval($row['total']), 2),
        $btn_detalle . ' ' . $btn_carrito
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data'            => $result
]);
