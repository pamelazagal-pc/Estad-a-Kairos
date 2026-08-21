<?php
 
    $server ="localhost";
    $user ="root";
    $password ="";
    $db ="kairos_db";

//crear conexion a la base de datos

    $connection = new mysqli($server, $user, $password, $db);

    //evaluar la conexion

    if($connection -> connect_errno){
        die("conexion fallida" . $connection -> connect_errno);


    }else{
        // Conexión exitosa, no se imprime nada
    }




















