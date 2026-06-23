<?php
include('control.php');
include('sdba/sdba.php');

// Parametros de DataTables
$draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
$orderColumn = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 0;
$orderDir = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'desc' ? 'desc' : 'asc';

// Mapeo de columnas para ORDER BY
$columnsOrder = [
    0 => 'p.nom_prod',
    1 => 'u.codigo',
    2 => 'p.id_producto',
    3 => 'stock_actual',  // Cambiar para usar el stock real
    4 => 'p.precio_venta'
];

$db = Sdba::db();

// Total sin filtro (solo productos activos)
$totalRecords = Sdba::table('productos')->where('estado', '1')->total();

// WHERE para busqueda: cada palabra debe aparecer en alguna columna
$whereSearch = ' WHERE p.estado = 1';
if ($search != '') {
    $palabras = array_filter(explode(' ', trim($search)));
    $condiciones = [];
    foreach ($palabras as $p) {
        $esc = $db->escape('%'.$p.'%', true);
        $condiciones[] = "(p.nom_prod LIKE '{$esc}' OR p.codigo_producto LIKE '{$esc}' OR m.marca LIKE '{$esc}')";
    }
    $whereSearch .= ' AND ' . implode(' AND ', $condiciones);
}

// ORDER BY
$orderBy = 'p.nom_prod';
if (isset($columnsOrder[$orderColumn])) {
    $orderBy = $columnsOrder[$orderColumn];
}
$orderDir = ($orderDir === 'desc') ? 'DESC' : 'ASC';

// Query para total filtrado
$sqlCount = "SELECT COUNT(*) as total
    FROM productos p
    LEFT JOIN unidades u ON p.unidad_prod = u.id_unidad
    LEFT JOIN marca m ON p.marca = m.id_marca
    {$whereSearch}";

$countResult = $db->query($sqlCount)->row();
$filteredRecords = (int)$countResult['total'];

// Query principal con paginacion
$sql = "SELECT p.*, u.codigo as unidad_codigo, m.marca as marca_nombre,
               IFNULL((SELECT stockt FROM stock WHERE producto = p.id_producto ORDER BY id_stock DESC LIMIT 1), 0) as stock_actual
    FROM productos p
    LEFT JOIN unidades u ON p.unidad_prod = u.id_unidad
    LEFT JOIN marca m ON p.marca = m.id_marca
    {$whereSearch}
    ORDER BY {$orderBy} {$orderDir}
    LIMIT {$start}, {$length}";

$data = $db->query($sql)->result();

// Formato de respuesta
$result = [];
foreach ($data as $row) {
    $stockt = $row['stock_actual'];  // Usar stock real desde tabla stock
    $nombre_completo = $row['codigo_producto'].' '.$row['nom_prod'].' '.($row['marca_nombre'] ?? '');

    $result[] = [
        '<span class="nom_prod" style="text-transform:uppercase;">'.$nombre_completo.'</span>',
        '<span class="unidad" style="text-transform:uppercase;">'.($row['unidad_codigo'] ?? '').'</span>',
        '<span class="stock">'.$stockt.'</span>',
        '<span class="precio_venta">'.$row['precio_venta'].'</span>',
        '<button id="agregar" value="'.$row['id_producto'].'" class="btn btn-xs btn-success"> + </button>'
    ];
}

// Respuesta JSON
header('Content-Type: application/json');
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data' => $result
]);
