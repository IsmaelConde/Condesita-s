<?php

if($resultado_publicacion_compartida[0]){ // Existe la publicación compartida
    $publicacion_compartida = mysqli_fetch_assoc($resultado_publicacion_compartida[1]);

    // Ahora obtenemos el id de la publicacion
    $id_publicacion = $publicacion_compartida["id_publicacion"];

    // Ahora tengo que saber si la publicación es privada o no
    $resultado_publicacion = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_publicaciones"] . " WHERE id_publicacion = '" . $id_publicacion . "';");
    if($resultado_publicacion[0]){ // Existe la publicación
        $publicacion = mysqli_fetch_assoc($resultado_publicacion[1]);

        if(($publicacion["visibilidad"] == "1") || ($publicacion["id_usuario"] == $GLOBALS["id_usuario"] && $publicacion["visibilidad"] == "0")){ // Comprobamos la visibilidad
            include $GLOBALS["root_path"] . "callback/rellenar_contenidos.php";

            $incluir_cabecera = "";

            $resultado_usuario = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_usuarios"] . " WHERE id_usuario = '" . $publicacion["id_usuario"] . "';");
            if($resultado_usuario[0]){ // Existe el usuario
                $usuario = mysqli_fetch_assoc($resultado_usuario[1]);
                $incluir_cabecera = "<a href=\"" . $GLOBALS["ruta_url"] . "perfil/" . $usuario["nombre_arroba_usuario"] . "\" class=\"cabecera_publicacion_compartida\"><img src=\"" . $GLOBALS["ruta_url"] . "images/img_foto_perfil/" . $usuario["img_perfil"] . "\"><div class=\"info_perfil_publicacion_compartida\"><h3>" . $usuario["nombre_usuario"] . "</h3><p>" . urldecode($usuario["nombre_arroba_usuario"]) . "</p></div></a>";
            }else{
                $incluir_cabecera = "No se encuentra al usuario";
            }

            $insertar_contenido = "<header>" . $incluir_cabecera . "</header><a class=\"publicacion_compartida\" href=\"" . $GLOBALS["ruta_url"] . "publicacion/" . $id_publicacion . "\">" . $insertar_contenidos . "</a>";
        }else {
            $es_publicacion_compartida = false; // Reiniciamos la variable
            $insertar_contenido = "<p>Publicación no Disponible</p>";
        }
    }else{ // No existe la publicación
        $es_publicacion_compartida = false; // Reiniciamos la variable
        $insertar_contenido = "<p>No existe la publicación</p>";
    }
}else{ // No se encuentra la publicación compartida
    $es_publicacion_compartida = false; // Reiniciamos la variable
    $insertar_contenido = "<p>No se encuentra la publicación</p>";
}

?>