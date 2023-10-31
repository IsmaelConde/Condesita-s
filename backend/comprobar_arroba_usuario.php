<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se ha recibido ningún POST";
    die(json_encode($devolver));
}

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No tienes la sesión iniciada";
    die(json_encode($devolver));
}

include "../config.php";
include $root_path . "backend/funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];
$nombre_arroba = $_POST["nombre"];

// Comprobar si el nombre arroba lleva el @ delante
if($nombre_arroba[0] == "@"){
    // Tiene @
    $nombre_arroba = substr($nombre_arroba, 1);
}

$nombre_arroba = urlencode(str_replace(" ", "_", $nombre_arroba));

$resultado_usuarios = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario != '" . $id_usuario . "' AND nombre_arroba_usuario = '@" . $nombre_arroba . "';");
if($resultado_usuarios[0]){ // Existe
    $devolver["estado"] = 200;
    $devolver["contenido"] = "Este usuario ya existe";
    $devolver["parametros_js"] = "rojo";
}else{ // No existe
    $devolver["estado"] = 202;
    $devolver["contenido"] = "Este usuario todavía no existe";
    $devolver["parametros_js"] = "negro";
}

$devolver["js"] = "pintar_nombre_arroba";

echo json_encode($devolver);

?>