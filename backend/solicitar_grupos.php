<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe ningún POST";
    die(json_encode($devolver));
}

if(!$_POST["datos_publicacion"]){
    $devolver["estado"] = 300;
    $devolver["contneido"] = "No se recibe el parametro de los datos";
    die(json_encode($devolver));
}

session_start();

$datos_publicacion = json_decode($_POST["datos_publicacion"]);

$id_publicacion = $datos_publicacion[0];
$nombre_arroba_usuario_publicacion = $datos_publicacion[1];

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No tienes la sesión iniciada";
    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];

$resultado_grupos_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " WHERE id_grupo IN (SELECT id_grupo FROM " . $nombre_tabla_grupos_usuarios . " WHERE id_usuario = '" . $id_usuario . "');");
if($resultado_grupos_usuario[0]){ // Hay grupos
    $html = "";
    while($grupos_usuario = mysqli_fetch_assoc($resultado_grupos_usuario[1])){
        $html .= "
        <div id=\"" . $grupos_usuario["id_grupo"] . "\" class=\"escoger_grupo\">
            <p>" . $grupos_usuario["descripcion_grupo"] . "</p>
        </div>";
    }
    $devolver["estado"] = 202;
    $devolver["parametros_js"] = $html;
}else{ // No hay grupos
    $devolver["estado"] = 202;
    $devolver["parametros_js"] = "Todavía no estás en ningún grupo";
}

$devolver["js"] = "escoger_grupo";
$devolver["javascript"] = "datos_publicacion = \"" . $id_publicacion . "{separacion}" . urldecode($nombre_arroba_usuario_publicacion) . "\";";

echo json_encode($devolver);
?>