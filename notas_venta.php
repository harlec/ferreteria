<?php
include('inc/control.php');
$todas     = isset($_GET['todas']) && $_GET['todas'] == '1';
$ajax_url  = '/inc/notas_venta_ajax.php' . ($todas ? '?todas=1' : '');
$meses     = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
              '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
$mes_nombre = $meses[date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema - Notas de Venta</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <style>
        #carrito-panel {
            position: fixed;
            bottom: 0; right: 0;
            width: 320px;
            background: #fff;
            border: 2px solid #5cb85c;
            border-radius: 6px 0 0 0;
            z-index: 1050;
            box-shadow: -2px -2px 10px rgba(0,0,0,0.2);
        }
        #carrito-header {
            background: #5cb85c;
            color: #fff;
            padding: 8px 12px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #carrito-body { padding: 10px; display: none; }
        #carrito-items { max-height: 200px; overflow-y: auto; font-size: 12px; }
        .carrito-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            border-bottom: 1px solid #eee;
        }
        .carrito-item .quitar { cursor: pointer; color: #d9534f; font-weight: bold; margin-left: 6px; }
        #carrito-total { font-weight: bold; font-size: 15px; margin-top: 8px; text-align: right; }
        #carrito-acciones { margin-top: 8px; display: flex; gap: 6px; }
        #carrito-acciones a { flex: 1; text-align: center; font-size: 12px; }
        #carrito-count {
            background: #d9534f;
            color: #fff;
            border-radius: 50%;
            padding: 1px 6px;
            font-size: 11px;
        }
    </style>
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
                <li class="active"><a href="notas_venta.php">Notas de Venta</a></li>
            </ul>
        </div>
    </nav>

    <div class="kbg">
        <div class="cuerpofull">
            <div class="titulo">
                <h3>Notas de Venta<?php echo $todas ? '' : ' - '.$mes_nombre; ?></h3>
                <p class="text-muted">
                    <?php if ($todas): ?>
                        <a href="notas_venta.php" class="btn btn-sm btn-default">Ver solo este mes</a>
                    <?php else: ?>
                        <a href="notas_venta.php?todas=1" class="btn btn-sm btn-primary">Ver todas</a>
                    <?php endif; ?>
                </p>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="kdashboard">
                            <div class="panel panel-default pa">
                                <div class="panel-body">
                                    <table id="tabla-notas" class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ID</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Total</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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

