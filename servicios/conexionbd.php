<?php
    /* Esta clase se encarcara de prestar el servicio de conexion con la base de datos, de realizar las consultas y ademas de 
    alamcenar ciertos codigos de estados HTTP para las respuestas a los entes que llamen a los recursos  */

    class ConexionBD{
        private $servidor   =   "localhost"; //Servidor en el que se almacena la base de datos       
        private $usuario    =   "root";   // Usuario con acceso al servidor    
        private $contrasena =   "";    // Contraseña del usuario 
        private $nombreBD   =   "juricol"; // Nombre de la base de datos a utilizar        
        
        private $conexion           = null; // Atributo que conserva la conexion con la base de datos 
        private $codigoRespuesta    = null; // Atributo que almacena el codigo con el que se responde de la base de datos 
        private $cabeceraRespuesta  = null; // Atributo que almacena la cabecera asociada al codigo de respuesta 

        private $resultados = null; 

       //Metodo que obtiene el codigo de estado HTTP devuelto por la consulta 
        public function GetCodigoRespuesta(){
            return $this->codigoRespuesta;
        }
        // Metodo que obtiene los errores entregados por la base de datos cuando se realiza una consulta erronea 
        public function GetError(){
            return $this->conexion->error;
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
            $this->conexion = @new mysqli($this->servidor, $this->usuario, $this->contrasena, $this->nombreBD);
            
            if ($this->conexion->connect_error) {  
                                                    
                $this->codigoRespuesta = 503; 
                $this->cabeceraRespuesta = "HTTP/1.0 503 Service Unavailable"; 
            }
            else{                                   
                $this->codigoRespuesta = 200; 
                $this->cabeceraRespuesta = "HTTP/1.0 200 Ok";
            }
        }

       
        // Metodo que ejecuta la consulta sql respectiva al recurso que utiliza este metodo 
        public function EjecutarSQL($consultaSQL){
            //Ejecucion de la consulta y almacenamiento de cabeceras y codigo de acuerdo a la respuesta dada por la base de datos 
            if ( !($this->resultados = $this->conexion->query($consultaSQL)) )  { 
                $this->codigoRespuesta = 400;
                $this->cabeceraRespuesta = "HTTP/1.0 400 Bad Request";
            }else{

                    $this->codigoRespuesta = 200;
                    $this->cabeceraRespuesta = "HTTP/1.0 200 Ok";                
                
            }
        }
    }
?>