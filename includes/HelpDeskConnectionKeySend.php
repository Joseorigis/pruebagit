<?php

// ----------------------------------------------------------------------------------------------------
// HELP DESK TICKET OFFLINE
// ----------------------------------------------------------------------------------------------------

		// Verificamos si ya están vinculadas las librerías necesarias...
		if (!isset($appcontainer)) {
			
			// Iniciamos el controlador de SESSIONs de PHP
				session_start();
			
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

		} 


// --------------------
// INICIO CONTENIDO
// --------------------

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();
	
		// Connecting to database ALTERNATE
		$dbconnectionalternate = new database($configuration['db1type'],
							$configuration['db1host'], 
							$configuration['db1name'],
							$configuration['db1username'],
							$configuration['db1password']);		


	
	// --------------------------------------------------
	// SCRIPT PARAMS
	// --------------------------------------------------

		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
		$AppCurrentPath = str_replace("/includes", "", $AppCurrentPath);

		// ItemId
		if (!isset($itemid)) {
			$itemid = "0";
		}
		// CardNumber
		if (!isset($cardnumber)) {
			$cardnumber = "0";
		}
		
		
	// --------------------------------------------------
	// INTERACTION PARAMS
	// --------------------------------------------------

		// Get Local SMTP Host
		$InteractionSMTPHost = ini_get('SMTP');

		$InteractionResult  = '';
		$InteractionSent 	= 99;
		$InteractionService	= '';
		
				// INTERACTION SERVICE
					// EMAIL
					$InteractionService = $InteractionSMTPHost;
			

	// --------------------------------------------------
	// INTERACTION CONTENT
	// --------------------------------------------------
	
			// Extraemos los datos de la campaña
			$InteractionId  		= 0;
			$InteractionSentId 		= 0;
			$InteractionType  		= 'EMAIL';
			$InteractionName  		= '';
			$InteractionStatusId  	= 0;
			$InteractionMonitor 	= ''; 
			$InteractionListId		= 0; 
			$InteractionListContent	= 'https://orbis.orveecrm.com/templates/HelpDeskTicketOfflineTemplate.html';  
				$InteractionListSQL	= implode('', file($InteractionListContent)); 
			
			$InteractionFrom  		= $configuration['adminemail'];
			$InteractionFromName    = 'Orvee HelpDesk';
			$InteractionReplyTo  	= $configuration['adminreplyto'];
			$InteractionSubject 	= 'Connection |CONNECTIONID| @ '.$configuration['instancelastname'].'';
			$InteractionContent 	= 'https://orbis.orveecrm.com/templates/HelpDeskTicketOfflineTemplate.html'; 
				$InteractionContentText	= implode('', file($InteractionContent));
				
			$InteractionCode		= '';
			$InteractionCodeAuth	= '';


	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
		
			// CONTENT VARIABLES		
				$records 				= 0;
				$EmailDistributionList  	= "";
				$EmailDistributionListCc  	= "";
				
				// Current User
				if (isset($_SESSION[$configuration['appkey']]['email'])) {
					$EmailDistributionList = $_SESSION[$configuration['appkey']]['email'];
				}
				
				if (trim($OperationContent['OperationSendTo']) !== "") {
					$EmailDistributionListCc = $OperationContent['OperationSendTo'];
				}

				if ($EmailDistributionList == "") {
					$EmailDistributionList = $OperationContent['OperationSendTo'];
					$EmailDistributionListCc = "";
				}

				if ($EmailDistributionList == "") {
					$EmailDistributionList = 'helpdesk@orbisfarma.com.mx';
				}

				//$EmailAuthList 			= 'raulbg@origis.com';
				//$EmailDistributionList 	= 'raulbg@origis.com';
				$ContentBody			= '';
				$SidebarSummary 		= '';
				$SidebarSummaryEmpty	= '';
				
				
					// ------------------------------
					// OPERATION CONTENT
					// ------------------------------
						$OperationStatus	= strtoupper($OperationContent['ConnectionKeyStatus']);
						$OperationStatus	= $OperationAction;
						$OperationToday		= date('M d, Y');

	
							$InteractionId 		= $OperationContent['RecordId'];
							$InteractionSentId 	= $OperationContent['RecordId'];
							
							if (isset($OperationContent)) {	
					
								// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
									// Contenido
									$InteractionCode 		= "OrveeCRM.".$InteractionId.".".$InteractionSentId.".0.".date("YmdHis");
									$InteractionCodeAuth 	= md5($InteractionCode);
									$InteractionCodeUnique  = "-@id:".$InteractionCode."-";
									
								// APPLICATION PATH & URL
									// Application Current Path 
									$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
									$AppCurrentPath = str_replace("/includes", "", $AppCurrentPath);
									
									// SI es diferente la aplicación origen
									if (strtolower($configuration['appkey']) !== strtolower($OperationContent['RecordSource'])) {
											$items = 0;
											$queryapp  = "EXEC dbo.usp_app_ApplicationsListManage
																'0', 
																'".$configuration['appkey']."', 
																'view', 
																'crm', 
																'0', 
																'".$OperationContent['RecordSource']."';";
											$dbsecurity->query($queryapp);
											$items = $dbsecurity->count_rows();
											if ($items > 0) {
													$my_app=$dbsecurity->get_row();
													$AppCurrentPath = trim($my_app['ApplicationPath']);
											} 
									}
									
								// STATUS
									$StatusColor = "FFCC00";

									switch ($OperationStatus) {
										case "NEW":
											$StatusColor = "FFCC00";
											break;
										case "MODIFIED":
											$StatusColor = "FF3300";
											break;
										case "AUTHORIZED":
											$StatusColor = "00CC00";
											break;
										case "ACTIVATE":
											$StatusColor = "00CC00";
											break;
										case "BLOCK":
											$StatusColor = "FF0000";
											break;
										case "COMPLETED":
											$StatusColor = "00CCFF";
											break;
										default:
										   $StatusColor = "FFCC00";
									}									
									
								
		
								// CONTENT LIST
									// Extraemos los resultados de las tareas...
									$ContentBody = "";
		
									$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
									
									// TICKET ID
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:12px;">';
										$ContentBody .= '<td style="background-color:#0072C6;border-right:1px solid #ADB1BD;padding:5px;color:#FFFFFF;" colspan="2">';
										$ContentBody .= 'ID ';
										$ContentBody .= '<span style="color:#FFFF00;font-weight:bold;">'.$OperationContent['ConnectionId'].'</span>';
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';

									// STATUS
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#'.$StatusColor.';border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Status';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;background-color:#'.$StatusColor.';">';
										$ContentBody .= '<span style="font-weight:bold;color:#FFFFFF;">';
										$ContentBody .= $OperationStatus;
										$ContentBody .= '</span>';
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
		
									// CONNECTION
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Conexi&oacute;n';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $OperationContent['ConnectionName']." [".$OperationContent['ConnectionId']."]";
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';

									// CONNECTION SETTINGS
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Configuraci&oacute;n';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $OperationContent['ConnectionType'];
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';

										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Licencias';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $OperationContent['ConnectionLicenses']." licencias";
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
										
									//  CONNECTION KEY
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Key';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $OperationContent['ConnectionKey'];
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';

									// USER & NOTES
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F0F0;border-right:1px solid #ADB1BD;padding:5px;" colspan="2">';
										$ContentBody .= 'Alta ';
										$ContentBody .= '<i>'.$_SESSION[$configuration['appkey']]['username']."";
										$ContentBody .= ' @ '.date('M d, Y H:i:s').'</i>';
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
		
									$ContentBody .= '</table>';
						
					
	
							} else {
								
								$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
								$ContentBody .= '<tr><td><span style="font-style:italic;">Sin Informaci&oacute;n de la conexi&oacute;n</span></td></tr>';
								$ContentBody .= '</table><br />';
							}
							
				
				// --------------------
				// SIDEBAR ACTIONS
				// --------------------

						$SidebarSummary .= '<span style="font-weight:bold;">ACTIONS</span>';
						$SidebarSummary .= '<br />';
				
					// CONNECTION	
						$SidebarSummary .= '<a href="'.$AppCurrentPath.'?m=helpdesk&s=connections&a=view&n='.$OperationContent['ConnectionId'].'" target="_blank" style="text-decoration:none;">';
						$SidebarSummary .= '&middot; Ver Conexi&oacute;n';
						$SidebarSummary .= '</a>';
						
						$SidebarSummary .= '</span>';
						$SidebarSummary .= '<br />';
					
					
				// EMAIL HEADERS
				$EmailMessage['Headers'] = "";
				$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
				$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
				$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
		
				// To, From & Subject del Email
				$EmailTo 		= $EmailDistributionList;
				$EmailCc		= $EmailDistributionListCc;
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
				//$EmailMessage['Subject'] = str_replace("|ACTIONDATE|", $TicketToday, $EmailMessage['Subject']);
				$EmailMessage['Subject'] = str_replace("|CONNECTIONID|", $OperationContent['ConnectionId'], $EmailMessage['Subject']);
				// Status at subject
				$EmailMessage['Subject'] = "[".$OperationStatus."] ".$EmailMessage['Subject'];
				
				// EMAIL CONTENT					
				$EmailMessage['Content'] = $InteractionContent;
	
				// REGISTRANT CONTENT
				$EmailMessage['Body'] = $InteractionContentText;
					$EmailMessage['Body'] = str_replace("|CONTENTMESSAGE|", "&nbsp;", $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTTYPE|", "Conexi&oacute;n", $EmailMessage['Body']);
					
					$EmailMessage['Body'] = str_replace("|CONTENT|", $ContentBody, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTSIDEBAR|", $SidebarSummaryEmpty, $EmailMessage['Body']);
					//$EmailMessage['Body'] = str_replace("|CONTENTSIDEBAR|", $SidebarSummary, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTOWNER|", $configuration['appkey'], $EmailMessage['Body']);
					
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


	
		// Disconnect to databases &  ALTERNATE
		//include_once('includes/databaseconnectionrelease.php');	
		$dbconnectionalternate->disconnect();	

?>