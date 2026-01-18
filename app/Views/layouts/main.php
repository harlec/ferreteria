<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Sistema' ?> - Ferretería</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/bootstrap.min.css') ?>">
    <!-- jQuery UI -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/jquery-ui.min.css') ?>">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/custom.css') ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/select2.min.css') ?>">
    <!-- SweetAlert -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/sweetalert2.min.css') ?>">
    <!-- Easy Autocomplete -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/easy-autocomplete.min.css') ?>">
</head>
<body class="mobile dashboard">
    <!-- Navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/dashboard">
                    <img class="img-responsive logo" src="<?= $__view->asset('img/harlec-sistema.png') ?>">
                </a>
            </div>

            <?php $__view->include('navbar', ['menuActivo' => $menuActivo ?? '1']); ?>
        </div>
    </nav>

    <!-- Contenido principal -->
    <?= $content ?>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- jQuery UI -->
    <script src="<?= $__view->asset('js/jquery-ui.min.js') ?>"></script>
    <!-- jQuery Validate -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <!-- Select2 -->
    <script src="<?= $__view->asset('js/select2.full.min.js') ?>"></script>
    <!-- SweetAlert -->
    <script src="<?= $__view->asset('js/sweetalert2.all.min.js') ?>"></script>
    <!-- Easy Autocomplete -->
    <script src="<?= $__view->asset('js/jquery.easy-autocomplete.min.js') ?>"></script>

    <!-- Configuración global de DataTables en español -->
    <script>
    $.extend(true, $.fn.dataTable.defaults, {
        "language": {
            "decimal": ",",
            "thousands": ".",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "loadingRecords": "Cargando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "processing": "Procesando...",
            "search": "Buscar:",
            "searchPlaceholder": "Término de búsqueda",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla"
        }
    });
    </script>
</body>
</html>
