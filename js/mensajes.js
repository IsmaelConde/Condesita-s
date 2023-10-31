/**
 * Datos Script:
 * @author Ismael Conde <trabajosdeismaelconde@gmail.com>
 * @description Script para la interacción del usuario con la página "mensajes.html"
 * - Creado: 27/04/2023
 * 
 * - Editado: 06/05/2023
 */
const id_div_flotante = "ajustes_contacto";

var atras_chat_movil,
    boton_enviar_mensaje,
    id, contacto,
    bucle_mensajes,
    fecha_ultima_llamada,
    zona_mensajes,
    zona_presentacion_mensajes,
    zona_chat,
    contactos,
    ocultar_movil_lista_usuarios,
    mostrar_movil_chat,

    ajuste_contacto_abierto = false,
    ultimo_ajuste_mensaje,
    lista_mensajes;

/**
 * Función que se ejecuta cuando la página se carga por completo
 */
function cargado() {
    zona_presentacion_mensajes = document.getElementById("portada-defecto");
    zona_chat = document.getElementById("chat");
    contactos = document.getElementsByClassName("contacto");

    ocultar_movil_lista_usuarios = document.getElementById("main"),
        mostrar_movil_chat = document.getElementById("mostrar-mensajes");

    let path = window.location.pathname,
        id_grupo = path.split("/")[2];

    if (id_grupo != "" && id_grupo != undefined) {
        id = id_grupo;
        modificar_zona_chat();
        llamadaAjax("insertar_chat.php", JSON.stringify([id]));
    }

    // Agregamos los button con los que el usuario va interactuar
    const lista_contactos = document.getElementById("lista-contactos"), // Click en la lista de contactos
        input_buscar_contactos = document.getElementById("input-buscar-contacto-mensajes"), // Keydown en input buscar contacto
        separador = document.getElementById("separador"); // Click y arrastrar en Separador

    // Agregamos los listener a los button
    lista_contactos.addEventListener("click", presionar_contacto, false); // Listener con click de la lista de contactos
    lista_contactos.addEventListener("auxclick", ajustes_contacto, false); // Listener de click derecho a los contactos
    lista_contactos.addEventListener("contextmenu", prevenir_click_derecho_por_defecto, false); // Para prevenir el por defecto del click izquierdo

    input_buscar_contactos.addEventListener("keyup", buscar_contacto_mensajes, false); // Listener de cada vez que se aprieta una tecla en el input

    separador.addEventListener("mousedown", clickSeparador, false);

    window.addEventListener("resize", cambiosVentana, false);

    // Iniciamos otras funciones
    buscar_seccionActual();
}

/**
 * Función que me va a escuchar los mensajes
 */
function escuchador_mensajes() {
    llamadaAjax("obtener_mensajes_ajax.php", JSON.stringify([fecha_ultima_llamada, id]));
}

/**
 * Función que da opciones a un mensaje
 * @param {Event} e Recibe el evento por el cual se ha ejecutado la función
 */
function ajustes_mensaje(e) {
    /**
     * 
     * @param {HTMLElement} e 
     */
    function buscar_padre_mensaje(e) {
        if (e.classList.contains("mensaje")) { // Es el padre
            return e;
        } else {
            return buscar_padre_mensaje(e.parentElement);
        }
    }

    let padre = buscar_padre_mensaje(e.target),
        id_mensaje = padre.getAttribute("id_mensaje"),
        formData = new FormData();

    ultimo_ajuste_mensaje = padre;

    formData.append("datos", JSON.stringify({ "tipo": "devolver_opciones_mensaje", "id_mensaje": id_mensaje }));

    llamadaAjax_formData("ajustes_mensajes.php", formData);

    console.log(id_mensaje);
}

/**
 * Función que me comprobará a partir de un id si es la lista o no
 * @param {string} id_parametro El id que recibe
 * @returns {boolean} Devuelve "true" si no es la lista y devuelve "false" si es la lista
 */
function comprobar_noSer_lista(id_parametro) {
    if (id_parametro != "lista-contactos" && id_parametro != "lista-contactos-buscar") {
        return true;
    }

    return false; // En caso de ser la lista devolvemos false
}

