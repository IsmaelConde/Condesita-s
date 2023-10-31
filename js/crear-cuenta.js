/**
 * Datos Script:
 * @author Ismael Conde <trabajosdeismaelconde@gmail.com>
 * @description Script para la interacción del usuario con la página "crear-cuenta.html"
 * - Creado: 06/05/2023
 * 
 * - Editado: 06/05/2023
 */

var codigo_enviado = false;

/**
 * Función que se ejecuta cuando la página termina de cargar
 */
function cargado(){
    // Agregamos las interacciones de usuario
    const boton_crear_cuenta = document.getElementById("boton-crear_cuenta");

    // Le agregamos los listeners a las interacciones
    boton_crear_cuenta.addEventListener("click", crear_cuenta, false);
}

/* ========================================
        CREAR CUENTA
======================================== */
/**
 * Función que se ejecuta cuando el usuario da click en crear cuenta
 */
function crear_cuenta(e){
    if(!codigo_enviado){
        const datos = { // Guardamos los datos en un key array
            "nombre": document.getElementById("nombre"),
            "email": document.getElementById("email")
        };
    
        let hay_fallos = false, // Para hacer comprobaciones luego
        contenidos = []; // Donde guardaremos los datos para enviar al servidor
    
        for(let clave in datos){
            let comprobar_email = false, comprobar;
            if(clave == "email"){
                comprobar_email = true;
            }
    
            comprobar = comprobar_contenidos(datos[clave].value, true, comprobar_email);
            if(!comprobar[0]){
                // Su contenido no es correcto
                datos[clave].value = ""; // Reiniciamos sus valores
                datos[clave].placeholder = comprobar[1]; // Le agregamos un placeholder con el error
    
                hay_fallos = true;
            }else{ // En caso de que esté bien el contenido
                contenidos.push(datos[clave].value); // Almacenamos el contenido en la variable
            }
        }
    
        if(hay_fallos){ // En caso de que contenga errores
            return; // Finalizamos la función
        }
    
        // En caso de que todo esté correcto, continuamos

        let formData = new FormData();
        formData.append("datos", JSON.stringify({"nombre": contenidos[0], "email": contenidos[1], "metodo":"generar_codigo"}));
    
        llamadaAjax_formData("crear_cuenta.php", formData); // Le pasamos al servidor los datos
    }else{
        const datos = { // Guardamos los datos en un key array
            "nombre": document.getElementById("nombre"),
            "email": document.getElementById("email"),
            "pass": document.getElementById("password"),
            "confirmar_pass": document.getElementById("confirmar-password"),
            "codigo": document.getElementById("codigo_email")
        };
    
        let hay_fallos = false, // Para hacer comprobaciones luego
        contenidos = []; // Donde guardaremos los datos para enviar al servidor
    
        for(let clave in datos){
            let comprobar_email = false, comprobar;
            if(clave == "email"){
                comprobar_email = true;
            }
    
            comprobar = comprobar_contenidos(datos[clave].value, true, comprobar_email);
            if(!comprobar[0]){
                // Su contenido no es correcto
                datos[clave].value = ""; // Reiniciamos sus valores
                datos[clave].placeholder = comprobar[1]; // Le agregamos un placeholder con el error
    
                hay_fallos = true;
            }else{ // En caso de que esté bien el contenido
                contenidos.push(datos[clave].value); // Almacenamos el contenido en la variable
            }
        }
    
        if(hay_fallos){ // En caso de que contenga errores
            return; // Finalizamos la función
        }
    
        // En caso de que todo esté correcto, continuamos
        let formData = new FormData();
        formData.append("datos", JSON.stringify({"nombre": contenidos[0], "email": contenidos[1], "pass": contenidos[2], "confirmar_pass": contenidos[3], "codigo": contenidos[4], "metodo":"crear_cuenta"}));
    
        llamadaAjax_formData("crear_cuenta.php", formData); // Le pasamos al servidor los datos
    }

    e.target.disabled = true;

    setTimeout(() => {
        e.target.disabled = false;
    }, 500);
    
}