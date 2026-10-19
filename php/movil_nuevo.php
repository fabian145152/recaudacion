<?php
include_once "../funciones/funciones.php";
$con = conexion();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUEVA UNIDAD - TAXI</title>
    <?php head() ?>
    <link rel="stylesheet" href="../css/main.css">
</head>

<body>
    <div class="container">
        <h1 class="text-center mb-4">Nueva Unidad de Taxi</h1>
        <p class="text-center text-muted">Se crearán automáticamente los turnos DÍA y NOCHE para este móvil.</p>

        <form action="guardar_unidad.php" method="POST" onsubmit="return validarFormulario()">

            <fieldset>
                <legend>Datos del vehículo</legend>

                <!-- Tipo siempre es TAXI -->
                <input type="hidden" name="tipo" value="TAXI">

                <div class="input-row">
                    <label for="movil">Móvil (Nº < 3000)</label>
                            <input type="number" id="movil" name="movil" min="1" max="2999" required>
                </div>

                <div class="input-row">
                    <label for="marca">Marca</label>
                    <input type="text" id="marca" name="marca" required>
                </div>

                <div class="input-row">
                    <label for="modelo">Modelo</label>
                    <input type="text" id="modelo" name="modelo" required>
                </div>

                <div class="input-row">
                    <label for="dominio">Dominio</label>
                    <input type="text" id="dominio" name="dominio" required>
                </div>

                <div class="input-row">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" required>
                </div>
            </fieldset>

            <div class="acciones">
                <a href="taxis.php" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-success">Guardar unidad</button>
            </div>
        </form>
    </div>

    <script>
        function validarFormulario() {
            const movil = parseInt(document.getElementById('movil').value);
            if (isNaN(movil) || movil < 1 || movil > 2999) {
                alert("El número de móvil debe ser un número entre 1 y 2999.");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>