<?php

  $caracteristicas = json_decode($caracteristicas);

  // echo "<pre>";
  // var_dump($caracteristicas);
  // echo "</pre>";

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
      $sql="SELECT * FROM ".$tabla;                                   // Consultar la lista de administradores

      if( !is_null( $caracteristicas ) ){

        if( !is_null( $caracteristicas->filtros ) ){

          $condiciones=" ";
          $conteo=0;
          foreach ( $caracteristicas->filtros as $elemento){
            if($conteo==0)   $condiciones .= "WHERE ";
            $condiciones .=  $elemento->campo ." ".$elemento->condicion." '".$elemento->valor."' ";
            if( $conteo < ( count( $caracteristicas->filtros )-1 ) )   $condiciones .= "AND ";
            $conteo++;
          }
          $sql .= $condiciones;

        }

        if( !is_null( $caracteristicas->ordenamientos ) ){

          $ordenamientos=" ";
          $conteo=0;
          foreach ( $caracteristicas->ordenamientos as $elemento){
            if($conteo==0)   $ordenamientos .= "ORDER BY ";
            $ordenamientos .=  $elemento->columna ." ".$elemento->orden;
            if( $conteo < ( count( $caracteristicas->ordenamientos )-1 ) )   $ordenamientos .= ", ";
            $conteo++;
          }
          $sql .= $ordenamientos;

        }

      }

    }

    if($modo == "A"){
      $sql="SELECT ".$tabla.".*";

      if( !is_null( $caracteristicas->columnas ) ){

        $columnas=" ";
        $conteo=0;
        foreach ( $caracteristicas->columnas as $elemento){
          if($conteo==0)   $columnas .= ", ";
          if( !is_null( $elemento->tabla ) ) $columnas .= $elemento->tabla.".";
          $columnas .=  $elemento->columna;
          if( !is_null( $elemento->alias ) ) $columnas .= " AS ".$elemento->alias;
          if( $conteo < ( count( $caracteristicas->columnas )-1 ) )   $columnas .= ", ";
          $conteo++;                    
        }
        $sql .= $columnas;

      }

      $sql .= " FROM ".$tabla;

      if( !is_null( $caracteristicas->enlaces ) ){

        $enlaces=" ";
        foreach ( $caracteristicas->enlaces as $elemento){
          $enlaces .= "INNER JOIN ".$elemento->tablaE." ON ".$elemento->tablaPk.".id"." = ".$elemento->tablaFk.".".$elemento->tablaPk."_id ";
        }
        $sql .= $enlaces;

      }

      if( !is_null( $caracteristicas->filtros ) ){

        $condiciones=" ";
        $conteo=0;
        foreach ( $caracteristicas->filtros as $elemento){
          if($conteo==0)   $condiciones .= "WHERE ";
          if( !is_null( $elemento->tabla ) ) $condiciones .= $elemento->tabla.".";
          $condiciones .=  $elemento->campo ." ".$elemento->condicion." '".$elemento->valor."' ";
          if( $conteo < ( count( $caracteristicas->filtros )-1 ) )   $condiciones .= "AND ";
          $conteo++;
        }
        $sql .= $condiciones;

      }

      if( !is_null( $caracteristicas->ordenamientos ) ){

        $ordenamientos=" ";
        $conteo=0;
        foreach ( $caracteristicas->ordenamientos as $elemento){
          if($conteo==0)   $ordenamientos .= "ORDER BY ";
          $ordenamientos .=  $elemento->columna ." ".$elemento->orden;
          if( $conteo < ( count( $caracteristicas->ordenamientos )-1 ) )   $ordenamientos .= ", ";
          $conteo++;
        }
        $sql .= $ordenamientos;

      }



    }


    // echo "Consulta: || ".$sql." ||";
    
    $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
    
    if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
      $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
      $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
    }else{                                                                              // Verificacion si NO hay errores en la consulta
      $registros = $miConexion->GetResultados();
      if($tabla == "usuarios"){
        foreach ($registros as $indice => $registro){
          $registros[$indice]->clave = "";
        }
      }      
      
      $this->contrlRespst->preparar(200, 200, $registros );       // preparación de respuesta HTTP definida
    }
  }
  $this->contrlRespst->responder();
?>