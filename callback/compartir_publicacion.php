<?php
$resultado_compartido = obtenerDatos("SELECT COUNT(id_mensaje) as suma FROM " . $nombre_tabla_publicaciones_compartidas . " WHERE id_publicacion = '" . $id_publicacion . "';");
$num_compartido = establecer_numeros_datos($resultado_compartido);

// Para pintar el compartir si el usuario ha enviado por lo menos una vez esa publicación
$estado_compartido = "";
$resultado_compartido = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones_compartidas . " WHERE (id_mensaje IN (SELECT id_mensaje FROM " . $nombre_tabla_mensajes . " WHERE id_usuario = '" . $id_usuario . "')) AND (id_publicacion = '" . $id_publicacion . "');");
if($resultado_compartido[0]){
    // Entonces ha dado like
    $estado_compartido = "azul";
}
?>