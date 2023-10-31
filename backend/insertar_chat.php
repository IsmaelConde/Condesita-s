<?php
include "comprobar_solicitud_backend.php"; // Comprobamos que recibimos el post de datos
include "../config.php";
include $root_path . "backend/funciones_bd.php"; // Obtenemos las funciones de Base de Datos

session_start();

/**
 * $datos:
 * [0] = id_grupo
*/

$id_usuario = $_SESSION["usuario_id"];
$id_grupo = $datos[0];

$devolver = [];

function devolver_chat_grupo($grupo){
    $plantilla_mensaje = '
        <div class="mensaje {mio}" id_mensaje="{id_mensaje}">
            <div>
                <div class="{tipo_mensaje}">{contenido}</div>
                <p class="hora">{fecha}</p>
            </div>
        </div>
    ';

    $plantilla_chat = '
        <div id="header">
        <svg xmlns="http://www.w3.org/2000/svg" id="icono_atras-svg" viewBox="0 96 960 960" width="48"><path d="M480 896 160 576l320-320 42 42-248 248h526v60H274l248 248-42 42Z"/></svg>
        <img src="{ruta_url}images/img_foto_perfil/{nombre_img_perfil}" alt="Foto perfil">
        <div id="info-contacto-mensaje">
            <p>{nombre_grupo}</p>
            <p>Haz click, para obtener más información</p>
        </div>
        </div>
        <!-- Zona donde se va Insertar el chat -->
        <div id="contenido-chat">
            <div id="contenidos">
                {inyectar_mensajes}
            </div>
        </div>
        <!-- Fin Zona donde se va Insertar el chat -->
        <div id="zona-escribir">
            <svg xmlns="http://www.w3.org/2000/svg" id="emoticono-svg" viewBox="0 96 960 960" width="48"><path d="M626 523q22.5 0 38.25-15.75T680 469q0-22.5-15.75-38.25T626 415q-22.5 0-38.25 15.75T572 469q0 22.5 15.75 38.25T626 523Zm-292 0q22.5 0 38.25-15.75T388 469q0-22.5-15.75-38.25T334 415q-22.5 0-38.25 15.75T280 469q0 22.5 15.75 38.25T334 523Zm146 272q66 0 121.5-35.5T682 663H278q26 61 81 96.5T480 795Zm0 181q-83 0-156-31.5T197 859q-54-54-85.5-127T80 576q0-83 31.5-156T197 293q54-54 127-85.5T480 176q83 0 156 31.5T763 293q54 54 85.5 127T880 576q0 83-31.5 156T763 859q-54 54-127 85.5T480 976Zm0-400Zm0 340q142.375 0 241.188-98.812Q820 718.375 820 576t-98.812-241.188Q622.375 236 480 236t-241.188 98.812Q140 433.625 140 576t98.812 241.188Q337.625 916 480 916Z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" id="archivos-svg" viewBox="0 96 960 960" width="48"><path d="M460 976q-91 0-155.5-62.5T240 760V330q0-64 45.5-109T395 176q65 0 110 45t45 110v394q0 38-26 64.5T460 816q-38 0-64-28.5T370 720V328h40v395q0 22 14.5 37.5T460 776q21 0 35.5-15t14.5-36V330q0-48-33.5-81T395 216q-48 0-81.5 33T280 330v432q0 73 53 123.5T460 936q75 0 127.5-51T640 760V328h40v431q0 91-64.5 154T460 976Z"/></svg>
            <div id="enviar-mensaje">
                <input type="text" placeholder="Escribe un mensaje">
                <button id="boton-enviar">Enviar</button>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" id="voz-svg" viewBox="0 96 960 960" width="48"><path d="M480 633q-43 0-72-30.917-29-30.916-29-75.083V276q0-41.667 29.441-70.833Q437.882 176 479.941 176t71.559 29.167Q581 234.333 581 276v251q0 44.167-29 75.083Q523 633 480 633Zm0-228Zm-30 531V800q-106-11-178-89t-72-184h60q0 91 64.288 153t155.5 62Q571 742 635.5 680 700 618 700 527h60q0 106-72 184t-178 89v136h-60Zm30-363q18 0 29.5-13.5T521 527V276q0-17-11.788-28.5Q497.425 236 480 236q-17.425 0-29.212 11.5Q439 259 439 276v251q0 19 11.5 32.5T480 573Z"/></svg>
        </div>
    ';

    $mensajes;

    $resultado_mensajes = obtenerDatos("SELECT * FROM (SELECT * FROM " . $GLOBALS["nombre_tabla_mensajes"] . " WHERE id_grupo = '" . $grupo["id_grupo"] . "' AND visible = '" . true . "' ORDER BY fecha_enviado DESC LIMIT 20) AS ultimos_mensajes ORDER BY fecha_enviado ASC");
    if($resultado_mensajes[0]){ // Hay mensajes en el Grupo
        $mensajes = "";
        while($mensaje = mysqli_fetch_assoc($resultado_mensajes[1])){
            $insertar_contenido = "";
            $es_publicacion_compartida = false;
            if($mensaje["contenido"] == "{Publicacion_compartida}"){ // Es una publicación compartida
                $es_publicacion_compartida = true;

                $resultado_publicacion_compartida = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_publicaciones_compartidas"] . " WHERE id_mensaje = '" . $mensaje["id_mensaje"] . "';");

                include $GLOBALS["root_path"] . "callback/mostrar_publicaciones_compartidas.php";
            }else{
                $insertar_contenido = "<p>" . $mensaje["contenido"] . "</p>";
            }

            $tipo_mensaje = "";
            if($es_publicacion_compartida){
                $tipo_mensaje = "mensaje_publicacion_compartida";
            }

            $mio = "";
            if($mensaje["id_usuario"] == $GLOBALS["id_usuario"]){
                $mio = "mio";
            }
            $mensajes .= str_replace(
                [
                    "{mio}",
                    "{contenido}",
                    "{fecha}",
                    "{tipo_mensaje}",
                    "{id_mensaje}"
                ],
                [
                    $mio,
                    $insertar_contenido,
                    explode(" ", $mensaje["fecha_enviado"])[1],
                    $tipo_mensaje,
                    $mensaje["id_mensaje"]
                ],
                $plantilla_mensaje
            );
        }

    }else{ // Todavía no hay mensajes en el grupo
        $mensajes = "No hay ningún mensaje";
    }

    $plantilla_chat = str_replace(
        [
            "{inyectar_mensajes}",
            "{nombre_grupo}",
            "{ruta_url}",
            "{nombre_img_perfil}"
        ],
        [
            $mensajes,
            $grupo["descripcion_grupo"],
            $GLOBALS["ruta_url"],
            "nofoto.webp"
        ],
        $plantilla_chat
    );

    $devolver["estado"] = 202;
    $devolver["contenido"] = $plantilla_chat;
    $devolver["js"] = "insertar_chat";
    $devolver["parametros_js"] = $plantilla_chat;

    return $devolver;
}

