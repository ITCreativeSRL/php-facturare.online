<?php
namespace FacturareOnline;
require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

// setati id-ul facturii din DB pentru care doriti vizualizarea, campul `invoiceid`
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
echo '<embed width="100%" height="100%" id="printSection" src="data:application/pdf;headers=filename%3D' . $file . ';base64,' . $row['document'] . '">';
?>
