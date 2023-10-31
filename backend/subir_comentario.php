<?php
include "comprobar_solicitud_backend.php";
include "../config.php";
include "funciones_bd.php";

/**
 * $datos:
 * [0] = mensaje
 * [1] = [
 *      [0] = id_publicacion
 *      [1] = nombre_arroba_usuario
 * ]
*/

$devolver = [];

$nombre_arroba_usuario = $datos[1][1];
if($nombre_arroba_usuario[0] == "@"){
    $nombre_arroba_usuario = substr($nombre_arroba_usuario, 1);
}

$nombre_arroba_usuario = urlencode($nombre_arroba_usuario);

// Comprobar que el mensaje no esté vacío
if($datos[0] != ""){
    // Comprobar que el id_publicacion existe
    $comprobar_publicacion = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_publicacion ='" . $datos[1][0] . "';");
    if($comprobar_publicacion[0]){ // Existe publicación
        $publicacion = mysqli_fetch_assoc($comprobar_publicacion[1]);
        // Comprobar que el nombre arroba usuario existe
        $comprobar_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $nombre_arroba_usuario . "';");
        if($comprobar_usuario[0]){ // Existe usuario
            $usuario = mysqli_fetch_assoc($comprobar_usuario[1]);
            // Comprobar que el id_publicacion coincide con el id de su usuario
            if($publicacion["id_usuario"] == $usuario["id_usuario"]){
                // La publicación corresponde al usuario
                session_start();

                $id_usuario = $_SESSION["usuario_id"];

                if(insertarDatos($nombre_tabla_comentarios, [$datos[1][0], $id_usuario, $datos[0]], ["id_publicacion", "id_usuario", "contenido"])){
                    $devolver["estado"] = 202;
                    $devolver["contenido"] = "Se ha subido el comentario";
                    $devolver["js"] = "recargarPagina()";
                }else{
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "No se ha podido subir el comentario, intentalo de nuevo";
                }
            }else{
                $devolver["estado"] = 300;
                $devolver["contenido"] = "El usuario recibido no subió esta publicación";
            }
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "El usuario recibido no existe";
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No existe esta publicación";
    }
}else{
    $devolver["estado"] = 300;
    $devolver["contenido"] = "El mensaje no puede estar vacío";
}


echo json_encode($devolver);

?>