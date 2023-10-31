<?php

$plantilla_seccion = str_replace(
    [
        "{nombre_usuario_arroba}"
    ],
    [
        urldecode($usuario["nombre_arroba_usuario"])
    ],
    $plantilla_seccion
);

?>