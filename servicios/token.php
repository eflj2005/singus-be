<?php

    use Firebase\JWT\JWT;

    class TokenCabecera{
        public $jti;
        public $iat;
        public $nbf;
        public $exp;
        public $iss;
    }

    class TokenDatosParaUsuario{
        public $id;
        public $documento;        
        public $nombres;
        public $apellidos;
        public $correo;
        public $rol;

        function __construct($datosRecibidos){
            $this->id           = $datosRecibidos->id;
            $this->documento    = $datosRecibidos->documento;            
            $this->nombres      = $datosRecibidos->nombres;
            $this->apellidos    = $datosRecibidos->apellidos;
            $this->correo       = $datosRecibidos->correo;
            $this->rol          = $datosRecibidos->rol;
        }
    }

    class TokenDatosParaCodigo{
        public $codigo;
        public $tipo;
        public $id;

        function __construct($datosRecibidos){
            $this->codigo   = $datosRecibidos->codigo;
            $this->tipo     = $datosRecibidos->tipo;
            $this->id       = $datosRecibidos->id;
        }
    }

    class Token {

        private $llave;
        private $algoritmo;
        private $cabecera;
        private $datos;

        public function __construct(){
            $this->llave = base64_decode( $GLOBALS["configuracion"]->jwt->llave );
            $this->algoritmo = $GLOBALS["configuracion"]->jwt->algoritmo;
            $this->cabecera = new TokenCabecera();
        }

        private function PrepararToken(){
            $iat = time();
            $this->cabecera -> jti = base64_encode(openssl_random_pseudo_bytes(32));     //token id
            $this->cabecera -> iat = $iat;                                               //Momento creacion
            $this->cabecera -> nbf = $iat + 10;                                          //Momento minimo de uso
            $this->cabecera -> exp = $iat + ( 5 * 60 * 60 ) ;                            //expiración segundos
            $this->cabecera -> iss = $GLOBALS["configuracion"]->database->servidor;      //desde donde fue generado               
        }


        private function CargarDatos($objetoDatos, $tokenUsuario = true){
            if( $tokenUsuario ) $this->datos = new TokenDatosParaUsuario($objetoDatos);
            else                $this->datos = new TokenDatosParaCodigo($objetoDatos);
        }

        public function GenerarToken($objetoDatos, $tokenUsuario = true){
            $this->PrepararToken();
            $this->CargarDatos($objetoDatos, $tokenUsuario);

            $informacionToken = array();
            array_push ( $informacionToken ,  (array) $this->cabecera );
            $informacionToken["data"] =  (array) $this->datos;
              
            $token = JWT::encode( $informacionToken , $this->llave, $this->algoritmo );

            return $token;
        }

        public function Validar($tokenRecibido){
            try{
                $data = JWT::decode( $tokenRecibido, $this->llave, array($this->algoritmo) );
                
                return true;
            }catch(Exception $error){
                echo "<p>";
                print_r($error->getMessage());
                echo "</p>";  
                return false;
            }
        }
    }


?>