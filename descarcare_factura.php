<?php

namespace FacturareOnline;
require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

// setati id-ul facturii din DB pentru care doriti descarcarea, campul `invoiceid`
$invoiceid = 99999;

$conn = new \mysqli("localhost", "user", "pass", "database");
// Check connection
if ($conn->connect_error) {
    echo FO::response('', '00', 'Connection failed: ' . $conn->connect_error);
}
$sql = "select serie, numar, tip, document from `facturareonline_facturi` where `invoiceid`={$invoiceid};";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    $row = $result->fetch_assoc();
} else {
    echo FO::response('', '01', 'Could not find invoice in DB');
}

$conn->close();
$decoded = base64_decode($row['document']);
switch ($row['tip']) {
    case 'ff':
        $prefix = 'factura-fiscala-';
        break;
    case 'fp':
        $prefix = 'factura-proforma-';
        break;
    case 'c':
        $prefix = 'chitanta-';
        break;
}
$file = $prefix . $row['serie'] . '-' . $row['numar'] . '.pdf';
file_put_contents($file, $decoded);

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    unlink($file);
    exit;
}
