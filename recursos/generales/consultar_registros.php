<?php
    $filtros = json_decode($filtros);


    /* Este recurso se encarga de verificar la existencia del usuario admministrador */

    $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

    if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
        $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
        $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
    }  
    else{
        $sql="";
                                                                                       // Verificacion si NO hay error de servicio de la base de datos 
        if($modo == "S"){
            $sql="SELECT * FROM usuarios ";                                   // Consultar la lista de administradores
        }
        if($modo == "A"){

        }

        $condiciones="";
        if($filtros){
            $conteo=0;
            foreach ($filtros as $clave => $filtro){
                if($conteo==0)   $condiciones .= "WHERE ";
                $condiciones .=  $clave ." = '".$filtro."' ";
                if( $conteo < ( count($filtros)-1 ) )   $condiciones .= "AND ";
            }
        }
        $sql .= $condiciones;

        $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
        
        if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
            $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
            $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
        }else{                                                                              // Verificacion si NO hay errores en la consulta
      
            $this->contrlRespst->preparar(200, 200, array($miConexion->GetResultados()));       // preparación de respuesta HTTP definida
        }
    }
    $this->contrlRespst->responder();
?>