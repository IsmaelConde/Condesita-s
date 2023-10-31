<?php
include "comprobar_solicitud_backend.php";
include "../config.php";
include "funciones_bd.php";

session_start();

/**
 * $datos:
 * [0] = @nombre_usuario
*/

$devolver = [];

if($datos[0][0] == "@"){
    $datos[0] = substr($datos[0], 1);
}

$nombre_arroba_usuario = urlencode($datos[0]);

$resultado_usuario_perfil = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $nombre_arroba_usuario . "';");
if($resultado_usuario_perfil[0]){ // Existe el usuario
    $usuario_perfil = mysqli_fetch_assoc($resultado_usuario_perfil[1]);

    $plantilla_previsualizacion = '
        <img src="{ruta_url}/images/img_foto_portada/{nombre_img_portada}" alt="imagen-portada">
        <div class="datos_perfil-previsualizacion">
            <img src="{ruta_url}/images/img_foto_perfil/{nombre_img_perfil}" alt="foto perfil">
            <div class="cuadro-previsualizacion">
                <p>Subidas</p>
                <p>{num_subidas}</p>
            </div>
            <div class="cuadro-previsualizacion">
                <p>Seguidores</p>
                <p>{num_seguidores}</p>
            </div>
            <div class="cuadro-previsualizacion">
                <p>Siguiendo</p>
                <p>{num_siguiendo}</p>
            </div>
        </div>
        <div class="contenidos-perfil-previsualizacion">
            {inyectar_contenidos}
        </div>
    ';

    $plantilla_contenido = '
    <div class="contenido-perfil-previsualizacion">
        {inyectar_contenido}
    </div>
    ';

    $somos_nosotros;
    if($_SESSION["usuario_id"] == $usuario_perfil["id_usuario"]){
        $somos_nosotros = true;
    }else{
        $somos_nosotros = false;
    }

    $contenido_publicaciones = "";
    if($somos_nosotros){
        $resultado_publicaciones = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario = '" . $usuario_perfil["id_usuario"] . "' ORDER BY id_publicacion DESC LIMIT 10");
    }else{
        $resultado_publicaciones = obtenerDatos("SELECT * FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario = '" . $usuario_perfil["id_usuario"] . "' AND visibilidad = 1 ORDER BY id_publicacion DESC LIMIT 10");
    }

    if($resultado_publicaciones[0]){ // Hay publicaciones
        while($publicacion = mysqli_fetch_assoc($resultado_publicaciones[1])){
            $id_publicacion = $publicacion["id_publicacion"];
            include $root_path . "callback/rellenar_contenidos.php";

            $contenido_publicaciones .= str_replace(
                [
                    "{inyectar_contenido}"
                ],
                [
                    $insertar_contenidos
                ],
                $plantilla_contenido
            );
        }
    }else{
        $contenido_publicaciones = "No tiene publicaciones";
    }

    include $root_path . "callback/datos_perfil_usuario.php";

    $plantilla_previsualizacion = str_replace(
        [
            "{inyectar_contenidos}",
            "{ruta_url}",
            "{nombre_img_portada}",
            "{nombre_img_perfil}",
            "{num_subidas}",
            "{num_seguidores}",
            "{num_siguiendo}"
        ],
        [
            $contenido_publicaciones,
            $ruta_url,
            $usuario_perfil["img_portada"],
            $usuario_perfil["img_perfil"],
            $publicaciones_subidas,
            $seguidores,
            $siguiendo
        ],
        $plantilla_previsualizacion
    );

    $devolver["estado"] = 202;
    $devolver["contenido"] = $plantilla_previsualizacion;
    $devolver["js"] = "pintar_previsualizacion_perfil";
    $devolver["parametros_js"] = $plantilla_previsualizacion;
    /*
    "function pintar_previsualizacion_perfil(){
        let crear_div = document.createElement(\"DIV\");
        crear_div.innerHTML = '" . $plantilla_previsualizacion . "';
        evento_previsualizar_perfil.target.appendChild(crear_div);
    }";
    */

}else{ // El usuario no existe
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se ha encontrado al usuario";
}

echo json_encode($devolver);

?>