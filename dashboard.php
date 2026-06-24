<?php
include('inc/control.php');
include('inc/sdba/sdba.php');

$hoy        = date("Y-m-d");
$mes_inicio = date("Y-m-01");

$formas_nombre = ['1'=>'Efectivo','2'=>'Tar. Débito','3'=>'Tar. Crédito','4'=>'Crédito','5'=>'Yape','6'=>'Transferencia'];
$formas_color  = ['1'=>'#f97316','2'=>'#3b82f6','3'=>'#8b5cf6','4'=>'#94a3b8','5'=>'#22c55e','6'=>'#06b6d4'];
$tipos_nombre  = ['1'=>'Contado','2'=>'Crédito'];

// ── Ventas del día ──
$v_dia = Sdba::table('ventas');
$v_dia->where('fecha', $hoy)->and_where('estado !=', '2');
$ventas_dia_list  = $v_dia->get();
$ventas_dia_count = count($ventas_dia_list);
$ventas_dia_total = 0;
$venta_ids_dia = $dia_por_forma = $dia_forma_count = [];
foreach ($ventas_dia_list as $v) {
    $ventas_dia_total += floatval($v['total']);
    $venta_ids_dia[]   = $v['id_venta'];
}
if (!empty($venta_ids_dia)) {
    $pq = Sdba::table('pagos'); $pq->where_in('venta', $venta_ids_dia);
    foreach ($pq->get() as $p) {
        $f = $p['forma']; $m = floatval($p['monto']);
        if (!isset($dia_por_forma[$f])) { $dia_por_forma[$f] = 0; $dia_forma_count[$f] = 0; }
        $dia_por_forma[$f] += $m; $dia_forma_count[$f]++;
    }
}

// ── Ventas del mes ──
$v_mes = Sdba::table('ventas');
$v_mes->where('fecha >=', $mes_inicio)->and_where('estado !=', '2');
$ventas_mes_all   = $v_mes->get();
$ventas_mes_count = count($ventas_mes_all);
$ventas_mes_total = 0;
foreach ($ventas_mes_all as $v) $ventas_mes_total += floatval($v['total']);

// ── Productos ──
$total_productos  = Sdba::table('productos')->where('estado !=', '0')->total();
$stock_bajo       = Sdba::table('productos')->where('estado !=', '0')->and_where('stockp <=', '5')->total();
$db               = Sdba::db();
$stock_neg_list   = $db->query("SELECT nom_prod, stockp FROM productos WHERE estado != '0' AND stockp < 0 ORDER BY stockp ASC LIMIT 5")->result();

// ── Gráfico 7 días ──
$chart_labels = $chart_data = [];
for ($d = 6; $d >= 0; $d--) {
    $fd = date("Y-m-d", strtotime("-$d days"));
    $chart_labels[] = date("d/m", strtotime($fd));
    $vc = Sdba::table('ventas'); $vc->where('fecha', $fd)->and_where('estado !=', '2');
    $td = $vc->sum('total'); $chart_data[] = $td ? round(floatval($td), 2) : 0;
}
$chart_total = array_sum($chart_data);

