<?php

include "../config.php";
include "comprobar_publicacion.php";

/**
 * $datos:
 * [0] = id_publicacion
 * [1] = @usuario
*/

session_start();

$id_usuario = $_SESSION["usuario_id"];


// Comprobamos que somos los creadores de la publicacion
if($publicacion["id_usuario"] == $id_usuario){ // El usuario es el creador
    $sql = "DELETE FROM " . $nombre_tabla_publicaciones . " WHERE id_publicacion = '" . $publicacion["id_publicacion"] . "';";
    if(conectarQuery($sql, "Se ha borrado la Publicación '" . $id_publicacion . "'.")){
        $devolver["estado"] = 202;
        $devolver["js"] = "
        if(comprobar_si_es_publicacion){ // Estamos en los detalles de la publicación
            location.replace(\"" . $protocolo . "://" . $nombre_host . "\");
        }else{ // En caso de estar en inicio u otro sitio que no sea los detalles
            let padre = encontrar_padre(ultimo_click_ajustes_articulos);

            console.log(padre);

            padre.remove(); // Lo borramos
        }

        function encontrar_padre(e){
            if(e.hasAttribute(\"id_publicacion\")){ // Es el padre de la publicación
                return e;
            }else{
                return encontrar_padre(e.parentElement);
            }
        }
        ";
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido borrar la publicación";
    }
}else{ // El usuario no es el creador
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No eres el creador de la publicación como para querer borrarla";
}

echo json_encode($devolver);


?>