<?php

// ----------------------------------------------------------------------------------------------------
// APP @ setDeviceCardNotification
// ----------------------------------------------------------------------------------------------------

// https://storage.orveecrm.com/app/setDeviceCardNotification.php?token=a441f6b984a05ba240a1a4e7d1819d605f39e53846d9d9eb8a6f1f9558eb503a&message=holas
// https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/ApplePushService.html

	// HTML headers
//		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
//		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
//		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
//		header ('Pragma: no-cache');	// HTTP/1.0
//		//header ('X-Frame-Options: DENY');
//		header ('X-Frame-Options: SAMEORIGIN');

	// WARNINGS & ERRORS
		ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');



// --------------------
// INICIO CONTENIDO
// --------------------


	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 1000;
		// AUTHNUMBER for duplicate check
		// MESSAGE & KEY
		$requiredkey  = "6f6306fbd5fc4056ae0f2fec10fd90d1";
		
		$webservice = "https://eastus.api.cognitive.microsoft.com/vision/v1.0/ocr";
		
		

//	// PARAMS VALIDATION
//		// Validamos si hay afiliación que procesar...
//		// KEY
//			$webservicekey = "0";
//			if (isset($_GET['key'])) {
//				$webservicekey = setOnlyText($_GET['key']);
//				if ($webservicekey == "") { $webservicekey = "0"; }
//			} else {
//				$actionerrorid = 1;
//			}
//			//if ($webservicekeycheck !== $webservicekey) { $actionerrorid = 10; }
//

		
		//$devicetoken = "a441f6b984a05ba240a1a4e7d1819d605f39e53846d9d9eb8a6f1f9558eb503a";
		//$message = "Mas beneficios con tu tarjeta";

		$devicemessagetype = "2";
		$devicemessagecard = "2601006905078";
		
//		$devicemessagetype = "";
//		$devicemessagecard = "";


		$dataaction = array(
		  'type'  => $devicemessagetype,
		  'card'  => $devicemessagecard
		);

		$data = array(
		  'detectOrientation'      => "true",
		  'language'      => "es",
		  'url'      => "https://storage.orveecrm.com/filemanager/files/ticketoffline_8497.jpg"
		);
		
//// The data to send to the API
//$postData = array(
//    'kind' => 'blogger#post',
//    'blog' => array('id' => $blogID),
//    'title' => 'A new post',
//    'content' => 'With <b>exciting</b> content...'
//);
		
		
		$options = array(
		  'http' => array(
			'method'  => 'POST',
			'content' => json_encode( $data ),
			'header'=>  "Content-Type: application/json\r\n" .
						"Ocp-Apim-Subscription-Key: 6f6306fbd5fc4056ae0f2fec10fd90d1\r\n"
			)
		);
		
		
		if ($actionerrorid == 1000) {
			$context  = stream_context_create( $options );
			$result = file_get_contents( $webservice, false, $context );
			$response = json_decode( $result );
			
			echo var_dump($response);
		} else {
			echo $actionerrorid;
		}

	?>
