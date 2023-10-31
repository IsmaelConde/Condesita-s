/**
 * Nombre clases e id's de HTML
 */
const clase_opciones_articulo = "opciones_articulo",
id_opciones_perfil = "id_opciones_perfil",
clase_comentarios_articulo = "ajustes-coment",
class_opcion_menu = "opcion-menu";

/* VARIABLES GLOBALES */
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

var evento_previsualizar_perfil,
todas_camaras,
camara_actual,
stream_actual,
posicion_camara_actual;

var padre_historia,
dato_publicacion;

var cargar_mas = true,
botones_opciones_perfil,
botones_opciones_perfil_comentarios,
botones_perfiles,

botones_replicar,
botones_like_publicacion,
botones_guardar_publicacion,
botones_enviar_publicacion,

cuanto_scroll,

id_articulos_nuevos = [];
/* FIN VARIABLES GLOBALES */

/**
 * Función que se ejecuta cuando se carga por completo la página
 */
function cargado(){
    let formData = new FormData();
    formData.append("cargar_inicio", true);
    llamadaAjax_formData("cargar_articulos.php", formData);

    // Vamos a hacer la imagen de nuestro perfil en blob
    let direccion_imagen = document.querySelector(".mi-perfil").querySelector("img").src,
    nuevo_formData = new FormData();

    nuevo_formData.append("datos", JSON.stringify({"nombre_img":direccion_imagen.split("/")[5], "que_es":"img_foto_perfil", "queryDom": ".mi-perfil img"}))

    llamadaAjax_formData("obtener_data_imagen.php", nuevo_formData);

    // Agregamos los button que puede interaccionar el usuario
    const main_principal = document.getElementById("main"),
    
    zona_historias = document.getElementById("contenido-nuevo"),
    lista_historias = document.getElementsByClassName("historia"),
    
    ajustes_perfiles_usuarios = document.querySelector("#barra-derecha").querySelectorAll(".opciones_articulo");

    main = document.getElementById("main");

    // Agregamos las interacciones del usuario con los button
    main_principal.addEventListener("scroll", modificacionesVentana, true); // Para cuando el usuairo haga scroll

    window.addEventListener("resize", (e) => {ajustar_articulos()}, false);

    agregarFuncionesArray(lista_historias, "click", click_historia);

    agregarFuncionesArray(ajustes_perfiles_usuarios, "click", ajustes_perfiles);

    zona_historias.children[0].addEventListener("click", mostrar_subir_historia, false);

    //Iniciamos otras funciones
    buscar_seccionActual();

    //console.log(navigator.mediaDevices.enumerateDevices());
}

/**
 * Función que me ajsuta las publicaciones
 * @param {string|false} nuevosContenidos Si recibe un string es que queremos ajustar las publicaciones que estén en ese string, pero si es false, queremos ajustar todas las publicaciones
 */
