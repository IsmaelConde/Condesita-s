<?php

include "../config.php";
include "comprobar_publicacion.php";

/**
 * $datos:
 * [0] = id_publicacion
 * [1] = @usuario_publicacion
 * [2] = id_grupo
*/

session_start();

$id_usuario = $_SESSION["usuario_id"];

$id_grupo = $datos[2];

function enviarPublicacion($id_grupo){
    // Creamos un mensaje nuevo
    if(insertarDatos($GLOBALS["nombre_tabla_mensajes"], [$id_grupo, $GLOBALS["id_usuario"], "{Publicacion_compartida}"], ["id_grupo", "id_usuario", "contenido"])){ // Se ha podido crear un nuevo mensaje
        // Vamos a obtener el id de este mensaje
        $resultado_mensaje = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_mensajes"] . " WHERE id_grupo = '" . $id_grupo . "' AND id_usuario = '" . $GLOBALS["id_usuario"] . "' ORDER BY id_mensaje DESC LIMIT 1;");
        $mensaje = mysqli_fetch_assoc($resultado_mensaje[1]);

        // Vamos a crear una publicacion_compartida
        if(insertarDatos($GLOBALS["nombre_tabla_publicaciones_compartidas"], [$GLOBALS["id_publicacion"], $mensaje["id_mensaje"]], ["id_publicacion", "id_mensaje"])){ // Se ha creado una publicación compartida
            $devolver["estado"] = 202;
            $devolver["contenido"] = "Se ha podido compartir la publicación";
            $devolver["js"] = "sumar_publicacion_compartida()";
        }else{ // No se ha podido crear una publicación compartida
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha podido crear una publicación compartida";
        }
    }else{ // No se ha podido crear un nuevo mensaje
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido crear un nuevo mensaje";
    }

    return $devolver;
}

// Miramos si el id de este grupo existe
$resultado_grupo = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " WHERE id_grupo = '" . $id_grupo . "';");
if($resultado_grupo[0]){ // Existe el grupo
    // Comprobamos que el usaurio está en ese grupo
    $resultado_usuario_grupo = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos_usuarios . " WHERE id_grupo = '" . $id_grupo . "' AND id_usuario = '" . $id_usuario . "';");
    if($resultado_usuario_grupo[0]){ // Está en el grupo
        $devolver = enviarPublicacion($id_grupo);
    }else{ // No está en el grupo
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No perteneces al grupo";
    }
}else{ // No existe el grupo
    // Puede ser o porque recibe el nombre arroba en vez del id del grupo o porque ese id no existe
    if($id_grupo[0] == "@"){
        $id_grupo = substr($id_grupo, 1);
    }

    $nombre_arroba_usuario_envio = urlencode($id_grupo);

    // Si recibe el @ es porque no hay grupo
    $resultado_usuario_actual = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $id_usuario . "';");
    if($resultado_usuario_actual[0]){ // Existe el usuario
        $usuario_actual = mysqli_fetch_assoc($resultado_usuario_actual[1]);

        // Ahora buscamos al otro usuario
        $resultado_usuario_buscado = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $nombre_arroba_usuario_envio . "';");
        if($resultado_usuario_buscado[0]){ // El usuario buscado existe
            $usuario_buscado = mysqli_fetch_assoc($resultado_usuario_buscado[1]);

            // Comprobamos si tienen un grupo en común
            $resultado_buscar_grupo = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " WHERE id_grupo IN (SELECT tabla1.id_grupo FROM " . $nombre_tabla_grupos_usuarios . " AS tabla1 JOIN " . $nombre_tabla_grupos_usuarios . " AS tabla2 ON tabla1.id_grupo = tabla2.id_grupo WHERE tabla1.id_usuario = '" . $id_usuario . "' AND tabla2.id_usuario = '" . $usuario_buscado["id_usuario"] . "');");

            $grupo_creado = false;
            if($resultado_buscar_grupo[0]){ // Tienen al menos un grupo en común
                // Pero ahora hay que mirar si es un grupo con más gente o solo ellos 2
                while($buscar_grupo = mysqli_fetch_assoc($resultado_buscar_grupo[1])){
                    $resultado_usuarios_grupo = obtenerDatos("SELECT count(id_usuario) as suma FROM " . $nombre_tabla_grupos_usuarios . " WHERE id_grupo = '" . $buscar_grupo["id_grupo"] . "';");
                    $suma_usuarios = establecer_numeros_datos($resultado_usuarios_grupo);

                    if($suma_usuarios == 2){ // Entonces son ellos 2 y ya tienen un grupo en común
                        enviarPublicacion($buscar_grupo["id_grupo"]);
                        $grupo_creado = true;
                        break;
                    }
                }
            }

            if(!$grupo_creado){ // No tiene grupo en común
                if(insertarDatos($nombre_tabla_grupos, [urldecode($usuario_actual["nombre_arroba_usuario"]) . " - " . urldecode($usuario_buscado["nombre_arroba_usuario"])],["descripcion_grupo"])){
                    $resultado_grupos = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " ORDER BY id_grupo DESC LIMIT 1;");
                    $ultimo_grupo = mysqli_fetch_assoc($resultado_grupos[1]);
                
                    if(insertarDatos($nombre_tabla_grupos_usuarios, [$id_usuario, $ultimo_grupo["id_grupo"]], ["id_usuario", "id_grupo"])){
                        if(insertarDatos($nombre_tabla_grupos_usuarios, [$usuario_buscado["id_usuario"], $ultimo_grupo["id_grupo"]], ["id_usuario", "id_grupo"])){
                            $devolver = enviarPublicacion($ultimo_grupo["id_grupo"]);
                        }else{
                            $devolver["estado"] = 300;
                            $devolver["contenido"] = "No se pudo insertar al otro usuario en el grupo";
                        }
                    }else{
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se te pudo insertar en el grupo";
                    }
                }else{
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "No se ha podido crear el grupo";
                }
                
            }

        }else{ // El usuario buscado no existe (Posible cunado el usuario está modificando su @)
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha encotrado al otro usuario. Por favor vuelve a intentarlo más tarde.";
        }
    }else{ // No existe el usuario
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No existes";
    }
}


echo json_encode($devolver);
?>