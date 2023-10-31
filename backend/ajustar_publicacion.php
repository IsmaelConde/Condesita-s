<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe el POST";
    die(json_encode($devolver));
}

if(!$_POST["hacer_que"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe que ajuste quieres";
    die(json_encode($devolver));
}

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No estás logueado";
    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];
$hacer_que = json_decode($_POST["hacer_que"], true);
$datos_publicacion = $hacer_que["datos_publicacion"];

$datos_publicacion_buscados = comprobarDatos_publicacion($datos_publicacion);

switch($hacer_que["tipo"]){
    case "visibilidad":
        $devolver = visibilidad($id_usuario, $datos_publicacion_buscados);
        break;
    case "eliminar":
        $devolver = eliminar($id_usuario, $datos_publicacion_buscados);
        break;
    case "informar":
        $devolver = informar($id_usuario, $datos_publicacion_buscados);
        break;
    case "informar_comentario":
        $id_comentario = $hacer_que["id_comentario"];
        $comentario = obtenerComentario($id_comentario);
        $devolver = informar_comentario($id_usuario, $datos_publicacion_buscados, $comentario);
        break;
    case "eliminar_comentario":
        $id_comentario = $hacer_que["id_comentario"];
        $comentario = obtenerComentario($id_comentario);
        $devolver = eliminar_comentario($id_usuario, $datos_publicacion_buscados, $comentario);
        break;
    default:
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se sabe que quieres hacer. Por favor vuelve a intentarlo más tarde";
}

function informar_comentario($id_usuario, $datos_publicacion, $comentario){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "Se informará";

    return $devolver;
}

function eliminar_comentario($id_usuario, $datos_publicacion, $comentario){
    $publicacion = $datos_publicacion["publicacion"];
    $usuario_publicacion = $datos_publicacion["usuario_publicacion"];

    $devolver = ser_creador_publicacion($id_usuario, $datos_publicacion);
    if(($devolver["estado"] == 202) || ($comentario["id_usuario"] == $id_usuario)){ // En caso de ser el creador de la publicación tiene permiso
        $devolver["estado"] = 202;
        $devolver["js"] = "let confirmar_borrado = confirm('Estás seguro de que quieres borrar este comentario: \"" . $comentario["contenido"] . "\"?.'), formData = []; formData[0] = '" . $GLOBALS["datos_publicacion"][0] . "'; formData[1] = '" . $GLOBALS["datos_publicacion"][1] . "'; formData[2] = '" . $comentario["id_comentario"] . "'; formData[3] = '" . $comentario["id_usuario"] . "'; if(confirmar_borrado){llamadaAjax('borrar_comentario.php', JSON.stringify(formData))}";
    }else{ // En caso de no ser ni el creador de la publicación ni del comentario
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No puedes eliminar un comentario que no sea tuyo";
    }

    return $devolver;
}

function obtenerComentario($id_comentario){
    $resultado_comentario = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_comentarios"] . " WHERE id_comentario = '" . $id_comentario . "';");
    if($resultado_comentario[0]){ // Existe el comentario
        $comentario = mysqli_fetch_assoc($resultado_comentario[1]);

        return $comentario;
    }else{ // No se ha encontrado la publicacion
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha encontrado el comentario";

        die(json_encode($devolver));
    }
}

function obtenerPublicacion($id_publicacion){
    $resultado_publicacion = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_publicaciones"] . " WHERE id_publicacion = '" . $id_publicacion . "';");

    if($resultado_publicacion[0]){ // Se ha encontrado publicación
        $publicacion = mysqli_fetch_assoc($resultado_publicacion[1]);

        return $publicacion;
    }else{ // No se ha encontrado publicación
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha encontrado publicación";

        die(json_encode($devolver));
    }
}

function obtenerUsuario_publicacion($nombre_arroba_usuario){
    // Buscamos a su usuario
    $resultado_usuario_publicacion = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_usuarios"] . " WHERE nombre_arroba_usuario = '@" . $nombre_arroba_usuario . "';");
    if($resultado_usuario_publicacion[0]){ // Se ha encontrado al usuario
        $usuario_publicacion = mysqli_fetch_assoc($resultado_usuario_publicacion[1]);

        return $usuario_publicacion;
    }else{ // No se ha encontrado al usuario
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha encontrado al usuario";

        die(json_encode($devolver));
    }
}

