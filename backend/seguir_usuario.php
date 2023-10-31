<?php

include "comprobar_solicitud_backend.php";
include "funciones_bd.php";
include "../config.php";

session_start();

$usuario_a_seguir = explode("/", $_SERVER["HTTP_REFERER"])[4];

// Miramos si el $usuario_a_seguir tiene el @ delante
if(!preg_match("/^@/", $usuario_a_seguir)){
    $usuario_a_seguir = "@" . $usuario_a_seguir;
}

$id_usuario_seguidor = $_SESSION["usuario_id"];

$devolver = [];

// Comprobar que el usuario que quiere seguir, no lo sigue ya
$comprobar_seguidor = obtenerDatos("SELECT * FROM " . $nombre_tabla_seguidores . " WHERE id_usuario = (SELECT id_usuario FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '" . $usuario_a_seguir . "') AND id_seguidor = '" . $id_usuario_seguidor . "';");
if($comprobar_seguidor[0]){
    // Ya lo sigue, entonces quiere dejar de seguir
    if(conectarQuery("DELETE FROM " . $nombre_tabla_seguidores . " WHERE id_usuario = (SELECT id_usuario FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '" . $usuario_a_seguir . "') AND id_seguidor = '" . $id_usuario_seguidor . "';", "Se ha borrado un seguidor del usuario " . $usuario_a_seguir)){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha dejado de seguir a " . $usuario_a_seguir . ".";
        $devolver["js"] = "dejar_de_seguir()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha popdido dejar de seguir al usuario " . $usuario_a_seguir. ". Intentelo de nuevo.";
    }
}else{
    // No lo sigue
    $obtener_usuario_a_seguir = obtenerDatos("SELECT id_usuario FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '" . $usuario_a_seguir . "';");
    if(insertarDatos($nombre_tabla_seguidores, [mysqli_fetch_assoc($obtener_usuario_a_seguir[1])["id_usuario"], $id_usuario_seguidor], ["id_usuario", "id_seguidor"])){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Ahora sigues al usuario " . $usuario_a_seguir . ".";
        $devolver["js"] = "seguir_usuario()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido seguir al usuario " . $usuario_a_seguir . ".";
    }
}

echo json_encode($devolver);

?>