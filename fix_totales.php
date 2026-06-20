<?php
// Script de uso único para corregir ventas con total negativo.
// ELIMINAR este archivo después de ejecutarlo.
include('inc/control.php');
if ($_SESSION['type'] !== 'admin') { die('No autorizado'); }

include('inc/sdba/sdba.php');
$db = Sdba::db();

// Ver ventas afectadas
$afectadas = $db->query("
    SELECT v.id_venta, v.total AS total_actual,
           COALESCE(SUM(dv.total), 0) AS total_correcto
    FROM ventas v
    LEFT JOIN detalle_ventas dv ON dv.venta = v.id_venta
    WHERE v.total < 0
    GROUP BY v.id_venta
")->result();

if (isset($_POST['confirmar'])) {
    $db->query("
        UPDATE ventas v
        SET v.total = (
            SELECT COALESCE(SUM(dv.total), 0)
            FROM detalle_ventas dv
            WHERE dv.venta = v.id_venta
        )
        WHERE v.total < 0
    ");
    echo '<p style="color:green;font-size:18px;">✓ Totales corregidos. Puedes eliminar este archivo.</p>';
    // Mostrar resultado
    $corregidas = $db->query("SELECT id_venta, total FROM ventas WHERE id_venta IN (" . implode(',', array_column($afectadas, 'id_venta')) . ")")->result();
    echo '<table border="1" cellpadding="5"><tr><th>id_venta</th><th>Total nuevo</th></tr>';
    foreach ($corregidas as $r) {
        echo '<tr><td>v-'.$r['id_venta'].'</td><td>'.$r['total'].'</td></tr>';
    }
    echo '</table>';
} else {
    echo '<h3>Ventas con total negativo que se van a corregir:</h3>';
    if (empty($afectadas)) {
        echo '<p>No hay ventas con total negativo.</p>';
    } else {
        echo '<table border="1" cellpadding="5"><tr><th>id_venta</th><th>Total actual</th><th>Total correcto</th></tr>';
        foreach ($afectadas as $r) {
            echo '<tr><td>v-'.$r['id_venta'].'</td><td style="color:red">'.$r['total_actual'].'</td><td style="color:green">'.$r['total_correcto'].'</td></tr>';
        }
        echo '</table>';
        echo '<br><form method="post"><button name="confirmar" value="1" style="padding:10px 20px;background:green;color:white;font-size:16px;">Corregir ahora</button></form>';
    }
}
?>
