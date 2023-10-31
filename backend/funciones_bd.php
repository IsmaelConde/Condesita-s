<?php

/**
 * Función que establece la conexión con la Base de Datos
 */
function establecerConexion(){
    $ipServidor = $GLOBALS["ipServer"];
    $usuario =  $GLOBALS["usuario_acceso_bd"];
    $contra =  $GLOBALS["contrasena_acceso_bd"];
    $nombreDb =  $GLOBALS["nombre_bd"];

    // Creamos la conexión
    $conexion = mysqli_connect($ipServidor, $usuario, $contra, $nombreDb);

    // Comprobamos la conexión
    if(!$conexion){
        die("No se ha establecido la conexión. ERROR");
    }

    //echo "Se ha establecido la conexión a la Base de Datos<br>";

    return $conexion; // Devolvemos la conexión
}

/**
 * Función que me inserta las querys
 * @param string $sql Recibe la sentencia sql que se desea
 * @param string $titulo Recibe la información de lo que se ha hecho
 * Sirve para:
 *  - Crear Base de Datos
 *  - Para Crear Tabla
 *  - Insertar Datos
 */
function conectarQuery($sql, $titulo){
    $conexion = establecerConexion(); $devolver;
    if(mysqli_query($conexion, $sql)){ // Insertamos la query
        //echo "Se ha ".$titulo." perfectamente<br>";
        $devolver = true;
    }else{
        echo "No se ha podido ".$titulo." ERROR: ".mysqli_error($conexion);
    }
    mysqli_close($conexion);
    return $devolver;
}

/**
 * Función que me inserta varias querys a la vez
 * @param string $sql Recibe la sentencia sql que se desea
 * Sirve para:
 *  - Insertar Datos
 */
function multiQuery($sql){
    $conexion = establecerConexion();
    $devolver = false;
    if(mysqli_multi_query($conexion, $sql)){
        $devolver = true;
    }else{
        echo "No se han podido hacer una multi query. Error: " . mysqli_error($conexion);
    }
    mysqli_close($conexion);

    return $devolver;
}

/**
 * Función que obtiene los datos de un sql
 * @param string $sql Recibe la cadena de texto (el sql)
 */
function obtenerDatos($sql){
    $conexion = establecerConexion();
    $resultado = mysqli_query($conexion, $sql);
    $devolver=[];

    if(!empty($resultado) && mysqli_num_rows($resultado) > 0){
        $devolver[0] = true; // Se han encontrado datos
    }else{
        $devolver[0] = false; // No se han encontrado datos
    }
    $devolver[1] = $resultado;
    mysqli_close($conexion);
    return $devolver;
}

/**
 * Función que borra una tabla
 * @param string $nombre_tabla Recibe el nombre de la tabla que queremos borrar
 */
function borrarTabla($nombre_tabla){
    $sql = "DROP TABLE " . $nombre_tabla . ";";
    if(conectarQuery($sql, "Tabla " . $nombre_tabla . " borrado")){
        return true;
    }
}

/**
 * Función que va crear una tabla nueva
 * @param string $nombre_tabla Le pasamos el nombre de la tabla a crear
 * @param array $atributos Recibe un array con los datos de las columnas
 * ----------------
 * FORMATO $atributos:
 * [
 * "parametros_obligatorios" => ["string1", "string2", "etc"], // --> Ej: ["nombre_columna", "tipo_columna"]
 * "columnas => [
 *                  [ // Primera columna
 *                      "nombre_columna" => "primera_columna", 
 *                      "tipo_columna" => "tipo_primera_columna" ,
 *                      "extras" => "Cosas a agregar" // --> no es obligatorio
 *                  ],
 *                  [ // Segunda columna
 *                      "nombre_columna" => "segunda_columna", 
 *                      "tipo_columna" => "tipo_segunda_columna"
 *                  ]
 *              ]
 * ]
 */
