<?php

    /* Este recurso se encarga de verificar la existencia del usuario admministrador */

    $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

    if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
        $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
        $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
    }
    else{                                                                               // Verificacion si NO hay error de servicio de la base de datos 
        $sql="SELECT * FROM ".$tabla." WHERE id = ".$datos["idBuscado"];                                     // Consultar la lista de administradores
        $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
        
        if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
            $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
            $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
        }else{                                                                              // Verificacion si NO hay errores en la consulta
            if( $miConexion->GetCantidadResultados() == 1 ){                                                       // Verificacion si NO existen de administradores
                $datosUsuario = $miConexion->GetResultados();
                echo "<p>Recibido: ".$datosUsuario->codigovalidacion."</p>";
                echo "<p>En BD: ".$datos["codigoRecibido"]."</p>";
                if($datosUsuario->codigovalidacion == $datos["codigoRecibido"] ){
                    $this->contrlRespst->preparar(200, 200, true);       // preparación de respuesta HTTP definida
                }
                else{
                    $this->contrlRespst->preparar(203, 401, "El codigo registrado no concuerda con el enviado");       // preparación de respuesta HTTP definida
                }
            }
            else{
                $this->contrlRespst->preparar(203, 401, "Información para procesar incorrecta");       // preparación de respuesta HTTP definida
            }
        }
    }
    //$this->contrlRespst->responder();
?>