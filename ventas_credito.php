<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$hoy = date('Y-m-d');
$db  = Sdba::db();

$sql = "SELECT v.id_venta, v.fecha, v.total, v.fecha_pago,
               cl.cliente as nombre_cliente
        FROM ventas v
        LEFT JOIN clientes cl ON v.cliente = cl.id_cliente
        WHERE v.tipo = '2' AND v.estado != '2'
        ORDER BY v.fecha_pago ASC, v.id_venta DESC";

$ventas_list = $db->query($sql)->result();

$filas = '';
$i = 1;
foreach ($ventas_list as $v) {
    $id          = $v['id_venta'];
    $fecha_pago  = $v['fecha_pago'];
    $partes      = explode(' ', trim($v['nombre_cliente'] ?? 'VARIOS'));
    $cliente     = strtoupper(implode(' ', array_slice($partes, 0, 2)));

    // Estado de vencimiento
    $badge = '';
    $row_class = '';
    if ($fecha_pago && $fecha_pago !== '0000-00-00') {
        $diff = (strtotime($fecha_pago) - strtotime($hoy)) / 86400;
        if ($diff < 0) {
            $dias = abs((int)$diff);
            $badge = '<span class="label label-danger">Vencido hace '.$dias.' día'.($dias!=1?'s':'').'</span>';
            $row_class = 'danger';
        } elseif ($diff == 0) {
            $badge = '<span class="label label-warning">Vence hoy</span>';
            $row_class = 'warning';
        } elseif ($diff <= 7) {
            $badge = '<span class="label label-warning">Vence en '.(int)$diff.' día'.($diff!=1?'s':'').'</span>';
            $row_class = 'warning';
        } else {
            $badge = '<span class="label label-success">En '.(int)$diff.' días</span>';
        }
        $fecha_pago_fmt = date('d/m/Y', strtotime($fecha_pago));
    } else {
        $badge = '<span class="label label-default">Sin fecha</span>';
        $fecha_pago_fmt = '-';
    }

    $filas .= '<tr class="'.$row_class.'">
        <td>'.$i.'</td>
        <td>v-'.$id.'</td>
        <td>'.date('d/m/Y', strtotime($v['fecha'])).'</td>
        <td>'.$cliente.'</td>
        <td>S/ '.number_format(floatval($v['total']), 2).'</td>
        <td>'.$fecha_pago_fmt.'</td>
        <td>'.$badge.'</td>
        <td><a class="btn btn-primary btn-xs" href="ver_venta.php?id='.$id.'" title="Ver venta"><i class="fas fa-eye"></i></a></td>
    </tr>';
    $i++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema - Ventas a Crédito</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
</head>
<body class="mobile dashboard">
<div class="">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><img class="img-responsive logo" src="/assets/img/harlec-sistema.png"></a>
            </div>
            <?php menu('4'); ?>
        </div>
        <div class="submenu">
            <ul class="subtop-tabs">
                <li><a href="venta.php">Registrar venta</a></li>
                <li><a href="ventas.php">Listar ventas</a></li>
                <li><a href="ventap.php">Proforma</a></li>
                <li><a href="venta_comprobantes.php">Comprobantes</a></li>
                <li><a href="notas_venta.php">Notas de Venta</a></li>
                <li class="active"><a href="ventas_credito.php">Créditos</a></li>
            </ul>
        </div>
    </nav>
    <div class="kbg">
        <div class="cuerpofull">
            <div class="titulo">
                <h3>Ventas a Crédito</h3>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="kdashboard">
                            <div class="panel panel-default pa">
                                <div class="panel-body">
                                    <table id="datos" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Venta</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Total</th>
                                                <th>Fecha de Pago</th>
                                                <th>Estado</th>
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php echo $filas; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            info: "Mostrando _START_ al _END_ de _TOTAL_ registros",
            infoEmpty: "0 registros",
            infoFiltered: "(filtrado de _MAX_)",
            lengthMenu: "Mostrar _MENU_ registros",
            paginate: { first:"Primero", last:"Último", next:"Siguiente", previous:"Anterior" },
            search: "Buscar:",
            zeroRecords: "No hay ventas a crédito",
            emptyTable: "No hay ventas a crédito"
        }
    });
    $('#datos').DataTable({ order: [[5, 'asc']] });
});
</script>
</body>
</html>