<!-- Modal detalle productos -->
<div class="modal fade" id="modal-detalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Detalle de Nota de Venta <span id="modal-venta-id"></span></h4>
            </div>
            <div class="modal-body" id="modal-body-contenido">
                <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="modal-btn-carrito">
                    <i class="fas fa-cart-plus"></i> Agregar al carrito
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Carrito flotante -->
<div id="carrito-panel">
    <div id="carrito-header">
        <span><i class="fas fa-shopping-cart"></i> Notas seleccionadas <span id="carrito-count">0</span></span>
        <span id="carrito-toggle-icon">&#9650;</span>
    </div>
    <div id="carrito-body">
        <div id="carrito-items">
            <p class="text-muted text-center" id="carrito-vacio">Sin notas seleccionadas</p>
        </div>
        <div id="carrito-total">Total: S/ <span id="carrito-total-val">0.00</span></div>
        <div id="carrito-acciones">
            <a id="btn-ir-boleta" href="#" class="btn btn-warning btn-sm disabled">
                <i class="fab fa-bitcoin"></i> Boleta
            </a>
            <a id="btn-ir-factura" href="#" class="btn btn-primary btn-sm disabled">
                <i class="fas fa-file-invoice-dollar"></i> Factura
            </a>
            <button id="btn-vaciar-carrito" class="btn btn-danger btn-sm" title="Vaciar carrito">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // ── DataTable ──────────────────────────────────────────────
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            info: "Mostrando _START_ al _END_ de _TOTAL_ registros",
            infoEmpty: "0 registros",
            infoFiltered: "(filtrado de _MAX_)",
            loadingRecords: "Cargando...",
            lengthMenu: "Mostrar _MENU_ registros",
            paginate: { first:"Primero", last:"Último", next:"Siguiente", previous:"Anterior" },
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron notas de venta",
            emptyTable: "No hay notas de venta"
        }
    });

    $('#tabla-notas').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'desc']],
        ajax: { url: '<?php echo $ajax_url; ?>', type: 'GET' },
        columns: [
            { orderable: false, searchable: false },
            {},
            {},
            {},
            {},
            { orderable: false, searchable: false }
        ]
    });

    // ── Carrito (estado en memoria) ────────────────────────────
    var carrito = {}; // { id: { id, fecha, total, cliente } }

    function actualizarCarrito() {
        var ids = Object.keys(carrito);
        $('#carrito-count').text(ids.length);

        var $items = $('#carrito-items');
        $items.empty();

        if (ids.length === 0) {
            $items.html('<p class="text-muted text-center" id="carrito-vacio">Sin notas seleccionadas</p>');
            $('#carrito-total-val').text('0.00');
            $('#btn-ir-boleta, #btn-ir-factura').addClass('disabled').attr('href', '#');
            return;
        }

        var acumulado = 0;
        $.each(carrito, function(id, nota) {
            acumulado += parseFloat(nota.total);
            $items.append(
                '<div class="carrito-item">' +
                '<span>v-' + id + ' <small class="text-muted">(' + nota.cliente + ')</small> S/' + parseFloat(nota.total).toFixed(2) + '</span>' +
                '<span class="quitar" data-id="' + id + '" title="Quitar">&times;</span>' +
                '</div>'
            );
        });

        $('#carrito-total-val').text(acumulado.toFixed(2));

        var ids_str = ids.join(',');
        $('#btn-ir-boleta').removeClass('disabled').attr('href', 'boleta.php?ids=' + ids_str);
        $('#btn-ir-factura').removeClass('disabled').attr('href', 'factura.php?ids=' + ids_str);
    }

    function agregarAlCarrito(id, fecha, total, cliente) {
        if (carrito[id]) {
            alert('Esta nota ya está en el carrito.');
            return;
        }
        carrito[id] = { id: id, fecha: fecha, total: total, cliente: cliente };
        actualizarCarrito();
        // Mostrar carrito si estaba cerrado
        if ($('#carrito-body').is(':hidden')) {
            $('#carrito-body').show();
            $('#carrito-toggle-icon').html('&#9660;');
        }
    }

    // Botón agregar desde tabla
    $('body').on('click', '.btn-carrito', function() {
        var id      = $(this).data('id');
        var fecha   = $(this).data('fecha');
        var total   = $(this).data('total');
        var cliente = $(this).data('cliente');
        agregarAlCarrito(id, fecha, total, cliente);
    });

    // Quitar del carrito
    $('body').on('click', '.quitar', function() {
        var id = $(this).data('id');
        delete carrito[id];
        actualizarCarrito();
    });

    // Vaciar carrito
    $('#btn-vaciar-carrito').on('click', function() {
        carrito = {};
        actualizarCarrito();
    });

    // Toggle carrito
    $('#carrito-header').on('click', function() {
        $('#carrito-body').toggle();
        $('#carrito-toggle-icon').html($('#carrito-body').is(':visible') ? '&#9660;' : '&#9650;');
    });

    // ── Modal detalle ──────────────────────────────────────────
    var modalVentaData = {};

    $('body').on('click', '.btn-detalle', function() {
        var id = $(this).data('id');
        modalVentaData = { id: id };
        $('#modal-venta-id').text('v-' + id);
        $('#modal-body-contenido').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        $('#modal-detalle').modal('show');

        $.ajax({
            url: '/inc/notas_venta_detalle.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(resp) {
                if (!resp.items || resp.items.length === 0) {
                    $('#modal-body-contenido').html('<p class="text-muted">Sin productos.</p>');
                    return;
                }
                var html = '<table class="table table-condensed table-striped">';
                html += '<thead><tr><th>Código</th><th>Producto</th><th>Unidad</th><th class="text-right">Cant.</th><th class="text-right">Precio</th><th class="text-right">Total</th></tr></thead><tbody>';
                $.each(resp.items, function(i, item) {
                    html += '<tr><td>' + item.codigo + '</td><td>' + item.nombre + '</td><td>' + item.unidad + '</td>'
                         + '<td class="text-right">' + item.cantidad + '</td>'
                         + '<td class="text-right">S/' + item.precio + '</td>'
                         + '<td class="text-right">S/' + item.total + '</td></tr>';
                });
                html += '</tbody><tfoot><tr><td colspan="5" class="text-right"><strong>Total:</strong></td><td class="text-right"><strong>S/' + resp.total + '</strong></td></tr></tfoot>';
                html += '</table>';
                $('#modal-body-contenido').html(html);

                // Guardar data para el botón del modal
                modalVentaData.total   = parseFloat(resp.total.replace(',',''));
                modalVentaData.cliente = '';
            },
            error: function() {
                $('#modal-body-contenido').html('<p class="text-danger">Error al cargar el detalle.</p>');
            }
        });
    });

    // Agregar desde modal: buscar en la fila de la tabla
    $('#modal-btn-carrito').on('click', function() {
        var id = modalVentaData.id;
        // Buscar data del botón en la tabla
        var $btn = $('button.btn-carrito[data-id="' + id + '"]');
        if ($btn.length) {
            var fecha   = $btn.data('fecha');
            var total   = $btn.data('total');
            var cliente = $btn.data('cliente');
            agregarAlCarrito(id, fecha, total, cliente);
            $('#modal-detalle').modal('hide');
        } else {
            // Si está en otra página del datatable, solo usamos total del modal
            agregarAlCarrito(id, '', modalVentaData.total || 0, '');
            $('#modal-detalle').modal('hide');
        }
    });
});
</script>
</body>
</html>
