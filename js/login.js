/**
 * Datos Script:
 * @author Ismael Conde <trabajosdeismaelconde@gmail.com>
 * @description Script para la interacción del usuario con la página "login.html"
 * - Creado: 06/05/2023
 * 
 * - Editado: 06/05/2023
 */

/**
 * Función que se ejecuta cuando la página termina de cargar
 */
function cargado(){
    // Agregamos las interacciones de usuario
    const boton_iniciar_sesion = document.getElementById("boton-iniciar_sesion");

    // Le agregamos los listeners a las interacciones
    boton_iniciar_sesion.addEventListener("click", iniciar_sesion, false);
}

/* ========================================
        INICIAR SESIÓN
======================================== */
function iniciar_sesion(){
    // Obtenemos los values de los input
    const datos = [ // Metemos los datos en un Array
        document.getElementById("email"),
        document.getElementById("password")
    ];

    let fallos = false,
    contenidos = [];

    // Recorremos el bucle para comprobar sus contenidos
    for(let i = 0; i < datos.length; i++){
        let comprobar_email = false,
        comprobar;

        if(datos[i].type == "email"){
            comprobar_email = true;
        }
        
        comprobar = comprobar_contenidos(datos[i].value, true, comprobar_email);
        if(!comprobar[0]){
            datos[i].value = ""; // Borramos el valor
            datos[i].placeholder = comprobar[1];
            fallos = true;
        }else{
            contenidos.push(datos[i].value);
        }
    }

    // Comprobamos el estado de los datos
    if(fallos){ // En caso de que haya fallos
        return; // Terminamos de ejecutar
    }

    // En caso de que todo esté OK, seguimos
    llamadaAjax("iniciar_sesion.php", JSON.stringify(contenidos)); // Generamos la llamada Ajax al servidor
}