<?php
session_start();
include('sdba/sdba.php');

$term = isset($_GET['term']) ? $_GET['term'] : '';
$result = array();

if (strlen($term) >= 1) {
	$clientes = Sdba::table('clientes');
	$clientes->where('estado', '1');
	$clientes->where('cliente', '%'.$term.'%', false, false, 'AND', 'LIKE');
	$lista = $clientes->get(10);

	foreach ($lista as $c) {
		$result[] = array(
			'id' => $c['id_cliente'],
			'value' => $c['cliente'],
			'label' => $c['cliente'] . ($c['doc_identidad'] ? ' - ' . $c['doc_identidad'] : '')
		);
	}
}

header('Content-Type: application/json');
echo json_encode($result);
