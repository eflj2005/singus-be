<?php 
    /* Este recurso se encarga de validar el inicio de sesion y de ser coorreco crea el Token de autenticación */

    $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

    if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
        $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
        $this->contrlRespst->preparar(503,"Servicio No disponible BD, ".$error);                     // preparación de respuesta HTTP con error
    }
    else{                                                                               // Verificacion si NO hay error de servicio de la base de datos 
        $sql="SELECT * FROM usuarios WHERE documento = 1";                                   // Consultar la lista de administradores
        $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
        
        if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
            $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
            $this->contrlRespst->preparar(400, "Error al consultar ($sql)".$error);             // preparación de respuesta HTTP con error
        }else{                                                                              // Verificacion si NO hay errores en la consulta
            $resultado = $miConexion->GetResultados();                                          // Obtencion de resultados de la consulta 
            if( count($resultado) == 0 ){                                                       // Verificacion si NO existen de administradores
                $this->contrlRespst->preparar(404,"Administrador no detectado");                    // preparación de respuesta HTTP con error
            }
            else{                                                                               // Verificacion si existen de administradores 
                $this->contrlRespst->preparar(200,"Acceso correcto");                           // preparación de respuesta HTTP correcta
            }   
        }
    }
    $this->contrlRespst->responder();

?>