// ── Comprobantes del mes ──
$venta_ids_mes = array_column($ventas_mes_all, 'id_venta');
$comp_tipo_map = [];
if (!empty($venta_ids_mes)) {
    $cq = Sdba::table('comprobantes'); $cq->where_in('venta', $venta_ids_mes);
    foreach ($cq->get() as $c)
        if (($c['tipo']=='F'||$c['tipo']=='B') && !isset($comp_tipo_map[$c['venta']]))
            $comp_tipo_map[$c['venta']] = $c['tipo'];
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
        body.dashboard { background: #f0f2f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1e293b; }

        .page { padding: 24px 20px 40px; }

        /* ── Section label ── */
        .sec-label {
            font-size: 10px; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; color: #94a3b8; margin: 0 0 10px;
        }

        /* ── Card ── */
        .dcard {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .dcard-body { padding: 18px 20px; }

        /* ── Stat top row ── */
        .stat-row { display: flex; gap: 0; margin-bottom: 16px; }
        .stat-item {
            flex: 1; padding: 18px 20px;
            border-right: 1px solid #e2e8f0;
            background: #fff;
        }
        .stat-item {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            background: #fff;
        }
        .stat-wrap {
            display: flex; gap: 12px; margin-bottom: 16px; background: transparent;
            border: none;
        }
        .stat-icon { font-size: 13px; color: #94a3b8; margin-bottom: 6px; }
        .stat-icon i { margin-right: 4px; }
        .stat-label { font-size: 12px; color: #64748b; margin-bottom: 4px; font-weight: 500; }
        .stat-val { font-size: 28px; font-weight: 700; color: #1e293b; line-height: 1; }
        .stat-val.accent { color: #f97316; }
        .stat-sub { font-size: 12px; color: #94a3b8; margin-top: 4px; }

        /* ── Two-col layout ── */
        .two-col { display: flex; gap: 16px; margin-bottom: 16px; }
        .two-col .col-l { flex: 1; }
        .two-col .col-r { flex: 1; }

        /* ── Payment list ── */
        .pay-title { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 14px; }
        .pay-title i { margin-right: 6px; color: #94a3b8; }
        .pay-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #f1f5f9;
        }
        .pay-row:last-child { border-bottom: none; }
        .pay-left { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #334155; }
        .pay-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .pay-right { text-align: right; }
        .pay-amount { font-size: 14px; font-weight: 600; color: #1e293b; }
        .pay-count  { font-size: 11px; color: #94a3b8; }

        /* ── Comprobantes ── */
        .comp-title { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 14px; }
        .comp-title i { margin-right: 6px; color: #94a3b8; }
        .comp-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px;
        }
        .comp-row:last-child { border-bottom: none; }
        .comp-name { color: #334155; font-weight: 500; }
        .comp-right { text-align: right; }
        .comp-amount { font-weight: 600; color: #1e293b; font-size: 14px; }
        .comp-count  { font-size: 11px; color: #94a3b8; }
        .comp-total-row { display: flex; justify-content: space-between; padding-top: 10px; margin-top: 2px; border-top: 2px solid #f1f5f9; }
        .comp-total-label { font-size: 13px; font-weight: 700; color: #334155; }
        .comp-total-val   { font-size: 16px; font-weight: 700; color: #f97316; }

        /* ── Chart ── */
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .chart-title  { font-size: 13px; font-weight: 600; color: #475569; }
        .chart-title i { margin-right: 6px; color: #94a3b8; }
        .chart-total  { font-size: 13px; font-weight: 600; color: #64748b; }
        .chart-total span { color: #1e293b; }

        /* ── Alertas ── */
        .alert-row {
            display: flex; align-items: center; gap: 14px;
            padding: 12px 20px; border-bottom: 1px solid #f1f5f9;
        }
        .alert-row:last-child { border-bottom: none; }
        .alert-icon-wrap {
            width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 14px;
        }
        .ai-red    { background: #fef2f2; color: #ef4444; }
        .ai-amber  { background: #fffbeb; color: #f59e0b; }
        .ai-green  { background: #f0fdf4; color: #22c55e; }
        .alert-text { flex: 1; }
        .alert-text strong { font-size: 13px; font-weight: 600; color: #1e293b; display: block; }
        .alert-text span   { font-size: 12px; color: #64748b; }
        .badge-pill {
            font-size: 11px; font-weight: 600; padding: 3px 10px;
            border-radius: 20px; white-space: nowrap;
        }
        .bp-red   { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
        .bp-amber { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .bp-green { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

        .mb16 { margin-bottom: 16px; }
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

    <!-- ── RESUMEN DE HOY ── -->
    <p class="sec-label">Resumen de hoy</p>
    <div class="stat-wrap mb16">
        <div class="stat-item">
            <div class="stat-icon"><i class="fas fa-cash-register"></i> Ventas hoy</div>
            <div class="stat-val accent">S/ <?php echo number_format($ventas_dia_total, 2); ?></div>
            <div class="stat-sub"><?php echo $ventas_dia_count; ?> transacciones</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i> Ventas del mes</div>
            <div class="stat-val">S/ <?php echo number_format($ventas_mes_total, 2); ?></div>
            <div class="stat-sub"><?php echo $ventas_mes_count; ?> transacciones</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon"><i class="fas fa-boxes"></i> Productos activos</div>
            <div class="stat-val"><?php echo number_format($total_productos); ?></div>
            <div class="stat-sub">en inventario</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i> Stock bajo</div>
            <div class="stat-val <?php echo $stock_bajo > 0 ? 'accent' : ''; ?>"><?php echo number_format($stock_bajo); ?></div>
            <div class="stat-sub">requieren reposición</div>
        </div>
    </div>

    <!-- ── FORMAS DE PAGO + COMPROBANTES ── -->
    <div class="two-col">
        <div class="col-l">
            <div class="dcard">
                <div class="dcard-body">
                    <div class="pay-title"><i class="fas fa-wallet"></i> Formas de pago hoy</div>
                    <?php if (!empty($dia_por_forma)): ?>
                        <?php foreach ($dia_por_forma as $fk => $fv): ?>
                        <div class="pay-row">
                            <div class="pay-left">
                                <span class="pay-dot" style="background:<?php echo $formas_color[$fk] ?? '#94a3b8'; ?>;"></span>
                                <?php echo $formas_nombre[$fk] ?? 'Otro'; ?>
                            </div>
                            <div class="pay-right">
                                <div class="pay-amount">S/ <?php echo number_format($fv, 2); ?></div>
                                <div class="pay-count"><?php echo $dia_forma_count[$fk]; ?> venta<?php echo $dia_forma_count[$fk] != 1 ? 's' : ''; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px 0;">Sin ventas registradas hoy</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-r">
            <div class="dcard">
                <div class="dcard-body">
                    <div class="comp-title"><i class="fas fa-file-invoice"></i> Comprobantes del mes</div>
                    <div class="comp-row">
                        <span class="comp-name">Boletas</span>
                        <div class="comp-right">
                            <div class="comp-amount">S/ <?php echo number_format($boleta_total, 2); ?></div>
                            <div class="comp-count"><?php echo $boleta_count; ?> docs</div>
                        </div>
                    </div>
                    <div class="comp-row">
                        <span class="comp-name">Facturas</span>
                        <div class="comp-right">
                            <div class="comp-amount">S/ <?php echo number_format($factura_total, 2); ?></div>
                            <div class="comp-count"><?php echo $factura_count; ?> docs</div>
                        </div>
                    </div>
                    <div class="comp-row">
                        <span class="comp-name">Notas de venta</span>
                        <div class="comp-right">
                            <div class="comp-amount">S/ <?php echo number_format($nota_total, 2); ?></div>
                            <div class="comp-count"><?php echo $nota_count; ?> docs</div>
                        </div>
                    </div>
                    <div class="comp-total-row">
                        <span class="comp-total-label">Total mes</span>
                        <span class="comp-total-val">S/ <?php echo number_format($ventas_mes_total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── GRÁFICO ── -->
    <div class="dcard mb16">
        <div class="dcard-body">
            <div class="chart-header">
                <div class="chart-title"><i class="fas fa-chart-bar"></i> Ventas — últimos 7 días</div>
                <div class="chart-total">Total: <span>S/ <?php echo number_format($chart_total, 2); ?></span></div>
            </div>
            <canvas id="chartVentas" height="75"></canvas>
        </div>
    </div>

    <!-- ── ALERTAS ── -->
    <p class="sec-label">Alertas</p>
    <div class="dcard">
        <?php foreach ($stock_neg_list as $sn): ?>
        <div class="alert-row">
            <div class="alert-icon-wrap ai-red"><i class="fas fa-times-circle"></i></div>
            <div class="alert-text">
                <strong>Stock negativo detectado</strong>
                <span><?php echo htmlspecialchars($sn['nom_prod']); ?> — stock: <?php echo $sn['stockp']; ?> unidades</span>
            </div>
            <span class="badge-pill bp-red">Urgente</span>
        </div>
        <?php endforeach; ?>

        <?php if ($stock_bajo > 0): ?>
        <div class="alert-row">
            <div class="alert-icon-wrap ai-amber"><i class="fas fa-box"></i></div>
            <div class="alert-text">
                <strong><?php echo $stock_bajo; ?> productos con stock bajo</strong>
                <span>Nivel ≤ 5 unidades — revisar órdenes de compra</span>
            </div>
            <span class="badge-pill bp-amber">Atención</span>
        </div>
        <?php endif; ?>

        <div class="alert-row">
            <div class="alert-icon-wrap ai-green"><i class="fas fa-check"></i></div>
            <div class="alert-text">
                <strong>Sistema operando con normalidad</strong>
                <span><?php echo $ventas_dia_count; ?> venta<?php echo $ventas_dia_count != 1 ? 's' : ''; ?> registrada<?php echo $ventas_dia_count != 1 ? 's' : ''; ?> hoy sin errores</span>
            </div>
            <span class="badge-pill bp-green">OK</span>
        </div>
    </div>

</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="assets/js/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
Chart.register(ChartDataLabels);
var data = <?php echo json_encode($chart_data); ?>;
var colors = data.map(function(v, i) { return i === data.length - 1 ? '#f97316' : '#cbd5e1'; });

new Chart(document.getElementById('chartVentas'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            data: data,
            backgroundColor: colors,
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
            },
            datalabels: {
                anchor: 'end', align: 'end',
                formatter: function(v) {
                    if (v === 0) return '';
                    return v >= 1000 ? 'S/' + (v/1000).toFixed(1) + 'k' : 'S/' + v.toFixed(0);
                },
                font: { size: 10, weight: '600' },
                color: function(ctx) { return ctx.dataIndex === data.length - 1 ? '#f97316' : '#64748b'; }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9', drawBorder: false },
                ticks: { callback: v => 'S/' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v), font: { size: 11 }, color: '#94a3b8' }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 }, color: '#94a3b8' }
            }
        },
        layout: { padding: { top: 20 } }
    }
});
</script>
</body>
</html>
