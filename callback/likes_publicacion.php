<?php
$resultado_likes = obtenerDatos("SELECT count(id_like) as suma FROM " . $nombre_tabla_likes . " WHERE nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $publicacion["id_publicacion"] . "';" );
$num_likes = establecer_numeros_datos($resultado_likes);

// Para pintar el like si el usuario ya ha dado like
$estado_like = "";
$resultado_likes = obtenerDatos("SELECT * FROM " . $nombre_tabla_likes . " WHERE id_usuario = '" . $id_usuario . "' AND nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $publicacion["id_publicacion"] . "';");
if($resultado_likes[0]){
    // Entonces ha dado like
    $estado_like = "rojo";
}
?>