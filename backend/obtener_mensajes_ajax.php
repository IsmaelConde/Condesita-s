<?php

session_start();

include "comprobar_solicitud_backend.php";
include "../config.php";
include $root_path . "backend/funciones_bd.php";

/**
 * $datos:
 * [0] = fecha ultima llamada
 * [1] = id grupo
*/

$devolver = [];

$ultima_fecha = $datos[0];
$id_grupo = $datos[1];

$id_usuario = $_SESSION["usuario_id"];

$resultado_grupo = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " WHERE id_grupo = '" . $id_grupo . "';");
if($resultado_grupo[0]){ // Existe el grupo
    // Comprobamos si el fulano es del grupo
    $resultado_usuario_grupo = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos_usuarios . " WHERE id_grupo = '" . $id_grupo . "' AND id_usuario = '" . $id_usuario . "';");
    if($resultado_usuario_grupo[0]){ // El fulano pertenece al grupo
        $usuario_grupo = mysqli_fetch_assoc($resultado_usuario_grupo[1]);

        if($usuario_grupo["grupo_bloqueado"] == "1"){ // Tiene el grupo bloqueado
            $devolver["estado"] = 300;
            $devolver["contenido"] = "Tienes el grupo Bloqueado";
        }else{ // No tiene el grupo bloqueado
            $resultado_ultimas_llamadas = obtenerDatos("SELECT * FROM " . $nombre_tabla_mensajes . " WHERE id_grupo = '" . $id_grupo . "' AND fecha_enviado > '" . $ultima_fecha . "' AND id_usuario != '" . $id_usuario . "';");
            if($resultado_ultimas_llamadas[0]){ // Hay nuevos mensajes
                $ultima_llamada = mysqli_fetch_assoc($resultado_ultimas_llamadas[1]);

                $insertar_contenido = "";
                $es_publicacion_compartida = false;
                if($ultima_llamada["contenido"] == "{Publicacion_compartida}"){ // Es una publicación compartida
                    $es_publicacion_compartida = true;
                    $resultado_publicacion_compartida = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_publicaciones_compartidas"] . " WHERE id_mensaje = '" . $ultima_llamada["id_mensaje"] . "';");

                    include $GLOBALS["root_path"] . "callback/mostrar_publicaciones_compartidas.php";
                }else{ // No es una publicación compartida
                $insertar_contenido = "<p>" . $ultima_llamada["contenido"] . "</p>";
                }

                $devolver_contenido;
                if($es_publicacion_compartida){
                    $devolver_contenido = "<div class=\"mensaje_publicacion_compartida\">" . $insertar_contenido . "</div>";
                }else{
                    $devolver_contenido = $insertar_contenido;
                }
                
                $devolver["estado"] = 202;
                $devolver["js"] = "recibir_mensaje";
                $devolver["parametros_js"] = ["id_mensaje" => $ultima_llamada["id_mensaje"], "contenido_mensaje" => $devolver_contenido . "<p class=\"hora\">" . explode(" ", $ultima_llamada["fecha_enviado"])[1] . "</p>"];
            }else{ // No hay mensajes nuevos
                $devolver["estado"] = 100;
                $devolver["contenido"] = "No hay mensajes nuevos";
            }
        }
    }else{ // El fulano no pertenece al grupo
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No perteneces al grupo crack";
    }
}else{ // No existe
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No existe el grupo con id \"" . $id_grupo . "\"";
}

echo json_encode($devolver);

?>