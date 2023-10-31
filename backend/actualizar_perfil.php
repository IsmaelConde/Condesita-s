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
    $devolver["contenido"] = "No se ha iniciado sesión";
    die(json_encode($devolver));
}

include "../config.php";
include $root_path . "backend/funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];

$img_perfil = $_FILES["img_perfil"];
$img_portada = $_FILES["img_portada"];
$nombre_usuario = $_POST["nombre_usuario"];
$nombre_arroba_usuario = $_POST["nombre_arroba_usuario"];
$descripcion_perfil = $_POST["descripcion"];

// Comprobar si el nombre arroba lleva el @ delante
if($nombre_arroba_usuario[0] == "@"){
    //Tiene el @
    $nombre_arroba_usuario = substr($nombre_arroba_usuario, 1);
}

$nombre_arroba_usuario = urlencode(str_replace(" ", "_", $nombre_arroba_usuario));

if($nombre_arroba_usuario == ""){ // Está vacio
    $devolver["estado"] = 300;
    $devolver["contenido"] = "El nombre Arroba no puede quedar vacío";
}else{
    $resultado_usuarios = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario != '" . $id_usuario . "' AND nombre_arroba_usuario = '@" . $nombre_arroba_usuario . "';");
    if($resultado_usuarios[0]){ // Existe
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se pudo actualizar el perfil, porque el nombre arroba \"@". urldecode($nombre_arroba_usuario) . "\" ya existe en otro usuario";
    }else{ // No existe
        $obtener_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE id_usuario = '" . $id_usuario . "';");
        $usuario = mysqli_fetch_assoc($obtener_usuario[1]);

        $sql_actualizar_perfil = "UPDATE " . $nombre_tabla_usuarios . " SET ";

        comprobar_files($img_perfil, "perfil", $usuario);
        comprobar_files($img_portada, "portada", $usuario);

        $sql_actualizar_perfil .= " nombre_usuario = '" . $nombre_usuario . "', nombre_arroba_usuario = '@" . $nombre_arroba_usuario . "', descripcion_perfil = '" . $descripcion_perfil . "' WHERE id_usuario = '" . $id_usuario . "';";

        if(conectarQuery($sql_actualizar_perfil, "Se ha modificado los datos de usuario")){
            $seccion_llamada = explode("/", $llamada_desde)[3];

            if($seccion_llamada == "perfil"){ // Será el lugar donde queremos que se actualize el perfil del navegador
                $devolver["js"] = '
                    location.replace("' . $ruta_url . $seccion_llamada . '/@' . $nombre_arroba_usuario . '");
                ';
            }else{
                $devolver["js"] = "recargarPagina()";
            }

            $devolver["estado"] = 202;
            $devolver["contenido"] = "Todo ok";
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha podido actualizar la base de datos con los nuevos datos";
        }        
    }
}

function comprobar_files($file, $tipo, $usuario){
    if(!empty($file)){  // Si no está vacío
         // Comprobamos si existe la ruta, si no la creamos
         if(!file_exists($GLOBALS["root_path"] . $ruta_img)){
            if(!mkdir($ruta_img, 0770)){
                $devolver["estado"] = 300;
                $devolver["contenido"] = "No se ha podido crear el archivo";

                die(json_encode($devolver));
            }
        }

        $nombre_guardar = $usuario["id_usuario"] . "_" . basename($file["name"]);

        $contenido_archivo = file_get_contents($file["tmp_name"]);
        $ruta_img;
        $tabla_sql;
        switch($tipo){
            case "perfil":
                $ruta_img = "images/img_foto_perfil/";
                $tabla_sql = "img_perfil";
                break;
            case "portada":
                $ruta_img = "images/img_foto_portada/";
                $tabla_sql = "img_portada";
                break;
            default:
                $devolver["estado"] = 300;
                $devolver["contenido"] = "Error a la hora de comprobar el archivo";
                die(json_encode($devolver));
        }

       
        if(move_uploaded_file($file["tmp_name"], $GLOBALS["root_path"] . $ruta_img . $nombre_guardar)){
            $GLOBALS["sql_actualizar_perfil"] .= $tabla_sql . " = '" . $usuario["id_usuario"] . "_" . basename($file["name"]) . "',";
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "Error al subir el archivo";
            die(json_encode($devolver));
        }

    }
}

echo json_encode($devolver);

?>