function crearTabla($nombre_tabla, $atributos){
    $sql = "CREATE TABLE IF NOT EXISTS " . $nombre_tabla . "(";

    /**
     * Comprobamos que incluye los parametros obligatorios
     */
    for($i = 0; $i < count($atributos["columnas"]); $i++){ // Recorremos todas las columnas

        $comprobar_atributos_obligatorios = []; // Dentro se agregarán booleanos para confirmar si contiene todos los atributos obligatorios

        for($j = 0; $j < count($atributos["parametros_obligatorios"]); $j++){ // Recorremos los parametros obligatorios
            if($atributos["columnas"][$i][$atributos["parametros_obligatorios"][$j]]){
                array_push($comprobar_atributos_obligatorios, true) ;
                // echo "i: " . $i . ". Existe " . $atributos["parametros_obligatorios"][$j] . " en columna:" . $i . "<br>";
            }
        }

        // Una vez hecha la lectura de la columna
        if(count($comprobar_atributos_obligatorios) != count($atributos["parametros_obligatorios"])){
            die("La columna \"" . $i . "\" no contiene los parametros obligatorios");
        }        
    }

    // Si todas las columnas contiene los datos obligatorios, creamos el sql
    for($i = 0; $i < count($atributos["columnas"]); $i++){
        
        foreach($atributos["columnas"][$i] as $dato => $valor){
            $sql .= $atributos["columnas"][$i][$dato] . " ";
        }

        if($i < count($atributos["columnas"]) - 1){
            $sql .= ", ";
        }
    }

    // Una vez finalizada la lectura de todas las columnas
    $sql .= ");";

    //echo $sql;

    return $sql;

    // Una vez que tenemos la sentencia, la ejecutamos
    //conectarQuery($sql, "Creado la tabla " . $nombre_tabla);
}

/**
 * Función que me inserta los datos en una tabla
 * @param string $nombreTabla La tabla a la que le introduciremos nuevos datos
 * @param array $valores Vendrán los valores a introducir
 * @param false|array Si es false, es que se introducirán los datos en todas las columnas, en caso de ser array, contendrá el nmobre de las columnas a las que se agregarán los valores anteriores
 * -----------------------
 * Tipo de datos:
 * - $valores = ["dato_columna1", "dato_columna2", "etc"];
 * - $columnas = ["nombre_columna1", "nombre_columna2", "etc"]
 */
function insertarDatos($nombre_tabla, $valores, $columnas = false){
    $sql = "INSERT IGNORE INTO " . $nombre_tabla;
    if(!function_exists("insertarValores")){
        /**
         * Función que me insertará los VALUES
         * @param array $valores Array con los valores
         * @return string Devuelve la sentencia sql de "VALUES(lo, que, sea)"
         */
        function insertarValores($valores){
            $sql .= " VALUES (";
            for($i = 0; $i < count($valores); $i++){
                $sql .= "'" . $valores[$i] . "'";
                
                if($i < count($valores) - 1){
                    $sql .= ", ";
                }
            }

            $sql .= ");";

            return $sql;
        }
    }

    if(!$columnas){ // Hay que darle valor a todas las columnas
       $sql .= insertarValores($valores);
    }else{ // Hay que decir en que columnas se van agregar los valores
        $sql .= "(";
        for($i = 0; $i < count($columnas); $i++){
            $sql .= $columnas[$i];

            if($i < count($columnas) - 1){
                $sql .= ", ";
            }
        }

        $sql .= ")" . insertarValores($valores);
    }

    //echo $sql . "\n";
    // Una vez hecha la sentencia, la ejecutamos
    if(conectarQuery($sql, "Subido 1 dato a la tabla " . $nombre_tabla)){
        return true;
    }
}

/**
 * Función que va agregar una columna a una tabla
 * @param string $nombre_tabla Recibe el nombre de la tabla
 * @param string $nombre_columna Recibe el nombre de la nueva columna
 * @param string $atributos_columna Recibe el tipo de Columna que es con sus extras como el not null o lo que sea
 */
function agregarColumna($nombre_tabla, $nombre_columna, $atributos_columna){
    $sql = "ALTER TABLE " . $nombre_tabla . " ADD " . $nombre_columna ." " . $atributos_columna . ";";

    //echo $sql;
    if(conectarQuery($sql, "Se ha agregado la columna \"" . $nombre_columna . "\" a la tabla \"" . $nombre_tabla . "\"")){
        return true;
    }
}

/**
 * Función que me borra una columna de una tabla
 * @param string $nombre_tabla Recibe el nombre de la tabla
 * @param string $nombre_columna Recibe el nombre de la columna que se quiere borrar
 */
function borrarColumna($nombre_tabla, $nombre_columna){
    $sql = "ALTER TABLE " . $nombre_tabla . " DROP COLUMN " . $nombre_columna . ";";

    if(conectarQuery($sql, "Se ha borrado la columna \"" . $nombre_columna . "\" de la tabla \"" . $nombre_tabla . "\"")){
        return true;
    }
}

/**
 * Función que me establece los valores de seguidores y seguidos
 * @param mysqli_query $resultado_sql Contiene la query de la sentencia hecha arriba
 * @return int Devuelve un número en concreto
 */
function establecer_numeros_datos($resultado_sql){
    if($resultado_sql[0]){
        // Si hay resultado
        return mysqli_fetch_assoc($resultado_sql[1])["suma"];
    }

    // En caso de que no haya resultados
    return 0;
}
?>