<?php

session_start();

include "comprobar_solicitud_backend.php";
include "../config.php";
include $root_path . "backend/funciones_bd.php";

/**
 * $datos:
 * [0] = id del grupo
 * [1] = mensaje a subir
*/

$devolver = [];

$id_grupo = $datos[0];
$mensaje = $datos[1];

$id_usuario = $_SESSION["usuario_id"];

//Vamos a comprobar que el mensaje no esté vaCÍO
if($mensaje != ""){ // No está vacío
    //  Vamos a comprobar quue el grupo_existe
    $comprobar_grupos = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " WHERE id_grupo = '" . $id_grupo . "';");
    if($comprobar_grupos[0]){ // Existe el grupo
        $grupo = mysqli_fetch_assoc($comprobar_grupos[1]);
        if(insertarDatos($nombre_tabla_mensajes, [$id_grupo, $id_usuario, $mensaje], ["id_grupo", "id_usuario", "contenido"])){
            $devolver["estado"] = 202;
            $devolver["contenido"] = "Se ha subido el mensaje";
            $devolver["js"] = "subir_mensaje";

            $obtener_ultimo_mensaje = obtenerDatos("SELECT * FROM " . $nombre_tabla_mensajes . " WHERE id_usuario = '" . $id_usuario . "' ORDER BY fecha_enviado DESC LIMIT 1;");
            $ultimo_mensaje = mysqli_fetch_assoc($obtener_ultimo_mensaje[1]);

            $devolver["parametros_js"] = ["id_mensaje" => $ultimo_mensaje["id_mensaje"], "contenido" => "<p>" . $mensaje . "</p><p class=\"hora\">" . explode(" ", $ultimo_mensaje["fecha_enviado"])[1] . "</p>"];
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha podido subir el mensaje. Por favor vuelve a intentarlo";
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No existe el grupo con id: " . $id_grupo . ".";
    }
}else{
    $devolver["estado"] = 300;
    $devolver["contenido"] = "El mensaje no puede quedar vacío";
}


echo json_encode($devolver);

?>