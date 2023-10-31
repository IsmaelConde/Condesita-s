<?php
include "comprobar_solicitud_backend.php"; // Comprobamos que recibimos el post de datos
include "../config.php";
include "funciones_bd.php"; // Obtenemos las funciones de Base de Datos

$devolver = [];

/**
 * $datos: (Viene de "comprobar_solicitud_backend.php")
 * [0] = correo
 * [1] = pass
*/

// Lo primero es comprobar la contraseña
$resultados_usuario = obtenerDatos("SELECT * FROM Usuarios WHERE email = '" . $datos[0] . "';");

if($resultados_usuario[0]){
    // Encuentra resultados
    $usuario = mysqli_fetch_assoc($resultados_usuario[1]);
    if($usuario["pass_usuario"] == $datos[1]){
        // La contraseña coincide
        // Indicamos cuando fue la última sesión
        if(conectarQuery("UPDATE " . $nombre_tabla_usuarios . " SET ultimo_inicio = CURRENT_TIMESTAMP, esta_activo = '1', ultima_ip = '" . $ip_cliente . "' WHERE id_usuario = '" . $usuario["id_usuario"] . "';", "Se ha modificado el usuario")){
            // Todo ha fue correctamente
            session_start(); // Iniciamos sesión
            $_SESSION["usuario_id"] = $usuario["id_usuario"];
            $_SESSION["usuario_arroba"] = $usuario["nombre_arroba_usuario"];

            $devolver["estado"] = 202;
            $devolver["contenido"] = "La contraseña es correcta";
            $devolver["js"] = "location.reload()";
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha podido iniciar sesión. Por favor vuelve a intentarlo";
        }
    }else{
        // La contraseña no coincide
        $devolver["estado"] = 300;
        $devolver["contenido"] = "La contraseña no coincide";
    }
}else{
    // No encuentra el usuario
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se encuentra al usuario con correo \"" . $datos[0] . "\".";
}

echo json_encode($devolver);
?>