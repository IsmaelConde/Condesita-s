<?php
include "../config.php";
include $root_path . "backend/funciones_bd.php";

$id_publicacion = $_GET["id"];
if(!empty($id_publicacion)){
    $resultados_img_video = obtenerDatos("SELECT * FROM " . $nombre_tabla_img_video_publicaciones . " WHERE id_img_video = '" . $id_publicacion . "';" );
    if($resultados_img_video[0]){
        $img_video = mysqli_fetch_assoc($resultados_img_video[1]);
        switch($img_video["tipo_archivo"]){
            case "image":
                header("Content-type: image/*");
                break;
            case "video":
                header("Content-type: video/*");
                break;
            default:
                echo "No se reconoce el formato";
        }
        readfile($root_path . "images/publicaciones/" . $img_video["nombre_archivo"]);
    }else{
        die("No hay imagenes con ese id");
    }
}else{
    die("No hay id");
}
?>