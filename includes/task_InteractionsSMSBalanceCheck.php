<?php

// ----------------------------------------------------------------------------------------------------
// RULES BONUS NOTIFICATIONS SEND
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


	// --------------------------------------------------
	// SCRIPT PARAMS
	// --------------------------------------------------

		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'today';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyText($_GET['t']);
			if ($itemtype == '') { $itemtype = 'today'; }
		}
		$itemtype = strtoupper($itemtype);
				
		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));


		// INIT OUTPUT
		echo $itemtype." Interactions SMS Balance<br />";
		echo "<br />";
		
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// DELETE PREVIOUS
								$query  = "DELETE FROM ".$configuration['instanceprefix']."dbo.AppParameters  
											WHERE (ParameterType = 'Task')
											AND (ParameterName = 'InteractionsSMSBalanceCheck');";
								$dbconnection->query($query);
								// INSERT NEW EXECUTION
								$query  = "INSERT INTO ".$configuration['instanceprefix']."dbo.AppParameters
											(ParameterType, ParameterName, ParameterValue, ParameterDescription, ParameterLastDate)
											VALUES     
											('Task', 'InteractionsSMSBalanceCheck', 'Running...', '".$scriptactual."@".$configuration['appkey']."', GETDATE());";
								$dbconnection->query($query);
							// ------------------------------
							// PARAMETERS LOG:end
							// ------------------------------
	

	// --------------------------------------------------
	// BALANCE CHECK
	// --------------------------------------------------


		// INIT
			$actionerrorid = 99;
			$InteractionStatus = "FAIL";
			$InteractionResult = "@";
			$InteractionService = "";
			$InteractionService = "https://orbis.orveecrm.com/services/apisms.c3ntro.php?o=balance";

			$InteractionsBalance = "0";
			$InteractionsBalanceLog = "";

		// CONTENT CUSTOMIZATION
			$InteractionServiceCustome = $InteractionService;

		// INTERACION SEND	
			//echo $InteractionServiceCustome;
			$InteractionResult = implode('', file($InteractionServiceCustome));
			$InteractionResult = strtolower($InteractionResult);

			if (is_numeric($InteractionResult)) {
				$InteractionStatus = 'FINISHED';
				$InteractionsBalance = $InteractionResult;
			} else {
				$InteractionStatus = 'ERROR'.' ['.substr($InteractionResult,0,50).']';
				$InteractionsBalanceLog = $InteractionResult;
			}




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
			$InteractionListContent	= 'https://orbis.orveecrm.com/templates/HelpDeskTicketOfflineTemplate.html';  
				$InteractionListSQL	= implode('', file($InteractionListContent)); 
			
			$InteractionFrom  		= $configuration['adminemail'];
			$InteractionFromName    = 'Orvee HelpDesk';
			$InteractionReplyTo  	= $configuration['adminreplyto'];
			//$InteractionSubject 	= 'Ticket Offline '.$configuration['instancelastname'].' de |ACTIONDATE|';
			$InteractionSubject 	= 'SMS Balance @ |SETTLEMENTPERIOD|';
			$InteractionContent 	= 'https://orbis.orveecrm.com/templates/HelpDeskTicketOfflineTemplate.html'; 
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
				$InteractionAuthorized = 1;

				$ContentInsideGlobal = "";
				


							
							$EmailDistributionList = 'raulbg@origis.com,hectorm@origis.com,lilianap@origis.com,helpdesk@orbisfarma.com.mx';
							
							//require_once('../includes/HelpDeskTicketOfflineSend.php');	
							
							$ContentBody			= '';
							$SidebarSummary 		= '';
							$SidebarSummaryEmpty	= '';
							
						
							// ------------------------------
							// OPERATION CONTENT
							// ------------------------------
								$OperationStatus	= "NA";
								$OperationToday	= date('M d, Y');

								if ($InteractionsBalance < 10000) { $OperationStatus = "WARNING"; }
								if ($InteractionsBalance < 5000) { $OperationStatus = "ERROR"; }
								if ($InteractionsBalance < 1000) { $OperationStatus = "ERROR"; }
		
			
									$InteractionId = "1";
									$InteractionSentId = "1";
									
							
										// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
											// Contenido
											$InteractionCode 		= "OrveeCRM.".$InteractionId.".".$InteractionSentId.".0.".date("YmdHis");
											$InteractionCodeAuth 	= md5($InteractionCode);
											$InteractionCodeUnique  = "-@id:".$InteractionCode."-";
											
										// APPLICATION PATH & URL
											// Application Current Path 
											$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
											$AppCurrentPath = str_replace("/includes", "", $AppCurrentPath);
											
										// STATUS
											$StatusColor = "FFCC00";
		
											switch ($OperationStatus) {
												case "WARNING":
													$StatusColor = "FFCC00";
													break;
												case "ERROR":
													$StatusColor = "FF3300";
													break;
												case "AUTHORIZED":
													$StatusColor = "00CC00";
													break;
												case "COMPLETED":
													$StatusColor = "00CCFF";
													break;
												default:
												   $StatusColor = "00CC00";
											}									
											
										
				
										// CONTENT LIST
											// Extraemos los resultados de las tareas...
											$ContentBody = "";

											$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
											
											// TICKET ID
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:12px;">';
												$ContentBody .= '<td style="background-color:#0072C6;border-right:1px solid #ADB1BD;padding:5px;color:#FFFFFF;" colspan="2">';
												$ContentBody .= 'SMS Balance';
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';
											// STATUS
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
												$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
												$ContentBody .= 'Saldo';
												$ContentBody .= '</td>';
												$ContentBody .= '<td style="padding:5px;font-size:11px;background-color:#'.$StatusColor.';">';
												$ContentBody .= '<span style="font-weight:bold;color:#FFFFFF;font-size:14px;">';
												$ContentBody .= number_format($InteractionsBalance);
												$ContentBody .= '</span>';
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';
											// PERIODO
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
												$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
												$ContentBody .= 'Log';
												$ContentBody .= '</td>';
												$ContentBody .= '<td style="padding:5px;font-size:11px;">';
												$ContentBody .= $InteractionsBalanceLog;
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';
											// PERIODO
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
												$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
												$ContentBody .= 'Corte';
												$ContentBody .= '</td>';
												$ContentBody .= '<td style="padding:5px;font-size:11px;">';
												$ContentBody .= date('Ymd');
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';
												

											// USER & NOTES
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
												$ContentBody .= '<td style="background-color:#F0F0F0;border-right:1px solid #ADB1BD;padding:5px;" colspan="2">';
												$ContentBody .= 'info ';
												$ContentBody .= '<i>'."";
												$ContentBody .= ' @ '.date('M d, Y H:i:s').'</i>';
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';
				
											$ContentBody .= '</table>';
								
							
						
							
						// EMAIL HEADERS
						$EmailMessage['Headers'] = "";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
				
						// To, From & Subject del Email
						$EmailTo 		= $EmailDistributionList;
						$EmailCc		= "";
						//$EmailCc		= "helpdesk@orbisfarma.com.mx";
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
						$EmailMessage['Subject'] = str_replace("|SETTLEMENTPERIOD|", date('Ymd'), $EmailMessage['Subject']);
						// Status at subject
						if ($OperationStatus !== "NA") {
							$EmailMessage['Subject'] = "[".$OperationStatus."] ".$EmailMessage['Subject'];
						}
						$EmailMessage['Subject'] = $configuration['instancelastname'].' '.$EmailMessage['Subject'];		
						
						// EMAIL CONTENT					
						$EmailMessage['Content'] = $InteractionContent;
			
						// REGISTRANT CONTENT
						$EmailMessage['Body'] = $InteractionContentText;
							$EmailMessage['Body'] = str_replace("|CONTENTMESSAGE|", "&nbsp;", $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTTYPE|", "SMS Balance", $EmailMessage['Body']);
							
							$EmailMessage['Body'] = str_replace("|CONTENT|", $ContentBody, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTSIDEBAR|", $SidebarSummary, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTOWNER|", "ORIGIS", $EmailMessage['Body']);
							
							$EmailMessage['Body'] = str_replace("|CONTENTDATE|", $OperationToday, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|DATE|", $OperationToday, $EmailMessage['Body']);
		
							
							// Tags en APP & TIME
							//$EmailMessage['Body'] = str_replace("|LOCATION|", $OperationContent['RecordInstance'].' @ '.$OperationContent['RecordLocation'], $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|LOCATION|", $configuration['appkey'], $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|APP|", $script, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|TIME|", date('d/m/Y')." ".date('H:i:s'), $EmailMessage['Body']);
		
		
							// --------------------------------------------------
							// INTERACTION SEND!!!
							// --------------------------------------------------
							// if schedule ok...
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
									echo "... Not Sent!<br />";
							}
	

	
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// UPDATE CURRENT EXECUTION
								$query  = "UPDATE    ".$configuration['instanceprefix']."dbo.AppParameters
											SET              ParameterValue = '".$InteractionStatus."', ParameterLastDate = GETDATE(),
																ParameterDescription = '".$scriptactual."@".$configuration['appkey']."'
											WHERE     (ParameterType = 'Task') AND (ParameterName = 'InteractionsSMSBalanceCheck');";
								$dbconnection->query($query);
							// ------------------------------
							// PARAMETERS LOG:end
							// ------------------------------
						
						
		include_once('../includes/databaseconnectionrelease.php');	
	

	// FINAL OUTPUT
	echo "<br />";
	echo "ALL Notifications Sent!<br />";
	echo date('Ymd H:i:s');


	

?>