/* ==================================
       VARIABLES GLOBALES
================================== */
var ancho_ventana = window.innerWidth,
    abierto_chat = false, // Guardamos si está el chat abierto o no
    ultimo_click_id, // Guardamos el último click de usuario que se hizo
    anchoInicial_Separador,
    anchoFinal_Separador,
    buscando = false;

/* ==================================
        FIN VARAIBLES GLOBALES
==================================

-----------------------------------------------------------------------------------------------------------------

==================================
        ABRIR ZONA MENSAJE
================================== */

function atrasChat_movil() {
    clearInterval(bucle_mensajes);

    if (!abierto_chat) { // Comprobamos que no se ha abierto ya
        if (ancho_ventana < 671) { // Versión móvil
            ocultar_movil_lista_usuarios.style.display = "none";
            mostrar_movil_chat.style.display = "block";
        }

        abierto_chat = true; // Le decimos que ya se ha abierto
    } else { // En caso de que esté abierto
        if (ancho_ventana < 671) { // Versión móvil
            ocultar_movil_lista_usuarios.style.display = "block";
            mostrar_movil_chat.style.display = "none";
        }

        abierto_chat = false; // Le decimos que se ha cerrado
    }
}

/**
 * Función que detecta que presiono un contacto y muestra el chat
 * @param {Event} e Recibe el evento con el que el usuario inicio la función
 */
function presionar_contacto(e) {
    ancho_ventana = window.innerWidth;

    // Comprobamos que no haga click en la lista
    if (comprobar_noSer_lista(e.target.id)) { // Entonces no habrá presionado la lista y sí un contacto
        id = e.target.parentElement.id; // Obtenemos el id del padre (que es el contacto en si)
        contacto = e.target.parentElement;
        if (!comprobar_noSer_lista(id)) { // Comprobamos si no hicimos click en el propio padre
            id = e.target.id; // En caso de hacer click en su padre, recogemos su id
            contacto = e.target;
        }

        if (id == "") {
            return;
        }

        for (let i = 0; i < contactos.length; i++) {
            if (contactos[i].classList.contains("activo")) {
                contactos[i].classList.remove("activo");
            }
        }

        contacto.classList.add("activo");

        // Quitamos la zona de presentación de la sección de los mensajes y mostramos el chat
        modificar_zona_chat(e);

        ultimo_click_id = id; // Almacenamos en quien hizo el último click, para hacer luego las comprobaciones

        // Ahora aquí haríamos una llamada AJAX mediante POST
    }
}

function modificar_zona_chat(e) {
    if (!abierto_chat) { // Comprobamos que no se ha abierto ya
        bucle_mensajes = setInterval(escuchador_mensajes, 500);
        llamadaAjax("insertar_chat.php", JSON.stringify([id]));

        zona_presentacion_mensajes.style.display = "none";
        zona_chat.style.display = "grid";

        if (ancho_ventana < 671) { // Versión móvil
            ocultar_movil_lista_usuarios.style.display = "none";
            mostrar_movil_chat.style.display = "block";
        }

        abierto_chat = true; // Le decimos que ya se ha abierto
    } else { // En caso de que esté abierto
        abierto_chat = false; // Le decimos que se ha cerrado
        clearInterval(bucle_mensajes);

        if (ultimo_click_id == id && !buscando) { // Y en caso de que presione a la misma persona entonces

            zona_chat.style.display = "none";
            contacto.classList.remove("activo");
            zona_presentacion_mensajes.style.display = "block";

            if (ancho_ventana < 671) { // Versión móvil
                ocultar_movil_lista_usuarios.style.display = "block";
                mostrar_movil_chat.style.display = "none";
            }

        } else {
            presionar_contacto(e);
        }
    }
}

/* ==================================
        FIN ABRIR ZONA MENSAJE
====================================

---------------------------------------------------------------------------------------------------------------------

==================================
        BUSCAR CONTACTO
================================== */
/**
 * Función que me va buscar los contactos con nombre similar
 */
