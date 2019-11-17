<?PHP
    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Allow-Headers:*");
    header("Access-Control-Allow-Methods: *");

    require_once("config/config.php");                          //Llamado a archivo de configuración

    require_once("servicios/conexionbd.php");                   //Llamado a servicio de conexión BD
    require_once("servicios/controlrespuesta.php");             // Llamado al servicio "controlrespuesta", quien es el encargado de administrar
                                                                // las respuestas que retornen todos los recursos 

    $controlRespuesta = new ControlRespuesta();                        // instancia el servicio ControlRespuesta

    $metodo = $_SERVER['REQUEST_METHOD'];


    if($metodo == "OPTIONS"){

    }else{
        $accion = NULL;
        $token = apache_request_headers();
        
        if(!(isset($token['Authorization'])) || $token['Authorization'] == NULL || empty($token['Authorization'])){
            $token = NULL;
        }else {
            $token = $token['Authorization'];
        }

        //echo $metodo;

        if ($metodo == "POST" || $metodo == "PUT"){    

            
            $post_vars=file_get_contents("php://input"); // Extracción de datos
          
            $post_vars= json_decode($post_vars,true); // Descodificacion de json          
            echo "<p>post_vars: </p>";
            echo "<pre>";
            print_r($post_vars);
            echo "</pre>";  
            $accion = validarAccion($post_vars["accion"]);
            definirAccion($accion,$metodo,$token,$post_vars);
    
        }elseif ($metodo == "GET") {

            $accion = validarAccion($_GET['accion']);
            definirAccion($accion,$metodo,$token,$_GET);
    
        }elseif ($metodo == "DELETE"){

            $accion = validarAccion($_GET['accion']);
            definirAccion($accion,$metodo,$token,$_GET);

        }else {

            $respuesta->preparar("ERROR", 401,"Llamado por metodo erroneo");
            $respuesta->responder();

        }
    }



    function definirAccion($accion,$metodo,$token,$info){

        //require_once("servicios/token.php");
        require_once("servicios/enrutador.php"); 

        $miConexion = new ConexionBD ($GLOBALS["configuracion"]->database);    // Instancia del servicio ConexionBD
        $GLOBALS["controlRespuesta"]->asignarConexionBD($miConexion);          // Se asigna conexion de base de datos a Control de respuesta        
        $miConexion->Conectar();                                    // Metodo que ejecuta la conexion con la base de datos        
        
        if($miConexion->GetCodigoRespuesta()!= 200){
            $GLOBALS["controlRespuesta"]->preparar($miConexion->GetCodigoRespuesta(),"Error de Conexion, ".$miConexion->GetErrorConexion());
            $GLOBALS["controlRespuesta"]->responder();
        }
        else{

            if(gettype($accion) != "array"){

                if($accion == 'inicio'){
                    $enrutador->LlamarAccion($accion,$metodo,$info);
                }
                elseif( $accion == 'inicio_sesion'){

                    $enrutador->LlamarAccion($accion,$metodo,$info);

                }else{

/*
                    if(!(isset($token)) || $token == NULL || empty($token)){
                        $respuesta->preparar(401,'No existe token');
                        $respuesta->responder();
                    }else{
            
                        $result = $miToken->validar($token);
            
                        if($result != "Token valido"){
                            $respuesta->preparar(401,$result);
                            $respuesta->responder();
                        }else{
                            require_once("enrutador.php");
                        }
                    }          
*/                    

                    $GLOBALS["controlRespuesta"]->preparar(404,"Accion no existe");
                    $GLOBALS["controlRespuesta"]->responder();

                }
            }else {
                $GLOBALS["controlRespuesta"]->preparar($accion['Codigo'],$accion['Mensaje']);
                $GLOBALS["controlRespuesta"]->responder();
            }

        }
    }





    function validarAccion($accion){
        if(!(isset($accion)) || $accion == NULL || empty($accion)){
            return array("Codigo"=>404,"Mensaje"=>'Accion vacia o no enviada');
        }elseif(gettype($accion) == "string" ) {
            return $accion;
        }else {
            return array("Codigo"=>401,"Mensaje"=>'Tipo de dato de accion no valido');
        }
        
    }

 
?>