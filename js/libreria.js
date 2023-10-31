/**
 * Datos Script:
 * @author Ismael Conde <trabajosdeismaelconde@gmail.com>
 * @description Script para funciones generales de la página web
 * - Creado: 06/05/2023
 * 
 * - Editado: 06/05/2023
 */

const id_boton_buscar = "boton-buscar",
id_previsualizacion_perfil = "previsualizacion_perfil",
Tipos_Vistas = ["cuadrados", "lista"]; // Tipos de vista: "cuadrados" | "lista".;

var popUp_abierto = false, // Cuando se inicie el documento siempre se mostrará cerrado;
ultimo_boton_menu,

ultimo_click_ajustes_articulos,
cerrar_svg,
opciones_svg,
opciones_abierto = false;

window.addEventListener("load", cargadoLibreria, false); // Listener al documento para cuando la página termine de cargar

function cargadoLibreria(){
    const boton_buscar = document.getElementById(id_boton_buscar),
    boton_opciones = document.getElementById("boton-opciones");

    boton_buscar.addEventListener("click", function(e){mostrar_PopUp(e, "buscar")}, false), // Si se presiona el buscar
    boton_opciones.addEventListener("click", function(e){mostrar_PopUp(e, "opciones")}, false); // Si se presiona las opciones
    
    if(window.cargado){
        cargado();
    }

    ajustar_main();

    window.addEventListener("resize", ajustar_main, false);
}

/**
 * Función para arreglar el problema de "viewport height" en los moviles, ya que la barra de navegador la detecta como ventana
 */
function ajustar_main(){
    const main = document.querySelector("main"),
    secciones = document.getElementById("secciones");
    if (window.innerWidth < 770){ // Tamaño movil
        main.style.height = "calc(" + window.innerHeight + "px - "  + secciones.offsetHeight + "px)";
    }else{ // Tamaño tablet, pc, y más grandes
        main.style.height = "";
    }
}

function pintar_previsualizacion_perfil(contenido){
    borrar_resumen_perfil();
    
    let crear_div = document.createElement("DIV");
    crear_div.innerHTML = contenido;
    crear_div.id = id_previsualizacion_perfil;
    crear_div.style.bottom = 0 + "px";  
    crear_div.style.right = 0 + "px";
    //crear_div.style.left = "calc(var(--ancho-barra) - " + (evento_previsualizar_perfil.clientX + 10) + "px)";
    evento_previsualizar_perfil.target.parentElement.appendChild(crear_div);
}

/**
 * Función que comprobará que los contenidos no contengan fallos
 * @param {string} contenido Recibe el contenido a comprobar
 * @param {boolean} comprobar_vacio Recibe si quiere comprobar que esté vacío el contenido
 * @returns {[boolean|string]} Devuelve un booleano para saber el estado del contenido y el por qué de ese booleano
 */
function comprobar_contenidos(contenido, comprobar_vacio, comprobar_email){
    if(comprobar_vacio){
        // No lo quiere vacío
        if(contenido == ""){
            // Está vacío
            return [false, "El contenido no puede estar vacío"];
        }
    }

    if(comprobar_email){
        // Quiere comprobar que sea un email
        const expresion_regular_email = /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;

        if(!expresion_regular_email.test(contenido)){
            // No es un email
            return [false, "El contenido no es un email"];
        }
    }

    // En caso de que esté todo OK
    return [true, "Todo correcto amigo"];
}

/**
 * Función que me agrega los listeners a los elemntos de un array
 * @param {[]} array El array
 * @param {string} tipo El tipo de listener en formato string
 * @param {Function} funcion Nombre de la función
 */
function agregarFuncionesArray(array, tipo, funcion){
    for(let i = 0; i < array.length; i++){
        array[i].addEventListener(tipo, funcion, false);
    }
}

/**
 * Función que evita el por defecto del click derecho
 * @param {Event} e Evento por el cual fue ejecutada la función
 */
function prevenir_click_derecho_por_defecto(e){
    e.preventDefault();
}

/**
 * Función que borra el div si existe
 * @param {string} id Recibe el id del elemento que queremos borrar
 */
