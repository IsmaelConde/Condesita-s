<?php
include_once $root_path . "backend/funciones_bd.php";

$insertar_publicaciones = "";
//Tenemos que obtener todas las publicaciones que el usuario no siga que haya y que no salgan sus propias publicaciones
$obtener_publicaciones = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario NOT IN (SELECT id_usuario FROM " . $nombre_tabla_seguidores . " WHERE id_seguidor = '" . $id_usuario . "') AND id_usuario != '" . $id_usuario . "' AND visibilidad = 1 LIMIT 100");
if($obtener_publicaciones[0]){
    // Hay publicaciones de gente que el usuario no sigue
    $plantilla_contenido = file_get_contents($root_path . "html/plantilla_publicacion.htm");

    while($publicacion = mysqli_fetch_assoc($obtener_publicaciones[1])){
        $id_publicacion = $publicacion["id_publicacion"];
        include $root_path . "callback/rellenar_contenidos.php";

        $resultado_comentarios = obtenerDatos("SELECT count(id_comentario) as suma FROM " . $nombre_tabla_comentarios . " WHERE id_publicacion = '" . $publicacion["id_publicacion"] . "';");
        $num_comentarios = establecer_numeros_datos($resultado_comentarios);

        $resultado_likes = obtenerDatos("SELECT count(id_like) as suma FROM " . $nombre_tabla_likes . " WHERE nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $publicacion["id_publicacion"] . "';" );
        $num_likes = establecer_numeros_datos($resultado_likes);

        $insertar_publicaciones .= str_replace(
            [
                "{id_publicacion}",
                "{insertar_contenidos}",
                "{numero_comentarios}",
                "{cantidad_likes}"
            ],
            [
                $publicacion["id_publicacion"],
                $insertar_contenidos,
                $num_comentarios,
                $num_likes
            ],
            $plantilla_contenido
        );
    }

}else{
    // No hay publicaicones
    $insertar_publicaciones = "No hay publicaciones";
}

$plantilla_seccion = str_replace(
    [
        "{inyectar_contenidos}"
    ],
    [
        $insertar_publicaciones
    ],
    $plantilla_seccion
);

?>