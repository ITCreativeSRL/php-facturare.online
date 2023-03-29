<?php

namespace FacturareOnline;
require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

// setati id-ul facturii din DB pentru care doriti generarea chitantei, campul `invoiceid`
$invoiceid = 99999;
$suma = 1;
$data_incasare = date('Y-m-d H:i:s');
$exchange_rate = 1;

// ID-ul companiei din contul personal
$loginid = '';
$fo = new FO($loginid);
// CHEIA PUBLICA DIN CONTUL PERSONAL
$fo->setRSAKeyEncrypt('');
// IV din contul personal
$fo->setIV('');

$raspuns = $fo->incaseazaBancaFactura($invoiceid, $suma, $data_incasare, $exchange_rate);

if ($fo::isJson($raspuns) === true) {
    if ($fo::isError($raspuns)) {
        echo $raspuns;
        exit;
    } else {
        $incasare = json_decode($raspuns);
        $message = json_decode($incasare->response->message);

        // am incasat cu succes prin banca, o salvez incasarea in baza de date
        $conn = new \mysqli("localhost", "user", "pass", "database");
        // Check connection
        if ($conn->connect_error) {
            echo FO::response('', '00', 'Connection failed: ' . $conn->connect_error);
        }

        // you should start DB transaction
        $sql = "INSERT INTO `facturareonline_incasari_banca` (`invoiceid`, `amount`, `date`)
                VALUES (
                    {$message->invoiceid},
                    {$message->amount},
                    '{$message->date}'
                );";

        $conn->query($sql);

        $sql = "UPDATE `facturareonline_facturi` set `status` = {$message->invoice_status} where `invoiceid` = {$message->invoiceid} ";
        if ($conn->query($sql) === true) {
            echo FO::response('Bank transfer saved to DB');
        } else {
            echo FO::response('', '00', 'Bank transfer could not be saved to DB');
        }

        // end DB transaction
        $conn->close();
    }
} else {
    echo FO::response('', '02', 'The server response is not JSON');
}
