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

// Comprobamos que este usuario no ha ya una replica en la misma publicación
$comprobar_replica = obtenerDatos("SELECT * FROM " . $nombre_tabla_replicas . " WHERE id_usuario = '" . $id_usuario . "' AND id_publicacion = '" . $id_publicacion . "';");
if($comprobar_replica[0]){ // Si existe, vamos a comprobar que no sea a la misma publicación
    $id_replica = mysqli_fetch_assoc($comprobar_replica[1])["id_replica"];
    if(conectarQuery("DELETE FROM " . $nombre_tabla_replicas . " WHERE id_replica = '" . $id_replica . "';", "Se ha borrado una replica")){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha borrado la replica";
        $devolver["js"] = "restar_replica_publicacion()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido eliminar la replica. Por favor intentalo más tarde.";
    }
        
}else{
    // En caso de que el usuario no tenga replicas o que las replicas que tiene no son con esta publicación, entonces
    if(insertarDatos($nombre_tabla_replicas, [$id_publicacion ,$id_usuario], ["id_publicacion", "id_usuario"])){
        $devolver["estado"] = 202;
        $devolver["contenido"] = "Se ha hecho la replica exitosamente";
        $devolver["js"] = "sumar_replica_publicacion()";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido hacer la replica, intentalo otra vez";
    }
}

echo json_encode($devolver);

?>