function borrar_elemento_id(id){
    if(document.getElementById(id)){ // Si existe el elemento
        document.getElementById(id).remove(); // Lo borramos
    }
}

/**
 * Función que muestra por consola el mensaje
 * @param {string} mensaje 
 */
function mostrar_por_consola(mensaje){
    let e_json = es_json(mensaje);
    if(e_json){
        console.log(JSON.parse(mensaje));
    }else{
        console.log(mensaje);
    }
}

/**
 * Función que me recarga la página
 */
function recargarPagina(){
    location.reload();
}

/**
 * Función que me va decir si el dato es JSON o no
 * @param {JSON|*} dato El dato a comprobar
 */
function es_json(dato){
    let es_json = true;
    try{
        JSON.parse(dato);
    }catch(e){ // Si da error es porque no lo puede parsear ya que no es JSON
        es_json = false;
    }finally{
        return es_json; // De una manera u otra devolvemos si es json o no
    }
}

/**
 * Función que genera un div para mostrar el mensaje
 * @param {string} mensaje 
 */
function mostrar_mensajes_pantalla(mensaje){
    const id_div_mostrar_mensajes = "mostrar_mensajes";
    let insertar_mensajes;

    if(!document.getElementById(id_div_mostrar_mensajes)){
        insertar_mensajes = document.createElement("div");
        insertar_mensajes.style.position = "absolute";
        insertar_mensajes.style.width = "60%";
        insertar_mensajes.style.maxHeight = "50vh";
        insertar_mensajes.style.top = "0";
        insertar_mensajes.style.zIndex = "1000";
        insertar_mensajes.id = "mostrar_mensajes";
        insertar_mensajes.style.left = "20%";
    }else{
        insertar_mensajes = document.getElementById(id_div_mostrar_mensajes)
    }
    
    let nuevoDiv = document.createElement("div");
    nuevoDiv.style.transition = "300ms";
    nuevoDiv.style.position = "relative";
    nuevoDiv.style.border = "1px solid #000";
    nuevoDiv.style.borderRadius = "10px";
    nuevoDiv.style.top = "10px";
    nuevoDiv.style.width = "100%";
    nuevoDiv.style.textAlign = "center";
    nuevoDiv.style.background = "#fff"
    nuevoDiv.style.margin = "15px 0";

    let nuevoP = document.createElement("p");
    nuevoP.innerHTML = mensaje;

    nuevoDiv.appendChild(nuevoP);
    insertar_mensajes.appendChild(nuevoDiv); // Lo ponemos como hijo

    document.body.appendChild(insertar_mensajes);

    setTimeout(() =>{
        //nuevoDiv.style.visibility = "hidden";
        nuevoDiv.remove();
    }, 9000);
}

/**
 * Función para dibujar en la barra del menú en que sección nos encontramos
 */
function buscar_seccionActual(){
    const menu_visual = document.getElementById("menu");
    let referencia, obtener_seccion;

    for(let i = 0; i < menu_visual.children.length; i++){
        if(menu_visual.children[i].nodeName == "A"){
            // Tenemos todos los botones de secciones
            referencia = menu_visual.children[i].getAttribute("href");
            if(referencia != null){
                //Es un botón para cambiarse
                obtener_seccion = referencia.split("/");

                if(obtener_seccion[3] == ""){
                    // Es el index
                    obtener_seccion[3] = "Inicio";
                }
                
                if(obtener_seccion[3] == seccion){
                    // Si estamos en la seccion
                    menu_visual.children[i].classList.add("activo");
                }
            }
        }
    }
}

/**
 * Función que me dice si estamos en la sección de publicación
 * @returns {boolean} Me devolverá true si estamos en publicación, ya que contiene ese varaible y devolverá false si estamos en el inicio
 */
function comprobar_si_es_publicacion(){
    let es_publicacion = true;
    try{
        datos_publicacion.split("{separacion}");
    }catch(e){
        es_publicacion = false;
    }

    return es_publicacion;
}

/**
 * Función que me devolverá al padre, ya que es el que contiene todos los datos
 * @param {HTMLElement} e 
 * @param {string} nombreScript
 */
