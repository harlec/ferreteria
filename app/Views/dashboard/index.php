<div class="kbg">
    <div class="cuerpofull">
        <div class="titulo">
            <h3>Dashboard</h3>
        </div>
        <div class="container-fluid">
            <div class="row">
                <!-- Estadísticas -->
                <div class="col-md-3 col-sm-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fas fa-boxes fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?= $stats['productos'] ?></div>
                                    <div>Productos</div>
                                </div>
                            </div>
                        </div>
                        <a href="/productos" class="panel-footer">
                            <span class="pull-left">Ver detalles</span>
                            <span class="pull-right"><i class="fas fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="panel panel-green">
                        <div class="panel-heading" style="background-color: #5cb85c; color: white;">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?= $stats['clientes'] ?></div>
                                    <div>Clientes</div>
                                </div>
                            </div>
                        </div>
                        <a href="/clientes" class="panel-footer">
                            <span class="pull-left">Ver detalles</span>
                            <span class="pull-right"><i class="fas fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading" style="background-color: #f0ad4e; color: white;">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fas fa-shopping-cart fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?= $stats['ventas_hoy'] ?></div>
                                    <div>Ventas Hoy</div>
                                </div>
                            </div>
                        </div>
                        <a href="/ventas" class="panel-footer">
                            <span class="pull-left">Ver detalles</span>
                            <span class="pull-right"><i class="fas fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="panel panel-red">
                        <div class="panel-heading" style="background-color: #d9534f; color: white;">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?= $stats['stock_bajo'] ?></div>
                                    <div>Stock Bajo</div>
                                </div>
                            </div>
                        </div>
                        <a href="/reportes/stock" class="panel-footer">
                            <span class="pull-left">Ver detalles</span>
                            <span class="pull-right"><i class="fas fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resumen de ventas del día -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><i class="fas fa-chart-line"></i> Resumen del Día</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Total de Ventas Hoy</h4>
                                    <h2 class="text-success">
                                        S/ <?= number_format($stats['total_hoy'], 2) ?>
                                    </h2>
                                </div>
                                <div class="col-md-6">
                                    <h4>Número de Transacciones</h4>
                                    <h2 class="text-primary">
                                        <?= $stats['ventas_hoy'] ?>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.panel-heading .huge {
    font-size: 30px;
    font-weight: bold;
}
.panel-footer {
    padding: 10px 15px;
    background-color: #f5f5f5;
    display: block;
    text-decoration: none;
    color: #333;
}
.panel-footer:hover {
    background-color: #e5e5e5;
    text-decoration: none;
}
.panel-primary .panel-heading {
    background-color: #337ab7;
    color: white;
}
</style>
