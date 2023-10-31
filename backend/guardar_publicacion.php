<?php

include "../config.php";
include "comprobar_publicacion.php";

/**
 * $datos:
 * [0] = id_publicacion
 * [1] = @usuario
*/

session_start();

$id_usuario = $_SESSION["usuario_id"];

// Comprobamos que no se haya guardado antes
$comprobar_guardado = obtenerDatos("SELECT * FROM " . $nombre_tabla_guardados . " WHERE nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $id_publicacion . "' AND id_usuario = '" . $id_usuario . "';");
if($comprobar_guardado[0]){ // Existe
    $id_guardado = mysqli_fetch_assoc($comprobar_guardado[1])["id_guardado"];
    if(conectarQuery("DELETE FROM " . $nombre_tabla_guardados . " WHERE id_guardado = '" . $id_guardado . "';", "Se ha borrado un guardado")){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha borrado el guardao";
        $devolver["js"] = "restar_guardado_publicacion()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido eliminar el guardado. Por favor intentalo más tarde.";
    }
}else{
    // En caso de que el usuario no tenga guardada la publicación
    if(insertarDatos($nombre_tabla_guardados, [$id_usuario , $nombre_tabla_publicaciones . "_" . $id_publicacion], ["id_usuario", "nombreTabla_id"])){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha guardado la publicación";
        $devolver["js"] = "sumar_guardado_publicacion()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido guardar, intentalo otra vez";
    }
}

echo json_encode($devolver);

?>