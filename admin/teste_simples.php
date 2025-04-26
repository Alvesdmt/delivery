<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../vendor/autoload.php');

try {
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML('Teste');
    $mpdf->Output('teste.pdf', 'D');
} catch (Exception $e) {
    echo $e->getMessage();
}
?> 