function obtenerDatosPadre_publicacion(e, nombreScript, devolver = false){
    let id_publicacion, subido_por;

    if(e.hasAttribute("id_publicacion") && e.hasAttribute("subido_por")){
        id_publicacion = e.getAttribute("id_publicacion");
        subido_por = e.getAttribute("subido_por");

        // Cosas de Like
        button_like_publicacion = e.children[2].children[0].children[0];
        svg_like_publicacion = button_like_publicacion.children[0];
        num_like_publicacion = button_like_publicacion.children[1];

        // Cosas de guardado
        button_guardado_publicacion = e.children[2].children[0].children[4];
        svg_guardar_publicacion = button_guardado_publicacion.children[0];
        num_guardar_publicacion = button_guardado_publicacion.children[1];

        // Cosas de replicar
        button_replicar = e.children[2].children[0].children[3];
        svg_replicar = button_replicar.children[0];
        num_replicar = button_replicar.children[1];

        //Cosas de enviar
        button_enviar = e.children[2].children[0].children[2];
        svg_enviar = button_enviar.children[0];
        num_enviar = button_enviar.children[1];

        if(devolver){
            return [id_publicacion, subido_por];
        }else{
            llamadaAjax(nombreScript, JSON.stringify([id_publicacion, subido_por]));
        }
    }else{
        return obtenerDatosPadre_publicacion(e.parentElement, nombreScript, devolver);
    }
}

/* ===============================================
            REPLICAR PUBLICACIÓN
================================================ */

/**
 * Función que replica una publicación
 */
function replicar_publicacion(e){
    if(comprobar_si_es_publicacion()){
        llamadaAjax("replicar_publicacion.php", JSON.stringify(datos_publicacion.split("{separacion}")));
    }else{
        obtenerDatosPadre_publicacion(e.target, "replicar_publicacion.php");
    }
}

/**
 * Función que me sumará replica en cliente (para no recargar página)
 * Esta función se ejecuta desde servidor
 */
function sumar_replica_publicacion(){
    svg_replicar.classList.add("verde");
    num_replicar.innerHTML = +num_replicar.innerHTML + 1;

}

/**
 * Función que me quitará la replica
 * Esta función se ejecuta desde servidor
 */
function restar_replica_publicacion(){
    svg_replicar.classList.remove("verde");
    num_replicar.innerHTML = +num_replicar.innerHTML - 1;
}

/* =================================================
            FIN REPLICAR PUBLICACIÓN
====================================================

-----------------------------------------------------------------------------------------

=====================================================
            LIKE PUBLICACIÓN
=================================================== */

/**
 * Función que da like a una publicación
 */
function like_publicacion(e){
    if(comprobar_si_es_publicacion()){
        llamadaAjax("like_publicacion.php", JSON.stringify(datos_publicacion.split("{separacion}")));
    }else{
        obtenerDatosPadre_publicacion(e.target, "like_publicacion.php");
    }
}

/**
 * Función que sumará el like en cliente
 * Esta función se ejecuta desde servidor
 */
function sumar_like_publicacion(){
    svg_like_publicacion.classList.add("rojo");
    num_like_publicacion.innerHTML = +num_like_publicacion.innerHTML + 1;
}

/**
 * Función que quitará el like
 * Esta función se ejecuta desde servidor
 */
function restar_like_publicacion(){
    svg_like_publicacion.classList.remove("rojo");
    num_like_publicacion.innerHTML = +num_like_publicacion.innerHTML - 1;
}

/* ===================================================
            FIN LIKE PUBLICACIÓN
======================================================

---------------------------------------------------------------------------------------

/* ====================================================
        GUARDAR PUBLICACIÓN
==================================================== */
/**
 * Función que guarda la publicación
 */
function guardar_publicacion(e){
    if(comprobar_si_es_publicacion()){
        llamadaAjax("guardar_publicacion.php", JSON.stringify(datos_publicacion.split("{separacion}")));
    }else{
        obtenerDatosPadre_publicacion(e.target, "guardar_publicacion.php");
    }
}

