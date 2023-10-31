<?php
include_once $root_path . "backend/funciones_bd.php";

// Obtenemos la identificación del usuario
$nombre_arroba_usuario = $contenido_url[2];

// Miramos si la variable contiene el @
if(strpos($nombre_arroba_usuario, "@") === false){
    // No Tiene el @
    $nombre_arroba_usuario = "@" . $nombre_arroba_usuario;
}

// Vamos a obtener los datos
$resultado_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario='" . $nombre_arroba_usuario . "';");

if(!$resultado_usuario[0]){ // Si no hay resultados de la búsqueda
    $pedimos_seccion = "404";
    $plantilla_seccion = file_get_contents($root_path . "html/" . $pedimos_seccion . ".htm");
    if(file_exists($root_path . "php/" . $pedimos_seccion . ".php")){
        include $root_path . "php/" . $pedimos_seccion . ".php";
    }
}else{ // Si hay resultados de búsqueda
    $usuario_perfil = mysqli_fetch_assoc($resultado_usuario[1]); // Obtenemos todos los datos del usuario

    $insertar_botones_perfil = "";
    $somos_nosotros = comprobar_si_somos_nosotros($nombre_arroba_usuario, $usuario_perfil, $usuario); // Comprobamos si somos nosotros    
    
    include $root_path . "callback/datos_perfil_usuario.php";

    /* Está comentado, ya que ahora se encarga una función Ajax de inyectar los contenidos
    $insertar_publicaciones = "";
    if($publicaciones_subidas > 0){ // Si hay alguna publicación, entonces
        $plantilla_publicacion = file_get_contents($root_path . "html/plantilla_publicacion.htm");

        $resultado_publicaciones;
        if($somos_nosotros){
            $resultado_publicaciones = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario = '" . $usuario["id_usuario"] . "';");
        }else{
            $resultado_publicaciones = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario = '" . $usuario["id_usuario"] . "' AND visibilidad = 1;");
        }
       
    
        while($publicacion = mysqli_fetch_assoc($resultado_publicaciones[1])){
            $id_publicacion = $publicacion["id_publicacion"];

            include $root_path . "callback/rellenar_contenidos.php";

            $resultado_comentarios = obtenerDatos("SELECT count(id_comentario) as suma FROM " . $nombre_tabla_comentarios . " WHERE id_publicacion = '" . $id_publicacion . "';");
            $num_comentarios = establecer_numeros_datos($resultado_comentarios);

            $resultado_likes = obtenerDatos("SELECT count(id_like) as suma FROM " . $nombre_tabla_likes . " WHERE nombreTabla_id = '" . $nombre_tabla_publicaciones . "_" . $id_publicacion . "';" );
            $num_likes = establecer_numeros_datos($resultado_likes);

            $insertar_publicaciones .= str_replace(
                [
                    "{id_publicacion}",
                    "{insertar_contenidos}",
                    "{numero_comentarios}",
                    "{cantidad_likes}"
                ],
                [
                    $id_publicacion,
                    $insertar_contenidos,
                    $num_comentarios,
                    $num_likes
                ],
                $plantilla_publicacion
            );            
        }
        
    }else{ // Si no hay publicaciones
        $insertar_publicaciones .= "<h3>No se ha subido nada todavía<h3>";
    }
    */

    $descripcion_perfil = $usuario_perfil["descripcion_perfil"];
    if($descripcion_perfil == ""){ // Está vacío
        $descripcion_perfil = $usuario_perfil["nombre_usuario"] . " no tiene descripción";
    }

    $plantilla_seccion = str_replace(
        [
            "{nombre_usuario}",
            "{seguidores}",
            "{siguiendo}",
            "{num-publicaciones}",
            "{insertar_publicaciones}",
            "{insertar-botones}",
            "{nombre_archivo_img_perfil}",
            "{nombre_archivo_img_portada}",
            "{descripcion_usuario}"
        ],
        [
            $usuario_perfil["nombre_usuario"],
            $seguidores,
            $siguiendo,
            $publicaciones_subidas,
            $insertar_publicaciones,
            $insertar_botones_perfil,
            "img_foto_perfil/" . $usuario_perfil["img_perfil"],
            "img_foto_portada/" . $usuario_perfil["img_portada"],
            $descripcion_perfil
        ],
        $plantilla_seccion
    );
}

function comprobar_si_somos_nosotros($arroba_perfil, $usuario_perfil, $usuario){
    $platilla_botones_perfil = "";
    $soy_yo;
    if($usuario_perfil["id_usuario"] == $_SESSION["usuario_id"]){
        // Somos nosotros
        $platilla_botones_perfil .= '
            <a id="button-editar-perfil">Editar perfil</a>
        ';
        $soy_yo = true;
    }else{ // No somos nosotros
        //Miramos si lo sigue
        if($_SESSION["usuario_id"]){
            $resultado_seguir = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_seguidores"] . " WHERE id_usuario = (SELECT id_usuario FROM " . $GLOBALS["nombre_tabla_usuarios"] . " WHERE nombre_arroba_usuario = '" . $arroba_perfil . "') AND id_seguidor = (SELECT id_usuario FROM " . $GLOBALS["nombre_tabla_usuarios"] . " WHERE nombre_arroba_usuario = '" . $usuario["nombre_arroba_usuario"] . "');");

            $id_grupo_usuarios = "";

            $grupo_creado = false;
            $resultado_grupos_usuario = obtenerDatos("SELECT tabla1.* FROM " . $GLOBALS["nombre_tabla_grupos_usuarios"] . " AS tabla1 JOIN " . $GLOBALS["nombre_tabla_grupos_usuarios"] . " AS tabla2 ON tabla1.id_grupo = tabla2.id_grupo WHERE tabla1.id_usuario = '" . $usuario_perfil["id_usuario"] . "' AND tabla2.id_usuario = '" . $_SESSION["usuario_id"] . "';");
            if($resultado_grupos_usuario[0]){ // Tiene grupo con el usuario
                // Obtenemos los grupos en común
                while($grupo_usuarios = mysqli_fetch_assoc($resultado_grupos_usuario[1])){
                    $suma_usuarios = establecer_numeros_datos(obtenerDatos("SELECT count(id_usuario) as suma FROM " . $GLOBALS["nombre_tabla_grupos_usuarios"] . " WHERE id_grupo = '" . $grupo_usuarios["id_grupo"] . "';"));
                    if($suma_usuarios == 2){ // Es este el grupo
                        $id_grupo_usuarios = $grupo_usuarios["id_grupo"];
                        $grupo_creado = true;
                        break;
                    }
                }
            }

            $seguir_o_dejar;
            if($resultado_seguir[0]){
                // Entonces lo sigue
                $seguir_o_dejar = "Dejar de Seguir";
            }else{
                // No lo sigue
                $seguir_o_dejar = "Seguir";
            }

            $platilla_botones_perfil .= '
                    <a id="button-seguir-usuario">' . $seguir_o_dejar . '</a>
                    <a href="{ruta_url}mensajes/' . $id_grupo_usuarios . '">Envíar mensaje</a>
            ';
            
        }else{
            // No está logueado el usuario que quiere ver el perfil
            $platilla_botones_perfil .= "
                <a href=\"{ruta_url}\">Iniciar Sesión</a>
            ";
        }
        $soy_yo = false;
    }

    $GLOBALS["insertar_botones_perfil"] .= $platilla_botones_perfil;
    return $soy_yo;
}
?>