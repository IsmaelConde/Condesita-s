<?php
include "comprobar_solicitud_backend.php"; // Comprobamos que recibimos el post de datos
include "../config.php";
include $root_path . "backend/funciones_bd.php"; // Obtenemos las funciones de Base de Datos

$devolver = [];

/**
 * $datos: (Viene de "comprobar_solicitud_backend.php")
 * [0] = nombre usuario
 * [1] = email
 * [2] = contraseña
 * [3] = confirmar contraseá
 * [4] = que
*/

$nombre_usuario = $datos["nombre"];
$correo = $datos["email"];
$metodo = $datos["metodo"];

switch($metodo){
    case "generar_codigo":
        function generar_codigo_aleatorio($cantidad_digitos){
            $digitos = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
        
            $devover_codigo = "";
            for($i = 0; $i < $cantidad_digitos; $i++){
                $devolver_codigo .= $digitos[rand(0, strlen($digitos) - 1)];
            }
        
            return $devolver_codigo;
        }

        $resultado_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE email = '" . $correo . "';");
        if(!$resultado_usuario[0]){ // Todavía no se ha creado este usuario con este correo
            $codigo_aleatorio = generar_codigo_aleatorio(9);

            // Vamos a mirar si ya existe este correo en usuarios temporales
            $resultado_correo_temporal = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios_temporales . " WHERE correo = '" . $correo . "';");

            $se_ha_ejecutado_correctamente = false;

            if($resultado_correo_temporal[0]){ // Si existe
                $correo_codigo_temporal = mysqli_fetch_assoc($resultado_correo_temporal[1]);

                // Lo modificamos
                if(conectarQuery("UPDATE " . $nombre_tabla_usuarios_temporales . " SET codigo = '" . $codigo_aleatorio . "' WHERE correo = '" . $correo . "';", "Se ha actualizado")){ // Se ha modificado
                    $se_ha_ejecutado_correctamente = true;
                }else{ // no se ha modificado
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "No se ha podido actualizar el código en la base de datos";
                }
            }else{ // No existe
                if(insertarDatos($nombre_tabla_usuarios_temporales, [$correo, $codigo_aleatorio])){ // Se ha podido insertar el usuario a la base de datos temporal
                    $se_ha_ejecutado_correctamente = true;
                }else{ // No se ha podido insertar al usuario en la base de datos temporal
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "No se ha podido insertar al usuario. Por favor vuelve a intentarlo más tarde";
                }
            }

            if($se_ha_ejecutado_correctamente){ // Si todo ha ido bien
                // Pues ahora le enviamos un email
                $titulo = "Crear cuenta Condesita's";
                $mensaje = file_get_contents($root_path . "html/plantilla_mensaje_crear_cuenta.htm");

                $codigo_html = $codigo_aleatorio;

                $mensaje = str_replace(
                    [
                        "{nombre_usuario}",
                        "{insertar_codigo}"
                    ],
                    [
                        $nombre_usuario,
                        $codigo_html
                    ],
                    $mensaje
                );
                $cabeceras = "Content-type: text/html; charset=iso-8859-1\r\nFrom: " . $from_email . "\r\nReply-To: " . $reply_to_email . "\r\nX-Mailer: PHP/" . phpversion();

                if(mail($correo, $titulo, $mensaje, $cabeceras)){ // Se ha podido enviar el mensaje
                    $devolver["estado"] = 202;
                    $devolver["contenido"] = "Se ha enviado el código";
                    $devolver["js"] = "
                        let donde = document.querySelector(\"form\"),

                        label_codigo = document.createElement(\"label\"),
                        input_codigo = document.createElement(\"input\"),

                        label_contrasena = document.createElement(\"label\"),
                        input_pass = document.createElement(\"input\"),

                        label_pass_confirmar = document.createElement(\"label\"),
                        input_pass_confirmar = document.createElement(\"input\");

                        label_codigo.innerHTML = \"Código\";
                        label_codigo.setAttribute(\"for\", \"codigo_email\");

                        input_codigo.type = \"text\";
                        input_codigo.id = \"codigo_email\";
                        input_codigo.placeholder = \"Mira tu correo\";

                        label_contrasena.innerHTML = \"Contraseña\";
                        label_contrasena.setAttribute(\"for\", \"password\");

                        input_pass.type = \"password\";
                        input_pass.setAttribute(\"name\", \"password\");
                        input_pass.id = \"password\";

                        label_pass_confirmar.innerHTML = \"Confirmar Contraseña\";
                        label_pass_confirmar.setAttribute(\"for\", \"confirmar-password\");

                        input_pass_confirmar.type = \"password\";
                        input_pass_confirmar.setAttribute(\"name\", \"confirmar-password\");
                        input_pass_confirmar.id = \"confirmar-password\";

                        donde.insertBefore(label_codigo, donde.lastElementChild);
                        donde.insertBefore(input_codigo, donde.lastElementChild);
                        donde.insertBefore(label_contrasena, donde.lastElementChild);
                        donde.insertBefore(input_pass, donde.lastElementChild);
                        donde.insertBefore(label_pass_confirmar, donde.lastElementChild);
                        donde.insertBefore(input_pass_confirmar, donde.lastElementChild);

                        document.getElementById(\"boton-crear_cuenta\").innerHTML = \"Crear Cuenta\";
                        codigo_enviado = true;
                    ";

                    mail($reply_to_email, "Solicitud Crear Cuenta", "El usuario \"" . $nombre_usuario . "\" ha solicitado crear una cuenta en Condesita's.");
                }else{ // No se ha podido enviar el mensaje
                    if(conectarQuery("DELETE FROM " . $nombre_tabla_usuarios_temporales . " WHERE correo = '" . $correo . "' AND codigo = '" . $codigo_aleatorio . "';")){
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se ha podido enviar el código para que puedas crear la cuenta, por favor vuelve a intentarlo más tarde";
                    }else{ // No se ha podido eliminar la nueva tabla
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se ha podido enviar el código ni cambiarlo la base de datos. Por favor vuelve a intentarlo más tarde.";
                    }
                }
            }
        }else{
            // El email ya existe
            $devolver["estado"] = 300;
            $devolver["contenido"] = "El correo \"" . $correo . "\" ya tiene creada una cuenta.";
        }
        break;
    case "crear_cuenta";
        $contrasena = $datos["pass"];
        $confirmar_contrasena = $datos["confirmar_pass"];
        $codigo = $datos["codigo"];

        // Vamos a comprobar que las contraseñas coincide
        if($contrasena != $confirmar_contrasena){ // No coinciden
            $devolver["estado"] = 300;
            $devolver["contenido"] = "Las contraseñas no coinciden";
        }else{ // Las contraseñas coinciden
            // Comprobar el email
            $resultado_correo = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios_temporales . " WHERE correo = '" . $correo . "';");
            if($resultado_correo[0]){ // Se ha encontrado el correo el los usuarios temporales
                $correo_codigo = mysqli_fetch_assoc($resultado_correo[1]);
                
                // Comprobamos el codigo
                if($correo_codigo["codigo"] == $codigo){ // El código coincide
                    // Entonces creamos al usuario
                    $nombre_arroba = urlencode(str_replace(" ", "_", $nombre_usuario)); // Almacenamos el nombre en una variable quitandole todos los espacios
        
                    do{
                        $resultado_nombre_arroba = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE nombre_arroba_usuario = '@" . $nombre_arroba . "';"); // Hacer la sentencia
                        if($resultado_nombre_arroba[0]){ // Si hay resultado
                            $nombre_arroba .= rand(0, 9); // Le agregamos un número aleatorio
                        }
                    }while($resultado_nombre_arroba[0]); // Mientras siga existiendo ese nombre_arroba, ejecutamos esta funciçn

                    if(insertarDatos($nombre_tabla_usuarios, [$nombre_usuario, $correo, $contrasena, "@".$nombre_arroba], ["nombre_usuario", "email", "pass_usuario", "nombre_arroba_usuario"])){
                        $devolver["estado"] = 202;
                        $devolver["contenido"] = "Se ha creado la cuenta";
                        $devolver["js"] = "window.location = \"" . $ruta_url . "\"";

                        // Borramos de la tabla de usuarios temporales a este individuo
                        conectarQuery("DELETE FROM " . $nombre_tabla_usuarios_temporales . " WHERE correo = '" . $correo . "';", "Borrado el usuario \"" . $correo . "\" de la tabla \"" . $nombre_tabla_usuarios_temporales . "\".");
                    }else{
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se ha podido crear el usuario \"" . $nombre_usuario . "\"";
                    }
                }else{ // El código no coincide
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "Ese no es el código amigo";
                }
            }else{ // No se ha encontrado el correo en los usuarios temporales
                $devolver["estado"] = 300;
                $devolver["contenido"] = "Parece que \"" . $correo . "\" no ha sido el que ha querido crear una cuenta";
            }
        }
        break;
}

echo json_encode($devolver);
?>