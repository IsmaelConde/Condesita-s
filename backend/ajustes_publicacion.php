<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe el POST";
    die(json_encode($devolver));
}

if(!$_POST["json"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe el tipo";
    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No estás logueado";
    die(json_encode($devolver));
}

$id_usuario = $_SESSION["usuario_id"];
$datos = $_POST["json"];
$datos_decodificados = json_decode($datos, true);
$tipo = $datos_decodificados["tipo"];

function comprobar_publicacion($datos_publicacion){
    $id_publicacion = $datos_publicacion[0];
    $nombre_arroba_usuario_publicacion = urldecode($datos_publicacion[1]);
    
    $nombre_arroba_url = $nombre_arroba_usuario_publicacion;

    if($nombre_arroba_url[0] == "@"){
        $nombre_arroba_url = substr($nombre_arroba_usuario_publicacion, 1);
    }
    $nombre_arroba_url = urlencode($nombre_arroba_url);

    $comprobar_publicacion = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_publicaciones"] . " WHERE id_publicacion = '" . $id_publicacion . "';");
    if($comprobar_publicacion[0]){ // Existe la publicación
        $publicacion = mysqli_fetch_assoc($comprobar_publicacion[1]);
        // Comprobamos el usuario
        $comprobar_usuario = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_usuarios"] . " WHERE nombre_arroba_usuario = '@" . $nombre_arroba_url . "';");
        if($comprobar_usuario[0]){ // Existe el usuario
            $usuario_publicacion = mysqli_fetch_assoc($comprobar_usuario[1]);
            // Comprobamos que los id coincide
            if($publicacion["id_usuario"] == $usuario_publicacion["id_usuario"]){ // Los datos están bien
                $devolver["estado"] = 202;
                $devolver["publicacion"] = $publicacion;
                $devolver["usuario_publicacion"] = $usuario_publicacion;
            }else{ // Los datos recibidos no son correctos
                $devolver["estado"] = 300;
                $devolver["contenido"] = " Los datos son incorrectos";
            }
        }else{ // No existe el usuario
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No existe el usuario '" . $nombre_arroba_usuario_publicacion . "'";
            $devolver["cosas"] = $datos_publicacion[1];
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No existe tal publicación";
    }

    return $devolver;
}

if($tipo == "publicacion"){
    $datos_publicacion = $datos_decodificados["datos_publicacion"];

    $devolver = comprobar_publicacion($datos_publicacion);

    if($devolver["estado"] == 202){
        // Ahora miramos si la publicación es del usuario o otra persona
        $apartados = [];
        if($devolver["publicacion"]["id_usuario"] == $id_usuario){ // La persona actual es el creador
            $hacer_deshacer_visible = "";
            if($devolver["publicacion"]["visibilidad"] == "0"){ // No está visible la publicación
                $hacer_deshacer_visible = "Hacer visible para todos";
            }else{ // Está visible para todo el mundo
                $hacer_deshacer_visible = "Ocultar publicación";
            }

            array_push($apartados, [$hacer_deshacer_visible, "(e) => {let formData = new FormData(), dato_publicacion; if(comprobar_si_es_publicacion()){dato_publicacion = datos_publicacion.split('{separacion}');}else{dato_publicacion = obtenerDatosPadre_publicacion(e.target, '', true);} formData.append('hacer_que', JSON.stringify({'tipo':'visibilidad', 'datos_publicacion':dato_publicacion})); llamadaAjax_formData('ajustar_publicacion.php', formData);}"]);
            array_push($apartados, ["Eliminar publicación", "(e) => {let formData = new FormData(), dato_publicacion; if(comprobar_si_es_publicacion()){dato_publicacion = datos_publicacion.split('{separacion}');}else{dato_publicacion = obtenerDatosPadre_publicacion(e.target, '', true);} formData.append('hacer_que', JSON.stringify({'tipo':'eliminar', 'datos_publicacion':dato_publicacion})); llamadaAjax_formData('ajustar_publicacion.php', formData);}"]);
        }else{ // La persona actual no es el creador
            array_push($apartados, ["Informar de la publicación", "(e) => {let formData = new FormData(), dato_publicacion; if(comprobar_si_es_publicacion()){dato_publicacion = datos_publicacion.split('{separacion}');}else{dato_publicacion = obtenerDatosPadre_publicacion(e.target, '', true);} formData.append('hacer_que', JSON.stringify({'tipo':'informar', 'datos_publicacion':dato_publicacion})); llamadaAjax_formData('ajustar_publicacion.php', formData);}"]);
        }

        $devolver["estado"] = 200;
        $devolver["js"] = "insertar_ajustes";
        $devolver["parametros_js"] = $apartados;
    }    
           
}else if($tipo == "comentario"){
    $datos_publicacion = $datos_decodificados["datos_publicacion"];
    $datos_comentario = $datos_decodificados["extra"];

    $usuario_comentario = $datos_comentario["usuario"];
    $id_comentario = $datos_comentario["id_comentario"];

    $usuario_comentario_url = $usuario_comentario;
    if($usuario_comentario_url[0] == "@"){
        $usuario_comentario_url = substr($usuario_comentario, 1);
    }

    $usuario_comentario_url = urlencode($usuario_comentario_url);
    
    $devolver = comprobar_publicacion($datos_publicacion);

    if($devolver["estado"] == 202){
        // Ahora miramos si los datos de los comentarios coinciden
        $resultado_comentario = obtenerDatos("SELECT * FROM " . $nombre_tabla_comentarios . " WHERE id_comentario = '" . $id_comentario . "';");
        if($resultado_comentario[0]){ // Existe el comentario
            $comentario = mysqli_fetch_assoc($resultado_comentario[1]);

            // Ahora miramos si el usuario del comentario existe
            $resultado_usuario_comentario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $usuario_comentario_url . "';");
            if($resultado_usuario_comentario[0]){ // Se ha encontrado al usuario
                $comentario_usuario = mysqli_fetch_assoc($resultado_usuario_comentario[1]);

                // Comprobamos si coincide
                if($comentario["id_usuario"] == $comentario_usuario["id_usuario"]){ // Los datos son correctos
                    $apartados = [];
                    // Miramos si somos los creadores de la publicación
                    if(($devolver["publicacion"]["id_usuario"] == $id_usuario) || ($comentario["id_usuario"] == $id_usuario)){ // Nosotros publicamos la vaina esta
                        array_push($apartados,
                            [
                                "Eliminar comentario",
                                "(e) => {let formData = new FormData(), dato_publicacion; if(comprobar_si_es_publicacion()){dato_publicacion = datos_publicacion.split('{separacion}');}else{dato_publicacion = obtenerDatosPadre_publicacion(e.target, '', true);} formData.append('hacer_que', JSON.stringify({'tipo':'eliminar_comentario', 'datos_publicacion':dato_publicacion, 'id_comentario':" . $id_comentario . "})); llamadaAjax_formData('ajustar_publicacion.php', formData);}"
                            ]
                        );
                    }else{ // No publicamos nosotros la publicación
                        array_push($apartados,
                            [
                                "Informar comentario",
                                "(e) => {let formData = new FormData(), dato_publicacion; if(comprobar_si_es_publicacion()){dato_publicacion = datos_publicacion.split('{separacion}');}else{dato_publicacion = obtenerDatosPadre_publicacion(e.target, '', true);} formData.append('hacer_que', JSON.stringify({'tipo':'informar_comentario', 'datos_publicacion':dato_publicacion, 'id_comentario':" . $id_comentario . "})); llamadaAjax_formData('ajustar_publicacion.php', formData);}"
                            ]
                        );
                    }

                    $devolver["estado"] = 200;
                    $devolver["js"] = "insertar_ajustes";
                    $devolver["parametros_js"] = $apartados;

                }else{ // Los datos no son correctos
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "Los datos no son correctos";
                }
            }else{ // No se ha encontrado al usuario
                $devolver["estado"] = 300;
                $devolver["contenido"] = "No se ha encontrado al usuario '" . $usuario_comentario . "'.";
            }
        }else{ // No existe el comentario
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha encontrado el comentario";
        }
    }
}

echo json_encode($devolver);

?>