<?php

  // /* Este recurso se encarga de crear o modificar registros de acuerdo a la modalidad que se reciba */

  $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

  $dbRefs = array();

  if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
    $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
    $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
  }
  else{                                                                     // Verificacion si NO hay error de servicio de la base de datos 
    $arregloInsercciones = array();
    $arregloActualizaciones = array();
    $arregloEliminaciones  = array();

    foreach ($datos as $clave => $dato){
      $modo = $dato["modo"];
      unset( $dato["modo"]);
      if($modo == 'I') $arregloInsercciones[]    = $dato;
      if($modo == 'A') $arregloActualizaciones[] = $dato;
      if($modo == 'E') $arregloEliminaciones[] = $dato;
    }  

    // $instruccionSqlInserccion = $miConexion->ConstruirSQL("I", $tabla, $arregloInsercciones);
    // $instruccionSqlActualiacion = $miConexion->ConstruirSQL("A", $tabla, $arregloActualizaciones);
    
    $miConexion->IniciarTransaccion();                                                     // Ejecución de consulta en la base de datos  

    $validador = true;

    foreach ($arregloInsercciones as $clave => $inserccion){
      if($validador){
        $instruccion=$miConexion->ConstruirSQL("I", $tabla, $inserccion);
        $miConexion->EjecutarSQL($instruccion);
        if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
          $this->contrlRespst->preparar(203, 400,  $miConexion->GetError());             // preparación de respuesta HTTP con error
          $validador=false;
          $miConexion->ReversarInstruccionesSQL();
        }
        else{
          $dbRefs[] = array( "dbRef" => $inserccion["dbRef"], "id" => $miConexion->ConsultarIdInsertado() );
        }
      }
    }    

    if($validador){
      foreach ($arregloActualizaciones as $clave => $actualizacion){
        if($validador){

          if($tabla == "usuarios" &&  $actualizacion["clave"] != "" ){
            $actualizacion["clave"] = password_hash($actualizacion["clave"], PASSWORD_DEFAULT);
          }

          $instruccion=$miConexion->ConstruirSQL("A", $tabla, $actualizacion);
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
      foreach ($arregloEliminaciones as $clave => $eliminacion){
        if($validador){


          $instruccion=$miConexion->ConstruirSQL("E", $tabla, $eliminacion);
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
      $this->contrlRespst->preparar(200, 200, array("estado"=>true, "dbRefs" => $dbRefs) );
      if(!$miConexion->ConfirmarInstruccionesSQL()){
            echo $miConexion->GetError();
      }
    }

    $miConexion->TerminarTransaccion(); 
    $this->contrlRespst->responder();
    
  }

  
?>