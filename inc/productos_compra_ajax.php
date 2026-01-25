<?php
include('sdba/sdba.php');

header('Content-Type: application/json');

// Parámetros de DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$order_col = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$order_dir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

$db = Sdba::db();

// Total sin filtro
$total_records = Sdba::table('productos')->where('estado','1')->total();

// Escapar búsqueda
$searchEsc = $db->escape('%'.$search.'%', true);

// WHERE
$whereSearch = " WHERE p.estado = '1'";
if ($search != '') {
	$whereSearch .= " AND (p.nom_prod LIKE '{$searchEsc}' OR m.marca LIKE '{$searchEsc}')";
}

// Query para total filtrado
$sqlCount = "SELECT COUNT(*) as total
	FROM productos p
	LEFT JOIN marca m ON p.marca = m.id_marca
	LEFT JOIN unidades u ON p.unidad_prod = u.id_unidad
	{$whereSearch}";
$countResult = $db->query($sqlCount)->row();
$filtered_records = (int)$countResult['total'];

// Ordenamiento
$orderBy = 'p.nom_prod';
if ($order_col == 1) {
	$orderBy = 'u.nombre';
}
$orderDir = ($order_dir === 'desc') ? 'DESC' : 'ASC';

// Query principal
$sql = "SELECT p.id_producto, p.nom_prod, m.marca as marca_nombre, u.nombre as unidad_nombre
	FROM productos p
	LEFT JOIN marca m ON p.marca = m.id_marca
	LEFT JOIN unidades u ON p.unidad_prod = u.id_unidad
	{$whereSearch}
	ORDER BY {$orderBy} {$orderDir}
	LIMIT {$start}, {$length}";

$lista = $db->query($sql)->result();

// Construir datos
$data = array();
foreach ($lista as $row) {
	$marca_nombre = isset($row['marca_nombre']) && $row['marca_nombre'] ? $row['marca_nombre'] : '';
	$unidad_nombre = isset($row['unidad_nombre']) ? $row['unidad_nombre'] : '';
	$nombre_completo = trim($row['nom_prod'] . ' ' . $marca_nombre);

	$data[] = array(
		'<span style="text-transform:uppercase;" class="nom_prod">' . htmlspecialchars($nombre_completo) . '</span>',
		'<span style="text-transform:uppercase;" class="unidad">' . htmlspecialchars($unidad_nombre) . '</span>',
		'<button id="agregar" value="'.$row['id_producto'].'" class="btn btn-xs btn-success"> + </button>'
	);
}

echo json_encode(array(
	'draw' => $draw,
	'recordsTotal' => $total_records,
	'recordsFiltered' => $filtered_records,
	'data' => $data
));
