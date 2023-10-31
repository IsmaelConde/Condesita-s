/**
 * Datos Script:
 * @author Ismael Conde <trabajosdeismaelconde@gmail.com>
 * @description Script para la interacción del usuario con la página "explorar.html"
 * - Creado: 06/05/2023
 * 
 * - Editado: 06/05/2023
*/

const id_div_flotante = "id_flotante_resultado"; // Le agregamos una constante de id    ;

var ancho_ventana;
/**
 * Función que se ejecuta cuando la página termina de cargar
 */
function cargado(){
    // Buscamos las interacciones
    const input_buscar_usuarios = document.getElementById("input-buscar_usuarios");

    // Agregamos listeners a las interacciones
    input_buscar_usuarios.addEventListener("click", buscar_usuarios, false);
    input_buscar_usuarios.addEventListener("keyup", buscar_usuarios, false);

    window.addEventListener("resize", reajustarContenidos, false);

    // Iniciamos otras funciones
    buscar_seccionActual();
    reajustarContenidos();
}

function reajustarContenidos(){
    ancho_ventana = document.querySelector("#publicaciones").offsetWidth;

    const lista_contenidos = document.getElementsByClassName("contenido"),
    ancho_minimo_contenido = "150",
    ancho_maximo_contenido = "260",
    numero_contenidos_total = lista_contenidos.length,
    margin = "8";

    let maximo_por_linea = 4,
    calculo_contenido,
    pixeles_contenido; 

    do{
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

    document.querySelector("#publicaciones").style.setProperty("--pixeles-cuadrados", (pixeles_contenido - (margin * 2) - 11) + "px");

    console.log("Ancho Ventana: " + ancho_ventana + ", maximo por linea: " + maximo_por_linea + ", pixeles por conteido: " + (pixeles_contenido - (margin * 2) - 2));
}

/**
 * Función que se va ejecutar cuando el usuario interactue con el buscador
 * @param {Event} e Recibe el evento por el cual se ha ejecutado esta función
 */
function buscar_usuarios(e){
    const id_zona_contenidos = "zona-contenidos";

    if(e.target.value == ""){ // Si el valor de busqueda es vacío
        borrar_elemento_id(id_div_flotante); // Lo borramos si existe
    }else{ // En caso de que no esté vacío
        if(!buscar_botones_por_id(id_div_flotante)[0]){
            let div_flotante;

            div_flotante = document.createElement("div"); // Generamos un div
            div_flotante.id = id_div_flotante;
            div_flotante.classList.add("movil");

            document.getElementById(id_zona_contenidos).appendChild(div_flotante); // Lo agregamos a la zona de contenidos
        }

        // Hacemos la llamada al servidor
        llamadaAjax("buscar_usuarios.php", JSON.stringify([e.target.value]));
    }
    // Tenemos que hacer una llamada al servidor y que este nos devuelva los usuarios con esos nombres
}

function insertar_usuarios(respuesta){
    let div_flotante = buscar_botones_por_id(id_div_flotante);

    if(div_flotante[0]){
        div_flotante[1].innerHTML = respuesta;
    }
}