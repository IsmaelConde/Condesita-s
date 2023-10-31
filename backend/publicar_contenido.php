<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe el POST";
    die(json_encode($devolver));
}

if(!$_POST["datos"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe el parámetro";

    die(json_encode($devolver));
}

session_start(); // Iniciamos sesión

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No tienes la sesión iniciada";

    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];

$datos = json_decode($_POST["datos"], true);
$texto = $datos["texto"];
$descripcion = $datos["descripcion"];
$visibilidad = $datos["visibilidad"];

$alt_archivos = $_POST["alt_archivos"];
$archivos = $_FILES["file"];

$publicacion_visible = 1;
if($visibilidad != "true"){
    $publicacion_visible = 0;
}

if($texto != "" || !empty($archivos)){ // Hay contenido
    // Creamos la publicación
    $todo_ok = true;
    $array_fallos = array();
    if(conectarQuery("INSERT IGNORE INTO " . $nombre_tabla_publicaciones . " (id_usuario, descripcion, visibilidad) VALUES ('" . $id_usuario . "', '" . $descripcion . "', " . $publicacion_visible . ");", "Subido 1 publicación")){ // Creamos una publicación en la base de datos
        // Ahora vamos a obtener el identificador de la públicación
        $busqueda_publicacion = obtenerDatos("SELECT id_publicacion as id FROM " . $nombre_tabla_publicaciones . " WHERE id_usuario = '" . $id_usuario . "' ORDER BY id_publicacion DESC LIMIT 1");
        $publicacion = mysqli_fetch_assoc($busqueda_publicacion[1]);
        $id_publicacion = $publicacion["id"];

        // Vamos a comprobar que el texto tiene contenido para crear la tabla de texto
        if($texto != ""){ // En caso de que no esté vacio
            if(!insertarDatos($nombre_tabla_texto_publicaciones, [$id_publicacion, $texto], ["id_publicacion", "contenido"])){
                array_push($array_fallos, "No se ha podido insertar el texto \"" . $texto . "\" en la base de Datos.");
                $todo_ok = false;
            }else{
                $busqueda_texto = obtenerDatos("SELECT id_texto FROM " . $nombre_tabla_texto_publicaciones . " WHERE id_publicacion = '" . $id_publicacion . "';");
                $id_texto = mysqli_fetch_assoc($busqueda_texto[1])["id_texto"]; // Guardamos el id del texto para que en caso de error lo podamos borrar
            }
        }

        if(!empty($archivos)){ // Hay archivos
            $carpeta_insertar = $root_path . "images/publicaciones"; // Buscamos la carpeta donde se van a insertar los archivos
            if(!file_exists($carpeta_insertar)){ // En caso de que no exista la carpeta de las publicaciones
                if(!mkdir($carpeta_insertar, 0770)){// La creamos
                    // En caso de que no se cree
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "No se ha podido crear el fichero donde se guardan las publicaciones";

                    die(json_encode($devolver));
                } 
            }

            foreach($archivos["name"] as $indice => $nombre_archivo){
                $tipo_archivo = $archivos['type'][$indice];
                $tamano_archivo = $archivos['size'][$indice];
                $temp_archivo = $archivos['tmp_name'][$indice];
                $error_archivo = $archivos['error'][$indice];

                // Miramos si hay algún error en la imagen
                if($error_archivo == UPLOAD_ERR_OK){ // No hay fallos
                    $contenido_archivo = file_get_contents($temp_archivo); // Obtenemos el contenido del archivo

                    $nombre_guardar = $id_publicacion . "_" . basename($nombre_archivo); // Guardamos el archivo como idPublicacion_nombreArchivo
                    
                    if(!file_put_contents($carpeta_insertar . "/" . $nombre_guardar, $contenido_archivo)){ // Insertamos el contenido en la carpeta
                        // En caso de que no se haya insertado
                        $todo_ok = false;
                        array_push($array_fallos, "No se ha podido insertar el archivo \"" . $nombre_archivo . "\" en la carpeta de las publicaciones");
                    }else{ // En caso de que se haya insertado sin problemas
                        if(!insertarDatos($nombre_tabla_img_video_publicaciones, [$id_publicacion, $nombre_guardar, explode("/", $tipo_archivo)[0], $alt_archivos[$indice]], ["id_publicacion", "nombre_archivo", "tipo_archivo", "alt_archivo"])){ // Lo insertamos en la base de datos                            $todo_ok = false;
                            array_push($array_fallos, "No se ha podido subir el archivo \"" . $nombre_archivo . "\" a la base de datos");
                        } 
                    }
                }else{ // Hay fallos
                    $error;
                    switch($error_archivo){
                        case UPLOAD_ERR_INI_SIZE:
                            $error = "Se ha excedido el máximo de tamaño del archivo";
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $error = "Se está intentando subir más archivos de lo permitido";
                            break;
                        default:
                            $error = "Error general";
                    }
                    array_push($array_fallos, "Error al subir el archivo \"" . $nombre_archivo . "\": " . $error . ".");
                    $todo_ok = false;
                }
            }
        }

        if($todo_ok){
            $devolver["estado"] = 202;
            $devolver["contenido"] = "Se ha subido la publicación exitosamente";
            $devolver["js"] = "publicacion_subida";
            $devolver["parametros_js"] = $id_publicacion;
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "";
            for($i = 0; $i < count($array_fallos); $i++){
                $devolver["contenido"] .= $array_fallos[$i];
            }
            

            if(conectarQuery("DELETE FROM " . $nombre_tabla_publicaciones . " WHERE id_publicacion = '" . $id_publicacion . "';", "Se ha borrado 1 dato de la tabla publicaciones")){ // Borramos la tabla publicaciones ya que hay fallos en los contenidos
                $devolver["contenido"] .= "Se ha borrado la publicación por errores en el sistema";
            } 
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha podido subir la publicación. Por favor vuelve a intentarlo";
    }
}else{ // No hay contenidos a publicar
    $devolver["estado"] = 300;
    $devolver["contenido"] = "Tienes que insertar texto, imagenes/videos o ambas";
}

echo json_encode($devolver); // Devolvemos lo que hay que hacer

?>