<?php

namespace FacturareOnline;

require_once('libraries/autoloader.php');

use FacturareOnline\FO as FO;

$vat_number = 'RO456';
$cnp = '199';

$request = array();
if (empty($vat_number)) {
    // persoana fizica
    $request['client'] = array(
        'tip' => 'pf',
        'denumire' => 'Nume si prenume',
        'adresa' => 'Adresa completa',
        'tara' => 'RO', //folositi codurile de tara din fisierele coduri_tara.xls
        'judet' => 'B',  //folositi codurile de judet din fisierul coduri_judet.xls
        'localitate' => 'Sector 1',
        'telefon' => '0740000000',
        'email' => 'email@domeniu.ro'
    );
    if (!empty($cnp)) {
        $request['cnp'] = $cnp;
    }
} else {
    // persoana juridica
    $cui = preg_replace( '/[^0-9]/', '', $vat_number );
    $atribut_fiscal = preg_replace('/[^a-z]/i', '', $vat_number);

    $vat = 0; // neplatitor de tva
    if (!empty($atribut_fiscal)) {
        $vat = 1; //platitor de tva
    }

    $request['client'] = array(
        'tip' => 'pj',
        'denumire' => 'Companie de test',
        'cui' => $cui,
        'nr_reg_com' => 'J00/00/2000',
        'atribut_fiscal' => $atribut_fiscal, // RO, R, -
        'vat' => $vat, // 0 - neplatitor tva, 1 - platitor tva
        'adresa' => 'Sos. de test',
        'tara' => 'RO', //folositi codurile de tara din fisierele coduri_tara.xls
        'judet' => 'B',  //folositi codurile de judet din fisierul coduri_judet.xls
        'localitate' => 'Sector 4',
        'telefon' => '0740000001',
        'email' => 'email@domeniu.ro'
    );
}

$produse = array();

$pret_initial = round(100, 4);
$pret_final = round(99, 4);
$produse[] = array(
    'cod' => '123',
    'denumire' => 'Produs 1',
    'denumire_english' => '',
    'denumire_french' => '',
    'denumire_german' => '',
    'denumire_hungarian' => '',
    'pret' => $pret_initial,
    'vat' => 19,
    'tip' => 1,
    'cantitate' => round(2, 2),
    'info' => '',
    'um' => 'buc'
);

if ($pret_initial > $pret_final) { //avem reducere pe produsul initial
    $produse[] = array(
        'cod' => 'RED-123',
        'denumire' => 'Reducere pentru Produs 1',
        'denumire_english' => '',
        'denumire_french' => '',
        'denumire_german' => '',
        'denumire_hungarian' => '',
        'pret' => round($pret_initial - $pret_final, 4),
        'vat' => 19,
        'tip' => 1,
        'cantitate' => round(-2, 2),
        'info' => 'reducere pt primul produs',
        'um' => 'buc'
    );
}

// daca avem linie de reducere pe factura
$produse[] = array(
    'cod' => 'RED linie',
    'denumire' => 'REDUCERE linie',
    'denumire_english' => '',
    'denumire_french' => '',
    'denumire_german' => '',
    'denumire_hungarian' => '',
    'pret' => round((float)1.1, 4),
    'vat' => 19,
    'tip' => 1,
    'cantitate' => -1,
    'info' => '',
    'um' => 'buc'
);


// daca avem livrare pentru comanda
$produse[] = array(
    'cod' => 'liv1',
    'denumire' => 'Livrare prin curier',
    'denumire_english' => '',
    'denumire_french' => '',
    'denumire_german' => '',
    'denumire_hungarian' => '',
    'pret' => round(15, 4),
    'vat' => 19,
    'tip' => 1,
    'cantitate' => 1,
    'info' => '',
    'um' => 'buc'
);

$request['produse'] = $produse;
$request['factura'] = array(
    'tip' => 'ff', // ff - factura fiscala, fp - factura proforma
    'serie' => '', // se preia din interfata API din Facturare.Online, pe aceasta serie se vor emite facturile
    'limba' => 'RO', // limba in care se va emite factura - RO, EN, DE, FR
    'moneda' => 'RON', // moneda de emitere, in format iso3
    'orderid' => uniqid(), // numarul comenzii
    'website' => str_replace('www.', '', $_SERVER['SERVER_NAME']),
    'curs_valutar' => round(1, 4), // 1- RON, curs valutar valuta/ron
    'zecimale' => (int)4, //int, cate zecimale se trimit la produsele de pe factura
    'platita'      => (int)0, // int, daca factura e platita, daca nu e platita si compania e inrolata in sistemul de plata cu cardul se va genera link de plata in mediul LIVE si API e in mediul LIVE
    // 'denumire_reducere'     	=> '',
    // 'valoare_reducere'      	=> (float)1.00,
    // 'dataora_expeditie'     	=> date('Y-m-d\TH:i:sP',strtotime('2017-09-29 01:59:58')),
    // 'mt_expeditie'     		=> '',
    // 'mtnr_expeditie'        	=> '',
    // 'delegat_expeditie'     	=> '',
    // 'delegat_ci'     		=> '',
    // 'delegat_eliberat'     	=> '',
    // 'dataora_livrare'		=> date('Y-m-d\TH:i:sP',strtotime('2017-09-29 01:59:58')),
    // 'data_plata_avans'		=> date('Y-m-d\TH:i:sP',strtotime('2017-09-29 01:59:58')),
    // 'data_aviz'				=> date('Y-m-d\TH:i:sP',strtotime('2017-09-29 01:59:58')),
    // 'nr_aviz'				=> '',
);
$request['factura']['observatii'] = 'Comanda #' . $request['factura']['orderid'] . ' pe ' . $request['factura']['website'];
$request['factura']['zile_scadenta'] = (int)2;

// ID-ul companiei din contul personal
$loginid = '';
$fo = new FO($loginid);
// CHEIA PUBLICA DIN CONTUL PERSONAL
$fo->setRSAKeyEncrypt('');
// IV din contul personal
$fo->setIV('');

$raspuns = $fo->adauga_factura($request);

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

// inainte de a genera o factura va rugam sa va asigurati ca nu exista deja o factura emisa pe comanda curenta
// facturile considerate active sunt cele care au status diferit de 3 - Anulata si 6 - Stornata
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
