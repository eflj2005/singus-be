<?php

    var_dump($parametros);

    // /* Este recurso se encarga de verificar la existencia del usuario admministrador */

    // $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

    // if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
    //     $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
    //     $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
    // }
    // else{                                                                               // Verificacion si NO hay error de servicio de la base de datos 
    //     $sql="SELECT * FROM usuarios WHERE roles_id = 1";                                   // Consultar la lista de administradores
    //     $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
        
    //     if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
    //         $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
    //         $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
    //     }else{                                                                              // Verificacion si NO hay errores en la consulta

    //         $respuesta = false;
    //         if( $miConexion->GetCantidadResultados() > 0 ){                                                       // Verificacion si NO existen de administradores
    //             $respuesta = true;                 
    //         }
       
    //         $this->contrlRespst->preparar(200, 200, $respuesta);       // preparación de respuesta HTTP definida
    //     }
    // }
    // $this->contrlRespst->responder();
?>