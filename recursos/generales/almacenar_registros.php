<?php

  // /* Este recurso se encarga de crear o modificar registros de acuerdo a la modalidad que se reciba */

  $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

  if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
    $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
    $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
  }
  else{                                                                     // Verificacion si NO hay error de servicio de la base de datos 
    $arregloInsercciones = array();
    $arregloActualizaciones = array();

    foreach ($datos as $clave => $dato){
      $modo = $dato["modo"];
      unset( $dato["modo"]);
      if($modo == 'I') $arregloInsercciones[]    = $dato;
      if($modo == 'A') $arregloActualizaciones[] = $dato;
    }  

    $instruccionSqlInserccion = $miConexion->ConstruirSQL("I", $tabla, $arregloInsercciones);
    $instruccionSqlActualiacion = $miConexion->ConstruirSQL("A", $tabla, $arregloActualizaciones);
    
    $miConexion->IniciarTransaccion();                                                     // Ejecución de consulta en la base de datos  

    $validador = true;
    
    foreach ($instruccionSqlInserccion as $clave => $instruccion){
      if($validador){
        $miConexion->EjecutarSQL($instruccion);
        if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
          $this->contrlRespst->preparar(203, 400,  $miConexion->GetError());             // preparación de respuesta HTTP con error
          $validador=false;
          $miConexion->ReversarInstruccionesSQL();
        }
      }
    }

    if($validador){
      foreach ($instruccionSqlActualiacion as $clave => $instruccion){
        if($validador){
          $miConexion->EjecutarSQL($instruccion);
          if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
            $this->contrlRespst->preparar(203, 400,  $miConexion->GetError());             // preparación de respuesta HTTP con error
            $validador=false;
            $miConexion->ReversarInstruccionesSQL();              
          }
        }
      }
    }

    if($validador){
      $this->contrlRespst->preparar(200, 200, true);
      if(!$miConexion->ConfirmarInstruccionesSQL()){
            echo $miConexion->GetError();
      }
    }

    $miConexion->TerminarTransaccion(); 
    $this->contrlRespst->responder();
    
  }

  
?>