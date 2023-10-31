<?php

include "../config.php";
include "funciones_bd.php";

/**
 * $datos:
 * [0] = id_publicacion
 * [1] = @usuario
 * [2] = id_comentario
 * [3] = id_usuario_comentario
*/

session_start();

$id_usuario = $_SESSION["usuario_id"];

$datos = json_decode($_POST["datos"]);

$id_publicacion = $datos[0];
$nombre_arroba_usuario = $datos[1];
$id_comentario = $datos[2];
$id_usuario_comentario = $datos[3];

if($datos[1][0] == "@"){
    $nombre_arroba_usuario = substr($datos[1], 1);
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
        
        // Comprobamos el comentario
        $resultado_comentario = obtenerDatos("SELECT * FROM " . $nombre_tabla_comentarios . " WHERE id_comentario = '" . $id_comentario . "';");
        if($resultado_comentario[0]){ // Existe el comentario
            $comentario = mysqli_fetch_assoc($resultado_comentario[1]);

            // Comprobamos el usuario de comentario
            $resultado_usuario_comentario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $id_usuario_comentario . "';");
            if($resultado_usuario_comentario[0]){ // Existe el usuario
                $usuario_comentario = mysqli_fetch_assoc($resultado_usuario_comentario[1]);

                // Comprobamos si el el comentario fue publicado por el usuario
                if($comentario["id_usuario"] == $usuario_comentario["id_usuario"]){ // Los datos coincide
                    // Comprobamos que somos los creadores de la publicacion o que somos los creadores del comentario
                    if(($publicacion["id_usuario"] == $id_usuario) || ($comentario["id_usuario"] == $id_usuario)){ // El usuario es el creador
                        $devolver = borrar_comentario($comentario);
                    }else{ // El usuario no es el creador ni de la publicación ni del comentario
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No eres el creador del comentario como para querer borrarlo";
                    }
                }else{ // Los datos no coincide
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "El usuario recibido no ha subido este comentario";
                }
            }else{ // No existe el usuario
                $devolver["estado"] = 300;
                $devolver["contenido"] = "No existe el usuario del comentario";
            }
        }else{ // No existe el comentario
            $devolver["estado"] = 300;
            $devolver["contenido"] = "El comentario no existe";
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "El usuario recibido no existe";
    }
}else{
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No existe esta publicación";
}


function borrar_comentario($comentario){
    $sql = "DELETE FROM " . $GLOBALS["nombre_tabla_comentarios"] . " WHERE id_comentario = '" . $comentario["id_comentario"] . "';";
    if(conectarQuery($sql, "Se ha borrado el Comentario '" . $comentario["id_comentario"] . "'.")){
        $devolver["estado"] = 202;
        
        $resultado_numero_comentarios_publicacion = obtenerDatos("SELECT count(id_comentario) as suma FROM " . $GLOBALS["nombre_tabla_comentarios"] . " WHERE id_publicacion = '" . $comentario["id_publicacion"] . "';");
        $numero_comentarios_publicacion = establecer_numeros_datos($resultado_numero_comentarios_publicacion);

        if($numero_comentarios_publicacion == 0){
            $devolver["js"] = "ultimo_click_ajustes_articulos.parentElement.innerHTML = \"Sé el primero en comentar\"";
        }else{
            $devolver["js"] = "ultimo_click_ajustes_articulos.parentElement.remove()";
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido borrar el comentario";
    }

    return $devolver;
}

echo json_encode($devolver);


?>