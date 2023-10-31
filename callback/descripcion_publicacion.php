<?php
    $descripcion_publicacion = "";
    if($publicacion["descripcion"] != ""){ // No Está vacío
        $descripcion_publicacion = str_replace(
            [
                "{descripcion_publicacion}"
            ],
            [
                $publicacion["descripcion"]
            ],
            $plantilla_descripcion
        );
    }
?>