function comprobarDatos_publicacion($datos_publicacion){
    $id_publicacion = $datos_publicacion[0];
    $nombre_arroba_usuario = urldecode($datos_publicacion[1]);

    if($nombre_arroba_usuario[0] == "@"){
        $nombre_arroba_usuario = substr($nombre_arroba_usuario, 1);
    }

    $nombre_arroba_usuario = urlencode($nombre_arroba_usuario);

    $devolver = [];

    $publicacion = obtenerPublicacion($id_publicacion);
    $usuario_publicacion = obtenerUsuario_publicacion($nombre_arroba_usuario);

    // Comprobamos que el id del usuario coincide con la publicacion
    if($publicacion["id_usuario"] == $usuario_publicacion["id_usuario"]){ // Los datos son correctos
        $devolver["publicacion"] = $publicacion;
        $devolver["usuario_publicacion"] = $usuario_publicacion;
    }else{ // Los datos no son correctos
        $devolver["estado"] = 300;
        $devolver["contenido"] = "Los datos de la publicación no son correctos";

        die(json_encode($devolver));
    }

    return $devolver;

}

function ser_creador_publicacion($id_usuario, $datos_publicacion){
    $publicacion = $datos_publicacion["publicacion"];
    $usuario_publicacion = $datos_publicacion["usuario_publicacion"];

    $devolver["estado"] = 202;
    if($usuario_publicacion["id_usuario"] != $id_usuario){ // Es el usuario
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No eres el creador de esta publicación como para hacer esto";
    }

    return $devolver;
}

function visibilidad($id_usuario, $datos_publicacion){
    $publicacion = $datos_publicacion["publicacion"];
    $usuario_publicacion = $datos_publicacion["usuario_publicacion"];

    $devolver = [];

    $devolver = ser_creador_publicacion($id_usuario, $datos_publicacion);

    if($devolver["estado"] == 202){
        // En caso de ser el creador

        // Miramos si la publicación está visible o no
        $sql = "UPDATE " . $GLOBALS["nombre_tabla_publicaciones"] . " SET visibilidad = '";
        if($publicacion["visibilidad"] == false){
            $sql .= true;
        }else{
            $sql .= false;
        }

        $sql .= "' WHERE id_publicacion = '" . $publicacion["id_publicacion"] . "';";
        if(conectarQuery($sql, "Actualizado la visibilidad de la publicación '" . $publicacion["id_publicacion"] . "'")){
            $devolver["estado"] = 202;
            $devolver["contenido"] = "Se ha actualizado la visibilidad de la publicación";
            $devolver["js"] = "recargarPagina()";
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha podido cambiar la visibilidad de la publicación. Por favor vuelve a intentarlo más tarde.";
        }
    }

    return $devolver;
}

function eliminar($id_usuario, $dato_publicacion){
    $publicacion = $dato_publicacion["publicacion"];
    $usuario_publicacion = $dato_publicacion["usuario_publicacion"];

    $devolver = [];

    $devolver = ser_creador_publicacion($id_usuario, $dato_publicacion);
    if($devolver["estado"] == 202){
         // En caso de ser el creador
        $devolver["estado"] = 202;
        $devolver["js"] = "let confirmar_borrado = confirm('Estás seguro de que quieres borrar esta publicación? Puede ocultarla si lo desea.'), formData = []; formData[0] = '" . $GLOBALS["datos_publicacion"][0] . "'; formData[1] = '" . $GLOBALS["datos_publicacion"][1] . "'; if(confirmar_borrado){llamadaAjax('borrar_publicacion.php', JSON.stringify(formData))}";
    }

    return $devolver;
}

function informar($id_usuario, $datos_publicacion){
    $publicacion = $datos_publicacion["publicacion"];
    $usuario_publicacion = $datos_publicacion["usuario_publicacion"];

    $devolver["estado"] = 300;
    $devolver["contenido"] = "Se informará";

    return $devolver;
}

echo json_encode($devolver);
?>