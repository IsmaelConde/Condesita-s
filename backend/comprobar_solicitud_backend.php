<?php

$devolver = [];

if(!$_POST){ // Si no recibe POST
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe el método correcto";
    die(json_encode($devolver));
}

if(!$_POST["datos"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe los parametros necesarios";
    die(json_encode($devolver));
}

$datos = json_decode($_POST["datos"], true);

?>