<?php

session_start(); // Iniciamos sesion

include "config.php"; // Contiene la configuración del servidor
include "primera_vez.php"; // Va a crear las tablas y bases de datos
include_once $root_path . "backend/funciones_bd.php";

$javascript = "";

// Obtenemos la cabecera
$plantilla = file_get_contents($root_path . "html/plantilla_head.htm");

// Obtenemos el body
$plantilla_body = file_get_contents($root_path . "html/plantilla_body.htm");

// Buscamos la seccion html
$pedimos_seccion = "404";
if(file_exists($root_path . "html/" . $seccion . ".htm")){
    $pedimos_seccion = $seccion;
}
$usuario;
if(!$_SESSION["usuario_id"]){
    // Si no tiene sesión
    $zonas_permitidas = [
        "crear-cuenta",
        "login",
        "olvidar-contrasena",
        "404",
        "perfil"
    ];
    $permiso_acceder = false;

    foreach($zonas_permitidas as $zona_permitida){
        if($pedimos_seccion == $zona_permitida){
            $permiso_acceder = true;
            break;
        }
    }
    
    if(!$permiso_acceder){ // Si no es una de las zonas permitidas
        $pedimos_seccion = "login";
    }
}else{
    $id_usuario = $_SESSION["usuario_id"];

    $resultado_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $id_usuario . "';");
    if($resultado_usuario[0]){
        $usuario = mysqli_fetch_assoc($resultado_usuario[1]);
    }
}

$plantilla_seccion = file_get_contents($root_path . "html/" . $pedimos_seccion . ".htm");

// Buscamos la seccion php
if(file_exists($root_path . "php/" . $pedimos_seccion . ".php")){
    include $root_path . "php/" . $pedimos_seccion . ".php";
}

// Incluimos una variable de javascript
$javascript .= "var seccion=\"" . $pedimos_seccion . "\";";

// Modificamos body
$plantilla_body = str_replace(
    [
        "{insertar_seccion}",
        "{ruta_url}",
        "{nombre_usuario_arroba}"
    ],
    [
        $plantilla_seccion,
        $ruta_url,
        urldecode($usuario["nombre_arroba_usuario"])
    ],
    $plantilla_body
);

// Modificamos la plantilla base
$ruta_css;
if(file_exists($root_path . "css/" . $pedimos_seccion . ".css")){
    $ruta_css = $ruta_url . "css/" . $pedimos_seccion . ".css";
}

$ruta_js;
if(file_exists($root_path . "js/" . $pedimos_seccion . ".js")){
    $ruta_js = $ruta_url . "js/" . $pedimos_seccion . ".js";
}

$plantilla = str_replace(
    [
        "{titulo}",
        "{url_css}",
        "{url_js}",
        "{ruta_url_css}",
        "{ruta_url_js}",
        "{insertar_body}",
        "{ruta_url}",
        "{javascript}"
    ],
    [
        $pedimos_seccion,
        $ruta_css,
        $ruta_js,
        $ruta_url . "css/",
        $ruta_url . "js/",
        $plantilla_body,
        $ruta_url,
        $javascript
    ],
    $plantilla
);

// Mostramos la plantilla base ya montada con los css, js, con el body y su sección
echo $plantilla;

?>