function ajustar_articulos(nuevosContenidos = false){
    id_articulos_nuevos = [];
    const clase_zona_inserccion_imagenes_videos_fila = "mostrar_imagenes_videos_fila";
    let base_contenidos;

    if(nuevosContenidos == false){
        base_contenidos = document.getElementsByClassName("base_contenidos");
    }else{
        base_contenidos = obtener_claseHTML_desdeString(nuevosContenidos, "base_contenidos");
    }

    // No se puede usar timeout, hay que mirar que el contenido carge

    for(let i = 0; i < base_contenidos.length; i++){
        for(let j = 0; j < base_contenidos[i].children.length; j++){
            if(base_contenidos[i].children[j].classList.contains(clase_zona_inserccion_imagenes_videos_fila)){ // En caso de que contenga el div del flex
                for(let k = 0; k < base_contenidos[i].children[j].children.length; k++){
                    tipo_contenidos(base_contenidos[i].children[j].children[k]);
                }
            }else{
                tipo_contenidos(base_contenidos[i].children[j]);
            }
            
        }
    }

    /**
     * Función que me va a ir llamando a cada contenido de la publicación para ver que tipo es y comprobar que se ha cargado, para ajustarlo en la función "contenido_cargado"
     * @param {HTMLElement} contenido Recibe el elemnto que contien la publicación
     */
    function tipo_contenidos(contenido){
        let crear_evento = new CustomEvent("evento_contenidos", {
            detail: {message: 'El contenido está cargado'},
            bubbles: false, // Bloqueamos la propagación del evento
            cancelable: true // Queremos tener la posibilidad de poder cancelar el evento
        });
        switch (contenido.tagName){
            case "VIDEO":
                if(contenido.readyState >= 2){ // Está cargado
                    contenido.addEventListener("evento_contenidos", contenido_cargado);
                    contenido.dispatchEvent(crear_evento); // Llamamos a la función, pasandole el evento
                }else{ // No está cargado
                    contenido.addEventListener("canplay", contenido_cargado, false);
                }
                break;
            case "IMG":
                if(contenido.complete){ // Si ya está cargado
                    contenido.addEventListener("evento_contenidos", contenido_cargado);
                    contenido.dispatchEvent(crear_evento); // Llamamos a la función, pasandole el evento
                }else{
                    contenido.addEventListener("load", contenido_cargado, false);
                }
                break;
            default:
                //console.log("tag defecto", contenido);
        }
    }

    /**
     * Función que me dice que publicaciones a cargado su contenido para poder ajustarla
     * @param {Event} e Recibe el evento, por lo que también recibe el target
     */
    function contenido_cargado(e){
        //console.log(e.target.parentElement);
        // Hay que ajustarlo

        let base_contenidos = e.target.parentElement;
        if(e.target.parentElement.classList.contains(clase_zona_inserccion_imagenes_videos_fila)){
            // Hay que mirar si todos los hijos están cargados
            let todo_cargado = true;
            for(let i = 0; i < e.target.parentElement.children.length; i++){
                switch(e.target.parentElement.children[i].tagName){
                    case "VIDEO":
                        //console.log("Estado video: ", e.target.parentElement.children[i].readyState);
                        if(e.target.parentElement.children[i].readyState < 1){ // Si no está cargado
                            todo_cargado = false;
                            break;
                        }
                        break;
                    case "IMG":
                        if(!e.target.parentElement.children[i].complete){
                            todo_cargado = false;
                            break;
                        }
                        break;
                    default:
                        console.log("defecto");
                }
            }

            if(todo_cargado){ // Ajustamos los contenidos
                //console.log("todos los contenidos cargados", e.target.parentElement);
                base_contenidos = e.target.parentElement.parentElement;
            }else{
                //console.log("No están cargados todos los contenidos del articulo", e.target.parentElement);
                return;
            }
        }

        // Imgs y video del articulo cargado
        if(!id_articulos_nuevos.includes(base_contenidos.parentElement.parentElement.getAttribute("id_publicacion"))){ // Porque 
            id_articulos_nuevos.push(base_contenidos.parentElement.parentElement.getAttribute("id_publicacion"));
            ajustar_contenidos(base_contenidos.parentElement.parentElement.getAttribute("id_publicacion")); // Le pasamos el id de la publicación a la función
        }
    }

    /**
     * Función que me escoje las publicaciones que les pide que se modifiquen
     * @param {number|false} id_publicacion Recibe el id de la publicación que deseamos ajustar y si no recibe nada es false, por lo que queremos ajustar todas las publicaciones
     */
    function ajustar_contenidos(id_publicacion = false){
        let alto_ventana = document.getElementById("main").offsetHeight;
        if(id_publicacion == false){
            id_articulos_nuevos.forEach(ajustar_contenido(document.querySelector(".articulo"))); // Recorremos todos los articulos
        }else{
            let articulo = document.querySelector('[id_publicacion="' + id_publicacion + '"]');

            ajustar_contenido(articulo);
        }

        /**
         * Función que me ajusta una publicación en concreto
         * @param {HTMLElement} articulo 
         */
        function ajustar_contenido(articulo){
            //console.log("Ajustamos:", articulo);

            let contenido = articulo.getElementsByClassName("contenido")[0],
            mas_contenido = articulo.getElementsByClassName("mas-contenido")[0],
            base_contenido = contenido.getElementsByClassName("base_contenidos")[0],
            imagenes_articulo = base_contenido.getElementsByClassName("imagen_publicacion"),
            videos_articulo = base_contenido.getElementsByClassName("video_publicacion"),
            contenidos_cargados = true;

            //console.log("Imagenes:", imagenes_articulo, "Videos:", videos_articulo);
            if(imagenes_articulo.length > 0){
                for(let i = 0; i < imagenes_articulo.length; i++){
                    if(!imagenes_articulo[i].complete){
                        contenidos_cargados = false;
                        imagenes_articulo[i].addEventListener("load", (e) => {ajustar_contenido(articulo)}, false);
                    }
                }
            }

            if(videos_articulo.length > 0){
                for(let i = 0; i < videos_articulo.length; i++){
                    if(videos_articulo[i].readyState < 1){
                        contenidos_cargados = false;
                        videos_articulo[i].addEventListener("canplay", (e) => {ajustar_contenido(articulo)}, false);
                    }
                }
            }

            console.log("Se puede ajustar:", contenidos_cargados, articulo);

            if(contenidos_cargados){
                // Le damos el máximo de alto al articulo
                contenido.style.setProperty("--maximo-contenido_articulo", "calc(" + alto_ventana + "px - var(--alto-headers-foto_perfil-index) - 5px - " + mas_contenido.offsetHeight + "px - 45px)");
                if(base_contenido.offsetHeight > contenido.offsetHeight){
                    ajustar_base_contenido(base_contenido, contenido);
                }else{
                    base_contenido.style.setProperty("--ancho_base_contenido", "100%");
                    ajustar_base_contenido(base_contenido, contenido);
                }

                /**
                 * Función recursiva para ajustar los contenidos del interior de las bases de contenido
                 * @param {HTMLElement} base_contenido Div base donde se van insertar los contenidos de la publicación
                 * @param {HTMLElement} contenido Div con todos los contenidos de cada publicación
                 */
                function ajustar_base_contenido(base_contenido, contenido){
                    //console.log("base:", base_contenido.offsetHeight, ". Contenido:", contenido.offsetHeight, contenido);
                    while(base_contenido.offsetHeight > contenido.offsetHeight){
                        base_contenido.style.setProperty("--ancho_base_contenido", (base_contenido.offsetWidth - 10) + "px");
                    }
                }
            }         

        }
    }

    /**
     * Función que me va devolver un objeto del DOM que contenga la clase que recibe por parametro
     * @param {string} string Recibe todo el HTML en formato string
     * @param {string} nombre_clase Recibe el nombre de la clase de los elemntos que queremos obtener
     * @returns {HTMLElement} Devuelve los objetos que tienen la clase que ha recibido por parametro
     */
    function obtener_claseHTML_desdeString(string, nombre_clase){
        let nuevoDiv = document.createElement("div");
        nuevoDiv.innerHTML = string;

        let objetos = nuevoDiv.getElementsByClassName(nombre_clase);

        return objetos;
    }
    
}

