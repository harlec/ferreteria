<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$hoy = date("Y-m-d");
$mes_inicio = date("Y-m-01");

$formas_nombre = array('1'=>'Efectivo','2'=>'Tar. Débito','3'=>'Tar. Crédito','4'=>'Crédito','5'=>'Yape','6'=>'Transferencia');
$tipos_nombre  = array('1'=>'Contado','2'=>'Crédito');

// Ventas del día
$v_dia = Sdba::table('ventas');
$v_dia->where('fecha', $hoy)->and_where('estado !=', '2');
$ventas_dia_list  = $v_dia->get();
$ventas_dia_count = count($ventas_dia_list);
$ventas_dia_total = 0;
$venta_ids_dia    = array();
$dia_por_forma    = array();
$dia_forma_count  = array();
$dia_por_tipo     = array();
$dia_tipo_count   = array();
foreach ($ventas_dia_list as $v) {
    $t = floatval($v['total']);
    $ventas_dia_total += $t;
    $venta_ids_dia[]   = $v['id_venta'];
    $tp = $v['tipo'];
    if (!isset($dia_por_tipo[$tp])) { $dia_por_tipo[$tp] = 0; $dia_tipo_count[$tp] = 0; }
    $dia_por_tipo[$tp]  += $t;
    $dia_tipo_count[$tp]++;
}
if (!empty($venta_ids_dia)) {
    $pagos_dia_q = Sdba::table('pagos');
    $pagos_dia_q->where_in('venta', $venta_ids_dia);
    foreach ($pagos_dia_q->get() as $p) {
        $f = $p['forma']; $m = floatval($p['monto']);
        if (!isset($dia_por_forma[$f])) { $dia_por_forma[$f] = 0; $dia_forma_count[$f] = 0; }
        $dia_por_forma[$f] += $m; $dia_forma_count[$f]++;
    }
}

// Ventas del mes
$v_mes = Sdba::table('ventas');
$v_mes->where('fecha >=', $mes_inicio)->and_where('estado !=', '2');
$ventas_mes_all   = $v_mes->get();
$ventas_mes_count = count($ventas_mes_all);
$ventas_mes_total = 0;
$mes_por_forma    = array();
$mes_forma_count  = array();
$mes_por_tipo     = array();
$mes_tipo_count   = array();
foreach ($ventas_mes_all as $v) {
    $t = floatval($v['total']);
    $ventas_mes_total += $t;
    $tp = $v['tipo'];
    if (!isset($mes_por_tipo[$tp])) { $mes_por_tipo[$tp] = 0; $mes_tipo_count[$tp] = 0; }
    $mes_por_tipo[$tp]  += $t;
    $mes_tipo_count[$tp]++;
}

// Productos
$prod      = Sdba::table('productos');
$prod->where('estado !=', '0');
$total_productos = $prod->total();

$prod_bajo = Sdba::table('productos');
$prod_bajo->where('estado !=', '0')->and_where('stockp <=', '5');
$stock_bajo = $prod_bajo->total();

// Gráfico 7 días
$chart_labels = array();
$chart_data   = array();
for ($d = 6; $d >= 0; $d--) {
    $fecha_d = date("Y-m-d", strtotime("-$d days"));
    $chart_labels[] = date("d/m", strtotime($fecha_d));
    $v_chart = Sdba::table('ventas');
    $v_chart->where('fecha', $fecha_d)->and_where('estado !=', '2');
    $total_d = $v_chart->sum('total');
    $chart_data[] = $total_d ? floatval($total_d) : 0;
}

// Formas de pago del mes
$venta_ids_mes = array_column($ventas_mes_all, 'id_venta');
if (!empty($venta_ids_mes)) {
    $pagos_mes_q = Sdba::table('pagos');
    $pagos_mes_q->where_in('venta', $venta_ids_mes);
    foreach ($pagos_mes_q->get() as $p) {
        $f = $p['forma']; $m = floatval($p['monto']);
        if (!isset($mes_por_forma[$f])) { $mes_por_forma[$f] = 0; $mes_forma_count[$f] = 0; }
        $mes_por_forma[$f] += $m; $mes_forma_count[$f]++;
    }
}

