/**
 * Datos Script:
 * @author Ismael Conde <trabajosdeismaelconde@gmail.com>
 * @description Script para la interacción del usuario con la página "perfil.html"
 * - Creado: 27/04/2023
 * 
 * - Editado: 10/05/2023
*/

/* ================================================================
    VARIABLES GENERALES
=============================================================== */
var pos_array_Vista = 0, // Se inicializa con cuadrados
ancho_ventana;

var boton_seguir_dejar,
numero_seguidores;
/* ================================================================
    FIN VARIABLES GENERALES
============================================================= */

/**
 * Función que se ejecuta cuando la página está totalmente cargada
 */
function cargado(){
    // Miramos si contiene un hash
    if(window.location.hash.split("#").length >= 2){
        cambios_url();
    }else{ // En otro caso, insertamos las publicaciones mediante ajax
        llamadaAjax("mostrar_subseccion_perfil.php", JSON.stringify(["Publicaciones"]));
        document.getElementsByClassName("subapartado")[0].classList.add("activo");
    }
    //Obtenemos partes del HTML
    numero_seguidores = document.getElementById("num_seguidores");

    // Bucamos los button con los que va interaccionar el usuario
    const modo_vista_publicaciones = document.getElementById("modo-visualizacion"),
    hacer_scroll = document.getElementById("hacer-scroll");
    window.addEventListener("popstate", cambios_url, false);

    const botones_dependientes = [
        {
            "buscar_boton": buscar_botones_por_id("button-seguir-usuario"),
            "funcion": seguir_o_dejar_usuario
        },
        {
            "buscar_boton": buscar_botones_por_id("button-editar-perfil"),
            "funcion": llamar_editar_perfil
        }
    ];

    // Le agregamos listeners a los button
    modo_vista_publicaciones.addEventListener("click", cambiar_vista, false);
    hacer_scroll.addEventListener("scroll", mover_img_portada, false);
    window.addEventListener("resize", reajustarContenidos, false);

    for(let i = 0; i < botones_dependientes.length; i++){
        if(botones_dependientes[i]["buscar_boton"][0]){
            botones_dependientes[i]["buscar_boton"][1].addEventListener("click", botones_dependientes[i]["funcion"], false);
        }
    }

    // Iniciamos otras funciones
    reajustarContenidos();
    buscar_seccionActual();
}

function obtenerSubseccion(){
    const lista_subsecciones = document.getElementsByClassName("subapartado");
    let hash = window.location.hash,
    seccion = hash.split("#")[1];

    for(let i = 0; i < lista_subsecciones.length; i++){
        if(lista_subsecciones[i].getAttribute("href") == "#" + seccion){
            lista_subsecciones[i].classList.add("activo");
        }else{
            if(lista_subsecciones[i].classList.contains("activo")){
                lista_subsecciones[i].classList.remove("activo");
            }
        }
    }

    
}

function cambios_url(){
    let hash = window.location.hash,
	nuevaSeccion = hash.split("#")[1];

    console.log(nuevaSeccion);
    llamadaAjax("mostrar_subseccion_perfil.php", JSON.stringify([nuevaSeccion]))
    obtenerSubseccion();
}

function cambiar_seccion(contenido){
    const donde = document.getElementById("main");

    donde.innerHTML = contenido;

    reajustarContenidos();
}


/* ====================================
        VISTA PUBLICACIONES
==================================== */

/**
 * Función que va a cambiar la vista de las publicaciones
 */
function cambiar_vista(){
    const zona_vistas = document.getElementById("main"),
    visualizador_tipo = document.getElementById("modo-visualizacion");

    zona_vistas.classList.remove(Tipos_Vistas[pos_array_Vista]); // Borramos el actual

    // Comprobamos el estado del array
    if(pos_array_Vista != (Tipos_Vistas.length - 1)){ // Es decir si en la posicion 1 es igual a (2 - 1 = 1) entonces reiniciamos
        pos_array_Vista++;
        visualizador_tipo.children[0].children[(pos_array_Vista - 1)].classList.add("ocultar");
    }else{
        pos_array_Vista = 0; // Le sumamos 1 en el array para pasar al siguiente del array
        visualizador_tipo.children[0].children[visualizador_tipo.children.length].classList.add("ocultar");
    }

    console.log("Modo vista: " + Tipos_Vistas[pos_array_Vista] + ", i:" + pos_array_Vista);

    zona_vistas.classList.add(Tipos_Vistas[pos_array_Vista]);

    setTimeout(actualizarAncho_contenidoHover, 300);

    visualizador_tipo.children[0].children[pos_array_Vista].classList.remove("ocultar");

    reajustarContenidos();
}

