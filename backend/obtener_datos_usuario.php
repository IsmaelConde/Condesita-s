<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe el POST";
    die(json_encode($devolver));
}

if(!$_POST["modo"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe el modo";
    die(json_encode($devolver));
}

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No tienes la sesión iniciada";
    die(json_encode($devolver));
}

include "../config.php";
include $root_path . "backend/funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];

$resultado_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $id_usuario . "';");
if(!$resultado_usuario[0]){ // No existe el usuario con ese id
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No hay usuarios con id: \"". $id_usuario . "\".";
}else{ // Existe ese usuario
    $usuario = mysqli_fetch_assoc($resultado_usuario[1]);

    $devolver_parametros = array(
        $usuario["img_perfil"], 
        $usuario["img_portada"],
        $usuario["nombre_usuario"],
        urldecode($usuario["nombre_arroba_usuario"]),
        $usuario["descripcion_perfil"]
    );

    $devolver["estado"] = 202;
    $devolver["contenido"] = "Existe el usuario";
    $devolver["js"] = "editar_perfil";
    $devolver["parametros_js"] = $devolver_parametros;
}

echo json_encode($devolver);

?>