<?php
  
  /* Esta clase se encarcara de prestar el servicio de conexion con la base de datos, de realizar las consultas y ademas de 
  alamcenar ciertos codigos de estados HTTP para las respuestas a los entes que llamen a los recursos  */

  class ConexionBD{
    private $servidor   = null;  //Servidor en el que se almacena la base de datos       
    private $usuario    = null;   // Usuario con acceso al servidor    
    private $clave      = null;     // Contraseña del usuario 
    private $nombreBD   = null;  // Nombre de la base de datos a utilizar        
        
    private $conexion           = null; // Atributo que conserva la conexion con la base de datos 
    private $codigoRespuesta    = null; // Atributo que almacena el codigo con el que se responde de la base de datos 
    private $cabeceraRespuesta  = null; // Atributo que almacena la cabecera asociada al codigo de respuesta 

    private $resultados = null; 

    //configura el objeto de conección basado en el archivo de configuración
    function __construct( $config ) { 
      $this->servidor     = $config->servidor;
      $this->usuario      = $config->usuario;
      $this->clave        = $config->clave;
      $this->nombreBD     = $config->esquema;
    }

    public function CerrarConexion(){
      $this->conexion->close();
    }

     //Metodo que obtiene el codigo de estado HTTP devuelto por la consulta 
    public function GetCodigoRespuesta(){
      return $this->codigoRespuesta;
    }

    // Metodo que obtiene los errores entregados por la base de datos cuando se realiza una consulta erronea 
    public function GetError(){
      return $this->conexion->error;
    }
        
    public function GetErrorConexion(){
      return $this->conexion->connect_error;
    }
        
    // Metodo que obtiene la cabecera asociada al codigo HTTP
    public function GetCabeceraRespuesta(){
      return $this->cabeceraRespuesta;
    }

    //CONTROL DE TRANSACCIONES
    public function IniciarTransaccion(){         $this->conexion->autocommit(FALSE); }

    public function ConfirmarInstruccionesSQL(){  return $this->conexion->commit();   }

    public function ReversarInstruccionesSQL(){   $this->conexion->rollback();        }

    public function TerminarTransaccion(){        $this->conexion->autocommit(TRUE);  }

    // Metodo que obtiene los datos que entrega  la base de datos en un arreglo de objetos
    public function GetResultados(){
      $resultado = null;

      while ($objeto = $this->resultados->fetch_object()) {
        $resultado[] = $objeto;
      }

      return $resultado;
    }

    // Metodo que obtiene los datos que entrega  la base de datos
    public function GetCantidadResultados(){
      return $this->resultados->num_rows;
    }        

    //Metodo que obtiene el ID autogenerado de una petición SQL "INSERT" envida a la BD
    public function ConsultarIdInsertado(){
      return $this->conexion->insert_id;
    }
        
    // Metodo que obtiene la informacion de las filas afectadas por una consulta 
    public function ConsultarModificaciones(){
      return $this->conexion->affected_rows;
    }
        
    public function LiberarResultados(){
      // $result =$this->conexion->use_result();
      // $result->free_result ();
    }

    //Método que intenta establecer conexión con la BD
    public function Conectar(){
      //Dando uso de la libreria MySQLi de PHP se reaiza el intento de conexión y y la instancia de la 
      //conexión se conserva en el atributo $conexion
      $this->conexion = @new mysqli($this->servidor, $this->usuario, $this->clave, $this->nombreBD);
          
      if ($this->conexion->connect_error)     $this->codigoRespuesta = 503; 
      else                                    $this->codigoRespuesta = 200; 
    }
       
    // Metodo que ejecuta la(s) consulta(s) sql respectiva(s) al recurso que utiliza este metodo 
    public function EjecutarSQL($consultaSQL){
     
      //Ejecucion de una consulta y almacenamiento de cabeceras y codigo de acuerdo a la respuesta dada por la base de datos 
      if ( !($this->resultados = $this->conexion->query($consultaSQL)) )  $this->codigoRespuesta = 400;
      else                                                                $this->codigoRespuesta = 200;

    }
    
    // Metodo que extrae los nombres de los campos a partar del nombre de una tabla
    private function ObtenerCamposTabla(string $nombteTabla){
      $campos =array();                                                                       
      $this->EjecutarSQL("DESCRIBE ".$nombteTabla);
      foreach ($this->GetResultados() as $dato) $campos[] = $dato->Field;
      return $campos;
    }


    //Metodo construlle una instruccion SQL adecuada de acuerdo a partir de un tipo de consulta, un nombre de tabla y los datos aprocesar 
    public function ConstruirSQL(string $tipoConsulta, string $nombretabla, array $datosRecibidos){
      $instruccionSql= "";
      $campos = array();

      $camposTabla = $this->ObtenerCamposTabla($nombretabla);

      switch ($tipoConsulta){
        case "I":

          $nuevoDato=array();        

          foreach ($camposTabla as $claveCampos => $campo) {
            
              if( !array_key_exists ( $campo , $datosRecibidos ) )  $datosRecibidos[$campo] = 'NULL';
              else if( is_null($datosRecibidos[$campo]) )           $datosRecibidos[$campo] = 'NULL';
              else                                                  $datosRecibidos[$campo] = "'".$datosRecibidos[$campo]."'";

              $nuevoDato[] = $datosRecibidos[$campo];

          }

          $informacion  = implode ( "," ,  $nuevoDato );
          $instruccionSql = "INSERT INTO $nombretabla ( ".implode ( "," ,  $camposTabla )." ) VALUES ( $informacion )";
  
        break;
        case "A":

          $nuevoDato=array();

          foreach ($camposTabla as $claveCampos => $campo) {

            if( !array_key_exists ( $campo , $datosRecibidos ) )  $datosRecibidos[$campo] = 'NULL';
            else if (is_null($datosRecibidos[$campo]) )           $datosRecibidos[$campo] = $campo." = NULL";
            else                                                  $datosRecibidos[$campo] = $campo." = '".$datosRecibidos[$campo]."'";
              
            $nuevoDato[] = $datosRecibidos[$campo];

          }

          $informacion  = implode ( "," ,  $nuevoDato );
          $instruccionSql = "UPDATE $nombretabla SET $informacion WHERE ".$datosRecibidos['id'];

        break;
        case "E":

          $instruccionSql = "DELETE FROM $nombretabla WHERE id = ".$datosRecibidos['id'];

        break;        
      }

      return $instruccionSql;
    }
  }
?>