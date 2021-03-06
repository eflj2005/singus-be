<?php
  use Firebase\JWT\JWT;

  /* Este recurso se encarga de validar el inicio de sesion y de ser coorreco crea el Token de autenticación */

  $miConexion = $this->contrlRespst->obtenerConexion();                                                               // Asisgnacion de conexionBD a variable local

  if ($miConexion->GetCodigoRespuesta() == 503 ){                                                                     // Validación si hay error de servicio de la base de datos 
    $error = $miConexion->GetError();                                                                               // Obtencion del error transmitido por la base de datos        
    $this->contrlRespst->preparar(503,"Servicio No disponible BD, ".$error);                                        // preparación de respuesta HTTP con error
  }
  else{                                                                                                               // Validación si NO hay error de servicio de la base de datos 
    $sql="SELECT id, documento, nombres, apellidos, correo, rol, clave, estado FROM usuarios WHERE documento = ".$documento;                                                    // Consultar la lista de administradores
    $miConexion->EjecutarSQL($sql);                                                                                 // Ejecución de consulta en la base de datos  
        
    if ($miConexion->GetCodigoRespuesta() == 400){                                                                  // Validación si hay errores en la consulta
        $error = $miConexion->GetError();                                                                           // Obtencion del error transmitido por la base de datos
        $this->contrlRespst->preparar(203, 400, "Consulta: ||$sql|| Error:".$error);                                // preparación de respuesta HTTP con error
    }
    else{                                                                                                          // Validación si NO hay errores en la consulta
      $validación=false;                                                                                          // Definicion de variable de control en estado fallido
      $token = null;
        
      if( $miConexion->GetCantidadResultados() == 1 ){                                                            // Validación si existen existe el usuario
        $registro = $miConexion->GetResultados()[0];                                                               // Obtencion de resultados de la consulta                 

        if($registro->estado!= 'I' && $registro->estado!= 'B'){                                                 // Validación si usuario bloqueado o inactivo
          //password_Verify($clave,$registro->clave)
          //password_hash("rasmuslerdorf", PASSWORD_DEFAULT)
          if(password_Verify($clave,$registro->clave)){                                                                    // Validación si coincide la clave

            unset( $registro->estado, $registro->clave );

            if($generarToken){
              $nuevoToken = new Token();
              $this->contrlRespst->preparar( 200, 200,  $nuevoToken->GenerarToken($registro) );                                            // preparación de respuesta HTTP con satisfactorio
            }
            else{
              $this->contrlRespst->preparar( 200, 200,  true );                                            // preparación de respuesta HTTP con satisfactorio              
            }

          }
          else{
            $this->contrlRespst->preparar( 203, 401, "Información de ingreso es incorrecta");     // preparación de respuesta HTTP con error
          }
        }
        else{
          $this->contrlRespst->preparar( 203, 401, "Usuario bloqueado o inactivo, comuniquese con el administrador" );    // preparación de respuesta HTTP con error
        }
      }
      else{
        $this->contrlRespst->preparar( 203, 401, "Información de ingreso es incorrecta");     // preparación de respuesta HTTP con error
      }            
    }
  }

  $this->contrlRespst->responder();
?>