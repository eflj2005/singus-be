<?php
    /* Este recurso se encarga de obtener los campos de una tabla */

    $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

    if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
        $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
        $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
    }  
    else{
        $miConexion->EjecutarSQL("SHOW FULL COLUMNS FROM ".$tabla);                                                     // Ejecución de consulta en la base de datos  
        
        if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
            $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
            $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
        }else{                                                                              // Verificacion si NO hay errores en la consulta
            $campos = array();
            
            foreach ($miConexion->GetResultados() as $clave => $campo){
                $campos[] = array( 
                    "nombre"=> $campo->Field , 
                    "tipo" =>  $campo->Type , 
                    "restriccion" => $campo->Key ,
                    "comentarios" => $campo->Comment
                );
            }
            $this->contrlRespst->preparar(200, 200, $campos );       // preparación de respuesta HTTP definida
        }
    }
    $this->contrlRespst->responder();
?>