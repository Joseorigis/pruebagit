<?php
// This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)
require_once 'HTTP/Request2.php';

$request = new Http_Request2('https://eastus.api.cognitive.microsoft.com/vision/v1.0/ocr');
$url = $request->getUrl();

$headers = array(
    // Request headers
    'Content-Type' => 'application/json',

    // NOTE: Replace the "Ocp-Apim-Subscription-Key" value with a valid subscription key.
    'Ocp-Apim-Subscription-Key' => '6f6306fbd5fc4056ae0f2fec10fd90d1',
);

$request->setHeader($headers);

$parameters = array(
    // Request parameters
    'language' => 'en',
    'detectOrientation ' => 'true',
);

$url->setQueryVariables($parameters);

$request->setMethod(HTTP_Request2::METHOD_POST);

// Request body
//$request->setBody("{body}");    // Replace "{body}" with the body. For example, '{"url": "http://www.example.com/images/image.jpg"}'
$request->setBody('{"url": "https://storage.orveecrm.com/filemanager/files/ticketoffline_8497.jpg"}'); 
//https://storage.orveecrm.com/filemanager/files/ticketoffline_8497.jpg
//https://storage.orveecrm.com/filemanager/files/ticketoffline_8508.png

try
{
    $response = $request->send();
    echo $response->getBody();
}
catch (HttpException $ex)
{
    echo $ex;
}

?>