/**
 * Función que me actualiza el ancho de los div absolute que se activan con el hover
 */
function actualizarAncho_contenidoHover(){
    const contenidos_hover = document.getElementsByClassName("contenido-hover");

    for(let i = 0; i < contenidos_hover.length; i++){
        let padre = contenidos_hover[i].parentElement.getBoundingClientRect();

        contenidos_hover[i].style.setProperty("--ancho_contenido_hover", padre["width"] + "px");
    }
}

/* ===================================
        FIN VISTA PUBLICACIONES
======================================

----------------------------------------------------------------

=======================================
        MOVER IMG PORTADA
==================================== */

/**
 * Función que se ejecuta cuando en la ventana se hace scroll
 * @param {Event} e 
 */
function mover_img_portada(e){
    const imgPortada = document.getElementById("portada-usuario");

    actualizarAncho_contenidoHover();

    let cuanto_scroll = e.target.scrollTop;

    imgPortada.style.top = "-" + cuanto_scroll + "px";
}

/* ===================================
        FIN MOVER IMG PORTADA
======================================

-----------------------------------------------------------------------

=======================================
        REAJUSTAR TAMAÑO CUADRADOS
===================================== */

function reajustarContenidos(){
    const lista_contenidos = document.getElementsByClassName("contenido"),
    ancho_minimo_contenido = "150",
    ancho_maximo_contenido = "260",
    numero_contenidos_total = lista_contenidos.length,
    margin = "2";

    let maximo_por_linea = 4,
    calculo_contenido,
    pixeles_contenido; 

    let videos_main = document.querySelector("#main").querySelectorAll("video"),
    cargado_videos = false;

    if(videos_main.length == 0){ // No hay videos
        cargado_videos = true;
    }

    for(let i = 0; i < videos_main.length; i++){
        console.log(videos_main[i].readyState)
        if(videos_main[i].readyState < 2){ // no está cargago
            videos_main[i].addEventListener("canplay", reajustarContenidos, false);
        }else{ 
            cargado_videos = true;
        }
    }

    // En caso de que estén cargados
    //mostrar_mensajes_pantalla("Contenidos cargados, vamos a ajustar");

    do{
        ancho_ventana = document.querySelector("#main").offsetWidth;

        if(maximo_por_linea > 1){
            calculo_contenido = ancho_ventana / maximo_por_linea; // Tendremos el ancho de cada contenido para que quede ajustado
            if(calculo_contenido < ancho_minimo_contenido){
                maximo_por_linea--;
            }else if(calculo_contenido > ancho_maximo_contenido){
                maximo_por_linea++;
            }
        }else{
            break;
        }
    }while(calculo_contenido < ancho_minimo_contenido || calculo_contenido > ancho_maximo_contenido);

    // Ahora que tenemos el maximo
    pixeles_contenido = ancho_ventana / maximo_por_linea;

    for(let i = 0; i < numero_contenidos_total; i++){
        if(lista_contenidos[i].parentElement.classList.contains(Tipos_Vistas[0])){
            lista_contenidos[i].style.setProperty("--pixeles-cuadrados", (pixeles_contenido - (margin * 2) - 2) + "px");
        }
    }

    console.log("Ancho Ventana: " + ancho_ventana + ", maximo por linea: " + maximo_por_linea + ", pixeles por conteido: " + (pixeles_contenido - (margin * 2) - 2));
}

/* ==========================================
        FIN REAJUSTAR TAMAÑO CUADRADOS
=============================================

----------------------------------------------------------------------------

================================================================
        LLAMADAS DESDE EL SERVIDOR
============================================================ */
/**
 * Función que será llamada desde el servidor para que el usuario vea como deja de seguir a la persona
 */
function dejar_de_seguir(){
    boton_seguir_dejar.innerHTML = "Seguir";
    numero_seguidores.innerHTML = +numero_seguidores.innerHTML - 1;
}

/**
 * Función que será llamada desde el servidor para que el usuario vea como ha empezado a seguir al usuario
 */
function seguir_usuario(){
    boton_seguir_dejar.innerHTML = "Dejar de Seguir";
    numero_seguidores.innerHTML = +numero_seguidores.innerHTML + 1;
}

/**
 * Función que al hacer clik, sigue a un usuario en concreto
 */
function seguir_o_dejar_usuario(){
    llamadaAjax("seguir_usuario.php", JSON.stringify(""));
}


/* ==============================================================
        FIN LLAMADAS DESDE SERVIDOR
================================================================ */