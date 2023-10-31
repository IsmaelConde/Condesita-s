<?php

$devolver = [];

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No estás logueado como para cerrar sesión";
    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];

if(conectarQuery("UPDATE " . $nombre_tabla_usuarios . " SET esta_activo = '0' WHERE id_usuario = '" . $id_usuario . "';", "Se ha actualizado el parametro activo del usuario")){
    if(session_destroy()){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha cerrado la sesión";
        $devolver["js"] = "recargarPagina()";   
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido cerrar sesión";
    }
}else{
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se ha podido modificar el estado en la base de datos";
}

echo json_encode($devolver);

?>