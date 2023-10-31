<?php
include_once $root_path . "backend/funciones_bd.php";

// Obtenemos la identificación de la publicacion
$id_publicacion = $contenido_url[2];

$obtener_publicacion = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_publicacion = '" . $id_publicacion . "';");

if(!$obtener_publicacion[0]){ // Si no hay rsultados
    // Es porque no existe
    $pedimos_seccion = "404";
    $plantilla_seccion = file_get_contents($root_path . "html/" . $pedimos_seccion . ".htm");
    if(file_exists($root_path . "php/" . $pedimos_seccion . ".php")){
        include $root_path . "php/" . $pedimos_seccion . ".php";
    }
}else{ // Si existe
    /* ========================================
            OBTENEMOS DATOS PUBLICACION
    ======================================== */
    $publicacion = mysqli_fetch_assoc($obtener_publicacion[1]);

    if($publicacion["visibilidad"] == 0 && $publicacion["id_usuario"] != $id_usuario){ // Está oculto y no es el creador
        $pedimos_seccion = "404";
        $plantilla_seccion = file_get_contents($root_path . "html/" . $pedimos_seccion . ".htm");
        if(file_exists($root_path . "php/" . $pedimos_seccion . ".php")){
            include $root_path . "php/" . $pedimos_seccion . ".php";
        }
        
    }else{ // En caso de que no esté oculto o en caso de que esté oculto y sea el creador

        $fecha_publicacion = $publicacion["fecha"];

        $separar_fecha = explode(" ", $fecha_publicacion);

        $dia_publicacion = $separar_fecha[0];
        $hora_publicacion = $separar_fecha[1];

        $obtener_usuario_publicacion = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $publicacion["id_usuario"] . "';");
        $usuario_publicacion = mysqli_fetch_assoc($obtener_usuario_publicacion[1]);

        $javascript .= "var datos_publicacion = \"" . $id_publicacion . "{separacion}" . $usuario_publicacion["nombre_arroba_usuario"] . "\";";

        // Rellenamos el contenido de la publicación
        include $root_path . "callback/rellenar_contenidos.php"; // Para los contenidos

        /* =====================================
            FIN DATOS TABLA PUBLICACION
        ======================================

        --------------------------------------------------------------

        ===============================
            COMENTARIOS PUBLICACIÓN
        ============================ */
        $plantilla_comentario = file_get_contents($root_path . "html/plantilla_comentario.htm");

        $obtener_comentarios = obtenerDatos("SELECT * FROM " . $nombre_tabla_comentarios . " WHERE id_publicacion = '" . $id_publicacion . "';");

        $insertar_comentarios = "";
        if($obtener_comentarios[0]){
            // Contiene comentarios
            while($comentario = mysqli_fetch_assoc($obtener_comentarios[1])){
                include $root_path . "callback/comentarios_publicacion.php";
            }
        }else{
            // No contiene comentarios
            $insertar_comentarios = "Sé el primero en comentar";
        }

        /* =======================================
                FIN COMENTARIOS PUBLICACIÓN
        =========================================

        -------------------------------------------------------------------

        ==========================================
                REPLICAS PUBLICACIÓN
        ======================================= */

        include $root_path . "callback/replicas_publicacion.php";

        /* ==============================================
                FIN REPLICAS PUBLICACIÓN
        =================================================

        ----------------------------------------------------------------------

        ===================================================
            LIKES PUBLICACION
        =============================================== */
        
        include $root_path . "callback/likes_publicacion.php";

        /* ===============================================
                FIN LIKES PUBLICACION
        ============================================== 

        -------------------------------------------------------------------------

        =================================================
                GUARDAR PUBLICACIÓN
        =============================================== */
        
        include $root_path . "callback/guardar_publicacion.php";

        /* ===============================================
                FIN GUARDAR PUBLICACIÓN
        ==================================================

        -----------------------------------------------------------------------------------

        ==================================================
                COMPARTIR PUBLICACIÓN
        ================================================ */
        include $root_path . "callback/compartir_publicacion.php";
        /* ===============================================
                FIN COMPARTIR PUBLICACION
        ==================================================

        ------------------------------------------------------------------------------------

        =================================================
                DESCRIPCION PUBLICACIÓN
        ============================================== */
        $plantilla_descripcion = '
            <div id="descripcion">
                <p>{descripcion_publicacion}</p>
            </div>
        ';
        include $root_path . "callback/descripcion_publicacion.php";
        /* =============================================
                FIN DESCRIPCIÓN PUBLICACIÓN
        ================================================

        --------------------------------------------------------------------------------------

        =================================================
                INYECTAMOS LOS DATOS EN PLANTILLA
        ============================================== */

        /* ===============================================
                COMPROBAR PUBLICACIÓN PRIVADA
        =============================================== */
        $publicacion_privada;
        if($publicacion["visibilidad"] == 0){
            $publicacion_privada = "<div id=\"mensaje_privado\"><p class=\"info\">Esta publicación solo la puedes ver tú</p></div>";
        }
        /* =============================================
                FIN COMPROBAR PUBLICACIÓN PRIVADA
        =============================================== */
        $plantilla_seccion = str_replace(
            [
                "{nombre_usuario}",
                "{nombre_arroba_usuario}",
                "{insertar_contenidos}",
                "{insertar_mes}",
                "{insertar_hora}",
                "{insertar_comentarios}",
                "{inyectar_plantilla_descripcion_publicacion}",
                "{cantidad_replicas}",
                "{estado_replica}",
                "{cantidad_likes}",
                "{estado_like}",
                "{cantidad_guardados}",
                "{estado_guardado}",
                "{cantidad_compartido}",
                "{estado_compartido}",
                "{nombre_archivo_img_perfil}",
                "{insertar_mensaje_privado}"
            ],
            [
                $usuario_publicacion["nombre_usuario"],
                urldecode($usuario_publicacion["nombre_arroba_usuario"]),
                $insertar_contenidos,
                $dia_publicacion,
                $hora_publicacion,
                $insertar_comentarios,
                $descripcion_publicacion,
                $num_replicas,
                $estado_replica,
                $num_likes,
                $estado_like,
                $num_guardado,
                $estado_guardado,
                $num_compartido,
                $estado_compartido,
                "img_foto_perfil/" . $usuario_publicacion["img_perfil"],
                $publicacion_privada
            ],
            $plantilla_seccion
        );

        /* ===============================================
                FIN INYECCIÓN
        ============================================== */
    }

}

?>