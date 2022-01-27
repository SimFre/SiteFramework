<?php

class SpiriusSMS {
    // public $wsdl = "https://web.spiricom.spirius.com:56001/v1_0/.wsdl";
    // public $baseurl = "https://get.spiricom.spirius.com:55001/cgi-bin/sendsms";
    public $baseurl = "https://web.spiricom.spirius.com:56001/v1_0/.wsdl";
    // public $baseurl = "https://get.spiricom.spirius.com:56001/cgi-bin/sendsms";
    public $username = "";
    public $password = "";
    public $sender = "";
    public $result;
	public $rawresult;
    public $curlinfo;
	public $xml;

    public function sendMessage($recipient, $message) {
        // Fulhackad SOAP eftersom det blir TLS-fel med soapClient, och
		// utgående port 55001 är blockad. Felet är antingen relaterat till
		// root CA-hanteringen i IIS/PHP/Windows-kombinationen eller cipher
		// TLS 1.2 med PHPs inbyggda funktioner. cURL fungerar dock fint.
		// Vansinne och alldeles för mycket tid lagd på felsökning.
        $u = htmlspecialchars($this->username);
        $p = htmlspecialchars($this->password);
        $s = htmlspecialchars($this->sender);
        $r = htmlspecialchars($recipient);
        $m = htmlspecialchars($message);
        $soap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $soap .= <<<EOT
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:SPIRICOM_VER_1">
               <soapenv:Header/>
               <soapenv:Body>
                  <urn:sendMessage>
                     <User>$u</User>
                     <Pass>$p</Pass>
                     <To>$r</To>
                     <Msg>$m</Msg>
                     <From>$s</From>
                  </urn:sendMessage>
               </soapenv:Body>
            </soapenv:Envelope>
        EOT;

        $ch = curl_init($this->baseurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Strictly off for debugging
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Strictly off for debugging
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
		$this->rawresult = $result;
		curl_close($ch);
		$result = str_replace("<soap:Body>","",$result);
        $result = str_replace("</soap:Body>","",$result);
        $this->result = simplexml_load_string($result);
		$this->xml = $this->result->asXML();
        $this->curlinfo = curl_getinfo($ch);
    }
        


    //public function sendMessageGET($number, $message) {
    //    $q = http_build_query([
    //        "User" => $this->username,
    //        "Pass" => $this->password,
    //        "From" => $this->sender,
    //        "To"   => $number,
    //        "Msg"  => $message
    //    ]);
    //    $url = $this->baseurl . "?" . $q;
    //    return $data;
    //}
    //
    //function sendMessageSOAP($number, $message) {
    //    $data = [
    //        "User" => $this->username,
    //        "Pass" => $this->password,
    //        "To"   => $number,
    //        "From" => $this->sender,
    //        "Msg"  => $message
    //    ];
	//
    //    $client = new SoapClient($this->wsdl, [
    //        'soap_version' => SOAP_1_1,
    //        'trace' => 1,
    //        'stream_context' => stream_context_create([
    //            'ssl' => [
    //                'cafile' =>  'C:/Program Files/PHP/v8.0.12/cacert.pem',
    //                'crypto_method' =>  STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
    //            ],
    //        ])
    //    ]);
	//
    //    try {
    //        return $client->sendMessage($data);
    //    } catch (SoapFault $e) {
    //        die(var_export($e, true));
    //    }
    //}

}
?>