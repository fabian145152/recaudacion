<?php


## conexion a la base de datos
function conexion()
{
    $con = new mysqli("127.0.0.1", "root", "belgrado", "reca", 3306);
    if ($con->connect_errno) {
        echo "<br><br><br><br><br>";
        echo "Fallo al conectar a la DDBB: (" . $con->connect_errno . ") " . $con->connect_error;
    }
    return $con;
}

##Encabezado de la pagina



function head()
{
?>
    <link rel="icon" href="/imagenes/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/all.min.css">
    <link rel="stylesheet" href="/css/ultima.css">
    <script src="/js/jquery-3.4.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/bootbox.min.js"></script>
<?php
}