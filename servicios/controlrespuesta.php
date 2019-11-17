<?php
/*
HTTP/1.0 400 Consulta SQL Erronea
HTTP/1.0 401 No Autorizado
HTTP/1.0 403 Prohibido
HTTP/1.0 404 No Encontrado
HTTP/1.0 200 OK
HTTP/1.0 204 No hay contenido
HTTP/1.0 503 BD No Disponible
*/

    class ControlRespuesta{
        private $codigoActual;
        private $cabeceraActual;
        private $respuestaActual;
        private $conexionActual;

        public function asignarConexionBD( $conexion ){
            $this->conexionActual = $conexion;
        }

        public function obtenerConexion(){
           return $this->conexionActual;
        }

        public function preparar($codigo, $resultados){
            $this->codigoActual = $codigo;
            $this->cabeceraActual = "HTTP/1.0 ".$codigo;               
            $this->respuestaActual= $resultados;
            if($codigo!=200  ){
                $this->cabeceraActual = $this->cabeceraActual." ".$resultados;   
            }
            else{
                $this->cabeceraActual = $this->cabeceraActual." OK";   
            }
        }

        public function responder(){
                $respuesta = array( 
                    "codigo"=> $this->codigoActual,  
                    "mensaje"=>  $this->respuestaActual,
                );
                header($this->cabeceraActual);
                echo json_encode($respuesta);
                
        }

        public function responderToken($token){
            $respuesta = array( 
                "codigo"=> $this->codigoActual,  
                "mensaje"=>  $this->respuestaActual,
                "token"=> $token
            );             
            echo json_encode($respuesta);
            header($this->cabeceraActual);
        }

    }

?>