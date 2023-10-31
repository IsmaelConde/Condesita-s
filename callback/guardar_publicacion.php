<?php

$resultado_guardado = obtenerDatos("SELECT count(id_guardado) as suma FROM " . $nombre_tabla_guardados . " WHERE nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $publicacion["id_publicacion"] . "';");
$num_guardado = establecer_numeros_datos($resultado_guardado);

// Para pintar e guardao si el usuario ya lo ha guardado
$estado_guardado = "";
$resultado_guardado = obtenerDatos("SELECT * FROM " . $nombre_tabla_guardados . " WHERE id_usuario = '" . $id_usuario . "' AND nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $publicacion["id_publicacion"] . "';");
if($resultado_guardado[0]){
    // Entonces lo ha guardado
    $estado_guardado = "amarillo";
}

?>