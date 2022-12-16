<?php
namespace FacturareOnline;
require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

// ID-ul companiei din contul personal
$loginid = '';
$fo = new FO($loginid);
// CHEIA PRIVATA DIN CONTUL PERSONAL
$fo->setRSAKeyDecrypt('');
// IV din contul personal
$fo->setIV('');

$input = file_get_contents('php://input');

if (!empty($input) && $fo::isJson($input) === true) {
    $input = json_decode($input);
    $snif = $fo->snif($input->message, $input->crypt_message);
    if ($fo::isJson($snif) === true) {
        $snif = json_decode($snif);

        $conn = new \mysqli("localhost", "user", "pass", "database");
        // Check connection
        if ($conn->connect_error) {
            echo FO::response('', '00', 'Connection failed: ' . $conn->connect_error);
        }
        $sql = "UPDATE `facturareonline_facturi` set `status` = {$snif->status}, `document` = '{$snif->document}' where `invoiceid` = {$snif->invoiceid} ";
        if ($conn->query($sql) === true) {
            echo FO::response(array('invoiceid' => (int)$snif->invoiceid, 'status' => (int)$snif->status));
        } else {
            echo FO::response('', '00', 'Invoice voided but could not be saved to DB');
        }
        $conn->close();
    } else {
        echo FO::response('', '02', 'The message from server response is not JSON');
    }
}
?>