function ver_historia(contenidos){
    //history.pushState(null, null, "historias");

    let base_historias = buscar_botones_por_id("inyectar_contenido_historia");
    if(!base_historias[0]){ // Si no existe lo creamos
        let div_base = document.createElement("div"),
        div_inyeccion_historia = document.createElement("div"),
        img_historia = document.createElement("img"),
        cerrar_base_historia = document.createElement("button"),
        siguiente_historia = document.createElement("button"),
        footer = document.createElement("footer"),
        input_mensaje = document.createElement("input"),
        like_historia = document.createElementNS("http://www.w3.org/2000/svg", "svg");

        div_base.id = "base_historia";
        div_base.classList.add("ver_historias");

        div_inyeccion_historia.classList.add("inyectar_historia");
        div_inyeccion_historia.id = "inyectar_contenido_historia";

        footer.classList.add("footer_historia");

        //img_historia.src =

        cerrar_base_historia.innerHTML = "X";
        cerrar_base_historia.classList.add("cerrar-historia")
        cerrar_base_historia.classList.add("ver_historia");
        cerrar_base_historia.addEventListener("click", cerrar_base_video_history, false);

        siguiente_historia.innerHTML = ">";
        siguiente_historia.classList.add("pasar_historia");
        siguiente_historia.classList.add("derecha");
        siguiente_historia.addEventListener("click", (e) => click_historia(""), false);

        footer.appendChild(input_mensaje);
        footer.appendChild(like_historia);

        div_base.appendChild(div_inyeccion_historia);
        div_base.appendChild(footer);
        div_base.appendChild(cerrar_base_historia);
        div_base.appendChild(siguiente_historia);

        document.body.appendChild(div_base);

        base_historias[1] = div_inyeccion_historia;
    }
    
    // En caso de que exista, solo tenemos que cambiar el contenido

    base_historias[1].innerHTML = contenidos;
}

function click_historia(e){
    let usuario_historia, formData = new FormData();

    if(typeof e != "string"){ 
        if(e.target.classList.contains("historia")){
            padre_historia = e.target;
        }else{
            padre_historia = e.target.parentElement;
        }
    }

    usuario_historia = padre_historia.getAttribute("usuario");

    formData.append("usuario_historia", usuario_historia);

    llamadaAjax_formData("mostrar_historia.php", formData);
}

function insertar_video_historia(video_input){
    navigator.mediaDevices.getUserMedia({ video: { deviceId: camara_actual.deviceId } })
    .then(function(video) {
        video_input.srcObject = video;
        stream_actual = video;
    })
    .catch(function(error) {
        // Permiso denegado o error
        mostrar_mensajes_pantalla("No se puede acceder a la cámara: " + error);
    });
}

