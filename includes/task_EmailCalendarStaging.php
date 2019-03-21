<?php

// ----------------------------------------------------------------------------------------------------
// TRANSACTIONS EXPORT
// ----------------------------------------------------------------------------------------------------

		// HTML headers
			header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
			header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
			header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
			header ('Pragma: no-cache');	// HTTP/1.0
			header('Content-Type: application/json');


		// WARNINGS & ERRORS
			//ini_set('error_reporting', E_ALL&~E_NOTICE);
			error_reporting(E_ALL);
			ini_set('display_errors', '1');

		// INIT
			// Iniciamos el controlador de SESSIONs de PHP
			session_start();
			// Obtengo el nombre del script en ejecución
			$script = __FILE__;
			$camino = get_included_files();
			$scriptactual = $camino[count($camino)-1];


		// INCLUDES & REQUIRES
			include_once('../includes/configuration.php');	// Archivo de configuración
			include_once('../includes/functions.php');	// Librería de funciones
			include_once('../includes/database.class.php');	// Class para el manejo de base de datos
			include_once('../includes/databaseconnectiontransactions.php');	// Conexión a base de datos



	// --------------------
	// INICIO CONTENIDO
	// --------------------

		// INIT
			// ERROR ID ... inicializamos el indicador del error en el proceso
			$actionerrorid = 0;
			// AUTHNUMBER for duplicate check
			$actionauth = getActionAuth();
			$error = '0';
			$errormessage = '';



		// PARAMS
			// cardnumber
				$cardnumber = "";
				if (isset($_GET['cardnumber'])) {
					$cardnumber = setOnlyNumbers($_GET['cardnumber']);
					//if (isValidNumber($cardnumber, "EAN13") == 0) {
					//	$actionerrorid = 2;
					//	$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
					//}
				}

				if (isset($_GET['n'])) {
					$cardnumber = setOnlyNumbers($_GET['n']);
					//if (isValidNumber($cardnumber, "EAN13") == 0) {
					//	$actionerrorid = 2;
					//	$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
					//}
				}

			// action
				$itemtype = 'add';
				if (isset($_GET['t'])) {
					$itemtype = setOnlyLetters($_GET['t']);
					if ($itemtype == '') { $itemtype = 'add'; }
				}
				$itemtype = strtolower($itemtype);


							// ENVIO DE EMAIL DE NOTIFICACION

							$ftpupload 	 = "0000000";
							$error = '0';
							$errormessage = "OK!";

							//$mail->Subject = "Outlooked Event";
							$InteractionSubject 		= "Outlooked Event";
							$InteractionContentText		= "";
							$InteractionId				= $ftpupload;
							$InteractionFrom  			= "settlement@orbisfarma.com.mx";
							$InteractionFromName		= "OrbisFarma<settlement@orbisfarma.com.mx>";
							$InteractionReplyTo 		= "";
							$InteractionContent 			= "https://orbis.orveecrm.com/templates/OrbisMessage.html";
							//$InteractionContent 			= "http://www.mazsalud.com.mx/preproduccion/emailing/callcenter/MessageTemplate.html";
							$EmailDistributionList		= "davidfr@origis.com,davyford074@gmail.com";
							$EmailDistributionListCc	= "";
							$EmailDistributionListBCc	= "";

							// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
							// Contenido
							$InteractionCode 		= "Settlement.OrbisFarma.".$InteractionId.".".date("YmdHis");
							$InteractionCodeAuth 	= md5($InteractionCode);
							$InteractionCodeUnique  = "-@id:".$InteractionCode."-";

							// EMAIL HEADERS

							$EmailMessage['Headers'] = "";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
                            $EmailMessage['Headers'] .= "MIME-Version: 1.0\n";
                            $EmailMessage['Headers'] .= "Content-Type: multipart/alternative; boundary=".$InteractionCodeAuth."\r\n";
                            $EmailMessage['Headers'] .= "Content-class: urn:content-classes:calendarmessage\r\n";

							// To, From & Subject del Email
							$EmailTo 		= $EmailDistributionList;
							$EmailCc		= $EmailDistributionListCc;
							$EmailBcc 		= $EmailDistributionListBCc;

							// EMAIL FROM & TO
							$EmailMessage['From'] 	  = $InteractionFrom;
							$EmailMessage['FromName'] = $InteractionFromName;
							$EmailMessage['To']   	  = $EmailTo;
							$EmailMessage['ReplyTo']  = $InteractionReplyTo;
							$EmailMessage['Cc']  	  = $EmailCc;
							$EmailMessage['Bcc']  	  = $EmailBcc;

							// EMAIL SUBJECT
							$EmailMessage['Subject'] = $InteractionSubject;

							// EMAIL CONTENT
							//$EmailMessage['Content'] = $InteractionContent;
							//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
							//---------------------------------------------------------------------------------------------- BEGIN CALENDAR -------------------------------------------------------------------------------------------------------------------
							//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
							// event params

							$event_id = 1234;
							$sequence = 0;
							$status = 'TENTATIVE';

							$summary = 'Summary of the event';
							$venue = 'Simbawanga';
							$start = '20180705';
							$start_time = '160630';
							$end = '20180705';
							$end_time = '180630';

							// CALENDAR
                            $startTime = "05-07-2018 09:30:00";
                            $endTime = "05-07-2018 10:30:00";

                            $ical = $InteractionCodeAuth."\r\n";
                            $ical .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST'."\n";
                            $ical .= "Content-Transfer-Encoding: 8bit\n\n";
							$ical .= 'BEGIN:VCALENDAR' . "\r\n" .
			                'PRODID:-//Microsoft Corporation//Outlook MIMEDIR//EN' . "\r\n" .
			                'VERSION:2.0' . "\r\n" .
			                'METHOD:REQUEST' . "\r\n" .
			                'BEGIN:VTIMEZONE' . "\r\n" .
			                'TZID:Eastern Time' . "\r\n" .
			                'BEGIN:STANDARD' . "\r\n" .
			                'DTSTART:20091101T020000' . "\r\n" .
			                'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11' . "\r\n" .
			                'TZOFFSETFROM:-0500' . "\r\n" .
			                'TZOFFSETTO:-0500' . "\r\n" .
			                'TZNAME:EST' . "\r\n" .
			                'END:STANDARD' . "\r\n" .
			                'BEGIN:DAYLIGHT' . "\r\n" .
			                'DTSTART:20090301T020000' . "\r\n" .
			                'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3' . "\r\n" .
			                'TZOFFSETFROM:-0500' . "\r\n" .
			                'TZOFFSETTO:-0500' . "\r\n" .
			                'TZNAME:EDST' . "\r\n" .
			                'END:DAYLIGHT' . "\r\n" .
			                'END:VTIMEZONE' . "\r\n" .
			                'BEGIN:VEVENT' . "\r\n" .
                            'ORGANIZER;CN="DavyFord":MAILTO:davidf@origis.com' . "\r\n" .
			                'ATTENDEE;CN="Dave Flores'.'";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:davidfr@origis.com' . "\r\n" .
			                'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
			                'UID:'.date("Ymd\TGis", strtotime($startTime)).rand()."@origis.com" . "\r\n" .
			                'DTSTAMP:'.date("Ymd\TGis"). "\r\n" .
			                'DTSTART;TZID="Eastern Time":'.date("Ymd\THis", strtotime($startTime)). "\r\n" .
			                'DTEND;TZID="Eastern Time":'.date("Ymd\THis", strtotime($endTime)). "\r\n" .
			                'TRANSP:OPAQUE'. "\r\n" .
			                'SEQUENCE:1'. "\r\n" .
			                'SUMMARY:' . $EmailMessage['Subject'] . "\r\n" .
			                'LOCATION: MEXICO' . "\r\n" .
			                'CLASS:PUBLIC'. "\r\n" .
			                'PRIORITY:5'. "\r\n" .
			                'BEGIN:VALARM' . "\r\n" .
			                'TRIGGER:-PT15M' . "\r\n" .
			                'ACTION:DISPLAY' . "\r\n" .
			                'DESCRIPTION:Reminder' . "\r\n" .
			                'END:VALARM' . "\r\n" .
			                'END:VEVENT'. "\r\n" .
			                'END:VCALENDAR'. "\r\n";
                            $EmailMessage['ICal'] = $ical;

                            $EmailMessage['Content'] = file_get_contents($InteractionContent);
							$InteractionContentText	 = $EmailMessage['Content'];

                            //$InteractionContentText = '';
							//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
							//----------------------------------------------------------------------------------------------  END CALENDAR ---------------------------------------------------------------------------------------------------------------------
							//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

							// REGISTRANT CONTENT
							$EmailMessage['Body'] = $InteractionContentText;

							$EmailMessage['Attachment'] = "";
								// --------------------------------------------------
								// INTERACTION SEND!!!
								// --------------------------------------------------
										require_once('smtp/class.sendmailcalendar.php');
										// Interpretar respuesta para el OK
										// Reintentos?
										$EmailMessageSent = 0;
										$EmailMessageSentLog = "";
										$actionerrorid = 0;

										// Instanciamos un objeto de la clase sendmail
										$mail = new sendmail('smtpconnection0');

										// Enviamos notificación de de publicación de archivo.
										$EmailMessageSent = $mail->mail($EmailMessage['From'],
																		$EmailMessage['FromName'],
																		$EmailMessage['ReplyTo'],
																		$EmailMessage['To'],
																		$EmailMessage['Cc'],
																		$EmailMessage['Bcc'],
																		$EmailMessage['Subject'],
																		$EmailMessage['Body'],
																		$EmailMessage['Headers'],
																		$EmailMessage['Attachment'],
                                                                        $EmailMessage['ICal'] );

										if ($EmailMessageSent) {
											$InteractionResult = 'OK;';
											$InteractionSent = 1;
										} else {
											$InteractionResult = 'PHPError;';
											$InteractionSent = 0;
                                            $errormessage = $mail->InfoError();
										}

										//echo $InteractionResult .' - '.$InteractionSent;
										echo json_encode(array("Error" => "$error","Message"=>"$errormessage","SendEmail" => "$InteractionSent","EmailMessage"=>"$InteractionResult!"));


	// DATABASE CONNECTION CLOSE
		//include_once('includes/databaseconnectionrelease.php');

?>
