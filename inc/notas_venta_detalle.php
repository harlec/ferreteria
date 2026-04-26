<?php
include('control.php');
include('sdba/sdba.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { echo json_encode(['error' => 'ID inválido']); exit; }

$db  = Sdba::db();
$sql = "SELECT dv.cantidad, dv.precio, dv.total, dv.exonerada,
               p.nom_prod, p.codigo_producto,
               u.codigo as unidad_codigo
        FROM detalle_ventas dv
        LEFT JOIN productos p ON dv.producto = p.id_producto
        LEFT JOIN unidades u  ON p.unidad_prod = u.id_unidad
        WHERE dv.venta = {$id}";

$rows = $db->query($sql)->result();

$items = [];
$total = 0;
foreach ($rows as $r) {
    $total += floatval($r['total']);
    $items[] = [
        'codigo'   => $r['codigo_producto'],
        'nombre'   => strtoupper($r['nom_prod']),
        'unidad'   => $r['unidad_codigo'],
        'precio'   => number_format(floatval($r['precio']), 2),
        'cantidad' => $r['cantidad'],
        'total'    => number_format(floatval($r['total']), 2),
    ];
}

header('Content-Type: application/json');
echo json_encode(['items' => $items, 'total' => number_format($total, 2)]);
