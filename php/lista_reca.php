<?php
include_once "../funciones/funciones.php";

$con = conexion();
$con->set_charset("utf8mb4");

$sql = "SELECT * FROM recauda ORDER BY fecha ASC"; // Ordenar por fecha
$sql_l = $con->query($sql);

if ($sql_l->num_rows > 0) {
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="../css/listado.css">
        <style>
            /* Contenedor que centra la tabla */
            .table-container {
                display: flex;
                /* Activa flexbox */
                justify-content: center;
                /* Centra horizontalmente */
                align-items: center;
                /* Centra verticalmente */
                /* height: 100vh; */
                /* Ocupa toda la altura de la pantalla */
            }

            /* Tabla */
            table {
                width: 95%;
                /* Ajusta el ancho que quieras */
                border-collapse: collapse;
                text-align: center;
                background: white;
                border: 2px solid #000;
                box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
            }
        </style>
    </head>

    <body>

        <h2>Recaudacion Taxis</h2>
        <div class="table-container">

            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Móvil</th>
                    <th>Turno</th>
                    <th>Paga</th>
                    <th>% Com.</th>
                    <th>Comisión</th>
                    <th>Voucher</th>
                    <th>MP</th>
                    <th>FT</th>
                    <th>x unidad</th>
                </tr>
                <?php


                while ($row = $sql_l->fetch_assoc()) {
                ?>
                    <tr>
                        <?php
                        $id = $row['id'];
                        $fecha = $row['fecha'];
                        $movil = $row['movil'];
                        $turno = $row['turno'];
                        $paga = $row['paga'];
                        $porcent = $row['porcent'];
                        $comision = $row['comision'];
                        $voucher = $row['voucher'];
                        $mp = $row['mp'];
                        $ft = $row['ft'];
                        ?>
                        <td><?php echo $id ?></td>
                        <td><?php echo $fecha ?></td>
                        <td><?php echo $movil ?></td>
                        <td><?php echo $turno ?></td>
                        <td><?php echo $paga ?></td>
                        <td><?php echo $porcent ?></td>
                        <td><?php echo $comision ?></td>
                        <td><?php echo $voucher ?></td>
                        <td><?php echo $mp ?></td>
                        <td><?php echo $ft ?></td>
                        <?php
                        $dia = $comision + $mp + $ft + $comision;

                        ?>
                        <td><?php echo $dia ?></td>


                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
    <?php

} else {
    echo 'No hay registros para mostrar.';
}
    ?>

    </body>

    </html>