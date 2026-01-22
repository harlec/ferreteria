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

// Mapeo de columnas
$columns = [
    0 => 'id_producto',
    1 => 'codigo_producto',
    2 => 'nom_prod',
    3 => 'nombre',
    4 => 'marca',
    5 => 'nom_cat',
    6 => 'color',
    7 => 'exonerada',
    8 => 'stockp',
    9 => 'precio_venta'
];

// Total sin filtro
$totalRecords = Sdba::table('productos')->total();

// Consulta base
$productos = Sdba::table('productos');
$productos->left_join('categoria','categorias','id_categoria');
$productos->left_join('marca','marca','id_marca');
$productos->left_join('color','color','id_color');
$productos->left_join('unidad_prod','unidades','id_unidad');

// Busqueda
if ($search != '') {
    $productos->open_sub('AND');
    $productos->like('nom_prod', $search, ['%','%'], 'productos', 'OR');
    $productos->or_like('codigo_producto', $search, ['%','%'], 'productos');
    $productos->or_like('marca', $search, ['%','%'], 'marca');
    $productos->or_like('nom_cat', $search, ['%','%'], 'categorias');
    $productos->close_sub();
}

// Total filtrado
$filteredRecords = $productos->total();

// Ordenamiento
if (isset($columns[$orderColumn])) {
    $table = 'productos';
    if ($columns[$orderColumn] == 'marca') $table = 'marca';
    if ($columns[$orderColumn] == 'nom_cat') $table = 'categorias';
    if ($columns[$orderColumn] == 'color') $table = 'color';
    if ($columns[$orderColumn] == 'nombre') $table = 'unidades';

    $productos->order_by($columns[$orderColumn], $orderDir, $table);
}

// Paginacion
$data = $productos->get($length, $start);

// Formato de respuesta
$result = [];
foreach ($data as $row) {
    $stockt = $row['stockp'];
    $stockClass = ($stockt <= 4) ? 'style="color:red"' : '';

    $result[] = [
        '<span '.$stockClass.'>'.$row['id_producto'].'</span>',
        '<span '.$stockClass.'>'.$row['codigo_producto'].'</span>',
        '<span '.$stockClass.' style="text-transform:uppercase;">'.$row['nom_prod'].'</span>',
        '<span '.$stockClass.'>'.($row['nombre'] ?? '').'</span>',
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
