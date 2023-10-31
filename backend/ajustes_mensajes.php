<?php

$devolver = [];

if(!$_POST){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No se recibe el POST";

    die(json_encode($devolver));
}

if(!$_POST["datos"]){
    $devolver["estado"] = 404;
    $devolver["contenido"] = "No se encuentra el dato";

    die(json_encode($devolver));
}

session_start();

if(!$_SESSION["usuario_id"]){
    $devolver["estado"] = 300;
    $devolver["contenido"] = "No estás logueado";

    die(json_encode($devolver));
}

include "../config.php";
include "funciones_bd.php";

$datos = json_decode($_POST["datos"], true);
$id_usuario = $_SESSION["usuario_id"];
$tipo = $datos["tipo"];

switch($tipo){
    case "bloquear_grupo":
        $id_grupo = $datos["id_grupo"];
        $devolver = comprobar_grupo($id_grupo);
        if($devolver["estado"] == 202){ // Se ha encontrado el grupo
            $devolver = bloquear_grupo($devolver["grupo"], $id_usuario);
        }
        break;
    case "devolver_opciones_mensaje":
        $id_mensaje = $datos["id_mensaje"];
        $devolver = comprobar_mensaje($id_mensaje);
        if($devolver["estado"] == 202){ // Existe el mensaje
            $devolver = opciones_mensaje($devolver["mensaje"], $id_usuario);
        }
        break;
    case "borrar_mensaje":
        $id_mensaje = $datos["id_mensaje"];
        $devolver = comprobar_mensaje($id_mensaje);
        if($devolver["estado"] == 202){ // Existe el mensaje
            $devolver = borrar_mensaje($devolver["mensaje"], $id_usuario);
        }
        break;
    case "informar_mensaje":
        $devolver["estado"] = 300;
        $devolver["contenido"] = "Se informará";
        break;
    default:
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se sabe que es lo que quiere hacer";
}

