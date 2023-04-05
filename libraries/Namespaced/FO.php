<?php

namespace FacturareOnline;

use FacturareOnline\JsonSchema\Validator as JsonSchemaValidator;
use FacturareOnline\phpseclib3\Crypt\AES;
use FacturareOnline\phpseclib3\Crypt\RSA;
use FacturareOnline\sylouuu\Curl\Method as Curl;


class FO
{
    private static $rsa_public;
    private static $rsa_private;
    private static $iv;
    public static $loginid;

    // scheme validare
    private static $url_schema_factura_noua = 'https://facturare.online/static/validation/factura.json';
    private static $url_schema_anuleaza_factura = 'https://facturare.online/static/validation/anuleaza_factura.json';
    private static $url_schema_genereaza_chitanta_factura = 'https://facturare.online/static/validation/genereaza_chitanta_factura.json';
    private static $url_schema_incaseaza_banca_factura = 'https://facturare.online/static/validation/incaseaza_banca_factura.json';
    private static $url_schema_fiscala_din_proforma = 'https://facturare.online/static/validation/fiscala_din_proforma.json';

    // endpoint-uri
    private static $url_factura_noua = 'https://facturare.online/api/adauga-factura';
    private static $url_anuleaza_factura = 'https://facturare.online/api/anuleaza-factura';
    private static $url_genereza_chitanta_factura = 'https://facturare.online/api/genereaza-chitanta-factura';
    private static $url_incaseaza_banca_factura = 'https://facturare.online/api/incaseaza-banca-factura';
    private static $url_fiscala_din_proforma = 'https://facturare.online/api/fiscala-din-proforma';


    public function __construct($loginid)
    {
        self::$loginid = $loginid;
    }

    // setez cheia RSA pentru criptare
    public function setRSAKeyEncrypt($rsa_public)
    {
        self::$rsa_public = $rsa_public;
    }

    // setez cheia RSA pentru decriptare
    public function setRSAKeyDecrypt($rsa_private)
    {
        self::$rsa_private = $rsa_private;
    }

    // setez initial vector
    public function setIV($iv)
    {
        self::$iv = $iv;
    }

    public function adauga_factura($request)
    {
        $post = self::encryptMessageCompany($request, self::$url_schema_factura_noua);
        if (self::isError($post)) {
            return $post;
        } else {
            return $this->FOCommunicate(self::$url_factura_noua, json_decode($post));
        }
    }

    public function anuleazaFactura($invoiceid)
    {
        $request = array();
        $request['invoiceid'] = $invoiceid;
        $post = self::encryptMessageCompany($request, self::$url_schema_anuleaza_factura);
        if (self::isError($post)) {
            return $post;
        } else {
            return $this->FOCommunicate(self::$url_anuleaza_factura, json_decode($post));
        }
    }

    public function genereazaChitantaFactura($invoiceid, $suma)
    {
        $request = [];
        $request['invoiceid'] = $invoiceid;
        $request['suma'] = (float)$suma;
        $post = self::encryptMessageCompany($request, self::$url_schema_genereaza_chitanta_factura);
        if (self::isError($post)) {
            return $post;
        } else {
            return $this->FOCommunicate(self::$url_genereza_chitanta_factura, json_decode($post));
        }
    }

    public function incaseazaBancaFactura($invoiceid, $suma, $data = null, $exchange_rate = 1)
    {
        $request = [];
        $request['invoiceid'] = $invoiceid;
        // suma va fi mereu in RON
        $request['suma'] = round($suma * $exchange_rate, 2);
        if (!empty($data)) {
            $request['data'] = date('Y-m-d H:i:s', strtotime($data));
        }
        $post = self::encryptMessageCompany($request, self::$url_schema_incaseaza_banca_factura);
        if (self::isError($post)) {
            return $post;
        } else {
            return $this->FOCommunicate(self::$url_incaseaza_banca_factura, json_decode($post));
        }
    }

    public function fiscala_din_proforma($request)
    {
        $post = self::encryptMessageCompany($request, self::$url_schema_fiscala_din_proforma);
        if (self::isError($post)) {
            return $post;
        } else {
            return $this->FOCommunicate(self::$url_fiscala_din_proforma, json_decode($post));
        }
    }

