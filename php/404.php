<?php
/**
 * Script para controlar 404.htm
*/

$plantilla_seccion = str_replace(
    [
        "{ir_atras_url}",
        "{ir_menu_url}"
    ],
    [
        $llamada_desde,
        $ruta_url
    ],
    $plantilla_seccion
);

?>