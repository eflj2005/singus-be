<?php

    class RouterService{

        private $recursos = array();
        private $contrlRespst = null;
        private $respuestaError = null;

 
        public function __construct($controlR_recibido){
            $this->contrlRespst = $controlR_recibido;
        }

        public function AgregarRecurso($accion,$metodo,$ruta){
            $this->recursos[$accion] = (object) [ "metodo" => $metodo,  "ruta" => $ruta ]; 
        }

        public function ObtenerRecursos(){
            return $this->recursos;
        }

        private function BuscarRecurso($accionRecibida){
            $resultado = array_key_exists ( $accionRecibida , $this->recursos );
            if(!$resultado) $this->respuestaError = array( "codigo" => 404 , "mensaje" => false);
            return $resultado;
        }

        private function EvaluarMetodo($accionRecibida, $metodoRecibido ){
            $resultado = false;
            if(strcmp ($this->recursos[$accionRecibida]->metodo, $metodoRecibido) == 0) $resultado = true;
            if (!$resultado) $this->respuestaError = array( "codigo" => 403 , "mensaje" => false);
            return $resultado;
        }

        public function LlamarAccion($accion,$metodo,$parametros){
            $resultado = false;

            
            if( $this->BuscarRecurso($accion) ){

                if( $this->EvaluarMetodo($accion,$metodo) ){

                    $resultado = true;
                }                    
            }
            
            if($resultado){
                if($parametros == NULL || empty($parametros))   $parametros = NULL;
                else                                            extract($parametros);   // convierte en variale local cada campo del array $info 
                
                require_once($this->recursos[$accion]->ruta);                           //Cambia dinamicamente la sección de codigo asociada al recurso solicitiado

            }
            else{
                $this->contrlRespst->preparar( 203, $this->respuestaError["codigo"], $this->respuestaError["mensaje"] );
                $this->contrlRespst->responder();    
            }

        }
    }

    $enrutador = new RouterService($GLOBALS["controlRespuesta"]);

    $enrutador->AgregarRecurso("inicio","GET","recursos/usuarios/validar_administrador.php");
    $enrutador->AgregarRecurso("iniciar_sesion","POST","recursos/usuarios/iniciar_sesion.php");
    $enrutador->AgregarRecurso("registrar_agenda","POST","recursos/agendamiento/guardar_agenda.php");
    $enrutador->AgregarRecurso("registrar_agenda","POST","recursos/agendamiento/guardar_agenda.php");


?>