    public function snif($message, $crypt_message)
    {
        return self::decryptMessageCompany($message, $crypt_message);
    }

    private static function encryptMessageCompany($message, $schema)
    {
        $message = json_encode($message);
        $validate = self::validateJSONagainstSCHEMA($message, $schema);

        if (self::isError($validate)) {
            return $validate;
        }

        if (empty(self::$loginid)) {
            return self::response('', '02', 'Nu ati setat ID-ul companiei');
        }

        $response = array();

        $response['loginid'] = self::$loginid;
        // criptare aes
        $aes_key = substr(hash('sha256', uniqid(), 0), 0, 32);
        $aes = new AES('cbc');
        $aes->setIV(self::$iv);
        $aes->setKey($aes_key);
        $response['message'] = bin2hex(base64_encode($aes->encrypt($message)));

        // criptare rsa
        $key = RSA::loadPublicKey(self::$rsa_public);
        $response['crypt_message'] = base64_encode($key->encrypt($aes_key));
        return json_encode($response);
    }

    private static function decryptMessageCompany($message, $crypt_message)
    {
        if (empty(self::$loginid)) {
            return self::response('', '02', 'Nu ati setat ID-ul companiei');
        }
        if (!isset($message) || empty($message)) {
            return self::response('', '03', 'Decriptare raspuns - nu se primeste [criptul AES]');
        }
        if (!isset($crypt_message) || empty($crypt_message)) {
            return self::response('', '04', 'Decriptare raspuns - nu se primeste [criptul RSA]');
        }

        $key = RSA::loadPrivateKey(self::$rsa_private);
        $aes_key = $key->decrypt(base64_decode($crypt_message));

        if (empty($aes_key)) {
            return self::response('', '05', 'Nu am putut decripta cheia AES din RSA');
        }

        $aes = new AES('cbc');
        $aes->setKey($aes_key);
        $aes->setIV(self::$iv);
        $response = $aes->decrypt(base64_decode(self::hex2str($message)));

        if (!isset($response) || empty($response)) {
            return self::response('', '06', 'Nu am putut decripta mesajul din criptul AES');
        }
        return json_decode($response);
    }

    private static function validateJSONagainstSCHEMA($data, $schema)
    {
        $validator = new JsonSchemaValidator;
        $data = json_decode($data);

        $request = new Curl\Get($schema);

        $request->send();

        if ($request->getStatus() !== 200) {
            throw new \Exception('Nu am putut obtine schema de validare de la FacturareOnline');
        }
        $schemaFO = $request->getResponse();

        $validator->validate($data, $schemaFO, JsonSchema\Constraints\Constraint::CHECK_MODE_APPLY_DEFAULTS);

        if (!$validator->isValid()) {
            $message = array();
            $message['reason'] = 'JSON does not validate';
            $message['errors'] = array();
            foreach ($validator->getErrors() as $error) {
                $message['errors'][] = $error['property'] . ' ' . $error['message'];
            }
            return self::response('', '10', json_encode($message));
        }
    }

    public static function isError($message)
    {
        $message = json_decode($message);
        if (isset($message) && isset($message->response) && isset($message->response->error_code) && $message->response->error_code !== null) {
            return true;
        } else {
            return false;
        }
    }

    private static function hex2str($hex)
    {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $str;
    }

    public static function isJson($string)
    {
        if (is_object(json_decode($string))) {
            return true;
        }
        return false;
    }

    public static function response($message = null, $code = null, $reason = null)
    {
        $message = json_encode($message);
        if (!is_array($reason)) {
            $reason = json_encode(array('reason' => $reason, 'errors' => array()));
        }
        return json_encode(
            array(
                'response' => array(
                    'error_code' => $code,
                    'error_reason' => $reason,
                    'message' => $message
                )
            )
        );
    }

    private function FOCommunicate($url, $payload)
    {
        $request = new Curl\Post($url, [
            'data' => $payload,
            'is_payload' => true
        ]);
        $request->send();

        if ($request->getStatus() === 200) {
            return $request->getResponse();
        } else {
            throw new \Exception('Could not communicate with FacturareOnline');
        }
    }
}
