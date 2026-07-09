<?php
include('control.php');
include('sdba/sdba.php');

$draw        = isset($_GET['draw'])                   ? (int)$_GET['draw']    : 1;
$start       = isset($_GET['start'])                  ? (int)$_GET['start']   : 0;
$length      = isset($_GET['length'])                 ? (int)$_GET['length']  : 10;
$search      = isset($_GET['search']['value'])        ? $_GET['search']['value'] : '';
$orderColumn = isset($_GET['order'][0]['column'])     ? (int)$_GET['order'][0]['column'] : 4;
$orderDir    = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';

$id_usr    = (int)$_SESSION['id_usr'];
$tipo_usr  = $_SESSION['type'];
$filtro_comp = isset($_GET['tipo_comp']) ? $_GET['tipo_comp'] : '';

$db = Sdba::db();

// Filtro por usuario (dentro de cada parte del UNION)
$user_filter_venta    = $tipo_usr != 'admin' ? "AND v.usuario = {$id_usr}" : '';
$user_filter_proforma = $tipo_usr != 'admin' ? "AND p.usuario = {$id_usr}" : '';

// Filtro de fecha (mes actual si viene tipo_comp)
$fecha_filter = '';
if ($filtro_comp) {
    $mes_inicio   = date('Y-m-01');
    $fecha_filter = "AND fecha >= '{$mes_inicio}'";
}

// Tipo_comp filter en la parte de ventas
$comp_filter_venta = '';
if ($filtro_comp == 'B') {
    $comp_filter_venta = "AND comp_tipo = 'B'";
} elseif ($filtro_comp == 'F') {
    $comp_filter_venta = "AND comp_tipo = 'F'";
} elseif ($filtro_comp == 'NV') {
    $comp_filter_venta = "AND (comp_tipo IS NULL OR (comp_tipo != 'B' AND comp_tipo != 'F'))";
}

// Parte VENTAS del UNION
// Nota: el MAX(id_comprobante) se restringe a tipo F/B para no traer la nota de
// crédito (FC/NB) como si fuera el comprobante vigente de la venta.
$sql_ventas = "SELECT v.id_venta, v.tipo, v.forma, v.fecha, v.total, v.estado,
                      IFNULL(cl.cliente, 'VARIOS') as nombre_cliente,
                      c.tipo as comp_tipo, c.numero as comp_numero, c.url as comp_url,
                      c.anulado as comp_anulado, c.nota_credito_id as comp_nota_credito_id,
                      'venta' as origen
               FROM ventas v
               LEFT JOIN clientes cl ON v.cliente = cl.id_cliente
               LEFT JOIN comprobantes c ON c.venta = v.id_venta AND c.id_comprobante = (
                   SELECT MAX(id_comprobante) FROM comprobantes WHERE venta = v.id_venta AND tipo IN ('F','B')
               )
               WHERE v.estado != '2' {$user_filter_venta} {$fecha_filter}";

// Parte PROFORMA del UNION (solo si no se filtra por B o F específicamente)
$sql_proformas = '';
if ($filtro_comp != 'B' && $filtro_comp != 'F') {
    $sql_proformas = "UNION ALL
    SELECT p.id_venta, p.tipo, '' as forma, p.fecha, p.total, p.estado,
           IFNULL(cl.cliente, 'VARIOS') as nombre_cliente,
           NULL as comp_tipo, NULL as comp_numero, NULL as comp_url,
           NULL as comp_anulado, NULL as comp_nota_credito_id,
           'proforma' as origen
    FROM proforma p
    LEFT JOIN clientes cl ON p.cliente = cl.id_cliente
    WHERE p.estado != '2' {$user_filter_proforma} {$fecha_filter}";
}

$union_sql = "({$sql_ventas}) {$sql_proformas}";

// Búsqueda
$search_where = '';
if ($search != '') {
    $s = $db->escape('%'.$search.'%', true);
    $search_where = "WHERE (id_venta LIKE '{$s}' OR nombre_cliente LIKE '{$s}' OR fecha LIKE '{$s}' OR total LIKE '{$s}')";
}

// Mapeo de columnas ordenables (0:#, 1:Venta, 2:Tipo, 3:Forma, 4:Fecha, 5:Monto,
// 6:Comprobante, 7:Nota Créd., 8:Cliente, 9:Opciones, 10:flag anulado oculto)
$columnsOrder = [
    0 => 'id_venta', 1 => 'id_venta', 2 => 'tipo', 3 => 'forma',
    4 => 'fecha', 5 => 'total', 6 => 'id_venta', 7 => 'id_venta',
    8 => 'nombre_cliente', 9 => 'id_venta', 10 => 'id_venta'
];
$orderBy = isset($columnsOrder[$orderColumn]) ? $columnsOrder[$orderColumn] : 'fecha';

// Total sin filtro de búsqueda
$totalResult    = $db->query("SELECT COUNT(*) as total FROM ({$union_sql}) AS t {$comp_filter_venta}")->row();
$totalRecords   = (int)$totalResult['total'];

// Total filtrado
$filteredResult  = $db->query("SELECT COUNT(*) as total FROM ({$union_sql}) AS t {$comp_filter_venta} {$search_where}")->row();
$filteredRecords = (int)$filteredResult['total'];

// Datos paginados
$sql = "SELECT * FROM ({$union_sql}) AS t {$comp_filter_venta} {$search_where}
        ORDER BY {$orderBy} {$orderDir}, id_venta {$orderDir}
        LIMIT {$start}, {$length}";

$data = $db->query($sql)->result();

