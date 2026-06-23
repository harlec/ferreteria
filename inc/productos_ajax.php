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
    0 => 'p.id_producto',
    1 => 'p.codigo_producto',
    2 => 'p.nom_prod',
    3 => 'u.nombre',
    4 => 'm.marca',
    5 => 'c.nom_cat',
    6 => 'co.color',
    7 => 'p.exonerada',
    8 => 'stock_actual',  // Cambiar para usar el stock real
    9 => 'p.precio_venta'
];

$db = Sdba::db();

// Total sin filtro (excluye eliminados)
$totalRecords = Sdba::table('productos')->where('estado !=', '0')->total();

// WHERE para busqueda multi-palabra (AND LIKE por cada palabra)
$whereSearch = " WHERE (p.estado IS NULL OR p.estado != '0')";
if ($search != '') {
    $palabras = array_filter(explode(' ', trim($search)));
    $condiciones = [];
    foreach ($palabras as $p) {
        $esc = $db->escape('%'.$p.'%', true);
        $condiciones[] = "(p.nom_prod LIKE '{$esc}' OR p.codigo_producto LIKE '{$esc}' OR m.marca LIKE '{$esc}' OR c.nom_cat LIKE '{$esc}' OR co.color LIKE '{$esc}' OR u.nombre LIKE '{$esc}')";
    }
    $whereSearch .= ' AND ' . implode(' AND ', $condiciones);
}

// ORDER BY
$orderBy = 'p.id_producto';
if (isset($columnsOrder[$orderColumn])) {
    $orderBy = $columnsOrder[$orderColumn];
}
$orderDir = ($orderDir === 'desc') ? 'DESC' : 'ASC';

// Query para total filtrado
$sqlCount = "SELECT COUNT(*) as total
    FROM productos p
    LEFT JOIN categorias c ON p.categoria = c.id_categoria
    LEFT JOIN marca m ON p.marca = m.id_marca
    LEFT JOIN color co ON p.color = co.id_color
    LEFT JOIN unidades u ON p.unidad_prod = u.id_unidad
    {$whereSearch}";

$countResult = $db->query($sqlCount)->row();
$filteredRecords = (int)$countResult['total'];

// Query principal con paginacion
$sql = "SELECT p.*, c.nom_cat, m.marca, co.color, u.nombre as unidad_nombre,
               IFNULL((SELECT stockt FROM stock WHERE producto = p.id_producto ORDER BY id_stock DESC LIMIT 1), 0) as stock_actual
    FROM productos p
    LEFT JOIN categorias c ON p.categoria = c.id_categoria
    LEFT JOIN marca m ON p.marca = m.id_marca
    LEFT JOIN color co ON p.color = co.id_color
    LEFT JOIN unidades u ON p.unidad_prod = u.id_unidad
    {$whereSearch}
    ORDER BY {$orderBy} {$orderDir}
    LIMIT {$start}, {$length}";

$data = $db->query($sql)->result();

// Formato de respuesta
$result = [];
foreach ($data as $row) {
    $stockt = $row['stock_actual'];  // Usar stock real desde tabla stock
    $stockClass = ($stockt <= 4) ? 'style="color:red"' : '';

    $result[] = [
        '<span '.$stockClass.'>'.$row['id_producto'].'</span>',
        '<span '.$stockClass.'>'.$row['codigo_producto'].'</span>',
        '<span '.$stockClass.' style="text-transform:uppercase;">'.$row['nom_prod'].'</span>',
        '<span '.$stockClass.'>'.($row['unidad_nombre'] ?? '').'</span>',
        '<span '.$stockClass.'>'.($row['marca'] ?? '').'</span>',
        '<span '.$stockClass.'>'.($row['nom_cat'] ?? '').'</span>',
        '<span '.$stockClass.'>'.($row['color'] ?? '').'</span>',
        '<span '.$stockClass.'>'.$row['exonerada'].'</span>',
        '<span '.$stockClass.'>'.$stockt.'</span>',
        '<span '.$stockClass.'>'.$row['precio_venta'].'</span>',
        '<a href="editar_producto.php?id='.$row['id_producto'].'"><img src="assets/img/edit.png"/></a>
         <button class="btn-custom btn-borrar" value="'.$row['id_producto'].'"><img src="assets/img/trash.png" /></button>'
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
