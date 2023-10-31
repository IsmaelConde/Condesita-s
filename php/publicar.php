<?php

include_once $root_path . "backend/funciones_bd.php";

$nombre_img_perfil = $usuario["img_perfil"];
$nombre_usuario = $usuario["nombre_usuario"];

$plantilla_seccion = str_replace(
    [
        "{nombre_img_perfil}",
        "{nombre_usuario}"
    ],
    [
        $nombre_img_perfil,
        $nombre_usuario
    ],
    $plantilla_seccion
);

?>