/**
 * Función que me actualiza los datos de guardado
 * Esta función se ejecuta desde Servidor
 */
function sumar_guardado_publicacion(){
    svg_guardar_publicacion.classList.add("amarillo");
    num_guardar_publicacion.innerHTML = +num_guardar_publicacion.innerHTML + 1;
}

/**
 * Función que me actualiza los datos de guardado
 * Esta función se ejecuta desde Servidor
 */
function restar_guardado_publicacion(){
    svg_guardar_publicacion.classList.remove("amarillo");
    num_guardar_publicacion.innerHTML = +num_guardar_publicacion.innerHTML - 1;
}
/* ===================================================
        FIN GUARDAR PUBLICACIÓN
=================================================== */

/* ===================================================
        ENVIAR PUBLICACIÓN
================================================== */
function enviar_publicacion(e){
    let formData = new FormData();

    if(comprobar_si_es_publicacion()){
        dato_publicacion = datos_publicacion.split("{separacion}");
    }else{
        dato_publicacion = obtenerDatosPadre_publicacion(e.target, "", true);
    }

    formData.append("datos_publicacion", JSON.stringify(dato_publicacion));
    
    llamadaAjax_formData("solicitar_grupos.php", formData);
}

function escoger_grupo(contenido){
    let div_desplegado = buscar_botones_por_id("compartir_con");
    if(div_desplegado[0]){
        div_desplegado[1].remove();
    }

    let nuevoDiv = document.createElement("div"),
    nuevoHeader = document.createElement("header"),
    nuevoTexto = document.createElement("p"),
    nuevoInput = document.createElement("input"),
    div_inyectar_grupos = document.createElement("div"),
    boton_cerrar = document.createElement("button");
    
    nuevoDiv.id="compartir_con";

    nuevoHeader.classList.add("header_compartir");

    nuevoTexto.innerHTML = "Buscar usuario:";
    nuevoInput.placeholder = "Buscar Usuario"
    nuevoInput.addEventListener("keyup", (e) => {buscar_contactos_mensajes(e, contenido)}, false)

    div_inyectar_grupos.classList.add("inyectar_grupos_compartir");
    div_inyectar_grupos.innerHTML = contenido;

    boton_cerrar.classList.add("boton_cerrar_compartir");
    boton_cerrar.innerHTML = "X";
    boton_cerrar.addEventListener("click", cerrar_escoger_grupo, false);

    nuevoHeader.appendChild(nuevoTexto);
    nuevoHeader.appendChild(nuevoInput);

    nuevoDiv.appendChild(nuevoHeader);
    nuevoDiv.appendChild(div_inyectar_grupos);
    nuevoDiv.appendChild(boton_cerrar);

    document.body.appendChild(nuevoDiv);

    let botones_grupos = document.getElementsByClassName("escoger_grupo");

    agregarFuncionesArray(botones_grupos, "click", click_enviar_publicacion);
}

function buscar_contactos_mensajes(e, contenido){
    if(e.target.value == ""){ // Si está vacío
        escoger_grupo(contenido);
    }else{
        let contenido = e.target.value;
        llamadaAjax("buscar_contacto.php", JSON.stringify([contenido]));
    }
}

function click_enviar_publicacion(e){
    let padre, id_grupo, datos = datos_publicacion.split("{separacion}");
    if(e.target.hasAttribute("id")){ // Es el padre
        padre = e.target;
    }else{ // Es el hijo
        padre = e.target.parentElement;
    }

    id_grupo = padre.getAttribute("id");

    datos.push(id_grupo);

    llamadaAjax("enviar_publicacion.php", JSON.stringify(datos));
}

function cerrar_escoger_grupo(){
    let base_escoger = buscar_botones_por_id("compartir_con");
    dato_publicacion = null;

    if(base_escoger[0]){ // Existe
        base_escoger[1].remove();
    }
}

function sumar_publicacion_compartida(){
    let desplegable = buscar_botones_por_id("compartir_con");
    dato_publicacion = null;
    if(desplegable[0]){
        desplegable[1].remove();
    }
    svg_enviar.classList.add("azul");
    num_enviar.innerHTML = +num_enviar.innerHTML + 1;
}
/* ==================================================
        FIN ENVIAR PUBLICACIÓN
================================================== */