function buscar_contacto_mensajes() {
    const input_buscar = document.getElementById("input-buscar-contacto-mensajes"),
        lista_contactos_normal = document.getElementById("lista-contactos"),
        lista_contactos_buscar = document.getElementById("lista-contactos-buscar");

    // Lo hago de esta manera, para no tener que borrar los contactos ya buscados una vez y si al acabar de buscar queremos volver a ver los usuarios más recientes, tendríamos que volver a hacer una llamada sql y así evitamos trabajo al servidor

    var contenido = input_buscar.value;

    if (contenido == "") { // Si el contenido está vacío entonces la lista de contacto se reinicia
        buscando = false;
        lista_contactos_buscar.style.display = "none"; // No mostramos el div de los resultados
        lista_contactos_normal.style.display = "flex"; // Mostraoms el div de los contactos ya prevargados
    } else { // En caso de que contenga algo de contenido
        buscando = true;
        lista_contactos_normal.style.display = "none"; // No mostramos el div de los contactos precargados
        lista_contactos_buscar.style.display = "flex"; // Mostramos el div de los resultados

        llamadaAjax("buscar_contacto.php", JSON.stringify([contenido]));

        //lista_contactos_buscar.innerHTML = "INSERTAR LLAMADA AJAX MEDIANTE POST";
    }
}

function insertarContactos(contactos) {
    const donde = document.getElementById("lista-contactos-buscar");

    donde.innerHTML = contactos;

    let lista_contactos = document.getElementsByClassName("contacto");

    agregarFuncionesArray(lista_contactos, "click", presionar_contacto);
}

/* ==================================
        FIN BUSCAR CONTACTO
==================================

--------------------------------------------------------------------------

==================================
        MOVER SEPARADOR
================================== */

function clickSeparador(e) {
    //console.log(e);

    anchoInicial_Separador = e.clientX;

    function moverSeparador(x) {
        console.log("X: " + x);
        document.documentElement.style.setProperty("--izquierda_separador", "calc((var(--padding-barra-tamanho-derecha_izquierda-pc_tablet) + var(--ancho-barra)) - " + e.clientX + "px)");
    }

    moverSeparador(e.clientX);

    function moverMouse(ev) {
        moverSeparador(ev.pageX);
    }

    function soltarMouse(ev) {
        ev.target.removeEvent
    }

    e.target.addEventListener("mousemove", moverMouse);

    //e.target.addEventListener("mousedown", moverMove, false);

    //e.target.style = "--izquierda_separador: " + e.clientX + "px;";
    //document.documentElement.style.setProperty("--izquierda_separador", "calc((var(--padding-barra-tamanho-derecha_izquierda-pc_tablet) + var(--ancho-barra)) - " + e.clientX + "px)");
}

/*
function moverSeparador(e){
    e.target.addEventListener("mouseup", soltarClickSeparador, false);
}

function soltarClickSeparador(e){
    console.log(e);

    anchoFinal_Separador = e.clientX;

    console.log("Resultado: " + (anchoFinal_Separador - anchoInicial_Separador));
}

/* ==================================
        FIN MOVER SEPARADOR
=====================================

------------------------------------------------------------------

=====================================
        MODIFICACIONES VENTANA
===================================*/
function cambiosVentana() {
    ancho_ventana = window.innerWidth;

    console.log("chat abierto: " + abierto_chat + ", ancho: " + ancho_ventana);

    const id_main = document.getElementById("main"),
        chat = document.getElementById("mostrar-mensajes");

    if (ancho_ventana >= 671) {
        id_main.style.display = "block";
    }

    if (abierto_chat && ancho_ventana < 671) {
        id_main.style.display = "none";
        chat.style.display = "block";
    }
}
/* ==================================
        FIN MODIFICACIONES VENTANA
=================================== 

---------------------------------------------------------------------

===================================================
        AJUSTES CONTACTO
=============================================== */
/**
 * Función que se ejecuta cuando da click derecho
 * @param {Event} e Recibe el evento por el cual ha ejecutado la función
 */
