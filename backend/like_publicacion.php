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

// Comprobamos que este usuario no haya dado like a la misma publicación
$comprobar_like = obtenerDatos("SELECT * FROM " . $nombre_tabla_likes . " WHERE id_usuario = '" . $id_usuario . "' AND nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $id_publicacion . "';");
if($comprobar_like[0]){ // Si existe 
    $id_like = mysqli_fetch_assoc($comprobar_like[1])["id_like"];
    if(conectarQuery("DELETE FROM " . $nombre_tabla_likes . " WHERE id_like = '" . $id_like . "';", "Se ha borrado un like")){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha quitado el like de la publicación";
        $devolver["js"] = "restar_like_publicacion()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido quitar el like de la publicación";
    }
}else{
    // En caso de que el usuario no tenga replicas o que los likes que tiene no son con esta publicación, entonces
    if(insertarDatos($nombre_tabla_likes, [$id_usuario , $nombre_tabla_publicaciones . "_" . $id_publicacion], ["id_usuario", "nombreTabla_id"])){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha dado el like exitosamente";
        $devolver["js"] = "sumar_like_publicacion()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido dar el like, intentalo otra vez";
    }
}

echo json_encode($devolver);


?>