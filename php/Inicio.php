<?php

include_once $root_path . "backend/funciones_bd.php";

$_SESSION["paginacion_inicio"] = "";
$insertar_publicaciones = "";

$plantilla_perfiles_recomendado = '
<div class="perfil">
    <a href="{ruta_url}perfil/{nombre_arroba_usuario}">
        <img src="{foto_perfil}" alt="Foto perfil de {nombre_usuario}" title="Foto perfil de {nombre_usuario}">
    </a>
    <div class="perfil_nombre">
        <h5 class="nombre-usuario">{nombre_usuario}</h5>
        <p class="arroba_nombre-usuario">{nombre_arroba_usuario}</p>
    </div>
    <div class="opciones_articulo">
        <svg xmlns="http://www.w3.org/2000/svg" class="opciones_articulo-svg" viewBox="0 96 960 960"><path d="M479.858 896Q460 896 446 881.858q-14-14.141-14-34Q432 828 446.142 814q14.141-14 34-14Q500 800 514 814.142q14 14.141 14 34Q528 868 513.858 882q-14.141 14-34 14Zm0-272Q460 624 446 609.858q-14-14.141-14-34Q432 556 446.142 542q14.141-14 34-14Q500 528 514 542.142q14 14.141 14 34Q528 596 513.858 610q-14.141 14-34 14Zm0-272Q460 352 446 337.858q-14-14.141-14-34Q432 284 446.142 270q14.141-14 34-14Q500 256 514 270.142q14 14.141 14 34Q528 324 513.858 338q-14.141 14-34 14Z"/></svg>
    </div>
</div>
';

$insertar_usuarios_recomendados = "";

$resultado_usuarios_recomendados = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario NOT IN (SELECT id_usuario FROM " . $nombre_tabla_seguidores . " WHERE id_seguidor = '" . $id_usuario . "') AND id_usuario != '" . $id_usuario . "' LIMIT 5;");
if($resultado_usuarios_recomendados[0]){
    while($usuario_recomendado = mysqli_fetch_assoc($resultado_usuarios_recomendados[1])){

        $ruta_img = $root_path . "images/img_foto_perfil/" . $usuario_recomendado["img_perfil"];

        $foto_perfil = ""; // Por defecto, en caso de que no exista
        if(file_exists($ruta_img) && is_file($ruta_img)){
            $contenido = file_get_contents($ruta_img);
            $base64 = base64_encode($contenido);
            $imagen_contenido_mime = mime_content_type($ruta_img); // jpeg o png

            //$blob_imagen = new Blob([$contenido], ["type" => $imagen_contenido_mime]);

            $uri_imagen = "data:" . $imagen_contenido_mime . ";base64," . $base64;
            
            $foto_perfil = $uri_imagen;
        }

        $insertar_usuarios_recomendados .= str_replace(
            [
                "{nombre_arroba_usuario}",
                "{foto_perfil}",
                "{nombre_usuario}"
            ],
            [
                urldecode($usuario_recomendado["nombre_arroba_usuario"]),
                $foto_perfil,
                $usuario_recomendado["nombre_usuario"]
            ],
            $plantilla_perfiles_recomendado
        );
    }
}else{ // Sigue a todo dios
    $insertar_usuarios_recomendados = "Sigues a todo dios, no te puedo recomendar nada";
}

/* ==================================================================
            HISTORIAS
=================================================================== */
$plantilla_historia = '
<article class="historia" usuario="{nombre_arroba_usuario}">
    <img class="fondo" src="{ruta_url}images/img_foto_perfil/{nombre_img_perfil}" alt="Foto perfil de {nombre_usuario}">
    <p>{nombre_usuario}</p>
</article>
';

$insertar_historias = "";

$resultado_historias = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE (id_usuario IN (SELECT id_usuario FROM " . $nombre_tabla_historias . " WHERE fecha_subido > (SELECT TIMESTAMPADD(HOUR,-24,CURRENT_TIMESTAMP)) ORDER BY id_historia ASC)) AND (id_usuario IN (SELECT id_usuario FROM " . $nombre_tabla_seguidores . " WHERE id_seguidor = '" . $id_usuario . "') OR id_usuario = '" . $id_usuario . "');");
if($resultado_historias[0]){ // Hay historias para mostrar
    while($historia = mysqli_fetch_assoc($resultado_historias[1])){
        $resultado_usuario_historia = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $historia["id_usuario"] . "';");
        $usuario_historia = mysqli_fetch_assoc($resultado_usuario_historia[1]);

        $insertar_historias .= str_replace(
            [
                "{nombre_img_perfil}",
                "{nombre_usuario}",
                "{nombre_arroba_usuario}"
            ],
            [
                $usuario_historia["img_perfil"],
                $usuario_historia["nombre_usuario"],
                urldecode($usuario_historia["nombre_arroba_usuario"])
            ],
            $plantilla_historia
        );
    }
}else{
    $insertar_historias = "No hay historias";
}
/* ================================================================
        FIN HISTORIAS
================================================================ */

$plantilla_seccion = str_replace(
    [
        "{inyectar_publicaciones}",
        "{nombre_img_perfil}",
        "{nombre_usuario}",
        "{inyectar_perfiles_recomendados}",
        "{inyectar_historias}"
    ],
    [
        $insertar_publicaciones,
        $usuario["img_perfil"],
        $usuario["nombre_usuario"],
        $insertar_usuarios_recomendados,
        $insertar_historias
    ],
    $plantilla_seccion
);


?>