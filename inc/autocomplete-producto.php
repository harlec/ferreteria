<?php
session_start();
include('sdba/sdba.php');

$nombre = isset($_GET['term']) ? trim($_GET['term']) : '';
if ($nombre === '') { echo json_encode([]); exit; }

$db      = Sdba::db();
$palabras = array_filter(explode(' ', $nombre));

$condiciones = [];
foreach ($palabras as $p) {
    $esc = $db->escape('%'.$p.'%', true);
    $condiciones[] = "(CONCAT(p.nom_prod, ' ', IFNULL(m.marca,'')) LIKE '{$esc}')";
}
$where = implode(' AND ', $condiciones);

$rows = $db->query("
    SELECT p.id_producto, p.nom_prod, IFNULL(m.marca,'') as marca
    FROM productos p
    LEFT JOIN marca m ON p.marca = m.id_marca
    WHERE {$where}
    LIMIT 50
")->result();

$cliente = [];
foreach ($rows as $value) {
    $variantes = Sdba::table('variantes');
    $variantes->where('producto', $value['id_producto']);
    $v = $variantes->get();
    foreach ($v as $key) {
        $lote = ($key['variante'] == '0000-00-00') ? '-' : $key['variante'];
        $cliente[] = $value['nom_prod'] . '-' . $value['marca'] . '-' . $lote;
    }
}

echo json_encode($cliente);
?>
