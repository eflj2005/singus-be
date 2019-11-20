<?php
/*
LISTA DE RFC HEaders
Successful 2xx		
HTTP/1.0	200	OK                              => Respuesta correcta
HTTP/1.0	201	Created                         => Inserción Correcta
HTTP/1.0	202	Accepted                        => Actualización Correcta
HTTP/1.0	203	Non-Authoritative Information   => ERROR CONTROLADO
HTTP/1.0	204	No Content                      => Validación fallida
HTTP/1.0	205	Reset Content                   => Eliminación Correcta
HTTP/1.0	206	Partial Content                 
Redirection 3xx		
HTTP/1.0	300	Multiple Choices
HTTP/1.0	301	Moved Permanently
HTTP/1.0	302	Found
HTTP/1.0	303	See Other
HTTP/1.0	304	Not Modified
HTTP/1.0	305	Use Proxy
HTTP/1.0	306	(Unused)
HTTP/1.0		
HTTP/1.0	307	Temporary Redirect
Client Error 4xx		
HTTP/1.0	400	Bad Request                     => Consulta SQL Erronea
HTTP/1.0	401	Unauthorized                    => Usuario No Autorizado
HTTP/1.0	402	Payment Required
HTTP/1.0	403	Forbidden                       => Llamado con metodo erroneo
HTTP/1.0	404	Not Found                       => Recurso no existente
HTTP/1.0	405	Method Not Allowed
HTTP/1.0	406	Not Acceptable
HTTP/1.0	407	Proxy Authentication Required
HTTP/1.0	408	Request Timeout
HTTP/1.0	409	Conflict
HTTP/1.0	410	Gone
HTTP/1.0	411	Length Required
HTTP/1.0	412	Precondition Failed
HTTP/1.0	413	Request Entity Too Large
HTTP/1.0	414	Request-URI Too Long
HTTP/1.0	415	Unsupported Media Type
HTTP/1.0	416	Requested Range Not Satisfiable
HTTP/1.0	417	Expectation Failed
Server Error 5xx		
HTTP/1.0	500	Internal Server Error
HTTP/1.0	501	Not Implemented
HTTP/1.0	502	Bad Gateway
HTTP/1.0	503	Service Unavailable             => BD No Disponible
HTTP/1.0	504	Gateway Timeout
HTTP/1.0	505	HTTP Version Not Supported
*/



    class ControlRespuesta{
        private $codigoActual;
        private $cabeceraActual;
        private $respuestaActual;
        private $conexionActual;

        private $listaCodigosInternos;

        public function __construct(){
            $this->listaCodigosInternos = array(
                200 => "Resultados",
                201 => "Inserción Correcta",
                202 => "Actualización Correcta",
                204 => "Validación Fallida",
                205 => "Eliminación Correcta",
                400 => "Consulta SQL fallida",
                403 => "Llamado con metodo erroneo",
                404 => "Recurso no existente",
                401 => "Usuario No Autorizado",
                503 => "BD No Disponible"
            );
        }

        public function asignarConexionBD( $conexion ){
            $this->conexionActual = $conexion;
        }

        public function obtenerConexion(){
           return $this->conexionActual;
        }

        /*
            Codigo Base: 200=>Respuestas Correctas, 203=>Error Controlado, 401=>Usuario No Autorizado
        */
        public function preparar($codigoBase, $CodigoMensaje, $resultados){
            $this->cabeceraActual = "HTTP/1.0 ".$codigoBase;                           
            $this->codigoActual = $CodigoMensaje;
            $this->respuestaActual= $resultados;
        }

        public function responder(){
            $respuesta = array( 
                "codigo"  => $this->codigoActual,
                "asunto"  => $this->listaCodigosInternos[$this->codigoActual],
                "mensaje" =>  $this->respuestaActual
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