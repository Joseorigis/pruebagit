<?php
    
class apisms { 

    var $balance = 0;
    var $description = '';

    function Balance(){
        // INIT VARS
        $uri = "https://apisms.c3ntro.com:8282/webservice.php?wsdl";
        $credentials = array();
        $credentials['trace']       = TRUE;
        $credentials['exception']   = FALSE;

        // INSTANCE SOAPCLIENT
        $client = new SoapClient($uri, $credentials);
        // CALL FUNCTION
        $response = $client->Balance("Origis","origis17.");

       /*
        echo "<pre>====== REQUEST HEADERS =====" . PHP_EOL."</pre>";
        var_dump(htmlentities($client->__getLastRequestHeaders()));
        echo "<pre>========= REQUEST ==========" . PHP_EOL."</pre>";
        //"<pre>"; print_r(htmlentities($client->__getLastRequest())); echo"</pre>";
        echo "\n" . htmlentities(str_ireplace('><', ">\n<", $client->__getLastRequest())) . "\n";
        echo "<pre>========= RESPONSE =========" . PHP_EOL."</pre>";
        echo '<pre>'.print_r(htmlentities($client->__getLastResponse())).'</pre>';
        echo '<pre>'; print_r($response); echo '</pre>';
       */  

       $response = obj2array($response);

       return $response;
    }
}

// Convierte en arreglo
function obj2array($obj) {
  $out = array();
  foreach ($obj as $key => $val) {
    switch(true) {
        case is_object($val):
         $out[$key] = obj2array($val);
         break;
      case is_array($val):
         $out[$key] = obj2array($val);
         break;
      default:
        $out[$key] = $val;
    }
  }
  return $out;
}

if ($_GET["o"] == 'balance') {
    
    // Llamada a la clase e impresion de resultados 
    $apisms = apisms::Balance();
    echo $apisms["balance"];
}
else{
   echo 'options<br>o=balance'; 
}
?>