function mostrar_subir_historia(e){
    // Buscamos los dispositivos activos
    navigator.mediaDevices.enumerateDevices()
    .then(function(todos_dispositivos){ // Si es capaz, entonces
        todas_camaras = todos_dispositivos.filter(function(dispositivo){ // Obtenemos todas las camarás
            return dispositivo.kind === "videoinput"; // Las cámaras tienen su "kind" con "videoinput"
        });

        if(todas_camaras.length <= 0){
            return mostrar_mensajes_pantalla("Tu dispositivo no cuenta con cámaras");
        }

        // En caso de que cuente con alguna cámara

        if(posicion_camara_actual == undefined){ // En caso de cargarlo por primera vez, entonces cogemos la primera camara en el array
            posicion_camara_actual = 0;
        }

        camara_actual = todas_camaras[posicion_camara_actual];

        // Como está función se llama al querer subir una historia, pues iniciamos
        let base_video_historia = document.createElement("div"),
        nuevoVideo = document.createElement("video"),
        boton_captura = document.createElement("div"),
        cerrar_base_video_historia = document.createElement("button"),
        boton_cambiar_camara = document.createElement("button");

        insertar_video_historia(nuevoVideo) // Le pedimos permisos al usuario e incrustamos la cámara

        base_video_historia.id = "base_historia";

        nuevoVideo.id = "subir_video_historia";
        nuevoVideo.autoplay = true;

        boton_captura.id = "boton_captura_historia";
        boton_captura.addEventListener("click", (e) => sacar_foto_historia(nuevoVideo), false);

        cerrar_base_video_historia.innerHTML = "X";
        cerrar_base_video_historia.classList.add("cerrar-historia");
        cerrar_base_video_historia.classList.add("subir_historia");
        cerrar_base_video_historia.addEventListener("click", cerrar_base_video_history, false);

        boton_cambiar_camara.innerHTML = "cambiar cámara";
        boton_cambiar_camara.id = "cambiar-camara-historia";
        boton_cambiar_camara.addEventListener("click", (e) => cambiar_camara_historia(nuevoVideo), false);

        base_video_historia.appendChild(nuevoVideo);
        base_video_historia.appendChild(boton_captura);
        base_video_historia.appendChild(cerrar_base_video_historia);
        base_video_historia.appendChild(boton_cambiar_camara);
        
        document.body.appendChild(base_video_historia);
         
    })
    .catch(function(error){
        mostrar_mensajes_pantalla("No se puede encontrar dispositivos: " + error)
        cerrar_base_video_history();
    });
}

/**
 * 
 * @param {HTMLVideoElement} video_input 
 */
function cambiar_camara_historia(video_input){
    let nombre_camara = camara_actual.label;
    if(nombre_camara == ""){
        nombre_camara = posicion_camara_actual + 1;
    }
    mostrar_mensajes_pantalla("Cámara: " + nombre_camara);
    if(todas_camaras.length > 1){
        posicion_camara_actual++;

        if((todas_camaras.length - 1) < posicion_camara_actual){ // No cuenta con esa cámara
            posicion_camara_actual = 0;
        }

        cerrarCamara(); // Cerramos la cámara
        
        camara_actual = todas_camaras[posicion_camara_actual];

        insertar_video_historia(video_input);
    }else{
        mostrar_mensajes_pantalla('Solo cuentas con esta cámara');
    }    
}

function cerrarCamara(){
    if (stream_actual) {
        let tracks = stream_actual.getTracks();
        tracks[0].stop();
    }else{
        mostrar_mensajes_pantalla("Camara no encendida");
    }
}

function cerrar_base_video_history(){
    let boton = buscar_botones_por_id("base_historia");
    if(boton[0]){
        boton[1].remove();
        if(stream_actual){
            cerrarCamara();
        }
    }
}

/**
 * 
 * @param {HTMLVideoElement} camara 
 */
function sacar_foto_historia(camara){
    console.log(camara);
    let zona_previsualizacion = document.createElement("canvas");
    zona_previsualizacion.height = camara.clientHeight;
    zona_previsualizacion.width = camara.clientWidth;

    console.log("Ancho: " + camara.clientWidth);
    console.log("Alto: " + camara.clientHeight);
    zona_previsualizacion.getContext("2d").drawImage(camara, 0, 0, zona_previsualizacion.width, zona_previsualizacion.height);

    let foto = zona_previsualizacion.toDataURL('image/png');

    let formData = new FormData();
    formData.append("img64", foto);

    let nuevoDiv = document.createElement("div"),
    nuevaImg = document.createElement("img"),
    boton_subir = document.createElement("button");

    nuevoDiv.classList.add("mostrar_previsualizacion_historia");

    nuevaImg.src = foto;
    nuevaImg.classList.add("mostrar_imagen_historia");

    boton_subir.innerHTML = "Subir Historia";
    boton_subir.classList.add("boton_subir_historia");
    boton_subir.addEventListener("click", (e) => llamadaAjax_formData("subir_historia.php", formData), false)

    nuevoDiv.appendChild(boton_subir);
    nuevoDiv.appendChild(nuevaImg);

    document.body.appendChild(nuevoDiv);   

    //mostrar_mensajes_pantalla("<img style=\"width:100%; position:relative;\" src=\"" + foto + "\">");
}

