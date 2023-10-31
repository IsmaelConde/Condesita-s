<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe el POST";
    die(json_encode($devolver));
}

if(!$_POST["img64"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe el método";
    die(json_encode($devolver));
}

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No has iniciado sesión";
    die(json_encode($devolver));
}

$id_usuario = $_SESSION["usuario_id"];
$img64 = $_POST["img64"];

include "../config.php";
include $root_path . "backend/funciones_bd.php";

if(insertarDatos($nombre_tabla_historias, [$id_usuario, $img64], ["id_usuario", "contenido"])){
    $devolver["estado"] = 202;
    $devolver["contenido"] = "Se ha subido exitosamente la Historia";
    $devolver["js"] = "recargarPagina()";
}else{
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se ha podido subir la historia";
}

echo json_encode($devolver);

?>