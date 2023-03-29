<?php

namespace FacturareOnline;

require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

$request = array();
$request['invoiceid'] = 9999; // id-ul facturii proforme
$request['serie'] = 'SERIE FISCALA'; // seria facturii fiscale, daca nu exista se va crea, altfel se va continua
$request['generate_payment_link'] = 1; // daca sa generez link de plata PlatiOnline (daca compania e inrolata in PO)

// ID-ul companiei din contul personal
$loginid = '';
$fo = new FO($loginid);
// CHEIA PUBLICA DIN CONTUL PERSONAL
$fo->setRSAKeyEncrypt('');
// IV din contul personal
$fo->setIV('');

$raspuns = $fo->fiscala_din_proforma($request);

if (FO::isJson($raspuns) === true) {
    if (FO::isError($raspuns)) {
        echo $raspuns;
        exit;
    } else {
        $factura = json_decode($raspuns);
        if (FO::isJson($factura->response->message) === true) {
            $message = json_decode($factura->response->message);
        } else {
            echo FO::response('', '01', 'The message from server response is not JSON');
            exit;
        }
        // am emis cu succes factura, o salvez in baza de date

        $conn = new \mysqli("localhost", "user", "pass", "database");
        // Check connection
        if ($conn->connect_error) {
            echo FO::response('', '00', 'Connection failed: ' . $conn->connect_error);
        }
        $sql = "INSERT INTO `facturareonline_facturi` (`orderid`, `invoiceid`, `total`, `moneda`, `serie`, `numar`, `tip`, `code`, `status`, `document`)
                VALUES (
                    '$message->orderid',
                    $message->invoiceid,
                    $message->total,
                    '$message->moneda',
                    '$message->serie',
                    $message->numar,
                    '$message->tip',
                    '$message->code',
                    $message->status,
                    '$message->document'
                );";
        if ($conn->query($sql) === true) {
            echo FO::response('Invoice successfuly saved to DB');
        } else {
            echo FO::response('', '00', 'Could not save invoice to DB');
        }
        $conn->close();
    }
} else {
    echo FO::response('', '02', 'The server response is not JSON');
}

// LISTA STATUSURILOR FACTURILOR IN FACTURARE.ONLINE
// 1	Emisă
// 2	Ciornă
// 3	Anulată
// 4	Încasată integral
// 5	Încasată parţial
// 6	Stornată
// 7	Stornează o factură
// 8	Stornată parţial
// 9	Stornează parţial o factură
// 10	Emitere programată
