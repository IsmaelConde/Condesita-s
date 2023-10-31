<?php

include "comprobar_solicitud_backend.php";
include "../config.php";
include $root_path . "backend/funciones_bd.php";

/**
 * $datos:
 * [0] = nombre a buscar
*/

$devolver = [];

$obtener_usuarios = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_usuario LIKE '%" . $datos[0] . "%' LIMIT 20");
if($obtener_usuarios[0]){ // Hay usuarios
    $plantilla_usuarios = '
        <a href="{ruta_url}perfil/{nombre_arroba_usuario}" class="{nombre_clase}">
            <img src ="{ruta_url}images/img_foto_perfil/{nombre_img_perfil}" alt="Foto perfil">
            <div>
                <h4>{nombre_usuario}</h4>
                <p>{nombre_arroba}</p>
            </div>
        </a>
    ';
    $usuarios = "";
    while($usuario = mysqli_fetch_assoc($obtener_usuarios[1])){
        include $root_path . "callback/insertar_busqueda_usuarios.php";
    }

    $devolver["estado"] = 202;
    $devolver["contenido"] = $usuarios;
    $devolver["parametros_js"] = $usuarios;
    
}else{ // No se encuentran usuarios
    $devolver["estado"] = 202;
    $devolver["contenido"] = "No se encuentran usuarios";
    $devolver["parametros_js"] = "<a class=\"resultado\">No se encuentran usuarios<a>";
}

if(!empty($datos[1])){
    $devolver["js"] = "insertar_usuarios_pop_up";
}else{
    $devolver["js"] = 'insertar_usuarios';
}

echo json_encode($devolver);

?>