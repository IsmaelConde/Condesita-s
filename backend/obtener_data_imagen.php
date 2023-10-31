<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recibe ningún post";

    die(json_encode($devolver));
}

if(!$_POST["datos"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No recobe el parámetro correcto";

    die(json_encode($devolver));
}

include "../config.php";

$datos = json_decode($_POST["datos"], true);

$nombre_archivo = $datos["nombre_img"];
$nombre_subcarpeta = $datos["que_es"];
$querySelector = $datos["queryDom"];

$ruta_imagen = $root_path . "images/" . $nombre_subcarpeta . "/" . $nombre_archivo;

$datos_imagen = file_get_contents($ruta_imagen);
$base64_imagen = base64_encode($datos_imagen);
$tipo_imagen = mime_content_type($ruta_imagen);

$devolver["estado"] = 202;
$devolver["js"] = '
    let donde = document.querySelector("' . $querySelector . '"),
    string_binario_base64 = atob("' . $base64_imagen . '"),
    largo_binario = string_binario_base64.length;

    /*
    let bytes_binario = new Unit8Array(largo_binario);
    */
    let bytes_binario = new Array(largo_binario);
    for(let i = 0; i < largo_binario; i++){
        bytes_binario[i] = string_binario_base64.charCodeAt(i);
    }
    

    let byteArray = new Uint8Array(bytes_binario)
    blob = new Blob([byteArray], {type:"images/*"}),
    blobUrl = URL.createObjectURL(blob);

    donde.src = blobUrl;

    console.log(blobUrl, blob, byteArray, string_binario_base64);

    donde.addEventListener("load", () => {
        URL.revokeObjectURL(blobUrl);
        console.log("Adios");
    }, false);

    
';

echo json_encode($devolver);

?>