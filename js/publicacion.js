/* ===========================================
    VARIABLES GENERALES
========================================= */
var button_like_publicacion,
svg_like_publicacion,
num_like_publicacion,

button_guardado_publicacion,
svg_guardar_publicacion,
num_guardar_publicacion,

button_replicar,
svg_replicar,
num_replicar,

button_enviar,
svg_enviar,
num_enviar;
/* ===========================================
    FIN VARIABLES GENERALES
=========================================== */

function cargado(){
    // Cosas de Like
    button_like_publicacion = document.getElementById("button-like-publicacion");
    svg_like_publicacion = button_like_publicacion.children[0];
    num_like_publicacion = button_like_publicacion.children[1];

    // Cosas de guardado
    button_guardado_publicacion = document.getElementById("button-guardar-publicacion");
    svg_guardar_publicacion = button_guardado_publicacion.children[0];
    num_guardar_publicacion = button_guardado_publicacion.children[1];

    // Cosas de replicar
    button_replicar = document.getElementById("button-replicar");
    svg_replicar = button_replicar.children[0];
    num_replicar = button_replicar.children[1];

    // Cosas de enviar
    button_enviar = document.getElementsByClassName("enviar")[0];
    svg_enviar = button_enviar.children[0];
    num_enviar = button_enviar.children[1];

    // Otros botones
    const boton_enviar_comentario = document.getElementById("boton-enviar-comentario"),
    boton_opcion_publicacion = document.getElementById("opciones-perfil"),
    ajustes_comentarios = document.getElementsByClassName("ajustes-coment"),
    botones_perfiles = document.getElementsByClassName("boton-perfil");

    // Le agregamos listeners a los botones
    button_replicar.addEventListener("click", replicar_publicacion, false);
    boton_enviar_comentario.addEventListener("click", enviar_comentario, false);
    button_like_publicacion.addEventListener("click", like_publicacion, false);
    button_guardado_publicacion.addEventListener("click", guardar_publicacion, false);
    button_enviar.addEventListener("click", enviar_publicacion, false);
    boton_opcion_publicacion.addEventListener("click", opciones_articulos, false);

    agregarFuncionesArray(ajustes_comentarios, "click", opciones_articulos);
    agregarFuncionesArray(botones_perfiles, "mouseenter", mostrarResumen_perfil);
    agregarFuncionesArray(botones_perfiles, "mouseleave", borrar_resumen_perfil);
}

function insertarContactos(contactos){
    const donde = document.getElementById("compartir_con").children[1];

    donde.innerHTML = contactos;

    let lista_contactos = document.getElementsByClassName("contacto");

    agregarFuncionesArray(lista_contactos, "click", presionar_contacto);
}

function presionar_contacto(e){
    /**
     * 
     * @param {HTMLElement} e 
     */
    function encontrar_padre_contacto(e){
        if(e.hasAttribute("id")){ // Es el padre
            return e;
        }else{
            return encontrar_padre_contacto(e.parentElement);
        }
    }

    let padre = encontrar_padre_contacto(e.target),
    grupo_id = padre.id, datos;

    if(comprobar_si_es_publicacion()){
        datos = datos_publicacion.split("{separacion}");
    }else{
        datos = dato_publicacion;
    }

    datos.push(grupo_id);

    llamadaAjax("enviar_publicacion.php", JSON.stringify(datos));
}

function insertar_ajustes(contenidos){
    let boton_interactuar = buscar_botones_por_id("ajustes_publicacion");
    if(boton_interactuar[0]){
        boton_interactuar[1].classList.add("activo");
        setTimeout(funcion => {boton_interactuar[1].remove()}, 459);
    }else{
        const donde = ultimo_click_ajustes_articulos;
        let nuevoDiv = document.createElement("div"), apartado, apartado_p;
        nuevoDiv.id = "ajustes_publicacion";

        for(let i = 0; i < contenidos.length; i++){
            apartado = document.createElement("div");
            apartado_p = document.createElement("p");

            apartado.addEventListener("click", eval(contenidos[i][1]), false);

            apartado_p.innerHTML = contenidos[i][0];

            apartado.appendChild(apartado_p);

            nuevoDiv.appendChild(apartado);
        }

        donde.appendChild(nuevoDiv);
    }
    
}

/**
 * Función que enviará un comentario a la publicación
 */
function enviar_comentario(){
    const input_texto = document.getElementById("input-coment");

    llamadaAjax("subir_comentario.php", JSON.stringify([input_texto.value, datos_publicacion.split("{separacion}")]));
}