function ajustes_contacto(e) {
    if (!ajuste_contacto_abierto) { // no Está abierto
        const ajustes = {
            "bloquear": {
                "texto": "Bloquear/Desbloquear Grupo",
                "funcion": bloquear_grupo
            }
        };

        // FUNCIÓN SIN ACABAR

        let id, contacto,
            nuevoDiv, divApartado, nuevoP;

        // Comprobamos que donde se hace click no es en la lista si no en el contacto
        if (comprobar_noSer_lista(e.target.id)) {
            id = e.target.parentElement.id; // Obtenemos el id del padre (que es el contacto en si)
            contacto = e.target.parentElement;
            if (!comprobar_noSer_lista(id)) { // Comprobamos si no hicimos click en el propio padre
                id = e.target.id; // En caso de hacer click en su padre, recogemos su id
                contacto = e.target;
            }

            // Si existe el div, lo borramos
            borrar_elemento_id(id_div_flotante);

            // Creamos un div
            nuevoDiv = document.createElement("div");
            nuevoDiv.id = id_div_flotante;

            divApartado = document.createElement("h3");
            divApartado.innerHTML = "Ajustes Grupo: " + id + ".";
            divApartado.classList.add("texto_cabecera_ajustes_contacto");

            nuevoDiv.appendChild(divApartado);

            for (let llave_opcion in ajustes) {
                divApartado = document.createElement("div");
                divApartado.classList.add("opcion-grupo");
                nuevoP = document.createElement("p");
                nuevoP.innerHTML = ajustes[llave_opcion]["texto"];
                divApartado.appendChild(nuevoP);
                divApartado.addEventListener("click", (e) => { ajustes[llave_opcion]["funcion"](e, id); e.stopPropagation() }, false);
                nuevoDiv.appendChild(divApartado);
            }

            // Agregamos el div al contacto
            contacto.appendChild(nuevoDiv); // Agregamos el div al hijo
        }
    } else {
        let boton = buscar_botones_por_id(id_div_flotante);
        if (boton[0]) {
            boton[1].remove();
        }
    }

    ajuste_contacto_abierto = !ajuste_contacto_abierto;

}

/* ===================================================
        FIN AJUSTES CONTACTO
================================================== */

function insertar_chat(contenido, ultima_llamada) {
    const donde = document.getElementById("chat");

    donde.innerHTML = contenido;

    let escribir_mensaje = document.getElementById("enviar-mensaje").children[0],
        boton_microfono = document.querySelector("#voz-svg");

    escribir_mensaje.addEventListener("keyup", (e) => { if (e.key == "Enter") { enviar_mensaje(e) } }, false);

    boton_microfono.addEventListener("click", sistema_microfono, false);

    atras_chat_movil = document.getElementById("icono_atras-svg");
    atras_chat_movil.addEventListener("click", atrasChat_movil, false);

    boton_enviar_mensaje = document.getElementById("boton-enviar");
    boton_enviar_mensaje.addEventListener("click", enviar_mensaje, false);

    zona_mensajes = document.getElementById("contenido-chat");

    lista_mensajes = document.getElementsByClassName("mensaje");
    agregarFuncionesArray(lista_mensajes, "auxclick", ajustes_mensaje);
    agregarFuncionesArray(lista_mensajes, "contextmenu", prevenir_click_derecho_por_defecto);

    setTimeout(() => { zona_mensajes.scrollTop = zona_mensajes.scrollHeight; }, 150); // Bajamos el scroll al último mensaje

    llamadaAjax("actualizar_ultima_llamada.php", JSON.stringify([""]));
}