function buscar_botones_por_id(id_boton){
    let devolver = [true];
    try{
        if(document.getElementById(id_boton)){
            // Si existe
            devolver.push(document.getElementById(id_boton));

            if(id_boton == "button-seguir-usuario"){
                boton_seguir_dejar = document.getElementById("button-seguir-usuario");
            }
        }else{
            devolver = [false];
        }
    }catch(e){
        devolver = [false];
    }

    return devolver;
}

function opciones_articulos(e){
    let formData = new FormData(), dato_publicacion, tipo, extra;
    if(!comprobar_si_es_publicacion()){
        dato_publicacion = obtenerDatosPadre_publicacion(e.target, "", true);
    }else{
        dato_publicacion = datos_publicacion.split("{separacion}");
    }

    /**
     * 
     * @param {HTMLElement} e 
     */
    function buscar_padre(e){
        if(e.classList.contains("opciones_articulo") || e.classList.contains("ajustes-coment") || e.id == "opciones-perfil"){ // Es el padre
            return e;
        }else{
            return buscar_padre(e.parentElement);
        }
    }

    ultimo_click_ajustes_articulos = buscar_padre(e.target);

    if(cerrar_svg != undefined && opciones_svg != undefined){
        cerrar_svg.style.display = "none";
        opciones_svg.style.display = "block";
    }

    cerrar_svg = ultimo_click_ajustes_articulos.children[0].children[0];
    opciones_svg = ultimo_click_ajustes_articulos.children[0].children[1];

    if(ultimo_click_ajustes_articulos.classList.contains("opciones_articulo") || ultimo_click_ajustes_articulos.id == "opciones-perfil"){ // Es publicacion
        tipo = "publicacion";
    }else if(ultimo_click_ajustes_articulos.classList.contains("ajustes-coment")){ // Es comentario
        tipo = "comentario";
        extra = {
            "usuario": ultimo_click_ajustes_articulos.parentElement.children[0].children[0].getAttribute("usuario"),
            "id_comentario": ultimo_click_ajustes_articulos.parentElement.children[0].children[0].getAttribute("id")
        };
    }

    formData.append("json", JSON.stringify({"tipo":tipo, "datos_publicacion":dato_publicacion, "extra":extra}));
    llamadaAjax_formData("ajustes_publicacion.php", formData);
}

/* ==========================
        POP-UP MENÚ
======================== */

/**
 * Función que muestra o me oculta el Pop Up de la barra lateral
 * @param {Event} e
 * @param {string} zona 
 */
