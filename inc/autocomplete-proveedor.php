<?php
include('sdba/sdba.php');

$term = isset($_GET['term']) ? $_GET['term'] : '';
$result = array();

if (strlen($term) >= 1) {
	$proveedores = Sdba::table('proveedores');
	$proveedores->where('proveedor', '%'.$term.'%', false, false, 'AND', 'LIKE');
	$lista = $proveedores->get(10);

	foreach ($lista as $p) {
		$result[] = array(
			'id' => $p['id_proveedor'],
			'value' => $p['proveedor'],
			'label' => $p['proveedor'] . ($p['ruc'] ? ' - ' . $p['ruc'] : '')
		);
	}
}

header('Content-Type: application/json');
echo json_encode($result);
