<?php
session_start();
$usuario = $_SESSION['usuario'];
$tienda  = $_SESSION['tienda'];

include('inc/control.php');
include('inc/sdba/sdba.php');

$id_proforma = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_proforma) { header('Location: ventas.php'); exit; }

// Datos de la proforma
$proforma = Sdba::table('proforma');
$proforma->where('id_venta', $id_proforma);
$prof = $proforma->get_one();
if (!$prof) { header('Location: ventas.php'); exit; }

// Cliente de la proforma
$cl_tabla = Sdba::table('clientes');
$cl_tabla->where('id_cliente', $prof['cliente']);
$cl_row = $cl_tabla->get_one();
$cliente_nombre = $cl_row ? $cl_row['cliente'] : 'VARIOS';
$cliente_id     = $prof['cliente'];

// ID del cliente "Varios" para el campo por defecto
$cl_varios = Sdba::table('clientes');
$cl_varios->where('cliente', 'VARIOS');
$varios_row = $cl_varios->get_one();
$id_varios  = $varios_row ? $varios_row['id_cliente'] : $cliente_id;

// Items de la proforma con precio desde detalle_proforma
$db  = Sdba::db();
$sql = "SELECT dp.producto, dp.cantidad, dp.precio, dp.total,
               p.nom_prod, p.codigo_producto,
               u.codigo as unidad_codigo
        FROM detalle_proforma dp
        LEFT JOIN productos p ON dp.producto = p.id_producto
        LEFT JOIN unidades u  ON p.unidad_prod = u.id_unidad
        WHERE dp.venta = {$id_proforma}";
$items_proforma = $db->query($sql)->result();

$fecha_hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema - Convertir Proforma a Venta</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.0.5/sweetalert2.min.css">
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
                <li><a href="ventas_credito.php">Créditos</a></li>
            </ul>
        </div>
    </nav>
    <div class="kbg">
        <div class="cuerpo">
            <div class="titulo">
                <h3>Convertir Proforma P-<?php echo $id_proforma; ?> a Venta</h3>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="kdashboard">
                            <div class="panel panel-default pa">
                                <div class="panel-body">
                                    <form id="venta">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Fecha</label>
                                                    <input type="date" class="form-control" name="fecha" id="fecha" value="<?php echo $fecha_hoy; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Cliente</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="cliente_texto"
                                                        value="<?php echo htmlspecialchars($cliente_nombre); ?>"
                                                        placeholder="Buscar o vacío (Varios)">
                                                    <input type="hidden" name="cliente" id="cliente_id" value="<?php echo $cliente_id; ?>">
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-success" id="btn_nuevo_cliente" title="Agregar nuevo cliente"><i class="fas fa-plus"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Tipo</label>
                                                <select class="form-control" name="tipo" id="tipo">
                                                    <option value="1">Contado</option>
                                                    <option value="2">Crédito</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Forma</label>
                                                <select class="form-control" name="forma">
                                                    <option value="1">Efectivo</option>
                                                    <option value="2">Tar. Debito</option>
                                                    <option value="3">Tar. Credito</option>
                                                    <option value="4">Crédito</option>
                                                    <option value="5">Yape</option>
                                                    <option value="6">Transferencia</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3" id="campo_fecha_pago" style="display:none;">
                                                <div class="form-group">
                                                    <label>Fecha de pago</label>
                                                    <input type="date" class="form-control" name="fecha_pago" id="fecha_pago">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <h3 class="text-center">Items <small class="text-muted">(precio de proforma)</small></h3>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <table id="items" class="table table-striped table-condensed">
                                                    <thead>
                                                        <tr>
                                                            <th>Cantidad</th>
                                                            <th>Descripción</th>
                                                            <th>Unidad</th>
                                                            <th>Precio</th>
                                                            <th>Monto</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr></tr>
                                                        <?php
                                                        foreach ($items_proforma as $item):
                                                            $nom    = htmlspecialchars(strtoupper($item['nom_prod']));
                                                            $unidad = htmlspecialchars($item['unidad_codigo'] ?? '');
                                                            $precio = floatval($item['precio']);
                                                            $cant   = floatval($item['cantidad']);
                                                            $total_i = floatval($item['total']);
                                                            $id_p   = $item['producto'];
                                                        ?>
                                                        <tr class="child">
                                                            <input type="hidden" value="<?php echo $id_p; ?>" name="id_pro[]">
                                                            <input type="hidden" name="fv[]" value="">
                                                            <td><input class="cantidad" type="number" value="<?php echo $cant; ?>" name="cantidad[]"></td>
                                                            <td><?php echo $nom; ?></td>
                                                            <td><?php echo $unidad; ?></td>
                                                            <td><input type="number" class="pre" step="0.01" value="<?php echo $precio; ?>" name="precio[]"></td>
                                                            <td><input class="mon" type="text" value="<?php echo $total_i; ?>" name="total_pre[]"></td>
                                                            <td><button value="<?php echo $total_i; ?>" class="borrar">x</button></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <div class="text-right">
                                                    <strong>Total: S/ </strong><input id="total" name="total" type="text" value="<?php echo number_format(floatval($prof['total']), 2); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="guardar_venta" class="btn btn-success btn-block btn-lg">Registrar Venta</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detalles">
            <div class="titulo">
                <h3>Agregar más productos</h3>
            </div>
            <div class="panel panel-default pa">
                <div class="panel-body">
                    <table id="datos" class="table table-hover table-responsive">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Unidad</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="/assets/js/jquery-ui.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.0.5/sweetalert2.min.js"></script>
