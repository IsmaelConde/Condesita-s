<?php
include "comprobar_solicitud_backend.php";
include "funciones_bd.php";

/**
 * $datos:
 * [0] = id_publicacion
 * [1] = @usuario
*/

$id_publicacion = $datos[0];
$nombre_arroba_usuario = urldecode($datos[1]);

if($nombre_arroba_usuario[0] == "@"){
    $nombre_arroba_usuario = substr($nombre_arroba_usuario, 1);
}

$nombre_arroba_usuario = urlencode($nombre_arroba_usuario);

$devolver = [];

// Comprobar que la publicación existe
$comprobar_publicacion = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_publicacion ='" . $id_publicacion . "';");
if($comprobar_publicacion[0]){ // Existe publicación
    $publicacion = mysqli_fetch_assoc($comprobar_publicacion[1]);
    // Comprobar que el usuario existe
    $comprobar_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $nombre_arroba_usuario . "';");
    if($comprobar_usuario[0]){ // Existe usuario
        $usuario = mysqli_fetch_assoc($comprobar_usuario[1]);
        // Comprobar que el id_publicacion coincide con el id de su usuario
        if($publicacion["id_usuario"] == $usuario["id_usuario"]){
            // La publicación corresponde al usuario
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "El usuario recibido no subió esta publicación";

            echo json_encode($devolver);
            die();
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "El usuario recibido no existe";
        echo json_encode($devolver);
        die();
    }
}else{
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No existe esta publicación";

    echo json_encode($devolver);
    die();
}

?>