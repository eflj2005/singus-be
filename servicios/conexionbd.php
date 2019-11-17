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

        // Metodo que obtiene los datos que entrega  la base de datos
        public function GetResultados(){
            return $this->resultados->fetch_all(MYSQLI_ASSOC);
        }

        //Metodo que obtiene el ID autogenerado de una petición SQL "INSERT" envida a la BD
        public function ConsultarIdInsertado(){
            return $this->conexion->insert_id;
        }
        
        // Metodo que obtiene la informacion de las filas afectadas por una consulta 
        public function ConsultarModificaciones(){
            return $this->conexion->affected_rows;
        }
        

        //Método que intenta establecer conexión con la BD
        public function Conectar(){
            //Dando uso de la libreria MySQLi de PHP se reaiza el intento de conexión y y la instancia de la 
            //conexión se conserva en el atributo $conexion
            $this->conexion = @new mysqli($this->servidor, $this->usuario, $this->clave, $this->nombreBD);
            
            if ($this->conexion->connect_error)     $this->codigoRespuesta = 503; 
            else                                    $this->codigoRespuesta = 200; 
        }
       
        // Metodo que ejecuta la consulta sql respectiva al recurso que utiliza este metodo 
        public function EjecutarSQL($consultaSQL){
            //Ejecucion de la consulta y almacenamiento de cabeceras y codigo de acuerdo a la respuesta dada por la base de datos 
            if ( !($this->resultados = $this->conexion->query($consultaSQL)) )  $this->codigoRespuesta = 400;
            else                                                                $this->codigoRespuesta = 200;
        }
    }
?>