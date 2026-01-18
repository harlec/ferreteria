<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Login' ?> - Sistema Ferretería</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/bootstrap.min.css') ?>">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/custom.css') ?>">
    <!-- SweetAlert -->
    <link rel="stylesheet" type="text/css" href="<?= $__view->asset('css/sweetalert2.min.css') ?>">

    <style>
        body {
            background: url('<?= $__view->asset('img/fondo.jpg') ?>') no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            max-width: 200px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            background: #ff8101;
            border: none;
            color: white;
        }
        .btn-login:hover {
            background: #e67300;
            color: white;
        }
        #loader {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?= $content ?>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- jQuery Validate -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
    <!-- SweetAlert -->
    <script src="<?= $__view->asset('js/sweetalert2.all.min.js') ?>"></script>
</body>
</html>
