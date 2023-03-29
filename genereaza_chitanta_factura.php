<?php

namespace FacturareOnline;
require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

// setati id-ul facturii din DB pentru care doriti generarea chitantei, campul `invoiceid`
$invoiceid = 99999;
$suma = 1;

// ID-ul companiei din contul personal
$loginid = '';
$fo = new FO($loginid);
// CHEIA PUBLICA DIN CONTUL PERSONAL
$fo->setRSAKeyEncrypt('');
// IV din contul personal
$fo->setIV('');

$raspuns = $fo->genereazaChitantaFactura($invoiceid, $suma);

if ($fo::isJson($raspuns) === true) {
    if ($fo::isError($raspuns)) {
        echo $raspuns;
        exit;
    } else {
        $chitanta = json_decode($raspuns);
        $message = json_decode($chitanta->response->message);

        // am generat cu succes chitanta, o salvez in baza de date
        $conn = new \mysqli("localhost", "user", "pass", "database");
        // Check connection
        if ($conn->connect_error) {
            echo FO::response('', '00', 'Connection failed: ' . $conn->connect_error);
        }

        // you should start DB transaction

        $sql = "INSERT INTO `facturareonline_chitante` (`receiptid`, `invoiceid`, `serie`, `total`, `moneda`, `numar`, `code`, `document`)
                VALUES (
                    {$message->receiptid},
                    {$message->invoiceid},
                    '{$message->serie}',
                    {$message->total},
                    '{$message->moneda}',
                    {$message->numar},
                    '{$message->code}',
                    '{$message->document}'
                );";

        $conn->query($sql);

        $sql = "UPDATE `facturareonline_facturi` set `status` = {$message->invoice_status} where `invoiceid` = {$message->invoiceid} ";
        if ($conn->query($sql) === true) {
            echo FO::response('Receipt successfully generated and saved to DB');
        } else {
            echo FO::response('', '00', 'Receipt generated but could not be saved to DB');
        }

        // end DB transaction
        $conn->close();
    }
} else {
    echo FO::response('', '02', 'The server response is not JSON');
}
