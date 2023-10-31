<?php
include "comprobar_solicitud_backend.php";
include "../config.php";
include $root_path . "backend/funciones_bd.php";

session_start();

/**
 * $datos:
 * [0] = nombre que se busca
*/

$devolver = [];

$id_usuario = $_SESSION["usuario_id"];

$plantilla_usuarios = file_get_contents($root_path . "html/plantilla_contacto_mensaje.htm");

$obtenerContactos = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE (nombre_usuario LIKE '%" . $datos[0] . "%') AND (id_usuario != '" . $id_usuario . "');");
if($obtenerContactos[0]){ // Hay contactos
    $usuarios = "";
    while($usuario = mysqli_fetch_assoc($obtenerContactos[1])){
        $resultados_grupos = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos_usuarios . " AS tabla1 JOIN " . $nombre_tabla_grupos_usuarios . " AS tabla2 ON tabla1.id_grupo = tabla2.id_grupo WHERE tabla1.id_usuario = '" . $usuario["id_usuario"] . "' AND tabla2.id_usuario = '" . $id_usuario . "';");
        $grupo_id = "";
        if($resultados_grupos[0]){
            $grupo_id = mysqli_fetch_assoc($resultados_grupos[1])["id_grupo"];
        }else{
            $grupo_id = urldecode($usuario["nombre_arroba_usuario"]);
        }

        include $root_path . "callback/insertar_busqueda_usuarios.php";
    }

    $devolver["estado"] = 202;
    $devolver["contenido"] = "Hay usuarios";
    $devolver["parametros_js"] = $usuarios;
}else{ // No hay contactos
    $devolver["estado"] = 202;
    $devolver["contenido"] = "No hay usuarios que contengan \"" . $datos[0] . "\".";
    $devolver["parametros_js"] = '
        <div>
            <p>No se encuentran resultados</p>
        </div>
    ';
}

$devolver["js"] = "insertarContactos";

echo json_encode($devolver);

?>