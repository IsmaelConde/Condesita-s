<?php
$resultado_texto = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_texto_publicaciones"] . " WHERE id_publicacion = '" . $id_publicacion . "';");
$resultado_img_video = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_img_video_publicaciones"] . " WHERE id_publicacion = '" . $id_publicacion . "';");

$insertar_texto = "";
if($resultado_texto[0]){
    // Existe texto
    $texto = mysqli_fetch_assoc($resultado_texto[1]);
    $insertar_texto = "
        <p class=\"texto_publicacion\">" . $texto["contenido"] . "</p>
    ";
}

$insertar_imgs_videos = "";
if($resultado_img_video[0]){
    // Existe img o vídeo
    $resultado_num_img_video = obtenerDatos("SELECT count(id_img_video) as suma FROM " . $GLOBALS["nombre_tabla_img_video_publicaciones"] . " WHERE id_publicacion = '" . $id_publicacion . "';");

    $insertar_img_video = "";
    while($img_video = mysqli_fetch_assoc($resultado_img_video[1])){
        $alt_contenido = "Contenido publicación";
        if($img_video["alt_archivo"] != ""){
            $alt_contenido = $img_video["alt_archivo"];
        }
        switch($img_video["tipo_archivo"]){
            case "image":
                $insertar_img_video .= "<img class=\"imagen_publicacion\" title=\"" . $alt_contenido . "\" alt=\"" . $alt_contenido . "\" src=\"" . $GLOBALS["ruta_url"] . "callback/leer_fichero.php?id=" . $img_video["id_img_video"] . "\">";
                break;
            case "video":
                $insertar_img_video .= "<video preload=\"metadata\" class=\"video_publicacion\" alt=\"". $alt_contenido . "\" src=\"" . $ruta_url . "callback/leer_fichero.php?id=" . $img_video["id_img_video"] . "\" controls></video>";
                break;
            default:
                
        }
    }

    $num_img_video = establecer_numeros_datos($resultado_num_img_video);

    if($num_img_video > 1){ // Si hay más de 1 imagen o video, crearemos un div para que sea tipo flex
        $insertar_imgs_videos = "<div class=\"mostrar_imagenes_videos_fila\">" . $insertar_img_video . "</div>";
    }else{
        $insertar_imgs_videos = $insertar_img_video;
    }
    
    
}

$insertar_contenidos = "<div class=\"base_contenidos\">" . $insertar_texto . $insertar_imgs_videos . "</div>";

?>