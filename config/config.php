<?PHP

//Define el modo de ejecución de la BD => DEV = Desarrollo / PRO = Producción
$modo = "DEV3";


$database = null;

SWITCH ($modo){
  case "DEV1":
    $database = array (
      "servidor"  => "localhost",
      "usuario"   => "singususer",
      "clave"     => "Niwde830509",
      "esquema"   => "singus"
    );
  break;
  case "DEV2":
    $database = array (
      "servidor"  => "remotemysql.com:3306",
      "usuario"   => "kJda6oXEdO",
      "clave"     => "3HiwKtz86F",
      "esquema"   => "kJda6oXEdO"
    );
  break;
  case "DEV3":
    $database = array (
      "servidor"  => "db4free.net",
      "usuario"   => "singus_user",
      "clave"     => "Niwde830509",
      "esquema"   => "singus_pruebas"
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

