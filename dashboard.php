<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$hoy        = date("Y-m-d");
$mes_inicio = date("Y-m-01");

$formas_nombre = ['1'=>'Efectivo','2'=>'Tar. Débito','3'=>'Tar. Crédito','4'=>'Crédito','5'=>'Yape','6'=>'Transferencia'];
$tipos_nombre  = ['1'=>'Contado','2'=>'Crédito'];

// Ventas del día
$v_dia = Sdba::table('ventas');
$v_dia->where('fecha', $hoy)->and_where('estado !=', '2');
$ventas_dia_list  = $v_dia->get();
$ventas_dia_count = count($ventas_dia_list);
$ventas_dia_total = 0;
$venta_ids_dia = $dia_por_forma = $dia_forma_count = $dia_por_tipo = $dia_tipo_count = [];
foreach ($ventas_dia_list as $v) {
    $t = floatval($v['total']); $ventas_dia_total += $t; $venta_ids_dia[] = $v['id_venta'];
    $tp = $v['tipo'];
    if (!isset($dia_por_tipo[$tp])) { $dia_por_tipo[$tp] = 0; $dia_tipo_count[$tp] = 0; }
    $dia_por_tipo[$tp] += $t; $dia_tipo_count[$tp]++;
}
if (!empty($venta_ids_dia)) {
    $pq = Sdba::table('pagos'); $pq->where_in('venta', $venta_ids_dia);
    foreach ($pq->get() as $p) {
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
$mes_por_forma = $mes_forma_count = $mes_por_tipo = $mes_tipo_count = [];
foreach ($ventas_mes_all as $v) {
    $t = floatval($v['total']); $ventas_mes_total += $t;
    $tp = $v['tipo'];
    if (!isset($mes_por_tipo[$tp])) { $mes_por_tipo[$tp] = 0; $mes_tipo_count[$tp] = 0; }
    $mes_por_tipo[$tp] += $t; $mes_tipo_count[$tp]++;
}

// Productos
$total_productos = Sdba::table('productos')->where('estado !=', '0')->total();
$stock_bajo      = Sdba::table('productos')->where('estado !=', '0')->and_where('stockp <=', '5')->total();

// Gráfico 7 días
$chart_labels = $chart_data = [];
for ($d = 6; $d >= 0; $d--) {
    $fd = date("Y-m-d", strtotime("-$d days"));
    $chart_labels[] = date("d/m", strtotime($fd));
    $vc = Sdba::table('ventas'); $vc->where('fecha', $fd)->and_where('estado !=', '2');
    $td = $vc->sum('total'); $chart_data[] = $td ? floatval($td) : 0;
}

// Formas mes
$venta_ids_mes = array_column($ventas_mes_all, 'id_venta');
if (!empty($venta_ids_mes)) {
    $pmq = Sdba::table('pagos'); $pmq->where_in('venta', $venta_ids_mes);
    foreach ($pmq->get() as $p) {
        $f = $p['forma']; $m = floatval($p['monto']);
        if (!isset($mes_por_forma[$f])) { $mes_por_forma[$f] = 0; $mes_forma_count[$f] = 0; }
        $mes_por_forma[$f] += $m; $mes_forma_count[$f]++;
    }
}

// Comprobantes mes
$comp_tipo_map = [];
if (!empty($venta_ids_mes)) {
    $cq = Sdba::table('comprobantes'); $cq->where_in('venta', $venta_ids_mes);
    foreach ($cq->get() as $c) {
        if (($c['tipo']=='F'||$c['tipo']=='B') && !isset($comp_tipo_map[$c['venta']]))
            $comp_tipo_map[$c['venta']] = $c['tipo'];
    }
}
$boleta_count=$boleta_total=$factura_count=$factura_total=$nota_count=$nota_total=0;
foreach ($ventas_mes_all as $v) {
    $id=$v['id_venta']; $t=floatval($v['total']);
    if (isset($comp_tipo_map[$id])) {
        if ($comp_tipo_map[$id]=='B') { $boleta_count++;  $boleta_total  += $t; }
        else                          { $factura_count++; $factura_total += $t; }
    } else { $nota_count++; $nota_total += $t; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
    <style>
        body.dashboard { background: #f5f6fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

        .page { padding: 28px 24px; }

        .page-header { margin-bottom: 28px; }
        .page-header h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 2px; }
        .page-header p  { font-size: 13px; color: #94a3b8; margin: 0; }

        .section-title {
            font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .8px; color: #94a3b8; margin: 0 0 12px;
        }

        /* Card base */
        .card {
            background: #fff;
            border: 1px solid #e8ecf0;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        /* Stat card */
        .stat-body { padding: 20px; }
        .stat-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .stat-info-label { font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 6px; }
        .stat-info-value { font-size: 28px; font-weight: 700; color: #1e293b; line-height: 1; }
        .stat-info-sub   { font-size: 12px; color: #94a3b8; margin-top: 4px; }
        .stat-icon-wrap {
            width: 40px; height: 40px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .ic-blue   { background: #eff6ff; color: #3b82f6; }
        .ic-green  { background: #f0fdf4; color: #22c55e; }
        .ic-amber  { background: #fffbeb; color: #f59e0b; }
        .ic-red    { background: #fef2f2; color: #ef4444; }
        .ic-violet { background: #f5f3ff; color: #8b5cf6; }
        .ic-teal   { background: #f0fdfa; color: #14b8a6; }
        .ic-indigo { background: #eef2ff; color: #6366f1; }

        /* Breakdown */
        .breakdown { border-top: 1px solid #f1f5f9; padding: 10px 20px 14px; }
        .brow { display: flex; justify-content: space-between; font-size: 12px; padding: 3px 0; color: #64748b; }
        .brow span:last-child { font-weight: 600; color: #334155; }

        /* Clickable */
        a.card-link { text-decoration: none; color: inherit; display: block; }
        a.card-link:hover .card { border-color: #c7d2fe; background: #fafbff; }

        /* Chart */
        .chart-body { padding: 20px; }
        .chart-header { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 16px; }

        /* Divider between sections */
        .section-gap { margin-top: 8px; }
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
<div class="page">

    <div class="page-header">
        <h2>Dashboard</h2>
        <p><?php echo date('l, d \d\e F \d\e Y'); ?></p>
    </div>

    <!-- HOY -->
    <p class="section-title">Hoy</p>
    <div class="row">

        <div class="col-md-4 col-sm-6">
            <div class="card">
                <div class="stat-body">
                    <div class="stat-top">
                        <div>
                            <div class="stat-info-label">Ventas del día</div>
                            <div class="stat-info-value">S/ <?php echo number_format($ventas_dia_total, 2); ?></div>
                            <div class="stat-info-sub"><?php echo $ventas_dia_count; ?> venta<?php echo $ventas_dia_count != 1 ? 's' : ''; ?></div>
                        </div>
                        <div class="stat-icon-wrap ic-green"><i class="fas fa-cash-register"></i></div>
                    </div>
                </div>
                <?php if (!empty($dia_por_tipo)): ?>
                <div class="breakdown">
                    <?php foreach ($dia_por_tipo as $tk => $tv): ?>
                    <div class="brow">
                        <span><?php echo $tipos_nombre[$tk] ?? 'Otro'; ?> · <?php echo $dia_tipo_count[$tk]; ?></span>
                        <span>S/ <?php echo number_format($tv, 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($dia_por_forma)): ?>
                <div class="breakdown" style="border-top:1px dashed #f1f5f9;">
                    <?php foreach ($dia_por_forma as $fk => $fv): ?>
                    <div class="brow">
                        <span><?php echo $formas_nombre[$fk] ?? 'Otro'; ?></span>
                        <span>S/ <?php echo number_format($fv, 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card">
                <div class="stat-body">
                    <div class="stat-top">
                        <div>
                            <div class="stat-info-label">Productos activos</div>
                            <div class="stat-info-value"><?php echo $total_productos; ?></div>
                            <div class="stat-info-sub">en catálogo</div>
                        </div>
                        <div class="stat-icon-wrap ic-amber"><i class="fas fa-boxes"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card">
                <div class="stat-body">
                    <div class="stat-top">
                        <div>
                            <div class="stat-info-label">Stock bajo</div>
                            <div class="stat-info-value" style="color:<?php echo $stock_bajo > 0 ? '#ef4444' : '#22c55e'; ?>">
                                <?php echo $stock_bajo; ?>
                            </div>
                            <div class="stat-info-sub">productos con ≤ 5 unidades</div>
                        </div>
                        <div class="stat-icon-wrap ic-red"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MES -->
    <p class="section-title section-gap">Este mes — <?php echo date('F Y'); ?></p>
    <div class="row">

        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="stat-body">
                    <div class="stat-top">
                        <div>
                            <div class="stat-info-label">Total ventas</div>
                            <div class="stat-info-value">S/ <?php echo number_format($ventas_mes_total, 2); ?></div>
                            <div class="stat-info-sub"><?php echo $ventas_mes_count; ?> ventas</div>
                        </div>
                        <div class="stat-icon-wrap ic-blue"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
                <?php if (!empty($mes_por_tipo)): ?>
                <div class="breakdown">
                    <?php foreach ($mes_por_tipo as $tk => $tv): ?>
                    <div class="brow">
                        <span><?php echo $tipos_nombre[$tk] ?? 'Otro'; ?> · <?php echo $mes_tipo_count[$tk]; ?></span>
                        <span>S/ <?php echo number_format($tv, 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($mes_por_forma)): ?>
                <div class="breakdown" style="border-top:1px dashed #f1f5f9;">
                    <?php foreach ($mes_por_forma as $fk => $fv): ?>
                    <div class="brow">
                        <span><?php echo $formas_nombre[$fk] ?? 'Otro'; ?></span>
                        <span>S/ <?php echo number_format($fv, 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="ventas.php?tipo_comp=B" class="card-link">
                <div class="card">
                    <div class="stat-body">
                        <div class="stat-top">
                            <div>
                                <div class="stat-info-label">Boletas</div>
                                <div class="stat-info-value">S/ <?php echo number_format($boleta_total, 2); ?></div>
                                <div class="stat-info-sub"><?php echo $boleta_count; ?> emitidas</div>
                            </div>
                            <div class="stat-icon-wrap ic-teal"><i class="fas fa-file-invoice"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="ventas.php?tipo_comp=F" class="card-link">
                <div class="card">
                    <div class="stat-body">
                        <div class="stat-top">
                            <div>
                                <div class="stat-info-label">Facturas</div>
                                <div class="stat-info-value">S/ <?php echo number_format($factura_total, 2); ?></div>
                                <div class="stat-info-sub"><?php echo $factura_count; ?> emitidas</div>
                            </div>
                            <div class="stat-icon-wrap ic-violet"><i class="fas fa-file-invoice-dollar"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="ventas.php?tipo_comp=NV" class="card-link">
                <div class="card">
                    <div class="stat-body">
                        <div class="stat-top">
                            <div>
                                <div class="stat-info-label">Notas de venta</div>
                                <div class="stat-info-value">S/ <?php echo number_format($nota_total, 2); ?></div>
                                <div class="stat-info-sub"><?php echo $nota_count; ?> emitidas</div>
                            </div>
                            <div class="stat-icon-wrap ic-indigo"><i class="fas fa-receipt"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <!-- GRÁFICO -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="chart-body">
                    <div class="chart-header">Ventas — últimos 7 días</div>
                    <canvas id="chartVentas" height="65"></canvas>
                </div>
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
new Chart(document.getElementById('chartVentas'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($chart_data); ?>,
            backgroundColor: '#3b82f6',
            borderRadius: 5,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { label: c => ' S/ ' + c.parsed.y.toLocaleString('es-PE', {minimumFractionDigits:2}) }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9', drawBorder: false },
                ticks: { callback: v => 'S/ ' + v.toLocaleString(), font: { size: 11 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});
</script>
</body>
</html>
