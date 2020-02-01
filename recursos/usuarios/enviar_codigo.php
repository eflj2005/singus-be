<?php
  // Import PHPMailer classes into the global namespace
  // These must be at the top of your script, not inside a function
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;
  use PHPMailer\PHPMailer\Exception;

  /* Este recurso se encarga de generar codigo de validación y enviar correo */

  $miConexion = $this->contrlRespst->obtenerConexion();                               // Asisgnacion de conexionBD a variable local

  if ($miConexion->GetCodigoRespuesta() == 503 ){                                     // Verificacion si hay error de servicio de la base de datos 
    $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos        
    $this->contrlRespst->preparar(203, 503, $error);                     // preparación de respuesta HTTP con error
  }
  else{  
        
    // Verificacion si NO hay error de servicio de la base de datos 
    $sql="SELECT * FROM usuarios WHERE id = ".$datos["idBuscado"];                                   // Consultar la lista de administradores
    $miConexion->EjecutarSQL($sql);                                                     // Ejecución de consulta en la base de datos  
        
    if ($miConexion->GetCodigoRespuesta() == 400){                                      // Verificacion si hay errores en la consulta
      $error = $miConexion->GetError();                                                   // Obtencion del error transmitido por la base de datos
      $this->contrlRespst->preparar(203, 400, $error);             // preparación de respuesta HTTP con error
    }
    else{                                                                              // Verificacion si NO hay errores en la consulta
      if( $miConexion->GetCantidadResultados() == 1 ){                                                       // Verificacion si NO existen de administradores
        
        $codigo="" ;
        for($posicion=1;$posicion<=7;$posicion++){
          if($posicion != 4){
            $selector = random_int (0 , 1 );
            if($selector==0)  $ascii = random_int (48 , 57 );                       //se eligio numero
            else              $ascii = random_int (65 , 90 );                       //se eligio letra
            $codigo .= chr($ascii);
          }
          else{
            $codigo .= "-";
          }
        }
          
        $datosUsuario = $miConexion->GetResultados()[0];
        $miConexion->EjecutarSQL("UPDATE usuarios SET codigovalidacion='".$codigo."' WHERE id=".$datos["idBuscado"]);


        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
          //Server settings
          // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
          $mail->isSMTP();                                            // Send using SMTP
          $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = 'eflj2005@gmail.com';                     // SMTP username
          $mail->Password   = 'Niwde830509*';                               // SMTP password
          $mail->SMTPSecure = "TLS";         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
          $mail->Port       = 587;                                    // TCP port to connect to

          // SMTP username: Your Gmail address
          // SMTP password: Your Gmail password
          // SMTP server address: smtp.gmail.com
          // Gmail SMTP port (TLS): 587
          // SMTP port (SSL): 465
          // SMTP TLS/SSL required: yes


          //Recipients
          $mail->setFrom('eflj2005@gmail.com', 'S.in.G.U.S. Correo Automático');
          $mail->addAddress($datosUsuario->correo, $datosUsuario->nombres." ".$datosUsuario->apellidos);     // Add a recipient
          // $mail->addAddress('ellen@example.com');               // Name is optional
          // $mail->addReplyTo('info@example.com', 'Information');
          // $mail->addCC('cc@example.com');
          // $mail->addBCC('bcc@example.com');

          // Attachments
          // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
          // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML
          $mail->Subject = "S.In.G.U.S. - Código de validación";
          $mail->Body    = "Este es su codigo de validación => ".$codigo." <=";
          $mail->AltBody = "Este es su codigo de validación => ".$codigo." <=";

          $mail->send();

          $this->contrlRespst->preparar(200, 200, true);       // preparación de respuesta HTTP definida
        } 
        catch (Exception $e) {
          $this->contrlRespst->preparar(203, 401, "Su mensaje no a podido ser enviado: Error: ".$mail->ErrorInfo); 
        }           
      }
      else{
        $this->contrlRespst->preparar(203, 401, "Información para procesar incorrecta");       // preparación de respuesta HTTP definida
      }
    }    
  }
  @$this->contrlRespst->responder();
?>