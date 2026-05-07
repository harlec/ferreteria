<?php
session_start();
include('sdba/sdba.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id_usr'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesion no valida']);
    exit;
}

$venta_id    = isset($_POST['venta'])       ? intval($_POST['venta'])        : 0;
$forma       = isset($_POST['forma'])       ? intval($_POST['forma'])        : 0;
$monto       = isset($_POST['monto'])       ? floatval($_POST['monto'])      : 0;
$fecha_pagado = isset($_POST['fecha'])      ? $_POST['fecha']                : date('Y-m-d');

if ($venta_id <= 0 || $forma <= 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos invalidos']);
    exit;
}

// Verificar que la venta existe y es tipo crédito
$v = Sdba::table('ventas');
$v->where('id_venta', $venta_id);
$venta = $v->get_one();

if (!$venta || $venta['tipo'] != '2') {
    echo json_encode(['success' => false, 'mensaje' => 'Venta no encontrada o no es credito']);
    exit;
}

// Marcar como pagado
$upd = Sdba::table('ventas');
$upd->where('id_venta', $venta_id);
$upd->update(['pagado' => 1, 'fecha_pagado' => $fecha_pagado]);

// Registrar el pago en tabla pagos (si monto > 0)
if ($monto > 0) {
    $pago = Sdba::table('pagos');
    $pago->insert(['id_pago' => '', 'venta' => $venta_id, 'forma' => $forma, 'monto' => $monto]);
}

echo json_encode(['success' => true, 'mensaje' => 'Venta marcada como pagada']);
?>
