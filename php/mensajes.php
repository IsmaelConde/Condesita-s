<?php

include_once $root_path . "backend/funciones_bd.php";

/*
// Miramos si recibe un usuario en concreto para hablar
if($contenido_url[2] != ""){
    echo "Hay mensaje";
}
*/

$insertar_mensajes_recientes = "";

$plantilla_grupos = file_get_contents($root_path . "html/plantilla_contacto_mensaje.htm");

$resultado_grupos = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " WHERE id_grupo IN (SELECT id_grupo FROM " . $nombre_tabla_grupos_usuarios . " WHERE id_usuario = '" . $id_usuario . "');");
if($resultado_grupos[0]){ // Ya ha hablado con gente
    //$sql = "SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario IN (SELECT id_usuario FROM " . $nombre_tabla_mensajes . " WHERE id_grupo IN (SELECT id_grupo FROM " . $nombre_tabla_grupos_usuarios. " WHERE id_usuario = '" . $id_usuario . "')) ORDER BY fecha_enviado DESC;";
    while($grupo = mysqli_fetch_assoc($resultado_grupos[1])){
        $insertar_mensajes_recientes .= str_replace(
            [
                "{id_grupo}",
                "{ruta_url}",
                "{nombre_img_perfil}",
                "{nombre_usuario}"
            ],
            [
                $grupo["id_grupo"],
                $ruta_url,
                "nofoto.webp",
                $grupo["descripcion_grupo"]
            ],
            $plantilla_grupos
        );
    }
}else{ // No se ha hablado con nadie todavía
    $resultado_usuarios_seguidos = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario IN (SELECT id_usuario FROM " . $nombre_tabla_seguidores . " WHERE id_seguidor = '" . $id_usuario . "');");
    if($resultado_usuarios_seguidos[0]){ // Sigue a gente
        while($usuarios_seguidos = mysqli_fetch_assoc($resultado_usuarios_seguidos[1])){
            insertarDatos($nombre_tabla_grupos, [urldecode($usuario["nombre_arroba_usuario"]) . " - " . $usuarios_seguidos["nombre_arroba_usuario"]],["descripcion_grupo"]);
            $resultado_grupos = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " ORDER BY id_grupo DESC LIMIT 1;");
            $ultimo_grupo = mysqli_fetch_assoc($resultado_grupos[1]);
            insertarDatos($nombre_tabla_grupos_usuarios, [$id_usuario, $ultimo_grupo["id_grupo"]], ["id_usuario", "id_grupo"]);
            insertarDatos($nombre_tabla_grupos_usuarios, [$usuarios_seguidos["id_usuario"], $ultimo_grupo["id_grupo"]], ["id_usuario", "id_grupo"]);

            $insertar_mensajes_recientes .= str_replace(
                [
                    "{id_grupo}",
                    "{ruta_url}",
                    "{nombre_img_perfil}",
                    "{nombre_usuario}"
                ],
                [
                    $ultimo_grupo["id_grupo"],
                    $ruta_url,
                    "nofoto.webp",
                    $ultimo_grupo["descripcion_grupo"]
                ],
                $plantilla_grupos
            );
        }
    }else{ // No sigue a nadie
        $insertar_mensajes_recientes = "<div><p>Todavía no sigues a nadie</p></div>";
    }  
    //$insertar_mensajes_recientes = "<div><p>Todavía no hablaste con nadie</p></div>";
}

$plantilla_seccion = str_replace(
    [
        "{inyectar_mensajes_recientes}",
        "{nombre_img_perfil}",
    ],
    [
        $insertar_mensajes_recientes,
        $usuario["img_perfil"],
    ],
    $plantilla_seccion
);

?>