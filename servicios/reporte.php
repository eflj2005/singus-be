<?php 

require_once __DIR__ . '../../../vendor/autoload.php';
unset($content,$mpdf);

$mpdf = new \Mpdf\Mpdf();
ob_start();
include $_SERVER["DOCUMENT_ROOT"]."/juricol/recursos/reportes/RdemandasDeEmpleados.php";
$content  =  ob_get_clean();
$mpdf->WriteHTML($content);
$mpdf->Output();


?>

