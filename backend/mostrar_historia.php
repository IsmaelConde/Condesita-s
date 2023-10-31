<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No contiene el POST";
    die(json_encode($devolver));
}

if(!$_POST["usuario_historia"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No pasas el párametro";
    die(json_encode($devolver));
}

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No tienes inicida la sesión";
    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

$id_usuario = $_SESSION["usuario_id"];
$ver_historia_de = $_POST["usuario_historia"];

if($ver_historia_de[0] == "@"){ // Si contiene un @ el nombre
    $ver_historia_de =  substr($ver_historia_de, 1); // Se lo quitamos
}

$ver_historia_de = urlencode($ver_historia_de); // Lo codificamos a url

$resultado_usuario_historia = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $ver_historia_de . "';");
if($resultado_usuario_historia[0]){ // Existe el usuario con esa historia
    $usuario_historia = mysqli_fetch_assoc($resultado_usuario_historia[1]);

    /* =================================================================
            GUARDAMOS DATOS EN EL NAVEGADOR
    ================================================================ */
    if(!isset($_SESSION["historia_" . $ver_historia_de]) || empty($_SESSION["historia_" . $ver_historia_de])){

        $_SESSION["historia_" . $ver_historia_de] = [];
        $_SESSION["historia_" . $ver_historia_de]["pagina_actual"] = 0;
    }

    $numero_paginas = establecer_numeros_datos(obtenerDatos("SELECT count(id_historia) as suma FROM " . $nombre_tabla_historias . " WHERE id_usuario = '" . $usuario_historia["id_usuario"] . "' AND fecha_subido > (SELECT TIMESTAMPADD(HOUR,-24,CURRENT_TIMESTAMP));"));
    /* =======================================================================
            FIN GUARDAR DATOS EN EL NAVEGADOR
    ========================================================================= */

    if($_SESSION["historia_". $ver_historia_de]["pagina_actual"] >= $numero_paginas){ // Está en una página superior
        $devolver["estado"] = 300;
        $devolver["contenido"] = "Estás en una página más de la que tiene el usuario";

        $_SESSION["historia_" . $ver_historia_de]["pagina_actual"] = 0;

        // PASAR AL SIGUIENTE USUARIO CON HISTORIA
    }

    $resultado_historia_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_historias . " WHERE id_usuario = '" . $usuario_historia["id_usuario"] . "' AND fecha_subido > (SELECT TIMESTAMPADD(HOUR,-24,CURRENT_TIMESTAMP)) LIMIT " . $_SESSION["historia_" . $ver_historia_de]["pagina_actual"]  . ",1;");
    if($resultado_historia_usuario[0]){ // Si este usuario tiene historias y dentro del rango de 24 horas
        $historia_usuario = mysqli_fetch_assoc($resultado_historia_usuario[1]);

        $resultado_subido_hace = obtenerDatos("SELECT TIMEDIFF(CURRENT_TIMESTAMP, '" . $historia_usuario["fecha_subido"] . "') AS subido_hace_cuanto;");
        $subido_hace = mysqli_fetch_assoc($resultado_subido_hace[1])["subido_hace_cuanto"];

        $separar_subido_hace = explode(":", $subido_hace);

        $escoger = 0;
        do{
            $tiempo_subido = $separar_subido_hace[$escoger];
            $escoger++;
        }while($tiempo_subido == "00");

        // Si escoger = 1 => horas;
        //            = 2 => minutos;
        //            = 3 => segundos;

        $insertar_tiempo;
        switch($escoger){
            case 1:
                $insertar_tiempo = $tiempo_subido . " h";
                break;
            case 2:
                $insertar_tiempo = $tiempo_subido . " m";
                break;
            case 3:
                $insertar_tiempo = $tiempo_subido . " s";
                break;
            default: // Pues no se que erro puede dar la variable, pero por si acaso
                $insertar_tiempo = $tiempo_subido;
        }

        $base_contenido = "<p class=\"mostrar_tiempo\">" . $insertar_tiempo . "</p><img src=\"" . $historia_usuario["contenido"] . "\" class=\"img_historia\">";
        
        $devolver["estado"] = 202;
        $devolver["contenido"] = $base_contenido;
        $devolver["js"] = "ver_historia";
        $devolver["parametros_js"] = $base_contenido;

        $_SESSION["historia_" . $ver_historia_de]["pagina_actual"]++;
    }else{ // En caso de que el usuario no tenga historias o no tenga historias en el rango de 24 horas
        $devolver["estado"] = 300;
        $devolver["contenido"] = "El usuario '@" . urldecode($ver_historia_de) . "' no tiene ninguna historia que ver o ya han pasado 24 horas desde su última publicación";
    }
    
}else{ // No existe el usuario de esa historia
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No existe el usuario '@" . urldecode($ver_historia_de) . "'.";
}

echo json_encode($devolver);

?>