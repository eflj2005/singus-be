<?PHP
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: *");
  header("Access-Control-Allow-Methods:  *");
  
  ini_set('display_errors', 1);
  error_reporting(E_ALL);


  require_once("config/config.php");                          //Llamado a archivo de configuración

  require_once("servicios/conexionbd.php");                   //Llamado a servicio de conexión BD
  require_once("servicios/controlrespuesta.php");             // Llamado al servicio "controlrespuesta", quien es el encargado de administrar
                                                              // las respuestas que retornen todos los recursos 
  require_once("servicios/token.php");                        // Llamado al servicio "Token", quien es el encargado de administrar los token                                                      

  require_once("vendor/autoload.php");
  
  $controlRespuesta = new ControlRespuesta();                    // instancia el servicio ControlRespuesta
  $controlToken = new Token();
  
  $metodo = $_SERVER['REQUEST_METHOD'];
  

  if($metodo == "OPTIONS"){}
  else{
    $accion = NULL;
    $token = apache_request_headers();

    foreach ($token as $campo => $valor){                                     // pasa a minuscula la clave Authorization si se detecta para estandarizar
      if( strtolower($campo) == "authorization" ){
        $token["authorization"] = $token[$campo];
      }
    }

    if( !( isset( $token['authorization'] ) ) || $token['authorization'] == NULL || empty( $token['authorization'] ) ){
        $token = NULL;
    }else {
      preg_match('/Bearer\s(\S+)/', $token["authorization"], $matches);
      $token =$matches[1];
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
        //  var_dump($post_vars);

        if( validarAccion( $post_vars["accion"] ) ) {    definirAccion($post_vars["accion"],$metodo,$token,$post_vars);     }
        else                                        {    $controlRespuesta->preparar(203, 404, false); $controlRespuesta->responder();    }
      break;
      case "GET":
      case "DELETE":
        if( validarAccion( $_GET["accion"] ) )      {    definirAccion($_GET["accion"],$metodo,$token,$_GET);          }
        else                                        {    $controlRespuesta->preparar(203, 404, false); $controlRespuesta->responder();    }                    
      break;
      default:
        $controlRespuesta->preparar(203, 403, false);
        $controlRespuesta->responder();                
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
          // echo "<p>AQUI2</p>";    

          // echo "<p>Accion: $accion </p>";
          // echo "<p>Token: </p>";
          // echo "<p>";
          // var_dump($token);
          // echo "</p>";   

          if(!(isset($token)) || $token == NULL || empty($token)){
            $GLOBALS["controlRespuesta"]->preparar(401,401,'No existe token');
            $GLOBALS["controlRespuesta"]->responder();
          }else{
            $miToken = new Token();

            if( !$miToken->validar($token) ){
              $GLOBALS["controlRespuesta"]->preparar(203,401,'Token no valido');
              $GLOBALS["controlRespuesta"]->responder();
            }else{
              $enrutador->LlamarAccion($accion,$metodo,$info);
            }
          }          
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