// Comprobantes del mes
$comp_tipo_map = array();
if (!empty($venta_ids_mes)) {
    $comp_q = Sdba::table('comprobantes');
    $comp_q->where_in('venta', $venta_ids_mes);
    foreach ($comp_q->get() as $c) {
        if (($c['tipo'] == 'F' || $c['tipo'] == 'B') && !isset($comp_tipo_map[$c['venta']])) {
            $comp_tipo_map[$c['venta']] = $c['tipo'];
        }
    }
}
$boleta_count = 0; $boleta_total = 0;
$factura_count = 0; $factura_total = 0;
$nota_count = 0;   $nota_total = 0;
foreach ($ventas_mes_all as $v) {
    $id = $v['id_venta']; $total = floatval($v['total']);
    if (isset($comp_tipo_map[$id])) {
        if ($comp_tipo_map[$id] == 'B') { $boleta_count++;  $boleta_total  += $total; }
        else                            { $factura_count++; $factura_total += $total; }
    } else { $nota_count++; $nota_total += $total; }
}

$mes_nombre = strftime('%B', strtotime($mes_inicio));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
    <style>
        body.dashboard { background: #f0f2f5; }

        .dash-wrap { padding: 24px 20px; }

        .dash-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 20px;
        }
        .dash-title span {
            font-size: 13px;
            font-weight: 400;
            color: #888;
            margin-left: 8px;
        }

        /* ── Stat card ── */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 20px 20px 14px;
            margin-bottom: 20px;
            border-top: 4px solid transparent;
            transition: box-shadow .2s;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
        .stat-card.green  { border-top-color: #27ae60; }
        .stat-card.blue   { border-top-color: #2980b9; }
        .stat-card.orange { border-top-color: #e67e22; }
        .stat-card.red    { border-top-color: #e74c3c; }
        .stat-card.teal   { border-top-color: #16a085; }
        .stat-card.purple { border-top-color: #8e44ad; }
        .stat-card.yellow { border-top-color: #f39c12; }

        .stat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff;
        }
        .stat-icon.green  { background: #27ae60; }
        .stat-icon.blue   { background: #2980b9; }
        .stat-icon.orange { background: #e67e22; }
        .stat-icon.red    { background: #e74c3c; }
        .stat-icon.teal   { background: #16a085; }
        .stat-icon.purple { background: #8e44ad; }
        .stat-icon.yellow { background: #f39c12; }

        .stat-value { font-size: 26px; font-weight: 700; color: #1a1a2e; line-height: 1.1; }
        .stat-label { font-size: 13px; color: #888; margin-top: 2px; }
        .stat-badge {
            display: inline-block;
            background: #f0f2f5;
            color: #555;
            font-size: 11px;
            border-radius: 20px;
            padding: 1px 8px;
            margin-left: 4px;
        }

        /* breakdown list */
        .breakdown { margin-top: 10px; border-top: 1px solid #f0f2f5; padding-top: 8px; }
        .breakdown-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 3px 0; font-size: 12px; color: #555;
        }
        .breakdown-item strong { color: #1a1a2e; }
        .breakdown-dot {
            display: inline-block; width: 7px; height: 7px;
            border-radius: 50%; margin-right: 5px;
        }

        /* clickable card */
        .stat-card-link { text-decoration: none !important; color: inherit !important; display: block; }
        .stat-card-link:hover .stat-card { box-shadow: 0 4px 16px rgba(0,0,0,.15); }

        /* chart card */
        .chart-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 20px;
            margin-bottom: 20px;
        }
        .chart-title { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 14px; }

        /* section label */
        .section-label {
            font-size: 12px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .5px; color: #aaa; margin: 4px 0 12px;
        }
    </style>
</head>
<body class="mobile dashboard escritorio">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><img class="img-responsive logo" src="/assets/img/harlec-sistema.png"></a>
            </div>
            <?php menu('1'); ?>
        </div>
    </nav>

    <div class="kbg">
        <div class="dash-wrap">

            <div class="dash-title">
                Dashboard <span><?php echo date('d/m/Y'); ?></span>
            </div>

            <!-- ROW 1: Hoy -->
            <div class="section-label">Hoy</div>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card green">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">S/ <?php echo number_format($ventas_dia_total, 2); ?></div>
                                <div class="stat-label">Ventas hoy <span class="stat-badge"><?php echo $ventas_dia_count; ?></span></div>
                            </div>
                            <div class="stat-icon green"><i class="fas fa-cash-register"></i></div>
                        </div>
                        <?php if (!empty($dia_por_tipo)): ?>
                        <div class="breakdown">
                            <?php foreach ($dia_por_tipo as $tk => $tv): ?>
                            <div class="breakdown-item">
                                <span><?php echo $tipos_nombre[$tk] ?? 'Otro'; ?> (<?php echo $dia_tipo_count[$tk]; ?>)</span>
                                <strong>S/ <?php echo number_format($tv, 2); ?></strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dia_por_forma)): ?>
                        <div class="breakdown">
                            <?php foreach ($dia_por_forma as $fk => $fv): ?>
                            <div class="breakdown-item">
                                <span><?php echo $formas_nombre[$fk] ?? 'Otro'; ?> (<?php echo $dia_forma_count[$fk]; ?>)</span>
                                <span>S/ <?php echo number_format($fv, 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card orange">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $total_productos; ?></div>
                                <div class="stat-label">Productos activos</div>
                            </div>
                            <div class="stat-icon orange"><i class="fas fa-boxes"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card red">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $stock_bajo; ?></div>
                                <div class="stat-label">Stock bajo <span class="stat-badge">&le; 5</span></div>
                            </div>
                            <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2: Mes -->
            <div class="section-label">Este mes — <?php echo ucfirst($mes_nombre); ?></div>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card blue">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">S/ <?php echo number_format($ventas_mes_total, 2); ?></div>
                                <div class="stat-label">Total ventas <span class="stat-badge"><?php echo $ventas_mes_count; ?></span></div>
                            </div>
                            <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
                        </div>
                        <?php if (!empty($mes_por_tipo)): ?>
                        <div class="breakdown">
                            <?php foreach ($mes_por_tipo as $tk => $tv): ?>
                            <div class="breakdown-item">
                                <span><?php echo $tipos_nombre[$tk] ?? 'Otro'; ?> (<?php echo $mes_tipo_count[$tk]; ?>)</span>
                                <strong>S/ <?php echo number_format($tv, 2); ?></strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($mes_por_forma)): ?>
                        <div class="breakdown">
                            <?php foreach ($mes_por_forma as $fk => $fv): ?>
                            <div class="breakdown-item">
                                <span><?php echo $formas_nombre[$fk] ?? 'Otro'; ?> (<?php echo $mes_forma_count[$fk]; ?>)</span>
                                <span>S/ <?php echo number_format($fv, 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="ventas.php?tipo_comp=B" class="stat-card-link">
                        <div class="stat-card teal">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-value">S/ <?php echo number_format($boleta_total, 2); ?></div>
                                    <div class="stat-label">Boletas <span class="stat-badge"><?php echo $boleta_count; ?></span></div>
                                </div>
                                <div class="stat-icon teal"><i class="fas fa-file-invoice"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="ventas.php?tipo_comp=F" class="stat-card-link">
                        <div class="stat-card purple">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-value">S/ <?php echo number_format($factura_total, 2); ?></div>
                                    <div class="stat-label">Facturas <span class="stat-badge"><?php echo $factura_count; ?></span></div>
                                </div>
                                <div class="stat-icon purple"><i class="fas fa-file-invoice-dollar"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="ventas.php?tipo_comp=NV" class="stat-card-link">
                        <div class="stat-card yellow">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-value">S/ <?php echo number_format($nota_total, 2); ?></div>
                                    <div class="stat-label">Notas de venta <span class="stat-badge"><?php echo $nota_count; ?></span></div>
                                </div>
                                <div class="stat-icon yellow"><i class="fas fa-receipt"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- ROW 3: Gráfico -->
            <div class="row">
                <div class="col-md-12">
                    <div class="chart-card">
                        <div class="chart-title"><i class="fas fa-chart-bar" style="color:#2980b9;margin-right:6px;"></i>Ventas — últimos 7 días</div>
                        <canvas id="chartVentas" height="70"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
    $(document).ready(function() {
        var ctx = document.getElementById('chartVentas').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Ventas (S/)',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: 'rgba(41, 128, 185, 0.75)',
                    borderColor: 'rgba(41, 128, 185, 1)',
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(c) { return ' S/ ' + c.parsed.y.toLocaleString('es-PE', {minimumFractionDigits:2}); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0f2f5' },
                        ticks: {
                            callback: function(v) { return 'S/ ' + v.toLocaleString(); }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>
</body>
</html>
