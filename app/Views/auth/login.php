<div class="login-container">
    <div class="login-logo">
        <img src="<?= $__view->asset('img/harlec-sistema.png') ?>" alt="Logo">
    </div>

    <form id="login" method="post">
        <div class="form-group">
            <label for="usuario">Usuario</label>
            <input type="text" class="form-control" id="usuario" name="usuario"
                   placeholder="Ingrese su usuario" required>
        </div>

        <div class="form-group">
            <label for="pass">Contraseña</label>
            <input type="password" class="form-control" id="pass" name="pass"
                   placeholder="Ingrese su contraseña" required>
        </div>

        <button type="submit" class="btn btn-login" id="btn-login">
            Ingresar
        </button>

        <div id="loader">
            <span class="glyphicon glyphicon-refresh glyphicon-spin"></span>
            Verificando...
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $("#login").validate({
        submitHandler: function() {
            var str = $('#login').serialize();

            $.ajax({
                beforeSend: function() {
                    $('#loader').show();
                    $('#btn-login').prop('disabled', true);
                },
                cache: false,
                type: 'POST',
                dataType: 'json',
                url: '/login',
                data: str,
                success: function(response) {
                    if (response.respuesta === false) {
                        $('#btn-login').prop('disabled', false);
                        $('#loader').hide();
                        Swal.fire('Advertencia', response.mensaje, 'warning');
                    } else {
                        Swal.fire({
                            title: 'Bienvenido',
                            text: response.mensaje,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            document.location.href = '/dashboard';
                        });
                    }
                },
                error: function() {
                    $('#btn-login').prop('disabled', false);
                    $('#loader').hide();
                    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                }
            });

            return false;
        },
        errorPlacement: function() {
            // No mostrar mensajes de error inline
        }
    });
});
</script>
