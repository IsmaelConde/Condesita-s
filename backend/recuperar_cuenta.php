<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "Amigo, no envias POST";
    die(json_encode($devolver));
}

if(!$_POST["datos"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "Amigo, no me pasas los datos";
    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

function generar_codigo_aleatorio($cantidad_digitos){
    $digitos = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";

    $devover_codigo = "";
    for($i = 0; $i < $cantidad_digitos; $i++){
        $devolver_codigo .= $digitos[rand(0, strlen($digitos) - 1)];
    }

    return $devolver_codigo;
}

$datos = json_decode($_POST["datos"], true);
$modo = $datos["modo"];

switch($modo){
    case "correo":
        $correo = $datos["dato"];

        // Comprobamos en la base de datos este correo
        $resultado_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE email = '" . $correo . "';");
        if($resultado_usuario[0]){ // Se ha encontrado al usuario
            $usuario = mysqli_fetch_assoc($resultado_usuario[1]);

            $codigo_aleatorio = generar_codigo_aleatorio(8);

            if(conectarQuery("UPDATE " . $nombre_tabla_usuarios . " SET codigo_cambiar_contrasena = '" . $codigo_aleatorio . "' WHERE id_usuario = '" . $usuario["id_usuario"] . "';", "Agregar código cambiar contraseña")){ // Se ha insertado el código en la base de datos
                $titulo = "Recuperar Cuenta Condesita's";
                $mensaje = file_get_contents($root_path . "html/plantilla_mensaje_olvidar_contrasena.htm");

                $codigo_html = $codigo_aleatorio;

                $mensaje = str_replace(
                    [
                        "{nombre_usuario}",
                        "{insertar_codigo}"
                    ],
                    [
                        $usuario["nombre_usuario"],
                        $codigo_html
                    ],
                    $mensaje
                );

                $cabeceras = "Content-type: text/html; charset=iso-8859-1\r\nFrom: " . $from_email . "\r\nReply-To: " . $reply_to_email . "\r\nX-Mailer: PHP/" . phpversion();

                if(mail($correo, $titulo, $mensaje, $cabeceras)){ // Se ha podido enviar el correo
                    $devolver["estado"] = 202;
                    $devolver["contneido"] = "Se ha enviado el código";
                    $devolver["js"] = "
                    let donde = document.getElementById(\"email\").parentElement,

                    label_codigo = document.createElement(\"label\"),
                    input_codigo = document.createElement(\"input\"),
                    
                    label_pass = document.createElement(\"label\"),
                    input_pass = document.createElement(\"input\"),
                    
                    label_pass_confirmar = document.createElement(\"label\"),
                    input_pass_confirmar = document.createElement(\"input\");

                    label_codigo.innerHTML = \"Código\";
                    input_codigo.placeholder = \"Código recibido\";
                    input_codigo.id = \"codigo_recuperar\";

                    label_pass.innerHTML = \"Nueva Contraseña\";
                    input_pass.placeholder = \"Escribe la nueva contraseña\";
                    input_pass.id = \"contra_recuperar\";
                    input_pass.type = \"password\";

                    label_pass_confirmar.innerHTML = \"Confirma la Nueva Contraseña\";
                    input_pass_confirmar.placeholder = \"Confirma la nueva contraseña\";
                    input_pass_confirmar.id = \"confirmar_contra_recuperar\";
                    input_pass_confirmar.type = \"password\";

                    donde.insertBefore(label_codigo, donde.lastElementChild);
                    donde.insertBefore(input_codigo, donde.lastElementChild);
                    donde.insertBefore(label_pass, donde.lastElementChild);
                    donde.insertBefore(input_pass, donde.lastElementChild);
                    donde.insertBefore(label_pass_confirmar, donde.lastElementChild);
                    donde.insertBefore(input_pass_confirmar, donde.lastElementChild);

                    sistema_arrancado = true;
                    document.getElementById(\"boton-recuperar_cuenta\").innerHTML = \"Cambiar Contraseña\";
                    ";

                    mail($reply_to_email, "Solicitud Recuperar Cuenta", "El usuario \"" . $usuario["nombre_usuario"] . "\" ha solicitado cambiar la contraseña.");
                }else{ // No se ha podido enviar el correo
                    if(conectarQuery("UPDATE " . $nombre_tabla_usuarios . " SET codigo_cambiar_contrasena = '' WHERE id_usuario = '" . $usuario["id_usuario"] . "';", "Se ha quitado el código de recuperar contraseña")){ // Se ha podido eliminar
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se ha podido enviar el correo, por favor vuelve a intentarlo más tarde";
                    }else{ // No se ha podido eliminar
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se ha podido enviar el correo ni cambiar el código a nulo";
                    }
                }
            }else{ // No se pudo insertar el código en la base de datos
                $devolver["estado"] = 300;
                $devolver["contenido"] = "No se ha podido subir el código al servidor";
            }
        }else{ // No se encuentra al usuario
            $devolver["estado"] = 300;
            $devolver["contenido"] = "El correo '" . $correo . "' no está en la base de datos";
        }
        
        break;
    case "cambiar_pass":
        $contenidos = $datos["dato"];
        /**
         * contenidos:
         * [0] = email
         * [1] = codigo
         * [2] = contrasena
         * [3] = confirmar contraseña
         */
        $email = $contenidos[0];
        $codigo = $contenidos[1];
        $contrasena = $contenidos[2];
        $confirmar_contrasena = $contenidos[3];

        // Comprobamos si existe el usuario
        $resultado_usuario = obtenerDatos("SELECT * FROM " . $nombre_tabla_usuarios . " WHERE email = '" . $email . "';");
        if($resultado_usuario[0]){ // Existe ese usuario con ese email
            $usuario = mysqli_fetch_assoc($resultado_usuario[1]);

            // Comprobamos las contraseñas
            if($contrasena != $confirmar_contrasena){ // Las contraseñas no coincide
                $devolver["estado"] = 300;
                $devolver["contenido"] = "Las contraseñas no coinciden";
            }else{ // Las contraseñas coinciden
                // Comprobamos el código
                if($usuario["codigo_cambiar_contrasena"] == $codigo){ // El código es correcto
                    // Al estar todo OK, modificamos la base de datos
                    if(conectarQuery("UPDATE " . $nombre_tabla_usuarios . " SET pass_usuario = '" . $contrasena . "', codigo_cambiar_contrasena = '' WHERE id_usuario = '" . $usuario["id_usuario"] . "';", "Se ha modificado la contraseña del usuario \"" . $usuario["id_usuario"] . "\"")){ // Se ha cambiado todo bien
                        $devolver["estado"] = 200;
                        $devolver["js"] = "window.location = \"" . $ruta_url . "\"";
                    }else{ // No se ha podido modificar la contrasena
                        $devolver["estado"] = 300;
                        $devolver["contenido"] = "No se ha podido modificar la contraseña";
                    }
                }else{ // El código no es correcto
                    $devolver["estado"] = 300;
                    $devolver["contenido"] = "El código no es correcto";
                }
            }
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No existe el correo \"" . $email . "\".";
        }
        break;
    default:
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se sabe que modo es este";
}

echo json_encode($devolver);

?>