// Pre-cargar pagos_count para ventas de esta página
$pagos_count_map = [];
$venta_ids_pagina = array_filter(array_map(function($r) {
    return $r['origen'] === 'venta' ? intval($r['id_venta']) : 0;
}, $data));
if (!empty($venta_ids_pagina)) {
    $ids_str_p = implode(',', $venta_ids_pagina);
    $pagos_data = $db->query("SELECT venta, COUNT(*) as cnt, GROUP_CONCAT(forma) as formas FROM pagos WHERE venta IN ({$ids_str_p}) GROUP BY venta")->result();
    foreach ($pagos_data as $pr) {
        $pagos_count_map[$pr['venta']] = intval($pr['cnt']);
    }
}

// Pre-cargar datos de las notas de crédito asociadas (serie/numero/url) para esta página
$nc_map = [];
$nc_ids_pagina = array_filter(array_map(function($r) {
    return !empty($r['comp_nota_credito_id']) ? intval($r['comp_nota_credito_id']) : 0;
}, $data));
if (!empty($nc_ids_pagina)) {
    $ids_str_nc = implode(',', array_unique($nc_ids_pagina));
    $nc_data = $db->query("SELECT id_comprobante, serie, numero, url FROM comprobantes WHERE id_comprobante IN ({$ids_str_nc})")->result();
    foreach ($nc_data as $nc) {
        $nc_map[$nc['id_comprobante']] = $nc;
    }
}

// Mapas
$tipos_pago = ['1'=>'Contado', '2'=>'Crédito'];
$formas_pago = ['1'=>'Efectivo','2'=>'Tar. Débito','3'=>'Tar. Crédito','4'=>'Crédito','5'=>'Yape','6'=>'Transferencia'];

$result = [];
$num = $start + 1;
foreach ($data as $row) {
    $id     = $row['id_venta'];
    $origen = $row['origen'];

    $tipo  = isset($tipos_pago[$row['tipo']]) ? $tipos_pago[$row['tipo']] : '';
    // Determinar forma: si tiene pagos en tabla pagos, usar eso; si no, usar ventas.forma
    if ($origen === 'venta' && isset($pagos_count_map[$id])) {
        $forma = $pagos_count_map[$id] > 1 ? '<span class="label label-warning">Mixto</span>' : (isset($formas_pago[$row['forma']]) ? $formas_pago[$row['forma']] : '');
    } else {
        $forma = isset($formas_pago[$row['forma']]) ? $formas_pago[$row['forma']] : '';
    }

    // Badge proforma
    if ($origen === 'proforma') {
        $tipo  = '<span class="label label-info">Proforma</span>';
        $forma = '-';
    }

    // Comprobante
    $comp_tipo    = $row['comp_tipo'];
    $es_anulado   = ($row['comp_anulado'] == '1');
    if ($origen === 'venta' && $row['estado'] == '1' && ($comp_tipo == 'B' || $comp_tipo == 'F')) {
        $comp_html = '<a title="Ver comprobante" target="_BLANK" href="'.$row['comp_url'].'">'.$comp_tipo.$row['comp_numero'].'</a>';
        $ocultar   = 'ocultar';
    } else {
        $comp_html = $origen === 'proforma'
            ? '<span class="label label-default">Proforma</span>'
            : '<span class="label label-default">Nota de Venta</span>';
        $ocultar   = '';
    }
    // Ya anulada por baja o nota de crédito: no debe poder volver a facturarse/boletearse
    if ($es_anulado) {
        $ocultar = 'ocultar';
    }

    // Nota de crédito asociada, si el comprobante fue anulado por una
    $nc_html = '-';
    if (!empty($row['comp_nota_credito_id']) && isset($nc_map[$row['comp_nota_credito_id']])) {
        $nc = $nc_map[$row['comp_nota_credito_id']];
        $nc_html = '<a title="Ver nota de crédito" target="_BLANK" href="'.$nc['url'].'">'.$nc['serie'].'-'.$nc['numero'].'</a>';
    }

    // Cliente: primeros 2 palabras
    $partes  = explode(' ', trim($row['nombre_cliente']));
    $cliente = strtoupper(implode(' ', array_slice($partes, 0, 2)));

    // Opciones según origen
    if ($origen === 'proforma') {
        $opciones =
            '<a title="Ver proforma" class="btn btn-info btn-xs" href="ver_proforma.php?id='.$id.'"><i class="fas fa-eye"></i></a> ' .
            '<a title="Imprimir" class="btn btn-default btn-xs" href="recibop.php?id='.$id.'" target="_blank"><i class="fas fa-print"></i></a> ' .
            '<a title="Convertir a venta" class="btn btn-success btn-xs" href="proforma_to_venta.php?id='.$id.'"><i class="fas fa-exchange-alt"></i></a>';
    } else {
        $opciones =
            '<a title="Ver venta" class="btn btn-primary btn-xs" href="ver_venta.php?id='.$id.'"><i class="fas fa-eye"></i></a> ' .
            '<a class="btn btn-success btn-xs '.$ocultar.'" href="factura.php?id='.$id.'" title="Factura"><i class="fas fa-file-invoice-dollar"></i></a> ' .
            '<a class="btn btn-danger btn-xs '.$ocultar.'" href="boleta.php?id='.$id.'" title="Boleta"><i class="fab fa-bitcoin"></i></a> ' .
            '<button class="btn-custom btn-borrar" value="'.$id.'" title="Borrar"><img src="/assets/img/trash.png" /></button>';
    }

    $result[] = [
        $num++,
        'v-'.$id,
        $tipo,
        $forma,
        $row['fecha'],
        $row['total'],
        $comp_html,
        $nc_html,
        $cliente,
        $opciones,
        $es_anulado ? '1' : '0'
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data'            => $result
]);
