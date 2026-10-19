<?php
function openCon($config_ini)
{
    $config = parse_ini_file($config_ini);
    $con = new mysqli($config['SERVERNAME'], $config['USERNAME'], $config['PASSWORD'], $config['NAMEDB']);
    if ($con == false)
        die("No me pude conectar...");
    return $con;
}
function closeCon($conn)
{
    $conn->close();
}
