<?php
$resultado_seguidores = obtenerDatos("SELECT COUNT(id_seguidor) as suma FROM " . $nombre_tabla_seguidores . " WHERE id_usuario = '" . $usuario_perfil["id_usuario"] . "';");
$resultado_siguiendo = obtenerDatos("SELECT COUNT(id_usuario) as suma FROM " . $nombre_tabla_seguidores . " WHERE id_seguidor = '" . $usuario_perfil["id_usuario"] . "';");

if($somos_nosotros){
    $resultado_publicaciones = obtenerDatos("SELECT COUNT(id_publicacion) as suma  FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario = '" . $usuario_perfil["id_usuario"] . "';");
}else{
    $resultado_publicaciones = obtenerDatos("SELECT COUNT(id_publicacion) as suma  FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario = '" . $usuario_perfil["id_usuario"] . "' AND visibilidad = 1;");
}

$seguidores = establecer_numeros_datos($resultado_seguidores);
$siguiendo = establecer_numeros_datos($resultado_siguiendo);
$publicaciones_subidas = establecer_numeros_datos($resultado_publicaciones);
?>