var microfono_actual;
function sistema_microfono() {
    // Variables globales
    let mediaRecorder;
    let recordedChunks = [];
    
    /* No hace falta ahora

    let secretKey = generateSecretKey();

    // Función para generar una clave secreta
    function generateSecretKey() {
        const array = new Uint8Array(16);
        return crypto.getRandomValues(array);
    }

    // Función para cifrar el archivo de audio al momento
    function encriptacion_instantanea(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => {
              const data = new Uint8Array(reader.result);
              crypto.subtle.importKey(
                'raw',
                secretKey,
                'AES-CBC',
                true,
                ['encrypt']
              ).then((importedKey) => {
                crypto.subtle.encrypt(
                  { name: 'AES-CBC', iv: new Uint8Array(16) },
                  importedKey,
                  data
                ).then((encryptedData) => {
                  const encryptedBlob = new Blob([encryptedData], { type: 'application/octet-stream' });
                  resolve(encryptedBlob);
                }).catch((err) => {
                  reject(err);
                });
              }).catch((err) => {
                reject(err);
              });
            };
            reader.onerror = reject;
            reader.readAsArrayBuffer(blob);
          });
    }

    // Función para cifrar el archivo de audio
    function encryptRecording(key, blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => {
                const data = new Uint8Array(reader.result);
                crypto.subtle.importKey(
                    'raw',
                    key,
                    'AES-CBC',
                    true,
                    ['encrypt']
                ).then((importedKey) => {
                    crypto.subtle.encrypt(
                        { name: 'AES-CBC', iv: new Uint8Array(16) },
                        importedKey,
                        data
                    ).then((encryptedData) => {
                        const encryptedBlob = new Blob([encryptedData], { type: 'audio/webm' });
                        resolve(encryptedBlob);
                    }).catch((err) => {
                        reject(err);
                    });
                }).catch((err) => {
                    reject(err);
                });
            };
            reader.onerror = reject;
            reader.readAsArrayBuffer(blob);
        });
    }

    // Función para descifrar el archivo de audio
    function decryptRecording(key, blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => {
                const encryptedData = new Uint8Array(reader.result);
                crypto.subtle.importKey(
                    'raw',
                    key,
                    'AES-CBC',
                    true,
                    ['decrypt']
                ).then((importedKey) => {
                    crypto.subtle.decrypt(
                        { name: 'AES-CBC', iv: new Uint8Array(16) },
                        importedKey,
                        encryptedData
                    ).then((decryptedData) => {
                        const decryptedBlob = new Blob([decryptedData], { type: 'audio/webm' });
                        resolve(decryptedBlob);
                    }).catch((err) => {
                        reject(err);
                    });
                }).catch((err) => {
                    reject(err);
                });
            };
            reader.onerror = reject;
            reader.readAsArrayBuffer(blob);
        });
    }

    */

    // Función para iniciar la grabación
    function startRecording(micro) {
        recordedChunks = [];

        const constraints = {
            audio: {
                deviceId: micro,
            },
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(function (stream) {
                mediaRecorder = new MediaRecorder(stream);

                mediaRecorder.addEventListener('dataavailable', function (e) {
                    if (e.data.size > 0) {
                        recordedChunks.push(e.data);
                    }
                    console.log(e);
                    console.log(recordedChunks);
                });

                console.log("--- Iniciamos Grabación ---");
                mediaRecorder.start();
            }).catch(function (err) {
                console.error('No se pudo acceder al micrófono:', err);
            });
    }

    // Función para detener la grabación
    function stopRecording() {
        mediaRecorder.stop();
        console.log("--- Fin Grabación ---");
    }

    // Función para guardar el archivo de audio
    function saveRecording() {
        
        /*
        const blob = new Blob(recordedChunks, { type: 'audio/webm' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'grabacion.webm';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        */

        /*
        const blob = new Blob(recordedChunks, { type: 'audio/wav' });
        const secretKey = generateSecretKey();

        
        encriptacion_instantanea(blob).then(
            function(encryptedBlob){
                const url = URL.createObjectURL(encryptedBlob);
                const audio = document.createElement("audio");
                audio.setAttribute("controls", "");
                document.querySelector("#chat").appendChild(audio);

                audio.src = url;
            }
        ).catch(
            function(err){
                console.err("Error al cifrar el archivo", err);
            }
        );
            */
           /*
        
        encryptRecording(secretKey, blob)
        .then(function (encryptedBlob) {
            const url = URL.createObjectURL(encryptedBlob);
            const audio = document.createElement("audio");
            audio.setAttribute("controls", "");
            document.querySelector("#chat").appendChild(audio);

            decryptRecording(secretKey, encryptedBlob).then(
                function(decryptedBlob){
                    let url_desencriptada = URL.createObjectURL(decryptedBlob);
                    audio.src = url_desencriptada;
                }
            ).catch(
                function(erro){
                    console.error("Error", erro);
                }
            );               

            const audio2 = document.createElement("audio");
            audio2.setAttribute("controls", "");
            document.querySelector("#chat").appendChild(audio2);

            audio2.src = url; // Si desencriptar

            const a = document.createElement('a');
            a.href = url;
            a.download = 'grabacion.encrypted';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        })
        .catch(function (err) {
            console.error('Error al cifrar el archivo:', err);
        });
            */

        //Para enviar al servidor
        const blob = new Blob(recordedChunks, {type: "audio/wav"}),
        formData = new FormData();

        formData.append("audio", blob, "audio.wav");

        llamadaAjax_formData("enviar_audio_mensajes.php", formData);

    }

    // Función para actualizar la lista de dispositivos de audio
    function updateAudioInputList(select) {
        navigator.mediaDevices.enumerateDevices()
            .then(function (devices) {
                const audioInputs = devices.filter(device => device.kind === 'audioinput');

                select.innerHTML = '';

                audioInputs.forEach(function (input) {
                    const option = document.createElement('option');
                    option.value = input.deviceId;
                    option.text = input.label || `Micrófono ${select.length + 1}`;
                    select.appendChild(option);
                });
            })
            .catch(function (err) {
                console.error('Error al obtener la lista de dispositivos de audio:', err);
            });
    }

    function crearDom() {
        let div_base = document.createElement("div"),
            select = document.createElement("select"),
            boton_escoger_micro = document.createElement("button");

        select.addEventListener("change", escoger_micro, false);
        boton_escoger_micro.addEventListener("click", escoger_micro, false);

        boton_escoger_micro.innerHTML = "Seleccionar Microfono";

        div_base.appendChild(select);
        div_base.appendChild(boton_escoger_micro);
        document.querySelector("#chat").appendChild(div_base);

        updateAudioInputList(select);
    }

    function escoger_micro(e) {
        let seleccionador = e.target.parentElement.querySelector("select"),
            boton_iniciar_grabar = document.createElement("button"),
            boton_finalizar_grabacion = document.createElement("button"),
            guardar_audio = document.createElement("button");

        boton_iniciar_grabar.innerHTML = "Grabar audio";
        boton_iniciar_grabar.addEventListener("click", e => { startRecording(seleccionador.value) }, false);

        boton_finalizar_grabacion.innerHTML = "Finalizar Grabación";
        boton_finalizar_grabacion.addEventListener("click", stopRecording, false);

        guardar_audio.innerHTML = "Guardar audio";
        guardar_audio.addEventListener("click", saveRecording, false);

        e.target.parentElement.appendChild(boton_iniciar_grabar);
        e.target.parentElement.appendChild(boton_finalizar_grabacion);
        e.target.parentElement.appendChild(guardar_audio);
    }

    // Actualizar la lista de dispositivos de audio
    crearDom();

}

function actualizar_ultima_llamada(ultima) {
    fecha_ultima_llamada = ultima;
}

function enviar_mensaje(e) {
    const mensaje = document.getElementById("enviar-mensaje").children[0].value;
    llamadaAjax("enviar_mensaje.php", JSON.stringify([id, mensaje]));
}

function subir_mensaje(contenido) {
    const input = document.getElementById("enviar-mensaje").children[0],
        lugar = document.getElementById("contenidos");

    input.value = "";
    input.focus();

    let nuevoDiv = document.createElement("div"),
        div_inline = document.createElement("div");

    nuevoDiv.classList.add("mensaje");
    nuevoDiv.classList.add("mio");
    nuevoDiv.setAttribute("id_mensaje", contenido["id_mensaje"]);
    div_inline.innerHTML = contenido["contenido"];

    nuevoDiv.appendChild(div_inline);

    lugar.appendChild(nuevoDiv);

    lista_mensajes = document.getElementsByClassName("mensaje");
    agregarFuncionesArray(lista_mensajes, "auxclick", ajustes_mensaje);
    agregarFuncionesArray(lista_mensajes, "contextmenu", prevenir_click_derecho_por_defecto);

    zona_mensajes.scrollTop = zona_mensajes.scrollHeight; // Bajamos el scroll al último mensaje
}

function recibir_mensaje(datos) {
    const lugar = document.getElementById("contenidos");

    let nuevoDiv = document.createElement("div"),
        div_inline = document.createElement("div");

    nuevoDiv.classList.add("mensaje");
    nuevoDiv.setAttribute("id_mensaje", datos["id_mensaje"]);
    div_inline.innerHTML = datos["contenido_mensaje"];

    nuevoDiv.appendChild(div_inline);
    lugar.appendChild(nuevoDiv);

    zona_mensajes.scrollTop = zona_mensajes.scrollHeight; // Bajamos el scroll al último mensaje

    llamadaAjax("actualizar_ultima_llamada.php", JSON.stringify([""]));
}

function bloquear_grupo(e, id_grupo) {
    console.log("Bloquear el grupo " + id_grupo + ".");
    let formData = new FormData();
    formData.append("datos", JSON.stringify(
        {
            "tipo": "bloquear_grupo",
            "id_grupo": id_grupo
        }
    ));

    llamadaAjax_formData("ajustes_mensajes.php", formData);

    ajustes_contacto(e);
}