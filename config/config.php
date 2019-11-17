<?PHP

//Define el modo de ejecución de la BD => DEV = Desarrollo / PRO = Producción
$modo = "DEV";


$database = null;

SWITCH ($modo){
  case "DEV":
    $database = array (
      "servidor"  => "localhost",
      "usuario"   => "singususer",
      "clave"     => "Niwde830509",
      "esquema"   => "singus"
    );
  break;
  case "PRO":
    $database = array (
      "servidor"  => "localhost",
      "usuario"   => "singususer",
      "clave"     => "Niwde830509",
      "esquema"   => "singus"
    );    
  break;
}

$respuesta = array(
  "database" => (object) $database,
  "jwt" => (object) array(
    "llave"     => "cvMqCI8ZAChZpqjK2pJ/ZBZu9J3TVVVfEPN5u7BzXiimXkB4gxIAlT/35aoSzS2/EhwejoTR0uBUTLQjTmfWrg==",
    "algoritmo" => "HS256"
  ) 
);


$configuracion = (object) $respuesta;

/*
echo "<p>Recursos: </p>";
echo "<pre>";
print_r($configuracion);
echo "</pre>";
*/
?>

