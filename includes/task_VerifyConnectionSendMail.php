<?php

// ----------------------------------------------------------------------------------------------------
// RULES WARNINGS EMAIL SEND [ONE BY ONE]
// ----------------------------------------------------------------------------------------------------

	// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
		//header ('X-Frame-Options: DENY');
		header ('X-Frame-Options: SAMEORIGIN');

	// WARNINGS & ERRORS
		ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

	// SCRIPT
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
	

	// INCLUDES & REQUIRES 
		include_once('../includes/configuration.php');	// Archivo de configuración
		include_once('../includes/functions.php');	// Librería de funciones
		include_once('../includes/database.class.php');	// Class para el manejo de base de datos
		include_once('../includes/databaseconnection.php');	// Conexión a base de datos

		include_once('../includes/databaseconnectiontransactions.php');	// Conexión a base de datos


// --------------------
// INICIO CONTENIDO
// --------------------
	
	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();

		// DATABASE TRANSACTIONS
		// Connecting to database to TRANSACTIONS & POINTS
		$dbnotificationslist = new database($configuration['db2type'],
							$configuration['db2host'], 
							$configuration['db2name'],
							$configuration['db2username'],
							$configuration['db2password']);


	// --------------------------------------------------
	// SCRIPT PARAMS
	// --------------------------------------------------
	
		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'connections';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyText($_GET['t']);
			if ($itemtype == '') { $itemtype = 'today'; }
		}
		$itemtype = strtoupper($itemtype);

		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
		$AppCurrentPath = str_replace("/includes", "", $AppCurrentPath);
				
	
		// INIT OUTPUT
		echo $itemtype." Notifications<br />";
		echo "<br />";
		
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// DELETE PREVIOUS
								$query  = "DELETE FROM ".$configuration['instanceprefix']."dbo.AppParameters  
											WHERE (ParameterType = 'Task')
											AND (ParameterName = 'VerifyConnectionSendMail');";
								$dbconnection->query($query);
								// INSERT NEW EXECUTION
								$query  = "INSERT INTO ".$configuration['instanceprefix']."dbo.AppParameters
											(ParameterType, ParameterName, ParameterValue, ParameterDescription, ParameterLastDate)
											VALUES     
											('Task', 'VerifyConnectionSendMail', 'Running...', '".$scriptactual."@".$configuration['appkey']."', GETDATE());";
								$dbconnection->query($query);
							// ------------------------------
							// PARAMETERS LOG:end
							// ------------------------------
	
	
	// --------------------------------------------------
	// INTERACTION PARAMS
	// --------------------------------------------------

		// Get Local SMTP Host
		$InteractionSMTPHost = ini_get('SMTP');

		$InteractionResult  = '';
		$InteractionSent 	= 99;
		$InteractionService	= '';


	// --------------------------------------------------
	// INTERACTION CONTENT
	// --------------------------------------------------
	
			$AffiliationId = '0';
			
			// Extraemos los datos de la campaña
			$InteractionId  		= 0;
			$InteractionSentId 		= 0;
			$InteractionType  		= $itemtype;
			$InteractionName  		= '';
			$InteractionStatusId  	= 0;
			$InteractionMonitor 	= ''; 
			$InteractionListId		= 0; 
			$InteractionListContent	= 'https://orbis.orveecrm.com/templates/OrbisMessage.html';  
				$InteractionListSQL	= implode('', file($InteractionListContent)); 
			
			$InteractionFrom  		= $configuration['adminemail'];
			$InteractionFromName    = 'Orvee HelpDesk';
			$InteractionReplyTo  	= $configuration['adminreplyto'];
			$InteractionSubject 	= 'Verifica Env'.utf8_decode('í').'o Email a |ACTIONDATE| ['.$configuration['instancelastname'].']';
			$InteractionContent 	= 'https://orbis.orveecrm.com/templates/OrbisMessage.html'; 
				$InteractionContentText	= implode('', file($InteractionContent));
				
			$InteractionCode		= '';
			$InteractionCodeAuth	= '';

				
		
	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
		
			// NOTIFICATIONS LIST		
				$records = 0;
				$RecordId = 0;
				$DistributionList = '';
				$ContentBody			= '';
				$SidebarSummary 		= '';
				$SidebarSummaryEmpty	= '';
				$InteractionAuthorized = 1;
                $OperationToday	= date('M d, Y');
								
							// EMAIL HEADERS
						$EmailMessage['Headers'] = "";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
				
						// To, From & Subject del Email
						//$EmailTo 		= $EmailDistributionList;
						$EmailTo 		= "helpdesk@orveecrm.com";
						$EmailCc		= "";
						$EmailBcc 		= "";
				
						// EMAIL FROM & TO
						$EmailMessage['From'] 	  = $InteractionFrom;
						$EmailMessage['FromName'] = $InteractionFromName;
						$EmailMessage['To']   	  = $EmailTo;
						$EmailMessage['ReplyTo']  = $InteractionReplyTo;
						$EmailMessage['Cc']  	  = $EmailCc;
						$EmailMessage['Bcc']  	  = $EmailBcc;
			
						// EMAIL SUBJECT					
						$EmailMessage['Subject'] = $InteractionSubject;		
						//$EmailMessage['Subject'] = str_replace("|ITEMOWNER|", $ParameterOwner, $EmailMessage['Subject']);
						$EmailMessage['Subject'] = str_replace("|ACTIONDATE|", $OperationToday, $EmailMessage['Subject']);
						
						// EMAIL CONTENT					
						$EmailMessage['Content'] = $InteractionContent;
			
						// REGISTRANT CONTENT
						$EmailMessage['Body'] = $InteractionContentText;
                            $EmailMessage['Body'] = str_replace("|SOURCE|", $configuration['appkey'], $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|MESSAGEDESC|", "&nbsp; La verificaci&oacute;n de conexi&oacuten de env&iacute;o de Email esta OK!!. ", $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|MESSAGETYPE|", "Conexiones", $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|MESSAGENAME|", "Verifica la Conexi&oacute;n de Env&iacute;o de Email", $EmailMessage['Body']);
							
							$EmailMessage['Body'] = str_replace("|MESSAGEDATE|", $OperationToday, $EmailMessage['Body']);
							
							// Tags en APP & TIME
							//$EmailMessage['Body'] = str_replace("|LOCATION|", $OperationContent['RecordInstance'].' @ '.$OperationContent['RecordLocation'], $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|MESSAGELOCATION|", $configuration['appkey'], $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|APP|", $script, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|TIME|", date('d/m/Y')." ".date('H:i:s'), $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|MOREINFO|", '', $EmailMessage['Body']);
		
		
							// --------------------------------------------------
							// INTERACTION SEND!!!
							// --------------------------------------------------
							if ($InteractionAuthorized == 1) {
									// Interpretar respuesta para el OK		
									// Reintentos?
									$EmailMessageSent = 0;	
									$EmailMessageSentLog = "";
									$actionerrorid = 0;
												
									// Enviamos notificación de nuevo acceso
									$EmailMessageSent = sendAppEmailMessage($EmailMessage);
													
									if ($EmailMessageSent == 1) {
										$InteractionResult = 'OK;';
										$InteractionSent = 1;
									} else {
										$InteractionResult = 'PHPError;';
										$InteractionSent = 0;
									}
							
									echo "... Sent!<br />";
							} else {
									echo "... NOT Sent!<br />";
							}
	
	
	    
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
                            if ($InteractionSent == 1){
								// UPDATE CURRENT EXECUTION
								$query  = "UPDATE    ".$configuration['instanceprefix']."dbo.AppParameters
											SET              ParameterValue = 'FINISHED', ParameterLastDate = GETDATE(),
																ParameterDescription = '".$scriptactual."@".$configuration['appkey']."'
											WHERE     (ParameterType = 'Task') AND (ParameterName = 'VerifyConnectionSendMail');";
								$dbconnection->query($query);
                            }
							// ------------------------------
							// PARAMETERS LOG:end
							// ------------------------------
						
						
		include_once('../includes/databaseconnectionrelease.php');	
		$dbnotificationslist->disconnect();
	

	// FINAL OUTPUT
	echo "<br />";
	echo "ALL Notifications Sent!<br />";
	echo date('Ymd H:i:s');

	
	
// -------------------------------------------------	
// FUNCTIONS
// -------------------------------------------------
	
		function getDateFromDays($number_of_days) {
			$today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
			$subtract = $today - (86400 * $number_of_days);
		
			//choice a date format here
			//return date("Ymd", $subtract);
			return date("M d, Y", $subtract);
		}
	

?>
<?php
/*
require '../includes/smtp/PHPMailerAutoload.php';
//require_once('../includes/smtp/class.phpmailer.php');
//require_once('../includes/smtp/class.smtp.php');

$mail = new PHPMailer; // create a new object
$mail->IsSMTP(TRUE); // enable SMTP
$mail->SMTPAutoTLS = FALSE;

$mail->SMTPOptions = array(
'ssl' => array( 
'verify_peer' => false,
'verify_peer_name' => false,
'allow_self_signed' => true
)
);
//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = TRUE; // authentication enabled
$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
$mail->Host = "smtp.gmail.com";
$mail->Port = 465; // or 587
$mail->IsHTML(true);
//$mail->Username = "helpdesk@orveecrm.com";
//$mail->Password = "aNxuUgDulO6JMoMnRNcb9g";
$mail->From = 'noreply@orveecrm.com';
$mail->FromName = 'noreply@orveecrm.com';
//$mail->Username = "noreply.orvee@origis.com";
//$mail->Password = "HaQuebert53";
$mail->Username = "noreply@orveecrm.com";
$mail->Password = "orveecrmpassword";
$mail->SetFrom("noreply@orveecrm.com");
$mail->Subject = "Test";
$mail->Body = "hello";
$mail->AddAddress("davidf@origis.com");

 if(!$mail->Send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
 } else {
    echo "Message has been sent";
 }
 */
?>
