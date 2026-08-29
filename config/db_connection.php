<?php

date_default_timezone_set('America/Mexico_City');

    $server ="localhost";
    $user ="root";
    $password ="";
    $db ="kairos_db";

//crear conexion a la base de datos

    $connection = new mysqli($server, $user, $password, $db);
    if (!$connection->connect_errno) {
        $connection->set_charset('utf8mb4');
        $connection->query("SET time_zone = '-06:00'");
    }

    //evaluar la conexion

    if($connection -> connect_errno){
        die("conexion fallida" . $connection -> connect_errno);


    }else{
        // Conexión exitosa, no se imprime nada
    }




















