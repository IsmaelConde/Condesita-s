<?php

$usuarios .= str_replace(
    [
        "{ruta_url}",
        "{nombre_arroba_usuario}",
        "{nombre_clase}",
        "{nombre_img_perfil}",
        "{nombre_usuario}",
        "{nombre_arroba}",
        "{id_grupo}"
    ],
    [
        $ruta_url,
        urldecode($usuario["nombre_arroba_usuario"]),
        "resultado",
        $usuario["img_perfil"],
        $usuario["nombre_usuario"],
        urldecode($usuario["nombre_arroba_usuario"]),
        $grupo_id
    ],
    $plantilla_usuarios
);

?>