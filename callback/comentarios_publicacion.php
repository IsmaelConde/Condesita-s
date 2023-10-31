<?php
$obtener_usuario_comentario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $comentario["id_usuario"] . "';");
$usuario_comentario = mysqli_fetch_assoc($obtener_usuario_comentario[1]);

$insertar_comentarios .= str_replace(
    [
        "{nombre_usuario}",
        "{contenido_comentario}",
        "{nombre_arroba_usuario}",
        "{archivo_img_perfil}",
        "{id_comentario}"
    ],
    [
        $usuario_comentario["nombre_usuario"],
        $comentario["contenido"],
        urldecode($usuario_comentario["nombre_arroba_usuario"]),
        "img_foto_perfil/" . $usuario_comentario["img_perfil"],
        $comentario["id_comentario"]     
    ],
    $plantilla_comentario
);
?>