<?php

    require_once '../../vendor/autoload.php';
    use Firebase\JWT\JWT;

    class Token {
        private $key = '__Esth !@xmp//ser357';
        private $jwt;
        
        public function prepararToken($datos){
            $time = time();
            $this->jwt = array( 'iat' => $time, // Tiempo que inició el token
                                'exp' => $time + (10 * 60 * 60), // Tiempo que expirará el token (+1 hora)
                                'data' => $datos);

            if($this->jwt['data'] == NULL || $this->jwt == NULL){
                echo "Token vacio";
            }else{
                $this->jwt = JWT::encode($this->jwt,$this->key);
                }

        }

        public function entregar(){
           return $this->jwt;
        }

        public function validar($jwt){
            try{
                $data = JWT::decode($jwt, $this->key, array('HS256'));
                return 'Token valido';
            }catch(Exception $error){
                return 'Token no valido:  '.$error->getMessage(). "\n";
            }
        }
    }


?>