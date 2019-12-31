<?PHP
  header("Access-Control-Allow-Origin:*");
  header("Access-Control-Allow-Headers:*");
  header("Access-Control-Allow-Methods: *");

  require_once("config/config.php");                          //Llamado a archivo de configuración

  require_once("servicios/conexionbd.php");                   //Llamado a servicio de conexión BD
  require_once("servicios/controlrespuesta.php");             // Llamado al servicio "controlrespuesta", quien es el encargado de administrar
                                                       // las respuestas que retornen todos los recursos 

  require_once("vendor/autoload.php");
  
  $controlRespuesta = new ControlRespuesta();                        // instancia el servicio ControlRespuesta
  
  $metodo = $_SERVER['REQUEST_METHOD'];
  
  if($metodo == "OPTIONS"){}
  else{
    $accion = NULL;
    $token = apache_request_headers();
    
    if(!(isset($token['Authorization'])) || $token['Authorization'] == NULL || empty($token['Authorization'])){
        $token = NULL;
    }else {
        $token = $token['Authorization'];
    }
    
    switch ($metodo){
      case "POST":
      case "PUT":
        $post_vars=file_get_contents("php://input");    // Extracción de datos
        $post_vars= json_decode($post_vars,true);       // Descodificacion de json

        // echo "Accion: ".$post_vars["accion"];
        // echo "<p>post_vars: </p>";
        // echo "<pre>";
        // print_r($post_vars);
        // echo "</pre>";    
        // var_dump($post_vars);

        if( validarAccion( $post_vars["accion"] ) ) {    definirAccion($post_vars["accion"],$metodo,$token,$post_vars);     }
        else                                        {    $respuesta->preparar(203, 404, false); $respuesta->responder();    }
      break;
      case "GET":
      case "DELETE":
        if( validarAccion( $_GET["accion"] ) )      {    definirAccion($_GET["accion"],$metodo,$token,$_GET);          }
        else                                        {    $respuesta->preparar(203, 404, false); $respuesta->responder();    }                    
      break;
      default:
        $respuesta->preparar(203, 403, false);
        $respuesta->responder();                
      break;
    }
  }

  function definirAccion($accion,$metodo,$token,$info){

    //require_once("servicios/token.php");
    require_once("servicios/enrutador.php"); 

    $miConexion = new ConexionBD ($GLOBALS["configuracion"]->database);    // Instancia del servicio ConexionBD
    $GLOBALS["controlRespuesta"]->asignarConexionBD($miConexion);          // Se asigna conexion de base de datos a Control de respuesta        
    $miConexion->Conectar();                                                // Metodo que ejecuta la conexion con la base de datos        
    
    if($miConexion->GetCodigoRespuesta()!= 200){
      $GLOBALS["controlRespuesta"]->preparar( 203, $miConexion->GetCodigoRespuesta(), $miConexion->GetErrorConexion() );
      $GLOBALS["controlRespuesta"]->responder();
    }
    else{
      if(
        $accion == 'inicio' || 
        $accion == 'iniciar_sesion' || 
        $accion == 'generar_codigo' ||
        $accion == 'validar_codigo'
      ){
          $enrutador->LlamarAccion($accion,$metodo,$info);
      }
      else{

        if(isset($info["conSeguridad"])) $info["conSeguridad"]  = json_decode($info["conSeguridad"]); 

        if ( 
          ( 
            $accion == 'procesar_registros' || 
            $accion == 'obtener_registros' || 
            $accion == 'obtener_campos' 
          ) && 
          !$info["conSeguridad"] 
        ){    
           $enrutador->LlamarAccion($accion,$metodo,$info);
        }
        else{
          echo "<p>AQUI2</p>";    
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
  
  $GLOBALS["controlRespuesta"]->preparar(203, 404, false);
  $GLOBALS["controlRespuesta"]->responder();        
        }
      }
    }
    $miConexion->CerrarConexion();
  }

  function validarAccion($accion){
    $validar = true;
    if(!(isset($accion)) || $accion == NULL || empty($accion) || gettype($accion) == "array")  $validar = false;
    return $validar;    
  }

 
?>