function mostrar_PopUp(e, zona){
    const padre = document.getElementById("boton-" + zona);

    let fondo_oscuro;
    if(document.querySelector(".fondo_abrir_pop-up")){// Si existe
        fondo_oscuro = document.querySelector(".fondo_abrir_pop-up"); // Le indicamos cual es
    }else{ // Si no existe
        fondo_oscuro = document.createElement("div");
        fondo_oscuro.classList.add("fondo_abrir_pop-up");
        fondo_oscuro.classList.add("pc");
    }

    // Para quitar la clase de activado
    if((padre != ultimo_boton_menu) && (ultimo_boton_menu != undefined)){
        ultimo_boton_menu.classList.remove("activo");
    }

    // De momento no vamos a hacer comprobaciones de zona
    const pop_up = document.getElementById("pop_up");
    pop_up.innerHTML = ""; // Reiniciamos su contenido
    if(popUp_abierto){
        // Está abierto el pop-up
        pop_up.classList.add("ocultar");
        padre.classList.remove("activo");
        fondo_oscuro.remove();
    }else{
        // No está abierto el pop-up
        switch(zona){ // Según desde donde se llama, se le dará atributos
            case "opciones":
                pop_up.style.bottom = "1px";
                let nuevoApartado = document.createElement("div");

                nuevoApartado.classList.add("apartado-opciones");
                nuevoApartado.addEventListener("click", (e)=> llamadaAjax_formData("cerrar_sesion.php", ""), false);

                let texto = document.createElement("p");

                texto.innerHTML = "Cerrar Sesión";
                texto.classList.add("texto-opciones");
                
                nuevoApartado.appendChild(texto);

                pop_up.appendChild(nuevoApartado);

                nuevoApartado = document.createElement("div");

                nuevoApartado.classList.add("apartado-opciones");
                nuevoApartado.addEventListener("click", llamar_editar_perfil, false);

                texto = document.createElement("p");

                texto.innerHTML = "Editar Perfil";
                texto.classList.add("texto-opciones");

                nuevoApartado.appendChild(texto);

                pop_up.appendChild(nuevoApartado);

                break;
            case "buscar":
                pop_up.style.bottom = "";

                let nuevoInput = document.createElement("input"), 
                nuevaZona = document.createElement("div");

                nuevoInput.type = "text";
                nuevoInput.placeholder = "Buscar Usuario";
                nuevoInput.addEventListener("click", buscarUsuarios, false);
                nuevoInput.addEventListener("keyup", buscarUsuarios, false);

                nuevaZona.id="insertarUsuarios";

                pop_up.appendChild(nuevoInput);
                pop_up.appendChild(nuevaZona);

                setTimeout(funcion => {nuevoInput.focus();}, 70);
                break;
            default: // No se ha encontrado esa zona, por lo que se muestra tal y como está
        }

        pop_up.classList.remove("ocultar"); // Lo mostramos

        padre.classList.add("activo");

        document.body.appendChild(fondo_oscuro);
    }

    ultimo_boton_menu = padre;

    popUp_abierto = !popUp_abierto;
}

function llamar_editar_perfil(){
    let formData = new FormData();

    formData.append("modo", "editar_perfil");

    llamadaAjax_formData("obtener_datos_usuario.php", formData);
}

function pintar_nombre_arroba(color){
    const seccion_nombre_arroba = document.getElementsByClassName("editar-perfil_seccion-nombre-arroba-perfil")[0],
    input = seccion_nombre_arroba.children[1];

    switch(color){
        case "rojo":
            input.style.color = "var(--rojo)";
            break;
        case "negro":
            input.style.color = "var(--negro)";
            break;
        default:
            input.style.color = "var(--azul)";
    }
}

function comprobar_arroba_usuario(e){
    let data_enviar = new FormData();
    data_enviar.append("nombre" , e.target.value);

    llamadaAjax_formData("comprobar_arroba_usuario.php", data_enviar);
}

function click_actualizarDatos(){
    const subsecciones_contenidos = document.getElementsByClassName("subseccion-editar_perfil");
    
    let formData = new FormData(), img_perfil, img_portada;
    try{
        formData.append("img_perfil", subsecciones_contenidos[0].children[1].children[1].files[0], subsecciones_contenidos[0].children[1].children[1].files[0].name); // Input
    }catch(err){
        console.log("No hay imagen en img_perfil");
    }
    try{
        formData.append("img_portada", subsecciones_contenidos[1].children[1].children[1].files[0], subsecciones_contenidos[1].children[1].children[1].files[0].name); // Input
    }catch(err){
        console.log("No hay imagen en img_portada");
    }
    formData.append("nombre_usuario", subsecciones_contenidos[2].children[1].value); // Input
    formData.append("nombre_arroba_usuario", subsecciones_contenidos[3].children[1].value); // Input
    formData.append("descripcion", subsecciones_contenidos[4].children[1].value); // TextArea

    llamadaAjax_formData("actualizar_perfil.php", formData);
}

/**
 * Función para editar el perfil
 * @param {string[]} recibir Recibe un array con los datos
 */
