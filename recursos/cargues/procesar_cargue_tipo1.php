<?php

  // /* Este recurso se encarga de crear o modificar registros de acuerdo a la modalidad que se reciba */

  $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local


  if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
    $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
    $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
  }
  else{                                                                     // Verificacion si NO hay error de servicio de la base de datos 

    $sql= "".
    "SELECT personas.*, tiposdocumentos.sigla AS tipodocumento, municipios.descripcion AS expedicion ".
    "FROM personas ".
    " INNER JOIN tiposdocumentos ON personas.tiposdocumentos_id = tiposdocumentos.id ".
    " INNER JOIN municipios ON personas.municipios_id = municipios.id ".
    "ORDER BY iduniminuto ASC";
    $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
  
    if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
      $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
      $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
    }
    else{       
      $registrosPersonas = $miConexion->GetResultados();

      $sql= "".
      "SELECT *, CONCAT(instituciones_id,'_',programas_id,'_',tiposestudios_id) AS indice ".
      "FROM ofertas ".
      "ORDER BY indice ASC";
      $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
    
      if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
        $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
        $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
      }
      else{        
        $ofertasLista = $miConexion->GetResultados();

        switch($modoCargue){
          case 1:

            $nuevasPersonas = array();
            $nuevosEstudios = array();
            $cambiosRegistros = array();
            
            $sql= "".
              "SELECT estudios.*, personas.iduniminuto, programas.codigo AS programa, cohortes.descripcion AS cohorte ".
              "FROM personas ".
              " INNER JOIN estudios ON personas.id = estudios.personas_id ".
              " INNER JOIN ofertas ON ofertas.id = estudios.ofertas_id ".
              " INNER JOIN programas ON programas.id = ofertas.programas_id ".
              " INNER JOIN cohortes ON estudios.cohortes_id = cohortes.id ".
              "ORDER BY iduniminuto ASC";
            $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
            
            if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
              $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
              $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
            }
            else{       
              $registrosCarreras = $miConexion->GetResultados();
      
              $posicion = 0;
              foreach ($datos as $clave => $dato){
                $posicionPersona = BusquedaBinaria( $registrosPersonas, 0, count($registrosPersonas)-1, "iduniminuto", $dato["SPRIDEN_ID"], true );
                if ( $posicionPersona == false ) {
      
                  $encontrado=false;
                  $posicion = 0;
                  
                  while( ( $posicion < count($nuevasPersonas) ) && !$encontrado){                                   //OJO REVISAR NO DEBE FUNCIONAR
                    if( ($nuevasPersonas[$posicion] ==  $dato["SPRIDEN_ID"]) )    $encontrado = true;
                    else                                                          $posicion++;
                  }
      
                  if(!$encontrado){     $nuevasPersonas[] = $dato["ref"]; $nuevosEstudios[] = $dato["ref"]; }
                  else            {     $nuevosEstudios[] = $dato["ref"];                                   }
                  
                }
                else{
                  $encontrado=false;
                  $posicion = 0;
                  while( ( $posicion < count($registrosCarreras) ) && !$encontrado){
                    if( ($registrosCarreras[$posicion]->iduniminuto == $dato["SPRIDEN_ID"]) && ($registrosCarreras[$posicion]->programa == $dato["CARRERA"]) ){
                      $encontrado = true;
                    }
                    else{
                      $posicion++;
                    }
                  }
      
                  if(!$encontrado){
                    $nuevosEstudios[] = $dato["ref"];
                  }
                  else{

                    $posicionEstudio = $posicion;

                    $sql= "SELECT * FROM telefonos WHERE personas_id = '" . $dato["SPRIDEN_ID"] . "' ORDER BY tipo ASC, registro_fecha DESC";
                    $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos                   
                    if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
                      $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
                      $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
                    }else{
                      $registrosTelefonos = $miConexion->GetResultados();
      
                      $sql= "SELECT * FROM correos WHERE personas_id = '" . $dato["SPRIDEN_ID"] . "' ORDER BY tipo ASC, registro_fecha DESC";
                      $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos                   
                      if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
                        $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
                        $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
                      }
                      else{
                        $registrosCorreos = $miConexion->GetResultados();
        
                        $cambiosTemporal = Array();
                          
                        //TIPO_DOCUMENTO
                        if( strcmp( substr($dato["TIPO_DOCUMENTO"],0,2) , $registrosPersonas[$posicionPersona]->tipodocumento ) != 0 ){
                          $cambiosTemporal =  array_merge( $cambiosTemporal, Array("TIPO_DOCUMENTO" => $registrosPersonas[$posicionPersona]->tipodocumento ) );
                        }
                        //DOCUMENTO
                        if( $dato["DOCUMENTO"] != $registrosPersonas[$posicionPersona]->documento ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("DOCUMENTO" => $registrosPersonas[$posicionPersona]->documento ) );
                        }
                        //P_NOMBRE & S_NOMBRE
                        $nombre_1 = explode(" ", $registrosPersonas[$posicionPersona]->nombres)[0];
                        $nombre_2 = substr( $registrosPersonas[$posicionPersona]->nombres , strlen( $nombre_1 ) + 1 );
                        if( strcmp( $dato["P_NOMBRE"] , $nombre_1 ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("P_NOMBRE" =>  $nombre_1 ) );
                        }
                        if( strcmp( $dato["S_NOMBRE"] , $nombre_2 ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("S_NOMBRE" =>  $nombre_2 ) );
                        }
                        //APELLIDOS
                        if( strcmp( ($dato["APELLIDOS"]) , $registrosPersonas[$posicionPersona]->apellidos ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("APELLIDOS" => $registrosPersonas[$posicionPersona]->apellidos ) );
                        }
                        //CIUDAD_EXP_DOC
                        $ciudad = trim( explode( "(", $dato["CIUDAD_EXP_DOC"] )[0] );
                        if( strcmp( $ciudad , $registrosPersonas[$posicionPersona]->expedicion ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("CIUDAD_EXP_DOC" => $registrosPersonas[$posicionPersona]->expedicion ) );
                        }
                        //GENERO
                        if( $dato["GENERO"] != $registrosPersonas[$posicionPersona]->genero ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("GENERO" => $registrosPersonas[$posicionPersona]->genero ) );
                        }                        
                        // TEL_RE  
                        $encontrado=false;
                        $posicion = 0;
                        while( ( $posicion < count($registrosTelefonos) ) && !$encontrado){
                          if( ($registrosTelefonos[$posicion]->numero ==  $dato["TEL_RE"]) && ($registrosTelefonos[$posicion]->tipo == "F") )   $encontrado = true;
                          else                                                                                                                  $posicion++;
                        }          
                        if(!$encontrado){   $cambiosTemporal = array_merge( $cambiosTemporal, Array("TEL_RE" => "Nuevo" ) );   } 
                        // TEL_TR
                        $encontrado=false;
                        $posicion = 0;
                        while( ( $posicion < count($registrosTelefonos) ) && !$encontrado){
                          if( ($registrosTelefonos[$posicion]->numero ==  $dato["TEL_TR"]) && ($registrosTelefonos[$posicion]->tipo == "F") )   $encontrado = true;
                          else                                                                                                                  $posicion++;
                        }          
                        if(!$encontrado){   $cambiosTemporal = array_merge( $cambiosTemporal, Array("TEL_TR" => "Nuevo" ) );   }
                        // TEL_CEL
                        $encontrado=false;
                        $posicion = 0;
                        while( ( $posicion < count($registrosTelefonos) ) && !$encontrado){
                          if( ($registrosTelefonos[$posicion]->numero ==  $dato["TEL_CEL"]) && ($registrosTelefonos[$posicion]->tipo == "C") )  $encontrado = true;
                          else                                                                                                                  $posicion++;
                        }          
                        if(!$encontrado){   $cambiosTemporal = array_merge( $cambiosTemporal, Array("TEL_CEL" => "Nuevo" ) );   }
                        // MAIL_ESTU
                        $encontrado=false;
                        $posicion = 0;
                        while( ( $posicion < count($registrosCorreos) ) && !$encontrado){
                          if( ( strcmp( $registrosCorreos[$posicion]->correo , $dato["MAIL_ESTU"] ) == 0 ) && ($registrosCorreos[$posicion]->tipo == "I") )   $encontrado = true;
                          else                                                                                                                                $posicion++;
                        }          
                        if(!$encontrado){   $cambiosTemporal = array_merge( $cambiosTemporal, Array("MAIL_ESTU" => "Nuevo" ) );   }
                        //CARRERA
                        if( strcmp( $dato["CARRERA"] , $registrosCarreras[$posicionEstudio]->programa ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("CARRERA" => $registrosCarreras[$posicionEstudio]->programa ) );
                        }  
                        //PROMEDIO
                        if( $dato["PROMEDIO"] != $registrosCarreras[$posicionEstudio]->promedio ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("PROMEDIO" => $registrosCarreras[$posicionEstudio]->promedio ) );
                        }                   
                        //PER_GRADO
                        if( $dato["PER_GRADO"] != $registrosCarreras[$posicionEstudio]->cohorte ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("PER_GRADO" => $registrosCarreras[$posicionEstudio]->cohorte ) );
                        }                   
                        //FECHA_GRADO
                        if( strcmp( $dato["FECHA_GRADO"] , $registrosCarreras[$posicionEstudio]->grado_fecha ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("FECHA_GRADO" => $registrosCarreras[$posicionEstudio]->grado_fecha ) );
                        }  
                        //ACTA_GRADO
                        if( strcmp( $dato["ACTA_GRADO"] , $registrosCarreras[$posicionEstudio]->acta ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("ACTA_GRADO" => $registrosCarreras[$posicionEstudio]->acta ) );
                        }  
                        //LIBRO
                        if( strcmp( $dato["LIBRO"] , $registrosCarreras[$posicionEstudio]->libro ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("LIBRO" => $registrosCarreras[$posicionEstudio]->libro ) );
                        }  
                        //FOLIO
                        if( strcmp( $dato["FOLIO"] , $registrosCarreras[$posicionEstudio]->folio ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("FOLIO" => $registrosCarreras[$posicionEstudio]->folio ) );
                        }  
                        //N_DIPLOMA
                        if( strcmp( $dato["N_DIPLOMA"] , $registrosCarreras[$posicionEstudio]->diploma ) != 0 ){
                          $cambiosTemporal = array_merge( $cambiosTemporal, Array("N_DIPLOMA" => $registrosCarreras[$posicionEstudio]->diploma ) );
                        }  

                        if( count($cambiosTemporal) > 0 ){
                          $cambiosRegistros[] = Array( "referencia" => $dato["ref"], "cambios" => $cambiosTemporal );
                        }


                      }    

                    }                  

                  }
                  
                }
        
              }
      
              $this->contrlRespst->preparar(200, 200, array( "nuevasPersonas" => $nuevasPersonas, "nuevosEstudios" => $nuevosEstudios, "personasCambios" => $cambiosRegistros ) );
      
            }

          break;
          case 2:
            $miConexion->IniciarTransaccion();                                                     // Ejecución de consulta en la base de datos  
            $validador = true;
            $fecha = date("Ymd");
            // echo "<pre>";
            // var_dump($datos);
            // echo "</pre>";

            $registroPersonas = array();

            if( isset( $datos["arregloNuevasPersonas"] ) ){
              $nuevasPersonas = $datos["arregloNuevasPersonas"];        
              foreach ($nuevasPersonas  as $indice => $registro){

              //personas
              //direcciones
              //telefonos
              //correos

                if($validador){

                  // "RECTORIA",
                  // "NOMBRE_RECTORIA",
                  // "SEDE",
                  // "DESCRIPCION_SEDE",
                  // "SPRIDEN_ID", 
                  // "TIPO_DOCUMENTO", 
                  // "DOCUMENTO", 
                  // "APELLIDOS", 
                  // "P_NOMBRE", 
                  // "S_NOMBRE", 
                  // "SECUENCIA", 
                  // "FACULTAD", 
                  // "PROGRAMA", 
                  // "NOMBRE_PROGRAMA", 
                  // "PROGRAMAS_ACREDITADOS", 
                  // "CARRERA", 
                  // "CIUDAD_EXP_DOC", 
                  // "GENERO", 
                  // "STATUS_RESUL", 
                  // "FEC_CAMBIO_STATUS", 
                  // "EDAD", 
                  // "NIVEL", 
                  // "JORNADA_DESC", 
                  // "TEL_RE", 
                  // "TEL_TR", 
                  // "TEL_CEL", 
                  // "MAIL_ESTU", 
                  // "CREDITOS_TOTAL_INSCRITOS", 
                  // "CREDITOS_TOTAL_APROBADOS", 
                  // "PROMEDIO", 
                  // "STATUS_GRADO", 
                  // "PER_GRADO", 
                  // "ANIO_GRADO", 
                  // "FECHA_GRADO", 
                  // "ACTA_GRADO", 
                  // "LIBRO", 
                  // "FOLIO", 
                  // "N_DIPLOMA", 
                  // "SHRDGIH_HONR_CODE"

                  $registroPersona = array(
                    "id"                  => null,
                    "nacimiento_fecha"    => null,
                    "iduniminuto"         => $registro["SPRIDEN_ID"],
                    "nombres"             => $registro["P_NOMBRE"]." ".$registro["P_NOMBRE"],
                    "apellidos"           => $registro["APELLIDOS"],
                    "genero"              => $registro["GENERO"],
                    "documento"           => $registro["DOCUMENTO"],
                    "tiposdocumentos_id"  => $registro["foraneas"]["tiposdocumentos_id"],
                    "municipios_id"       => $registro["foraneas"]["municipios_id"],
                    "actualizacion_fecha" => $fecha,
                    "registro_fecha"      => $fecha,
                    "desempleado"         => "S",
                    "encuestaole"         => "N",
                    "habeasdata"          => "N",
                    "carnet"              => "N"
                  );

                  $registrosTelefonos= array();

                  if( isset( $registro["TEL_RE"] ) ){
                    if($registro["TEL_RE"] != ""){;
                      $registrosTelefonos[] = array(
                        "id"                => null,
                        "personas_id"       => null,
                        "numero"            => (int) $registro["TEL_RE"],
                        "tipo"              => "F",
                        "registro_fecha"    => $fecha               
                      );
                    }
                  }

                  if( isset( $registro["TEL_TR"] ) ){
                    if($registro["TEL_TR"] != ""){                
                      $registrosTelefonos[] = array(
                        "id"                => null,
                        "personas_id"       => null,
                        "numero"            => (int) $registro["TEL_TR"],
                        "tipo"              => "F",
                        "registro_fecha"    => $fecha               
                      );
                    }
                  }

                  if( isset( $registro["TEL_CEL"] ) ){
                    if($registro["TEL_CEL"] != ""){
                      $registrosTelefonos[] = array(
                        "id"                => null,
                        "personas_id"       => null,
                        "numero"            => (int) $registro["TEL_CEL"],
                        "tipo"              => "C",
                        "registro_fecha"    => $fecha               
                      );
                    }
                  }

                  $registroCorreos = array(
                    "id"                => null,
                    "personas_id"       => null,
                    "correo"            => $registro["MAIL_ESTU"],
                    "tipo"              => "I",
                    "registro_fecha"    => $fecha               
                  );

                  $instruccion=$miConexion->ConstruirSQL("I", "personas", $registroPersona);
                  $miConexion->EjecutarSQL($instruccion);

                  if ( $miConexion->GetCodigoRespuesta() == 400 ){                                      // Verificacion si hay errores en la consulta
                    $this->contrlRespst->preparar(203, 400,  $miConexion->GetError()." - SQL: ".$instruccion);             // preparación de respuesta HTTP con error
                    $validador=false;
                    $miConexion->ReversarInstruccionesSQL();
                  }
                  else{
                    $personas_id = $miConexion->ConsultarIdInsertado();
                    $registroPersona["id"] = $personas_id;

                    $registroPersonas[] = $registroPersona;
                    
                    // $registroCorreos["personas_id"]=$personas_id;
                    // $instruccion=$miConexion->ConstruirSQL("I", "correos", $registroCorreos);
                    // $miConexion->EjecutarSQL($instruccion);

                    // if ( $miConexion->GetCodigoRespuesta() == 400 ){                                      // Verificacion si hay errores en la consulta
                    //   $this->contrlRespst->preparar(203, 400,  $miConexion->GetError()." - SQL: ".$instruccion);             // preparación de respuesta HTTP con error
                    //   $validador=false;
                    //   $miConexion->ReversarInstruccionesSQL();
                    // }
                    // else{
                    //   $validador=true;
                    //   foreach ($registrosTelefonos as $indiceT => $telefono){
                    //     if($validador){
                    //       $telefono["personas_id"]=$personas_id;
                    //       $instruccion=$miConexion->ConstruirSQL("I", "telefonos", $telefono);
                    //       $miConexion->EjecutarSQL($instruccion);
          
                    //       if ( $miConexion->GetCodigoRespuesta() == 400 ){                                      // Verificacion si hay errores en la consulta
                    //         $this->contrlRespst->preparar(203, 400,  $miConexion->GetError()." - SQL: ".$instruccion);             // preparación de respuesta HTTP con error
                    //         $validador=false;
                    //         $miConexion->ReversarInstruccionesSQL();
                    //       }    
                    //     }                
                    //   }
                    // }
                  }
                }
              }
            }

            if( isset( $datos["arregloNuevosEstudios"] ) ){
              $nuevosEstudios = $datos["arregloNuevosEstudios"];
              $validador=true;
              foreach ($nuevosEstudios  as $indice => $registro){
                if($validador){

                  
                  
                  $registroEstudio = array(
                    "id"                    => null,
                    "personas_id"           => null,
                    "cohortes_id"           => $registro["foraneas"]["cohortes_id"],
                    "titulos_id"            => $registro["P_NOMBRE"]." ".$registro["P_NOMBRE"],
                    "grado_fecha"           => NULL,
                    "mecanismosgrados_id"   => 1,
                    "descripcionmecanismo"  => NULL,
                    "sedes_id"              => $registro["foraneas"]["tiposdocumentos_id"],
                    "ofertas_id"            => $registro["foraneas"]["municipios_id"],
                    "actualizacion_fecha"   => $fecha,
                    "registro_fecha"        => $fecha,
                    "promedio"              => NULL,
                    "acta"                  => NULL,
                    "libro"                 => NULL,
                    "folio"                 => NULL,
                    "diploma"               => NULL
                  );



                  echo "<pre>";
                  var_dump($registroEstudio);
                  echo "</pre>";

                  exit();

                  // `id` INT NOT NULL AUTO_INCREMENT,
                  // `personas_id` INT NOT NULL,
                  // `cohortes_id` INT NOT NULL,
                  // `titulos_id` INT NOT NULL,
                  // `grado_fecha` DATE NULL DEFAULT NULL,
                  // `mecanismosgrados_id` INT NOT NULL,
                  // `descripcionmecanismo` TEXT NULL,
                  // `sedes_id` INT NOT NULL,
                  // `ofertas_id` INT NOT NULL,
                  // `registro_fecha` DATE NOT NULL,
                  // `promedio` DECIMAL(3,1) NULL DEFAULT NULL,
                  // `acta` VARCHAR(20) NULL DEFAULT NULL,
                  // `libro` VARCHAR(20) NULL DEFAULT NULL,
                  // `folio` VARCHAR(20) NULL DEFAULT NULL,
                  // `diploma` VARCHAR(20) NULL DEFAULT NULL,

                }
              }
            }

            
            if( isset( $datos["arregloCambios"] ) ){
              $cambiosRegistros = $datos["arregloCambios"];
            }




            // $miConexion->ReversarInstruccionesSQL();


            
            
          
            if($validador)  {
              $this->contrlRespst->preparar(200,200, array( "nuevasPersonas" => $nuevasPersonas ) );
              $miConexion->TerminarTransaccion(); 
            }
          break;
        }
      }
      
    }


    $this->contrlRespst->responder();
  }



  function busquedaBinaria(Array $arreglo, $inicio, $fin, $atributo, $valorBuscado, $retornarPosicion = false){ 
    if ($fin < $inicio) 
        return false; 
   
    $mid = floor(($fin + $inicio)/2); 
    if ( $arreglo[$mid]->$atributo == $valorBuscado){
        if($retornarPosicion) $resultado = $mid;
        else                  $resultado = true;

        return $resultado; 
    }
  
    elseif ($arreglo[$mid]->$atributo > $valorBuscado) { 
  
        // call binarySearch on [inicio, mid - 1] 
        return busquedaBinaria($arreglo, $inicio, $mid - 1, $atributo, $valorBuscado, $retornarPosicion); 
    } 
    else { 
  
        // call binarySearch on [mid + 1, fin] 
        return busquedaBinaria($arreglo, $mid + 1, $fin, $atributo, $valorBuscado, $retornarPosicion); 
    } 
} 
  
?>