<?php
namespace FacturareOnline;
require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

// setati id-ul facturii din DB pentru care doriti anularea, campul `invoiceid`
$invoiceid = 99999;

// ID-ul companiei din contul personal
$loginid = '';
$fo = new FO($loginid);
// CHEIA PUBLICA DIN CONTUL PERSONAL
$fo->setRSAKeyEncrypt('');
// IV din contul personal
$fo->setIV('');

$raspuns = $fo->anuleazaFactura($invoiceid);
if ($fo::isJson($raspuns) === true) {
    if ($fo::isError($raspuns)) {
        echo $raspuns;
        exit;
    } else {
        $factura = json_decode($raspuns);
        $message = json_decode($factura->response->message);

        // am anulat cu succes factura, o salvez in baza de date
        $conn = new \mysqli("localhost", "user", "pass", "database");
        // Check connection
        if ($conn->connect_error) {
            echo FO::response('', '00', 'Connection failed: ' . $conn->connect_error);
        }
        $sql = "UPDATE `facturareonline_facturi` set `status` = {$message->status}, `document` = '{$message->document}' where `invoiceid` = {$message->invoiceid} ";
        if ($conn->query($sql) === true) {
            echo FO::response('Invoice successfuly voided and saved to DB');
        } else {
            echo FO::response('', '00', 'Invoice voided but could not be saved to DB');
        }
        $conn->close();
    }
} else {
    echo FO::response('', '02', 'The server response is not JSON');
}
?>
