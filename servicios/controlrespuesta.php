<?php

    class ControlRespuesta{
        private $codigoActual;
        private $cabeceraActual;
        private $respuestaActual;
        private $conexionActual;

        public function __construct($conexion){
            $this->conexionActual = $conexion;
        }

        public function preparar($codigo, $resultados){
            switch($codigo){
                case 401:
                    $this->codigoActual = 401;
                    $this->cabeceraActual = "HTTP/1.0 401 Unauthorized";   
                    $this->respuestaActual= $resultados;
                    break;
                case 403:
                    $this->codigoActual = 403;
                    $this->cabeceraActual = "HTTP/1.0 403 Forbidden";   
                    $this->respuestaActual= $resultados;
                break;
                case 404:
                    $this->codigoActual = 404;
                    $this->cabeceraActual = "HTTP/1.0 404 Not Found";  
                    $this->respuestaActual= $resultados;
                break;
                case 503:
                    $this->codigoActual = 503;
                    $this->cabeceraActual = $this->conexionActual->GetCabeceraRespuesta();   
                    $this->respuestaActual= $resultados;
                break;
                case 400:
                    $this->codigoActual = 400;
                    $this->cabeceraActual = $this->conexionActual->GetCabeceraRespuesta();  
                    $this->respuestaActual= $resultados;
                break;
                case 200:
                    $this->codigoActual = 200;
                    $this->cabeceraActual = "HTTP/1.0 200 ok";    
                    $this->respuestaActual= $resultados;
                    
                break;
                case 204:
                    $this->codigoActual = 204;
                    $this->cabeceraActual = "HTTP/1.0 204 No Content";    
                    $this->respuestaActual= $resultados;
            }
        }

        public function responder(){
                $respuesta = array( 
                    "codigo"=> $this->codigoActual,  
                    "mensaje"=>  $this->respuestaActual,
                );   
                echo json_encode($respuesta);
                header($this->cabeceraActual);
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