function borrar_mensaje($mensaje, $id_usuario){
    $devolver = [];
    // Miramos si el mensaje es del usuario
    if(es_mensaje_usuario_actual($mensaje, $id_usuario)){
        // Ejecutamos la query
        if(conectarQuery("UPDATE " . $GLOBALS["nombre_tabla_mensajes"] . " SET visible = '" . false . "' WHERE id_mensaje = '" . $mensaje["id_mensaje"] . "';", "No ver mensaje")){ // Ya no es visible
            $devolver["estado"] = 202;
            $devolver["js"] = "ultimo_ajuste_mensaje.remove()";
        }else{ // No se pudo hacer invisible
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha podido eliminar el mensaje. Vuelve a intentarlo más tarde.";
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "¿Como pretendes borrar un mensaje que no es tuyo?";
    }

    return $devolver;
}

function es_mensaje_usuario_actual($mensaje, $id_usuario){
    $devolver = false;
    if($mensaje["id_usuario"] == $id_usuario){
        $devolver = true;
    }

    return $devolver;
}

function opciones_mensaje($mensaje, $id_usuario){
    $devolver = [];

    $apartados = [];
    
    $funcion = "(e) => {
        let formData = new FormData();
        formData.append(\"datos\", JSON.stringify({\"tipo\":\"{tipo_funcion}\", \"id_mensaje\":" . $mensaje["id_mensaje"] . "}));
        llamadaAjax_formData(\"ajustes_mensajes.php\", formData);
    }";
    // Comprobamos si el mensaje es del usuario
    if(es_mensaje_usuario_actual($mensaje, $id_usuario)){ // El mensaje es del usuario
        $apartados = [
            [
                "texto" => "Borrar mensaje",
                "funcion" => "borrar_mensaje"
            ]
        ];
    }else{ // El mensaje no es del usuario
        $apartados = [
            [
                "texto" => "Informar",
                "funcion" => "informar_mensaje"
            ]
        ];
    }

    $devolver["estado"] = 202;
    $devolver["js"] = "
    let donde = buscar_botones_por_id(\"ajustes_mensaje\"),
    nuevoDiv = document.createElement(\"div\"),
    boton_cerrar = document.createElement(\"button\");

    if(donde[0]){ // Existe
        if(donde[1].parentElement == ultimo_ajuste_mensaje){
            donde[1].remove();
        }else{
            donde[1].remove();
            mostrar_ajustes();
        }
    }else{
        mostrar_ajustes();
    }

    function cerrar_opciones(e){
        let donde = buscar_botones_por_id(\"ajustes_mensaje\");
        if(donde[0]){
            donde[1].remove();
        }
    }

    function mostrar_ajustes(){
        nuevoDiv.id = \"ajustes_mensaje\";
        ultimo_ajuste_mensaje.appendChild(nuevoDiv);

        boton_cerrar.classList.add(\"cerrar_opciones\");
        boton_cerrar.innerHTML = \"X\";
        boton_cerrar.addEventListener(\"click\", cerrar_opciones, false);
        nuevoDiv.appendChild(boton_cerrar);
    ";

    for($i = 0; $i < count($apartados); $i++){
        $devolver["js"] .= "
        nuevoApartado = document.createElement(\"div\");
        nuevoApartado.classList.add(\"opcion_mensaje\");
        nuevoApartado.addEventListener(\"click\", " . str_replace(["{tipo_funcion}"],[$apartados[$i]["funcion"]], $funcion) . ", false);

        nuevoP = document.createElement(\"p\");
        nuevoP.innerHTML = \"" . $apartados[$i]["texto"] . "\";

        nuevoApartado.appendChild(nuevoP);
        nuevoDiv.appendChild(nuevoApartado);
        ";
    }

    $devolver["js"] .= "}";

    return $devolver;
}

function comprobar_mensaje($id_mensaje){
    $resultado_mensaje = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_mensajes"] . " WHERE id_mensaje = '" . $id_mensaje . "';");

    $devolver = [];
    if($resultado_mensaje[0]){ // Existe
        $devolver["estado"] = 202;
        $devolver["mensaje"] = mysqli_fetch_assoc($resultado_mensaje[1]);
    }else{ // No existe el mensaje
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No existe el mensaje con id \"" . $id_mensaje . "\".";
    }

    return $devolver;
}

function bloquear_grupo($grupo, $id_usuario){
    $resultado_grupo_usuario = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_grupos_usuarios"] . " WHERE id_grupo = '" . $grupo["id_grupo"] . "' AND id_usuario = '" . $id_usuario . "';");

    $devolver = [];

    if($resultado_grupo_usuario[0]){ // El usuario está en el grupo
        $grupo_usuario = mysqli_fetch_assoc($resultado_grupo_usuario[1]);
        $sql = "UPDATE " . $GLOBALS["nombre_tabla_grupos_usuarios"] . " SET grupo_bloqueado = '";
        if($grupo_usuario["grupo_bloqueado"] == true){
            $sql .= false;
            $devolver["contenido"] = "Se ha desbloqueado el grupo";
            $devolver["js"] = "id = '" . $grupo["id_grupo"] . "'; modificar_zona_chat();";
        }else{
            $sql .= true;
            $devolver["contenido"] = "Se ha bloqueado el grupo";
            $devolver["js"] = "id = '" . $grupo["id_grupo"] . "'; modificar_zona_chat();";
        }

        $sql .= "' WHERE id_usuario = '" . $id_usuario . "' AND id_grupo = '" . $grupo["id_grupo"] . "';";

        if(conectarQuery($sql, $devolver["contenido"])){
            $devolver["estado"] = 200;
        }else{
            $devolver["estado"] = 300;
            $devolver["contenido"] = "No se ha podido bloquear o desbloquear el grupo \"" . $id_grupo . "\". Vuelve a intentarlo más tarde.";
        }
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No perteneces al grupo";
    }

    return $devolver;
}

function comprobar_grupo($id_grupo){
    $resultado_grupo = obtenerDatos("SELECT * FROM " . $GLOBALS["nombre_tabla_grupos"] . " WHERE id_grupo = '" . $id_grupo . "';");

    $devolver = [];
    if($resultado_grupo[0]){
        $devolver["estado"] = 202;
        $grupo = mysqli_fetch_assoc($resultado_grupo[1]);
        $devolver["grupo"] = $grupo;
    }else{
        $devolver["estado"] = 300;
        $devolver["contenido"] = "No se ha encontrado el grupo con id \"" . $id_grupo . "\".";
    }

    return $devolver;
}

echo json_encode($devolver);

?>