$obtener_grupo = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos. " WHERE id_grupo = '" . $datos[0] . "';");
if($obtener_grupo[0]){ // Existe el grupo
    $grupo = mysqli_fetch_assoc($obtener_grupo[1]);

    $resultado_usuarios_grupos = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos_usuarios . " WHERE id_grupo = '" . $id_grupo . "' AND id_usuario = '" . $id_usuario . "';");
    if($resultado_usuarios_grupos[0]){ // Existe el fulano en el grupo
        $usuario_grupo = mysqli_fetch_assoc($resultado_usuarios_grupos[1]);
        if($usuario_grupo["grupo_bloqueado"] == "1"){ // Está bloqueado
            $devolver["estado"] = 300;
            $devolver["contenido"] = "Bloqueaste este grupo";
        }else{ // No está bloqueado
            $devolver = devolver_chat_grupo($grupo);
        }
    }else{ // El fulano no existe en el grupo
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No perteneces al grupo";
    }
    
}else{ // No existe el grupo
    // entonces $datos[0] = "nombre_arroba_usuario" sin codificar

    if($datos[0][0] == "@"){
        $datos[0] = substr($datos[0], 1);
    }

    $datos[0] = urlencode($datos[0]);

    $resultado_usuario_actual = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $id_usuario . "';");
    if($resultado_usuario_actual[0]){ // Existe este usuario
        $usuario_actual = mysqli_fetch_assoc($resultado_usuario_actual[1]);
        // Ahora vamos a buscar el otro usuario
        $resultado_usuario_buscado = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $datos[0] . "';");
        if($resultado_usuario_buscado[0]){ // El otro usuario existe
            $usuario_buscado = mysqli_fetch_assoc($resultado_usuario_buscado[1]);
            // Hay que comprobar si estos 2 ya tienen un grupo
            $resultado_buscar_grupo = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " WHERE id_grupo IN (SELECT tabla1.id_grupo FROM " . $nombre_tabla_grupos_usuarios . " AS tabla1 JOIN " . $nombre_tabla_grupos_usuarios . " AS tabla2 ON tabla1.id_grupo = tabla2.id_grupo WHERE tabla1.id_usuario = '" . $id_usuario . "' AND tabla2.id_usuario = '" . $usuario_buscado["id_usuario"] . "');");

            $grupo_creado = false;
            if($resultado_buscar_grupo[0]){ // Tienen un grupo en común
                // Hay que mirar si es un grupo con más gente
                while($buscar_grupo = mysqli_fetch_assoc($resultado_buscar_grupo[1])){
                    $resultado_usuarios_grupo = obtenerDatos("SELECT count(id_usuario) as suma FROM " . $nombre_tabla_grupos_usuarios . " WHERE id_grupo = '" . $buscar_grupo["id_grupo"] . "';");
                    $suma_usuarios = establecer_numeros_datos($resultado_usuarios_grupo);

                    if($suma_usuarios == 2){ // Entonces son ellos 2 y ya tienen un grupo en común
                        $resultado_usuario_grupo = obtenerDatos("SELECT * FROM " .$nombre_tabla_grupos_usuarios . " WHERE id_grupo = '" . $buscar_grupo["id_grupo"] . "' AND id_usuario = '" . $id_usuario . "';");
                        $usuario_grupo = mysqli_fetch_assoc($resultado_usuario_grupo[1]);

                        if($usuario_grupo["grupo_bloqueado"] == "1"){ // Tiene el grupo bloqueado
                            $devolver["estado"] = 300;
                            $devolver["contenido"] = "Bloqueaste este Grupo";
                        }else{ // No tiene el grupo bloqueado
                            $devolver = devolver_chat_grupo($buscar_grupo);
                            $devolver["javascript"] = "id = '" . $buscar_grupo["id_grupo"] . "';";
                            $grupo_creado = true;
                        }
                        break;
                    }
                }
            }

            if(!$grupo_creado){ // No tiene grupo en común
                insertarDatos($nombre_tabla_grupos, [urldecode($usuario_actual["nombre_arroba_usuario"]) . " - " . urldecode($usuario_buscado["nombre_arroba_usuario"])],["descripcion_grupo"]);
                $resultado_grupos = obtenerDatos("SELECT * FROM " . $nombre_tabla_grupos . " ORDER BY id_grupo DESC LIMIT 1;");
                $ultimo_grupo = mysqli_fetch_assoc($resultado_grupos[1]);
            
                if(insertarDatos($nombre_tabla_grupos_usuarios, [$id_usuario, $ultimo_grupo["id_grupo"]], ["id_usuario", "id_grupo"])){
                    if(insertarDatos($nombre_tabla_grupos_usuarios, [$usuario_buscado["id_usuario"], $ultimo_grupo["id_grupo"]], ["id_usuario", "id_grupo"])){
                        $devolver = devolver_chat_grupo($ultimo_grupo);
                        $devolver["javascript"] = "id = '" . $ultimo_grupo["id_grupo"] . "';";
                    }else{
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se pudo insertar al otro usuario en el grupo";
                    }
                }else{
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "No se te pudo insertar en el grupo";
                }
            }
            
        }else{ // El otro usuario no existe (Puede ocurrir cunadno el otro usuario está cambiando su @ cuando este estaba buscando a ese usuario)
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha encontrado al usuario \"@" . urldecode($datos[0]) . "\". Vuelve a intentarlo más tarde.";
        }
        
    }else{ // No existe el usuario
        $devolver["estado"] = 300;
        $devolver["contenido"] = "La persona que trataba de crear un grupo no existe";
    }
}

echo json_encode($devolver);

?>