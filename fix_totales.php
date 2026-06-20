<?php
// Script de uso único. ELIMINAR después de ejecutar.
include('inc/control.php');
if ($_SESSION['type'] !== 'admin') { die('No autorizado'); }
include('inc/sdba/sdba.php');
$db = Sdba::db();

echo '<style>
body{font-family:sans-serif;padding:20px}
table{border-collapse:collapse;margin:10px 0}
th,td{border:1px solid #ccc;padding:6px 10px}
.neg{color:red} .ok{color:green} .warn{background:#fff3cd}
h3{margin-top:30px}
input[type=text]{padding:6px;font-size:14px;width:300px}
button{padding:8px 16px;font-size:14px;cursor:pointer}
</style>';

echo '<h2>Investigar origen de ventas</h2>';

// IDs a investigar: por POST o por defecto los del screenshot
$ids_input = isset($_POST['venta_ids']) ? $_POST['venta_ids'] : '13289,13290';
$ids_raw   = array_filter(array_map('intval', explode(',', $ids_input)));

echo '<form method="post" style="margin-bottom:20px;">
    <label>IDs de ventas a investigar (separados por coma):</label><br><br>
    <input type="text" name="venta_ids" value="'.htmlspecialchars($ids_input).'">
    <button type="submit">Buscar proformas relacionadas</button>
</form>';

if (empty($ids_raw)) {
    echo '<p>Ingresa al menos un ID.</p>';
    exit;
}

$ids_str_v = implode(',', $ids_raw);

// Datos de las ventas
$ventas = $db->query("
    SELECT v.id_venta, v.fecha, v.cliente, v.total,
           COALESCE(cl.cliente, 'VARIOS') AS nombre_cliente
    FROM ventas v
    LEFT JOIN clientes cl ON cl.id_cliente = v.cliente
    WHERE v.id_venta IN ({$ids_str_v})
")->result();

if (empty($ventas)) {
    echo '<p>No se encontraron ventas con esos IDs.</p>';
    exit;
}

foreach ($ventas as $venta) {
    $vid   = intval($venta['id_venta']);
    $cli   = intval($venta['cliente']);
    $fecha = $venta['fecha'];

    echo '<h3>Venta v-'.$vid.' &mdash; '.$venta['nombre_cliente'].' &mdash; '.$fecha.' &mdash; S/ '.$venta['total'].'</h3>';

    // Productos de esta venta
    $prod_venta = $db->query("
        SELECT dv.producto, p.nom_prod, dv.cantidad, dv.precio, dv.total
        FROM detalle_ventas dv
        LEFT JOIN productos p ON p.id_producto = dv.producto
        WHERE dv.venta = {$vid}
    ")->result();

    echo '<strong>Ítems de la venta:</strong>';
    echo '<table><tr><th>Producto</th><th>Nombre</th><th>Cant</th><th>Precio</th><th>Total</th></tr>';
    foreach ($prod_venta as $item) {
        echo '<tr><td>'.$item['producto'].'</td><td>'.$item['nom_prod'].'</td><td>'.$item['cantidad'].'</td><td>'.$item['precio'].'</td><td>'.$item['total'].'</td></tr>';
    }
    echo '</table>';

    $prod_ids = array_column($prod_venta, 'producto');
    if (empty($prod_ids)) {
        echo '<p>Sin ítems registrados.</p>';
        continue;
    }

    $ids_str_p = implode(',', array_map('intval', $prod_ids));

    // Buscar proformas: mismo cliente + productos en común + fecha cercana (±60 días)
    $matches = $db->query("
        SELECT p.id_venta AS id_proforma, p.fecha AS fecha_proforma,
               p.total AS total_proforma, p.estado,
               COUNT(DISTINCT dp.producto) AS prod_comunes,
               GROUP_CONCAT(DISTINCT pr.nom_prod ORDER BY pr.nom_prod SEPARATOR ', ') AS nombres_comunes
        FROM proforma p
        INNER JOIN detalle_proforma dp ON dp.venta = p.id_venta
        LEFT JOIN productos pr ON pr.id_producto = dp.producto
        WHERE p.cliente = {$cli}
          AND p.fecha BETWEEN DATE_SUB('{$fecha}', INTERVAL 60 DAY) AND '{$fecha}'
          AND dp.producto IN ({$ids_str_p})
        GROUP BY p.id_venta
        ORDER BY prod_comunes DESC, ABS(DATEDIFF(p.fecha, '{$fecha}')) ASC
    ")->result();

    echo '<strong>Proformas coincidentes (mismo cliente + productos + hasta 60 días antes):</strong>';
    if (empty($matches)) {
        // Segundo intento: solo por productos sin filtrar por cliente
        $matches2 = $db->query("
            SELECT p.id_venta AS id_proforma, p.fecha AS fecha_proforma,
                   p.total AS total_proforma, p.estado,
                   COALESCE(cl.cliente,'VARIOS') AS nombre_cliente_pf,
                   COUNT(DISTINCT dp.producto) AS prod_comunes
            FROM proforma p
            INNER JOIN detalle_proforma dp ON dp.venta = p.id_venta
            LEFT JOIN clientes cl ON cl.id_cliente = p.cliente
            WHERE p.fecha BETWEEN DATE_SUB('{$fecha}', INTERVAL 60 DAY) AND '{$fecha}'
              AND dp.producto IN ({$ids_str_p})
            GROUP BY p.id_venta
            ORDER BY prod_comunes DESC
            LIMIT 10
        ")->result();

        if (empty($matches2)) {
            echo '<p>No se encontraron proformas con esos productos en los últimos 60 días.</p>';
        } else {
            echo '<p><em>No hay coincidencia exacta de cliente. Mostrando proformas con productos en común (cualquier cliente):</em></p>';
            $estados = ['0'=>'Activa','1'=>'Convertida','2'=>'Anulada'];
            echo '<table><tr><th>id_proforma</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Productos en común</th></tr>';
            foreach ($matches2 as $m) {
                $est = $estados[$m['estado']] ?? $m['estado'];
                $cls = $m['prod_comunes'] == count($prod_ids) ? ' class="ok"' : '';
                echo '<tr'.$cls.'><td>P-'.$m['id_proforma'].'</td><td>'.$m['fecha_proforma'].'</td><td>'.$m['nombre_cliente_pf'].'</td><td>'.$m['total_proforma'].'</td><td>'.$est.'</td><td>'.$m['prod_comunes'].' de '.count($prod_ids).'</td></tr>';
            }
            echo '</table>';
        }
    } else {
        $estados = ['0'=>'Activa','1'=>'Convertida','2'=>'Anulada'];
        echo '<table><tr><th>id_proforma</th><th>Fecha proforma</th><th>Total proforma</th><th>Estado</th><th>Prod. en común</th><th>Nombres</th></tr>';
        foreach ($matches as $m) {
            $est = $estados[$m['estado']] ?? $m['estado'];
            $cls = $m['prod_comunes'] == count($prod_ids) ? ' class="ok"' : ($m['estado'] == '1' ? ' class="warn"' : '');
            echo '<tr'.$cls.'><td>P-'.$m['id_proforma'].'</td><td>'.$m['fecha_proforma'].'</td><td>'.$m['total_proforma'].'</td><td>'.$est.'</td><td>'.$m['prod_comunes'].' de '.count($prod_ids).'</td><td>'.$m['nombres_comunes'].'</td></tr>';
        }
        echo '</table>';
    }
}
?>
