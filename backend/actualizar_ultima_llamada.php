<?php

include "comprobar_solicitud_backend.php"; // Comprobamos que recibimos el post de datos
include "../config.php";
include $root_path . "backend/funciones_bd.php"; // Obtenemos las funciones de Base de Datos

$obtenerFecha_actual = obtenerDatos("SELECT CURRENT_TIMESTAMP");

$devolver = [];

$devolver["estado"] = 202;
$devolver["js"] =  "actualizar_ultima_llamada";
$devolver["parametros_js"] = mysqli_fetch_assoc($obtenerFecha_actual[1])["CURRENT_TIMESTAMP"];

echo json_encode($devolver);
?>