function editar_perfil(recibir){
    let nombre_img_perfil = recibir[0],
    nombre_img_portada = recibir[1],
    nombre_usuario = recibir[2],
    nombre_arroba_usuario = recibir[3],
    descripcion_usuario = recibir[4];

    let donde = buscar_botones_por_id("base_editar_perfil");
    if(donde[0]){ // Existe
        donde[1].remove();
    }else{ // No existe
        let nuevoDiv = document.createElement("div"),
        seccion_img_perfil = document.createElement("div"),
        seccion_img_portada = document.createElement("div"),
        seccion_nombre_perfil = document.createElement("div"),
        seccion_nombre_arroba_perfil = document.createElement("div"),
        seccion_descripcion_perfil = document.createElement("div");
        
        nuevoDiv.id = "base_editar_perfil";

        seccion_img_perfil.classList.add("editar-perfil_seccion-img-perfil");
        seccion_img_perfil.classList.add("subseccion-editar_perfil");

        seccion_img_portada.classList.add("editar-perfil_seccion-img-portada");
        seccion_img_portada.classList.add("subseccion-editar_perfil");

        seccion_nombre_perfil.classList.add("editar-perfil_seccion-nombre-perfil");
        seccion_nombre_perfil.classList.add("subseccion-editar_perfil");

        seccion_nombre_arroba_perfil.classList.add("editar-perfil_seccion-nombre-arroba-perfil");
        seccion_nombre_arroba_perfil.classList.add("subseccion-editar_perfil");

        seccion_descripcion_perfil.classList.add("editar-perfil_seccion-descripcion-perfil");
        seccion_descripcion_perfil.classList.add("subseccion-editar_perfil");

        /* SECCION IMG - PERFIL */
        let imagenPerfil = document.createElement("img"),
        nuevoInput_file = document.createElement("input"),
        h3 = document.createElement("h3"),
        insertar_datos = document.createElement("div");

        imagenPerfil.src = window.location.protocol + "//" + window.location.hostname + "/images/img_foto_perfil/" + nombre_img_perfil;

        nuevoInput_file.type = "file";
        nuevoInput_file.accept = "image/*";
        //nuevoInput_file.name = "img_perfil"
        nuevoInput_file.addEventListener("change", cambios_img, false);

        insertar_datos.appendChild(imagenPerfil);
        insertar_datos.appendChild(nuevoInput_file);

        insertar_datos.classList.add("img-input");
        insertar_datos.classList.add("circulo-img");

        h3.innerHTML = "Sección Imagen Perfil";

        seccion_img_perfil.appendChild(h3);
        seccion_img_perfil.appendChild(insertar_datos);

        nuevoDiv.appendChild(seccion_img_perfil);

        /* FIN SECCIÓN IMG - PERFIL */

        /* SECCION IMG - PORTADA */
        imagenPerfil = document.createElement("img"),
        nuevoInput_file = document.createElement("input"),
        h3 = document.createElement("h3"),
        insertar_datos = document.createElement("div");

        imagenPerfil.src = window.location.protocol + "//" + window.location.hostname + "/images/img_foto_portada/" + nombre_img_portada;

        nuevoInput_file.type = "file";
        nuevoInput_file.accept = "image/*";
        //nuevoInput_file.name = "img_portada";
        nuevoInput_file.addEventListener("change", cambios_img, false);

        insertar_datos.appendChild(imagenPerfil);
        insertar_datos.appendChild(nuevoInput_file);

        insertar_datos.classList.add("img-input");

        h3.innerHTML = "Sección Imagen Portada"

        seccion_img_portada.appendChild(h3);
        seccion_img_portada.appendChild(insertar_datos);

        nuevoDiv.appendChild(seccion_img_portada);

        /* FIN SECCION IMG - PORTADA */

        /* SECCIÓN NOMBRE PERFIL */
        let nuevoInput_text = document.createElement("input");
        h3 = document.createElement("h3");

        nuevoInput_text.type = "text";
        nuevoInput_text.placeholder = "Escriba el nuevo nombre";
        nuevoInput_text.value = nombre_usuario;

        h3.innerHTML = "Sección Nombre Usuario";

        seccion_nombre_perfil.appendChild(h3);
        seccion_nombre_perfil.appendChild(nuevoInput_text);

        nuevoDiv.appendChild(seccion_nombre_perfil);
        /* FIN SECCIÓN NOMBRE PERFIL */

        /* SECCION NOMBRE ARROBA PERFIL */
        nuevoInput_text = document.createElement("input");
        h3 = document.createElement("h3");

        nuevoInput_text.type = "text";
        nuevoInput_text.placeholder = "Escriba el nuevo nombre arroba";
        nuevoInput_text.value = nombre_arroba_usuario;
        nuevoInput_text.addEventListener("keyup", comprobar_arroba_usuario, false);

        h3.innerHTML = "Sección Nombre Arroba Usuario";

        seccion_nombre_arroba_perfil.appendChild(h3);
        seccion_nombre_arroba_perfil.appendChild(nuevoInput_text);

        nuevoDiv.appendChild(seccion_nombre_arroba_perfil);
        /* FIN SECCIÓN NOMBRE ARROBA PERFIL */

        /* SECCIÓN DESCRIPCIÓN PERFIL */
        let nueva_textarea = document.createElement("textarea");
        h3 = document.createElement("h3");

        h3.innerHTML = "Sección Descripción Usuario";

        nueva_textarea.placeholder = "Escribe la descripción";
        nueva_textarea.value = descripcion_usuario;

        seccion_descripcion_perfil.appendChild(h3);
        seccion_descripcion_perfil.appendChild(nueva_textarea);

        nuevoDiv.appendChild(seccion_descripcion_perfil);
        /* FIN SECCIÓN DESCRIPCIÓN PERFIL */

        /* BOTÓN EXTRA CERRAR EL DIV FLOTANTE */
        let nuevoBoton = document.createElement("button");
        nuevoBoton.innerHTML = "X";
        nuevoBoton.classList.add("cerrar-editar-perfil");
        nuevoBoton.addEventListener("click", llamar_editar_perfil, false);

        nuevoDiv.appendChild(nuevoBoton);
        /* FIN BOTÓN EXTRA */

        /* ACTUALIZAR PERFIL */
        nuevoBoton = document.createElement("button");
        nuevoBoton.addEventListener("click", click_actualizarDatos, false);
        nuevoBoton.innerHTML = "Actualizar perfil";

        nuevoDiv.appendChild(nuevoBoton);
        /* FIN ACTUALIZAR PERFIL */

        document.body.appendChild(nuevoDiv);
    }
}

