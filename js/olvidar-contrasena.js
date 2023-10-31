var sistema_arrancado = false;

function cargado(){
    // Buscamos los botones
    const boton_recuperar = document.getElementById("boton-recuperar_cuenta");

    // Agregamos listeners a los botones
    boton_recuperar.addEventListener("click", clickeame_esta, false);
}

function clickeame_esta(e){
    if(!e.target.disabled){
        if(!sistema_arrancado){
            recuperar_cuenta(e);
        }else{
            cambiar_contra(e);
        }

        e.target.disabled = true;

        setTimeout(() => {
            e.target.disabled = false;
        }, 500);
    }
    

    
}

function recuperar_cuenta(e){
    const correo_input = document.getElementById("email");

    let comprobar = comprobar_contenidos(correo_input.value, true, true);
    if(!comprobar[0]){ // El contenido no es correcto
        correo_input.value = "";
        correo_input.placeholder = comprobar[1];
        return; // Devolvemos para terminar la secuencia
    }

    let formData = new FormData();
    formData.append("datos", JSON.stringify({"modo":"correo", "dato":correo_input.value}));

    llamadaAjax_formData("recuperar_cuenta.php", formData);
}

function cambiar_contra(){
    const datos = {
        "email":document.getElementById("email"),
        "codigo":document.getElementById("codigo_recuperar"),
        "contrasena":document.getElementById("contra_recuperar"),
        "contrasena_confirm":document.getElementById("confirmar_contra_recuperar")
    };

    let hay_fallos = false, contenidos = [], formData = new FormData();

    for(let llave in datos){
        let correo = false;
        if(llave == "email"){
            correo = true;
        }

        let comprobar = comprobar_contenidos(datos[llave].value, true, correo);
        if(!comprobar[0]){
            datos[llave].value = "";
            datos[llave].placeholder = comprobar[1];
            hay_fallos = true;
        }else{
            contenidos.push(datos[llave].value);
        }
    }

    if(hay_fallos){
        return;
    }

    formData.append("datos", JSON.stringify({"modo":"cambiar_pass", "dato":contenidos}));

    llamadaAjax_formData("recuperar_cuenta.php", formData);
}