<?php 
//sdas
    use Firebase\JWT\JWT;

    /* Este recurso se encarga de validar el inicio de sesion y de ser coorreco crea el Token de autenticación */
    
    $miConexion = $this->contrlRespst->obtenerConexion();                                                       // Asisgnacion de conexionBD a variable local

    if ($miConexion->GetCodigoRespuesta() == 503 ){                                                             // Validación si hay error de servicio de la base de datos 
        $error = $miConexion->GetError();                                                                           // Obtencion del error transmitido por la base de datos        
        $this->contrlRespst->preparar(503,"Servicio No disponible BD, ".$error);                                    // preparación de respuesta HTTP con error
    }
    else{                                                                                                       // Validación si NO hay error de servicio de la base de datos 
        $sql="SELECT * FROM usuarios WHERE documento = ".$documento;                                                // Consultar la lista de administradores
        $miConexion->EjecutarSQL($sql);                                                                             // Ejecución de consulta en la base de datos  
        
        if ($miConexion->GetCodigoRespuesta() == 400){                                                              // Validación si hay errores en la consulta
            $error = $miConexion->GetError();                                                                           // Obtencion del error transmitido por la base de datos
            $this->contrlRespst->preparar(203, 400, "Consulta: ||$sql|| Error:".$error);                                // preparación de respuesta HTTP con error
        }else{                                                                                                      // Validación si NO hay errores en la consulta
            $validación=false;                                                                                          // Definicion de variable de control en estado fallido
            $token = null;

            if( $miConexion->GetCantidadResultados() != 0 ){                                                                           // Validación si existen existe el usuario
                $registro = $miConexion->GetResultados();                                                                 // Obtencion de resultados de la consulta                 
                //password_Verify($clave,$registro->clave)
                //password_hash("rasmuslerdorf", PASSWORD_DEFAULT)
                if($registro->clave == $clave ){                                                                    // Validación si coincide la clave
                    $validación=true;                                                                                       // Cambio de variable de control a estado satisfactorio  
                    

                    $iat = time();
                  
                    $dataToken = array(
                        "jti"   =>  base64_encode(openssl_random_pseudo_bytes(32)),     //token id
                        "iat"   =>  $iat,                                               // momento creacion
                        "nbf"   =>  $iat + 10,                                          //  Momento minimo de uso
                        "exp"   =>  $iat + 86400,                                       //expiración segundos
                        "iss"   =>  $GLOBALS["configuracion"]->database->servidor,      //desde donde fue generado               
                        "data"  =>  array(                                              //datos adicionales
                          "id"=> $registro->id,
                          "nombres"=> $registro->nombres,
                          "apellidos"=> $registro->apellidos,
                          "correo"=> $registro->correo,
                          "roles_id"=> $registro->roles_id
                        )                                            
                    );
                    
                    $llave = base64_decode( $GLOBALS["configuracion"]->jwt->llave );
                    
                    $token = JWT::encode( $dataToken , $llave, $GLOBALS["configuracion"]->jwt->algoritmo );                    

                }
            }

            if($validación) $this->contrlRespst->preparar( 200, 200, $token );                                       // preparación de respuesta HTTP correcta
            else            $this->contrlRespst->preparar( 203, 204, "La información de ingreso es incorrecta");     // preparación de respuesta HTTP con error
        }
    }
    $this->contrlRespst->responder();

?>