<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {

    // Total inicial desde PHP
    var total = parseFloat($('#total').val()) || 0;

    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            info: "Mostrando _START_ al _END_ de _TOTAL_ registros",
            infoEmpty: "0 registros", infoFiltered: "(filtrado de _MAX_)",
            loadingRecords: "Cargando...", lengthMenu: "Mostrar _MENU_ registros",
            paginate: { first:"Primero", last:"Último", next:"Siguiente", previous:"Anterior" },
            processing: "Procesando...", search: "Buscar:",
            zeroRecords: "No se encontraron resultados", emptyTable: "Sin productos"
        }
    });

    $('#datos').DataTable({
        processing: true, serverSide: true,
        ajax: { url: '/inc/venta_ajax.php', type: 'GET' },
        columns: [
            { title: "Producto" }, { title: "Unidad" }, { title: "Stock" },
            { title: "Precio" }, { title: "", orderable: false, searchable: false }
        ]
    });

    // Agregar desde DataTable
    $('#datos').on('click', '#agregar', function() {
        var nombre  = $(this).closest('tr').find('.nom_prod').text();
        var precio  = $(this).closest('tr').find('.precio_venta').text();
        var unidad  = $(this).closest('tr').find('.unidad').text();
        var stock   = $(this).closest('tr').find('.stock').text();
        var id_p    = $(this).val();
        var monto   = parseFloat(precio);
        $('input[type=search]').val('');
        if (stock <= 0) { swal('Advertencia', 'Sin stock.', 'warning'); return; }
        total = (total + monto).toFixed(2);
        $('#items tr:last').after(
            '<tr class="child">' +
            '<input type="hidden" value="' + id_p + '" name="id_pro[]">' +
            '<input type="hidden" name="fv[]" value="">' +
            '<td><input class="cantidad" type="number" value="1" name="cantidad[]"></td>' +
            '<td style="text-transform:uppercase;">' + nombre + '</td>' +
            '<td style="text-transform:uppercase;">' + unidad + '</td>' +
            '<td><input type="number" class="pre" step="0.01" value="' + precio + '" name="precio[]"></td>' +
            '<td><input class="mon" type="text" value="' + monto.toFixed(2) + '" name="total_pre[]"></td>' +
            '<td><button value="' + monto.toFixed(2) + '" class="borrar">x</button></td>' +
            '</tr>'
        );
        $("#total").val(parseFloat(total).toFixed(2));
    });

    // Borrar item
    $("#items").on('click', '.borrar', function() {
        var resta = parseFloat($(this).val());
        $(this).parents("tr").remove();
        total = (parseFloat(total) - resta).toFixed(2);
        $("#total").val(total);
    });

    // Actualizar por cantidad
    $('body').on('change paste keyup', '.cantidad', function() {
        var anterior = parseFloat($(this).closest('tr').find('.mon').val());
        var precio   = parseFloat($(this).closest('tr').find('.pre').val());
        var cantidad = parseFloat($(this).closest('tr').find('.cantidad').val());
        var monto    = precio * cantidad;
        total = (parseFloat(total) - anterior + monto).toFixed(2);
        $(this).closest('tr').find('.mon').val(monto.toFixed(2));
        $(this).closest('tr').find('.borrar').val(monto.toFixed(2));
        $("#total").val(total);
    });

    // Actualizar por precio
    $('body').on('change paste keyup', '.pre', function() {
        var anterior = parseFloat($(this).closest('tr').find('.mon').val());
        var precio   = parseFloat($(this).closest('tr').find('.pre').val());
        var cantidad = parseFloat($(this).closest('tr').find('.cantidad').val());
        var monto    = precio * cantidad;
        total = (parseFloat(total) - anterior + monto).toFixed(2);
        $(this).closest('tr').find('.mon').val(monto.toFixed(2));
        $(this).closest('tr').find('.borrar').val(monto.toFixed(2));
        $("#total").val(total);
    });

    // Fecha de pago al seleccionar Crédito
    $('#tipo').on('change', function() {
        if ($(this).val() === '2') { $('#campo_fecha_pago').show(); }
        else { $('#campo_fecha_pago').hide(); $('#fecha_pago').val(''); }
    });

    // Autocomplete cliente
    $('#cliente_texto').autocomplete({
        source: function(request, response) {
            $.ajax({ type: 'GET', dataType: 'json', url: '/inc/autocomplete-cliente.php',
                data: { term: request.term }, success: function(data) { response(data); } });
        },
        minLength: 1,
        select: function(event, ui) {
            $('#cliente_id').val(ui.item.id);
            $('#cliente_texto').val(ui.item.value);
            return false;
        }
    });
    $('#cliente_texto').on('input', function() {
        $('#cliente_id').val('<?php echo $id_varios; ?>');
    });

    // Nuevo cliente
    $('#btn_nuevo_cliente').on('click', function() {
        swal({
            title: 'Nuevo Cliente',
            html: '<input id="swal-nombre" class="swal2-input" placeholder="Nombre *">' +
                  '<input id="swal-tel1" class="swal2-input" placeholder="Teléfono">' +
                  '<input id="swal-tel2" class="swal2-input" placeholder="Teléfono 2">',
            showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar',
            preConfirm: function() {
                var nombre = $('#swal-nombre').val().trim();
                if (!nombre) { swal.showValidationError('El nombre es requerido'); return false; }
                return { nombre: nombre, telefono: $('#swal-tel1').val().trim(), telefono2: $('#swal-tel2').val().trim() };
            }
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    type: 'POST', dataType: 'json', url: '/inc/registrar_cliente_rapido.php',
                    data: result.value,
                    success: function(resp) {
                        if (resp.success) {
                            $('#cliente_texto').val(resp.nombre);
                            $('#cliente_id').val(resp.id);
                            swal('Registrado', 'Cliente creado correctamente', 'success');
                        } else { swal('Error', resp.mensaje, 'error'); }
                    }
                });
            }
        });
    });

    // Guardar venta
    $('body').on('click', '#guardar_venta', function(e) {
        e.preventDefault();
        var str2 = $('#venta').serialize();
        $.ajax({
            cache: false, type: "POST", dataType: "json",
            url: "/inc/registrar_venta.php",
            data: str2,
            success: function(response) {
                if (response.respuesta == false) {
                    swal('Advertencia', response.mensaje, 'warning');
                } else {
                    swal('Perfecto', response.venta_id, 'success');
                    document.location.href = "ver_venta.php?id=" + response.venta_id;
                }
            },
            error: function() { swal('Advertencia', 'Error General del Sistema', 'warning'); }
        });
        $(this).hide();
    });
});
</script>
</body>
</html>