/* =========================
        MODIFICAR VENTANA
========================== */
/**
 * 
 * @param {Event} e 
 */
function modificacionesVentana(e){
    e.stopPropagation();
    if(e.target.offsetHeight + e.target.scrollTop >= (e.target.scrollHeight - 100)){
        if(cargar_mas){
            let formData = new FormData();
            formData.append("final", true);

            //Llamamos a más articulos
            llamadaAjax_formData("cargar_articulos.php", formData);

            cargar_mas = false;
            setTimeout((e) => {cargar_mas = true}, 500);
        }
    }    

    cuanto_scroll = e;
}

/* =========================
        FIN MODIFICAR VENTANA
============================

----------------------------------------------------------

=====================================
        POP-UP OPCIONES PERFILES
===================================== */

function insertar_ajustes(contenidos){
    if(opciones_abierto){
        cerrar_svg.style.display = "none";
        opciones_svg.style.display = "block";
    }else{
        cerrar_svg.style.display = "block";
        opciones_svg.style.display = "none";
    }

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

    opciones_abierto = !opciones_abierto;
}

/* =====================================
        FIN POP-UP OPCIONES PERFILES
========================================

--------------------------------------------------------------------------------

*/

function insertar_articulos(contenidos){
    const donde = document.getElementById("publicaciones");

    donde.insertAdjacentHTML("beforeend", contenidos);
    ajustar_articulos(contenidos); // Ajustamos el alto los articulos para que se vea fino. Le damos setTimeout para que cargue los contenidos y ajuste perfectamente

    botones_opciones_perfil = document.querySelector("#publicaciones").getElementsByClassName(clase_opciones_articulo);
    botones_opciones_perfil_comentarios = document.getElementsByClassName("ajustes-coment");
    botones_perfiles = document.getElementsByClassName("boton-perfil");
    
    botones_replicar = document.getElementsByClassName("replicar");
    botones_like_publicacion = document.getElementsByClassName("like");
    botones_guardar_publicacion = document.getElementsByClassName("guardar");
    botones_enviar_publicacion = document.getElementsByClassName("enviar");

    agregarFuncionesArray(botones_opciones_perfil, "click", opciones_articulos);
    agregarFuncionesArray(botones_opciones_perfil_comentarios, "click", opciones_articulos);
    agregarFuncionesArray(botones_perfiles, "mouseenter", mostrarResumen_perfil);
    agregarFuncionesArray(botones_perfiles, "mouseleave", borrar_resumen_perfil)

    agregarFuncionesArray(botones_replicar, "click", replicar_publicacion);
    agregarFuncionesArray(botones_like_publicacion, "click", like_publicacion);
    agregarFuncionesArray(botones_guardar_publicacion, "click", guardar_publicacion);
    agregarFuncionesArray(botones_enviar_publicacion, "click", enviar_publicacion);
}

function ajustes_perfiles(e){
    let padre;

    /**
     * 
     * @param {HTMLElement} e 
     */
    function encontrar_padre(e){
        if(e.classList.contains("perfil") || e.classList.contains("mi-perfil")){
            return e;
        }else{
            return encontrar_padre(e.parentElement);
        }
    }

    padre = encontrar_padre(e.target);

    if(!padre.disabled){
        if(padre.classList.contains("mi-perfil")){
            mostrar_mensajes_pantalla("Este es tu perfil");
        }else{
            mostrar_mensajes_pantalla("Este es el usuario: " + padre.querySelector(".perfil_nombre").querySelector(".nombre-usuario").innerHTML);
        }
    }
    
    padre.disabled = true;
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

    console.log(datos);

    llamadaAjax("enviar_publicacion.php", JSON.stringify(datos));
}

function insertarContactos(contactos){
    const donde = document.getElementById("compartir_con").children[1];

    donde.innerHTML = contactos;

    let lista_contactos = document.getElementsByClassName("contacto");

    agregarFuncionesArray(lista_contactos, "click", presionar_contacto);
}