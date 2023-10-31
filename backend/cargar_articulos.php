<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe ningún POST";
    die(json_encode($devolver));
}

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No has iniciado sesión";
    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];

if(empty($_SESSION["paginacion_inicio"])){ // No existe todavía la páginación
    $_SESSION["paginacion_inicio"] = [];
    $_SESSION["paginacion_inicio"]["pagina_actual"] = 0;
    $_SESSION["paginacion_inicio"]["contenido_por_pagina"] = 4;
    //echo "Está vacía la paginación";
}

$_SESSION["paginacion_inicio"]["numero_articulos"] = establecer_numeros_datos(obtenerDatos("SELECT count(id_publicacion) as suma FROM " . $nombre_tabla_publicaciones . " WHERE (id_usuario IN (SELECT id_usuario FROM " . $nombre_tabla_seguidores . " WHERE id_seguidor = '" . $id_usuario . "') OR (id_usuario = '" . $id_usuario . "')) AND (visibilidad = 1);"));
$_SESSION["paginacion_inicio"]["paginas_totales"] = ceil($_SESSION["paginacion_inicio"]["numero_articulos"] / $_SESSION["paginacion_inicio"]["contenido_por_pagina"]);

$devolver["paginacion"] = $_SESSION["paginacion_inicio"];

if($_SESSION["paginacion_inicio"]["numero_articulos"] <= 0){ // No hay contenidos
    $devolver["estado"] = 120;
    $devolver["js"] = "
    let texto_info = document.createElement(\"p\");

    texto_info.innerHTML = \"No has subido ninguna publicación y la gente que sigues no ha publicado nada, ve a <a href='" . $ruta_url . "explorar'>Explorar</a> para encontrar las publicaciones de usuarios que no sigues\";

    document.querySelector(\"#publicaciones\").appendChild(texto_info);
    ";
}else{
    if($_SESSION["paginacion_inicio"]["pagina_actual"] >= $_SESSION["paginacion_inicio"]["paginas_totales"]){

        $devolver["estado"] = 120;
        $devolver["contenido"] = "Ya no hay más publicaciones";
        $devolver["js"] = "document.querySelector(\"#main\").removeEventListener(\"scroll\", modificacionesVentana, true);";

        //$_SESSION["paginacion_inicio"]["pagina_actual"] = 0;
    }else{
        $plantilla_publicacion = '
        <article class="articulo" id_publicacion="{id_publicacion}" subido_por="{nombre_arroba_usuario}">
        <header>
            <a href="{ruta_url}perfil/{nombre_arroba_usuario}"><img src="{foto_perfil}" alt="imagen-usuario"></a>
            <div class="perfil_nombre">
                <h5 class="nombre-usuario">{nombre_usuario}</h5>
                <p class="arroba_nombre-usuario">{nombre_arroba_usuario}</p>
            </div>
            <div class="opciones_articulo">
                <svg xmlns="http://www.w3.org/2000/svg" class="opciones_articulo-svg" viewBox="0 96 960 960">
                    <path class="cerrar-svg" d="m249 849-42-42 231-231-231-231 42-42 231 231 231-231 42 42-231 231 231 231-42 42-231-231-231 231Z"/>
                    <path class="opciones-svg" d="M479.858 896Q460 896 446 881.858q-14-14.141-14-34Q432 828 446.142 814q14.141-14 34-14Q500 800 514 814.142q14 14.141 14 34Q528 868 513.858 882q-14.141 14-34 14Zm0-272Q460 624 446 609.858q-14-14.141-14-34Q432 556 446.142 542q14.141-14 34-14Q500 528 514 542.142q14 14.141 14 34Q528 596 513.858 610q-14.141 14-34 14Zm0-272Q460 352 446 337.858q-14-14.141-14-34Q432 284 446.142 270q14.141-14 34-14Q500 256 514 270.142q14 14.141 14 34Q528 324 513.858 338q-14.141 14-34 14Z"/>
                </svg>
            </div>
        </header>
        <div class="contenido">
            {insertar_contenidos}
        </div>
        <div class="mas-contenido">
            <div class="accion-usuario">
                <div class="like">
                    <svg xmlns="http://www.w3.org/2000/svg" class="like-svg {estado_like}" viewBox="0 96 960 960">
                        <path class="relleno" d="m480 935-41-37q-106-97-175-167.5t-110-126Q113 549 96.5 504T80 413q0-90 60.5-150.5T290 202q57 0 105.5 27t84.5 78q42-54 89-79.5T670 202q89 0 149.5 60.5T880 413q0 46-16.5 91T806 604.5q-41 55.5-110 126T521 898l-41 37Z"/>
                        <path class="no_releno" d="m480 935-41-37q-105.768-97.121-174.884-167.561Q195 660 154 604.5T96.5 504Q80 459 80 413q0-90.155 60.5-150.577Q201 202 290 202q57 0 105.5 27t84.5 78q42-54 89-79.5T670 202q89 0 149.5 60.423Q880 322.845 880 413q0 46-16.5 91T806 604.5Q765 660 695.884 730.439 626.768 800.879 521 898l-41 37Zm0-79q101.236-92.995 166.618-159.498Q712 630 750.5 580t54-89.135q15.5-39.136 15.5-77.72Q820 347 778 304.5T670.225 262q-51.524 0-95.375 31.5Q531 325 504 382h-49q-26-56-69.85-88-43.851-32-95.375-32Q224 262 182 304.5t-42 108.816Q140 452 155.5 491.5t54 90Q248 632 314 698t166 158Zm0-297Z"/>
                    </svg>
                    <p class="cantidad-likes">{num_likes}</p>
                </div>
                <div class="comentar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="comentar-svg {estado_comentar}" viewBox="0 96 960 960"><path d="M240 656h480v-60H240v60Zm0-130h480v-60H240v60Zm0-130h480v-60H240v60Zm640 580L720 816H140q-23 0-41.5-18.5T80 756V236q0-23 18.5-41.5T140 176h680q24 0 42 18.5t18 41.5v740ZM140 236v520h605l75 75V236H140Zm0 0v595-595Z"/></svg>
                    <p class="cantidad-comentarios">{num_comentarios}</p>
                </div>
                <div class="enviar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="enviar-svg {estado_compartir}" viewBox="0 96 960 960"><path d="M120 896V256l760 320-760 320Zm60-93 544-227-544-230v168l242 62-242 60v167Zm0 0V346v457Z"/></svg>
                    <p class="cantidad-envios">{num_compartido}</p>
                </div>
                <div class="replicar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="replicar-svg {estado_replicas}" viewBox="0 96 960 960"><path d="M480 976q-75 0-140.5-28T225 871q-49-49-77-114.5T120 616h60q0 125 87.5 212.5T480 916q125 0 212.5-87.5T780 616q0-125-85-212.5T485 316h-23l73 73-41 42-147-147 147-147 41 41-78 78h23q75 0 140.5 28T735 361q49 49 77 114.5T840 616q0 75-28 140.5T735 871q-49 49-114.5 77T480 976Z"/></svg>
                    <p class="cantidad-replicas">{num_replicas}</p>
                </div>
                <div class="guardar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="guardar-svg {estado_guardar}" viewBox="0 96 960 960">
                        <path class="relleno" d="M220 976q-24 0-42-18t-18-42V236q0-24 18-42t42-18h520q24 0 42 18t18 42v680q0 24-18 42t-42 18H220Zm266-474 97-56 97 56V236H486v266Z"/>
                        <path class="sin_relleno" d="M220 976q-24 0-42-18t-18-42V236q0-24 18-42t42-18h520q24 0 42 18t18 42v680q0 24-18 42t-42 18H220Zm0-60h520V236h-60v266l-97-56-97 56V236H220v680Zm0 0V236v680Zm266-414 97-56 97 56-97-56-97 56Z"/>
                    </svg>
                    <p class="cantidad-guardar">{num_guardados}</p>
                </div>
            </div>
            <div class="extra">
                {inyectar_plantilla_descripcion-publicacion}
                <div class="comentarios">
                    <!-- INYECTAR COMENTARIOS (2 MÁXIMO COMO PREVISUALIZACIÓN) -->            
                    {inyectar_comentarios}
                    <a class="ir-detalle-publicacion" href="{ruta_url}publicacion/{id_publicacion}">Ver Detalles Publicación</a>
                </div>
            </div>
        </div>
        </article>
        ';

        $plantilla_descripcion = '
            <div class="descripcion">
                <p class="texto-descripcion">{descripcion_publicacion}</p>
            </div>
        ';

        $insertar_publicaciones = "";

        $resultado_publicaciones = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE (id_usuario IN (SELECT id_usuario FROM " . $nombre_tabla_seguidores . " WHERE id_seguidor = '" . $id_usuario . "') OR (id_usuario = '" . $id_usuario . "')) AND (visibilidad = 1) ORDER BY id_publicacion DESC LIMIT " . ($_SESSION["paginacion_inicio"]["pagina_actual"] * $_SESSION["paginacion_inicio"]["contenido_por_pagina"]) . "," . $_SESSION["paginacion_inicio"]["contenido_por_pagina"] . ";");
        if($resultado_publicaciones[0]){
            $plantilla_comentario = file_get_contents($root_path . "html/plantilla_comentario.htm");

            while($publicacion = mysqli_fetch_assoc($resultado_publicaciones[1])){
                $id_publicacion = $publicacion["id_publicacion"];

                $resultado_usuario_publicacion = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $publicacion["id_usuario"] . "';");
                $usuario_publicacion = mysqli_fetch_assoc($resultado_usuario_publicacion[1]);

                /* ======================================================
                        RELLENAR CONTENIDO
                ===================================================== */
                include $root_path . "callback/rellenar_contenidos.php";
                /* ======================================================
                        FIN RELLENAR CONTENIDO
                =========================================================

                ---------------------------------------------------------------------------------------

                =========================================================
                        GUARDAR PUBLICACION
                ====================================================== */
                include $root_path . "callback/guardar_publicacion.php";
                /* =======================================================
                        FIN GUARDAR PUBLICACIÓN
                =========================================================

                -------------------------------------------------------------------------------------------

                ========================================================
                        LIKES PUBLICACION
                ========================================================= */
                include $root_path . "callback/likes_publicacion.php";
                /* =========================================================
                        FIN LIKES PUBLICACION
                ============================================================

                ----------------------------------------------------------------------------------------------

                ===========================================================
                    REPLICAS PUBLICACIÓN
                ========================================================= */
                include $root_path . "callback/replicas_publicacion.php";
                /* ========================================================
                        FIN REPLICAS PUBLICACIÓN
                ===========================================================

                --------------------------------------------------------------------------------------------

                ==========================================================
                    COMPARTIR PUBLICACIÓN
                ======================================================= */
                include $root_path . "callback/compartir_publicacion.php";
                /* ======================================================
                        FIN COMPARTIR PUBLICACIÓN
                =========================================================

                -------------------------------------------------------------------------------------------------

                ==========================================================
                        COMENTARIOS PUBLICACION
                ======================================================== */
                $obtener_comentarios = obtenerDatos("SELECT COUNT(id_comentario) as suma FROM " . $nombre_tabla_comentarios . " WHERE id_publicacion = '" . $id_publicacion . "';");
                $num_comentarios = establecer_numeros_datos($obtener_comentarios);

                $insertar_comentarios = "";
                if($num_comentarios > 0){
                    $obtener_comentarios = obtenerDatos("SELECT * FROM " . $nombre_tabla_comentarios . " WHERE id_publicacion = '" . $id_publicacion . "' ORDER BY id_comentario DESC LIMIT 2");
                    while($comentario = mysqli_fetch_assoc($obtener_comentarios[1])){
                        include $root_path . "callback/comentarios_publicacion.php";
                    }
                }else{
                    $insertar_comentarios = "<div class=\"comentario\">Sé el primero en comentar</div>";
                }

                
                /* =======================================================
                    FIN COMENTARIOS PUBLICACION
                ==========================================================

                -----------------------------------------------------------------------------

                ===========================================================
                    DESCRIPCIÓN
                ======================================================= */
                include $root_path . "callback/descripcion_publicacion.php";
                /* ======================================================
                    FIN DESCRIPCIÓN
                ====================================================== */

                /*
                // Este método tarda más
                $ruta_img = $root_path . "images/img_foto_perfil/" . $usuario_publicacion["img_perfil"];

                $foto_perfil = "";
                if(file_exists($ruta_img) && is_file($ruta_img)){
                    $contenido_img = file_get_contents($ruta_img);
                    $base64_img = base64_encode($contenido_img);
                    $imagen_contenido_mime = mime_content_type($ruta_img); // Nos da jpg, png, etc

                    $uri_imagen = "data:" . $imagen_contenido_mime . ";base64," . $base64_img;

                    $foto_perfil = $uri_imagen;
                }
                */

                $foto_perfil = $ruta_url . "images/img_foto_perfil/" . $usuario_publicacion["img_perfil"];

                $insertar_publicaciones .= str_replace(
                    [
                        "{nombre_usuario}",
                        "{nombre_arroba_usuario}",
                        "{insertar_contenidos}",
                        "{num_likes}",
                        "{estado_like}",
                        "{num_comentarios}",
                        "{num_compartido}",
                        "{estado_compartir}",
                        "{num_replicas}",
                        "{estado_replicas}",
                        "{num_guardados}",
                        "{estado_guardar}",
                        "{inyectar_plantilla_descripcion-publicacion}",
                        "{inyectar_comentarios}",
                        "{id_publicacion}",
                        "{foto_perfil}",
                        "{ruta_url}"
                    ],
                    [
                        $usuario_publicacion["nombre_usuario"],
                        urldecode($usuario_publicacion["nombre_arroba_usuario"]),
                        $insertar_contenidos,
                        $num_likes,
                        $estado_like,
                        $num_comentarios,
                        $num_compartido,
                        $estado_compartido,
                        $num_replicas,
                        $estado_replica,
                        $num_guardado,
                        $estado_guardado,
                        $descripcion_publicacion,
                        $insertar_comentarios,
                        $id_publicacion,
                        $foto_perfil,
                        $ruta_url
                    ],
                    $plantilla_publicacion
                );
            }
        }else{
            $insertar_publicaciones .= "La gente que sigues, no ha subido nada";
        }

        $devolver["estado"] = 202;
        $devolver["js"] = "insertar_articulos";
        $devolver["parametros_js"] = $insertar_publicaciones;
        

        $_SESSION["paginacion_inicio"]["pagina_actual"]++;
    }
}

//$_SESSION["paginacion_inicio"]["pagina_actual"]++;
echo json_encode($devolver);

?>