<?php

include "comprobar_solicitud_backend.php";
include "funciones_bd.php";
include "../config.php";

session_start();

$usuario_a_ver = urldecode(explode("/", $_SERVER["HTTP_REFERER"])[4]);

// Miramos si el $usuario_a_ver tiene el @ delante
if($usuario_a_ver[0] == "@"){
    $usuario_a_ver = substr($usuario_a_ver, 1);
}

$usuario_a_ver = urlencode($usuario_a_ver);

/**
 * $datos:
 * [0] = seccion
*/

$devolver = [];

$devolver["usuario"] = $usuario_a_ver;

$seccion = $datos[0];

// Comprobamos si somos nosotros
$resultado_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $usuario_a_ver . "';");
if($resultado_usuario[0]){ // Existe
    $usuario = mysqli_fetch_assoc($resultado_usuario[1]);
    if($usuario["id_usuario"] == $_SESSION["usuario_id"]){ // Somos nosotros
        $sql = "SELECT * FROM " . $seccion . " WHERE id_usuario = (SELECT id_usuario FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $usuario_a_ver . "');";
    }else{
        $sql_publicaciones;
        if($seccion == "Likes" || $seccion == "Guardados"){
            $sql_publicaciones = "nombreTabla_id NOT IN (SELECT CONCAT('" . $nombre_tabla_publicaciones . "_', id_publicacion) FROM " . $nombre_tabla_publicaciones . " WHERE visibilidad = '0')";
        }else{
            $sql_publicaciones = "id_publicacion NOT IN (SELECT id_publicacion FROM " . $nombre_tabla_publicaciones . " WHERE visibilidad = '0')";
        }
        $sql = "SELECT * FROM " . $seccion . " WHERE (id_usuario = (SELECT id_usuario FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $usuario_a_ver . "')) AND (" . $sql_publicaciones . ");";
    }

    $resultado_seccion = obtenerDatos($sql);
    if($resultado_seccion[0]){ // Hay contenido en esa sección
        $plantilla_publicacion = file_get_contents($root_path . "html/plantilla_publicacion.htm");
        $insertar_publicaciones = "";
        while($publicacion = mysqli_fetch_assoc($resultado_seccion[1])){
            $id_publicacion = $publicacion["id_publicacion"];

            if($seccion == "Likes" || $seccion == "Guardados"){
                $id_publicacion = explode("_", $publicacion["nombreTabla_id"])[1];
            }

            // Vamos a comprobar que la publicación existe
            $resultado_publicacion = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_publicacion = '" . $id_publicacion . "';");
            if($resultado_publicacion[0]){ // Existe la publicación
                include $root_path . "callback/rellenar_contenidos.php";

                $resultado_comentarios = obtenerDatos("SELECT count(id_comentario) as suma FROM " . $nombre_tabla_comentarios . " WHERE id_publicacion = '" . $id_publicacion . "';");
                $num_comentarios = establecer_numeros_datos($resultado_comentarios);

                $resultado_likes = obtenerDatos("SELECT count(id_like) as suma FROM " . $nombre_tabla_likes . " WHERE nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $id_publicacion . "';" );
                $num_likes = establecer_numeros_datos($resultado_likes);

                $insertar_publicaciones .= str_replace(
                    [
                        "{ruta_url}",
                        "{id_publicacion}",
                        "{insertar_contenidos}",
                        "{numero_comentarios}",
                        "{cantidad_likes}"
                    ],
                    [
                        $ruta_url,
                        $id_publicacion,
                        $insertar_contenidos,
                        $num_comentarios,
                        $num_likes
                    ],
                    $plantilla_publicacion
                );      
            }

                  
        }

        $devolver["estado"] = 202;
        //$devolver["contenido"] = $insertar_publicaciones;
        $devolver["parametros_js"] = $insertar_publicaciones;
    }else{ // No hay contenido en esa sección
        $devolver["estado"] = 202;
        $devolver["contenido"] = "No se ha encontrado datos";
        $devolver["parametros_js"] = "No se ha encontrado datos";
    }
}else{
    $devolver["estado"] = 202;
    $devolver["contenido"] = "No se ha encontrado el usuario";
    $devolver["parametros_js"] = "No se ha encontrado el usuario";
}
$devolver["js"] = "cambiar_seccion";

echo json_encode($devolver);

?>