function cambios_img(e){
    let lector = new FileReader();

    lector.readAsDataURL(e.target.files[0]);
    lector.onloadend = function(){
        e.target.parentElement.children[0].src = lector.result;
    }
    //e.target.parentElement.children[0].src = e.dataTransfer.files;
}

/* =======================
        FIN POP-UP MENÚ
======================== */

function buscarUsuarios(e){
    const contenido = e.target.value,
    donde = document.getElementById("insertarUsuarios");

    if(contenido != ""){
        llamadaAjax("buscar_usuarios.php", JSON.stringify([contenido, "pop_up"]));
    }else{
        donde.innerHTML = "";
    }
}

function insertar_usuarios_pop_up(respuesta){
    const donde = document.getElementById("insertarUsuarios");

    donde.innerHTML = respuesta;
}

/* ======================================
        MOUSE HOVER PERFILES
===================================== */

function borrar_resumen_perfil(){
    const boton = buscar_botones_por_id(id_previsualizacion_perfil);

    if(boton[0]){
        boton[1].remove();
    }
}

function mostrarResumen_perfil(e){
    let padre, id_padre;

    /**
     * Función recursiva para encontrar al padre
     * @param {HTMLElement} elemento
     * @returns {HTMLElement} Devuelve el padre
     */
    function encontrarPadre(elemento){
        if(elemento.classList.contains("boton-perfil")){
            return elemento;
        }else{
            encontrarPadre(elemento.parentElement);
        }
    }

    try{
        padre = encontrarPadre(e.target);

        id_padre = padre.getAttribute("usuario");

    }catch(t){

    }finally{
        if(id_padre != undefined){
            console.log(id_padre);

            evento_previsualizar_perfil = e;

            // LLamadaAjax
            llamadaAjax("mostrar_previsualizacion_perfil.php", JSON.stringify([id_padre]));
        }
    }
}

/* =====================================
        FIN MOUSE HOVER PERFILES
===================================== */



