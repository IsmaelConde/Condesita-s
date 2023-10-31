<?php
$resultado_replicas = obtenerDatos("SELECT count(id_replica) as suma FROM " . $nombre_tabla_replicas . " WHERE id_publicacion = '" . $publicacion["id_publicacion"] . "';");
$num_replicas = establecer_numeros_datos($resultado_replicas);

// Para pintar la replica si el usuario ha hecho una replica
$estado_replica = "";
$resultado_replicas = obtenerDatos("SELECT * FROM " . $nombre_tabla_replicas . " WHERE id_publicacion = '" . $publicacion["id_publicacion"] . "' AND id_usuario = '" . $id_usuario . "';");
if($resultado_replicas[0]){
    // Entonces ha hecho replica en esta publicacion
    $estado_replica = "verde";
}

?>