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

		// TicketId
		if (!isset($ticketid)) {
			$ticketid = "0";
		}
		// CardNumber
		if (!isset($cardnumber)) {
			$cardnumber = "0";
		}

		// session user
		$operationuserid = "0";
		if (isset($_SESSION[$configuration['appkey']]['userid'])) {
			$operationuserid = $_SESSION[$configuration['appkey']]['userid'];
		}
		$operationusername = "none";
		if (isset($_SESSION[$configuration['appkey']]['username'])) {
			$operationusername = $_SESSION[$configuration['appkey']]['username'];
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
			//$InteractionSubject 	= 'Ticket Offline '.$configuration['instancelastname'].' de |ACTIONDATE|';
			$InteractionSubject 	= 'Ticket Offline |CASENUMBER| @ '.$configuration['instancelastname'].'';
			$InteractionContent 	= 'https://orbis.orveecrm.com/templates/HelpDeskTicketOfflineTemplate.html'; 
				$InteractionContentText	= implode('', file($InteractionContent));
				
			$InteractionCode		= '';
			$InteractionCodeAuth	= '';


	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
		
			// CONTENT VARIABLES		
				$records 		= 0;
				$EmailAuthList  = "";
				$EmailDistributionList  = "";
				
					if (trim($TicketContent['TicketOfflineAdministrator']) !== "") {
						$EmailAuthList .= $TicketContent['TicketOfflineAdministrator'];
					}
					if (trim($invoicemonitor) !== "") {
						$EmailDistributionList .= $invoicemonitor;
					}
					if (trim($ticketmonitor) !== "") {
						$EmailDistributionList .= ','.$ticketmonitor;
					}
					
				if ($EmailAuthList == "") {
					$EmailAuthList = 'helpdesk@orbisfarma.com.mx';
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
					// TICKET CONTENT
					// ------------------------------
						$TicketToday	= date('M d, Y');
						$TicketStatus	= strtoupper($TicketContent['TicketOfflineStatus']);
						// for ticket image
						if (isset($TicketAction))  {
							if ($TicketAction == "image") {
								$TicketStatus = "IMAGE";
							}
						}

	
							$InteractionId = $TicketContent['RecordId'];
							$InteractionSentId = $TicketContent['RecordId'];
							
							if (isset($TicketContent)) {	
					
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
									if (strtolower($configuration['appkey']) !== strtolower($TicketContent['RecordSource'])) {
											$items = 0;
											$queryapp  = "EXEC dbo.usp_app_ApplicationsListManage
																'0', 
																'".$configuration['appkey']."', 
																'view', 
																'crm', 
																'0', 
																'".$TicketContent['RecordSource']."';";
											$dbsecurity->query($queryapp);
											$items = $dbsecurity->count_rows();
											if ($items > 0) {
													$my_app=$dbsecurity->get_row();
													$AppCurrentPath = trim($my_app['ApplicationPath']);
											} 
									}
									
								// STATUS
									$StatusColor = "FFCC00";

									switch ($TicketStatus) {
										case "NEW":
											$StatusColor = "FFCC00";
											break;
										case "REJECTED":
											$StatusColor = "FF3300";
											break;
										case "AUTHORIZED":
											$StatusColor = "00CC00";
											break;
										case "COMPLETED":
											$StatusColor = "00CCFF";
											break;
										case "IMAGE":
											$StatusColor = "990099";
											break;
										default:
										   $StatusColor = "FFCC00";
									}									
									

									$fileexists  	= 0;	
									$fileallowed 	= 0;	
									$fileexists 	= $TicketContent['TicketOfflineFileExists'];
									$fileallowed 	= $TicketContent['TicketOfflineFileAllowed'];
								
		
								// CONTENT LIST
									// Extraemos los resultados de las tareas...
									$ContentBody = "";
		
									$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
									
									// TICKET ID
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:12px;">';
										$ContentBody .= '<td style="background-color:#0072C6;border-right:1px solid #ADB1BD;padding:5px;color:#FFFFFF;" colspan="2">';
										$ContentBody .= 'Ticket Offline ';
										$ContentBody .= '<span style="color:#FFFF00;font-weight:bold;">'.$TicketContent['CaseNumber'].'</span>';
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
									// STATUS
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#'.$StatusColor.';border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Status';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;background-color:#'.$StatusColor.';">';
										$ContentBody .= '<span style="font-weight:bold;color:#FFFFFF;">';
										$ContentBody .= $TicketStatus;
										$ContentBody .= '</span>';
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
									// CARDNUMBER
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Tarjeta';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $TicketContent['CardNumber'];
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
									// CONNECTION & STORE
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Sucursal';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $TicketContent['ConnectionName']." [".$TicketContent['ConnectionId']."]"."<br />";
										$ContentBody .= ' @ Sucursal '.$TicketContent['Store'];
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
									// ITEM
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Art&iacute;culos';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $TicketContent['ItemQuantity']." x<br />";
										$ContentBody .= $TicketContent['ItemSKU']."<br />";
										$ContentBody .= $TicketContent['ItemName']." [".$TicketContent['ItemBrand']."]"."<br />";
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
		
									// INVOICE NUMBER & DATE
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'No. Ticket';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $TicketContent['InvoiceNumber'];
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
										
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Fecha';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:11px;">';
										$ContentBody .= $TicketContent['InvoiceDate'];
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';


									// FILE										
										if ($fileexists == 1) {
											$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
											$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
											$ContentBody .= 'Archivo';
											$ContentBody .= '</td>';
											$ContentBody .= '<td style="padding:5px;font-size:11px;">';
											$ContentBody .= '<a href="'.$TicketContent['TicketOfflineFile'].'" target="_blank" title="Ver Archivo Ticket">';
											$ContentBody .= '<img src="https://storage.orveecrm.com/filemanager/imageicon.png" />';
											$ContentBody .= '</a>';
											$ContentBody .= '</td>';
											$ContentBody .= '</tr>';
										} // [if ($fileexists == 1)]
										if ($fileexists == 0 && $fileallowed == 1) {
											$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
											$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
											$ContentBody .= 'Archivo';
											$ContentBody .= '</td>';
											$ContentBody .= '<td style="padding:5px;font-size:11px;">';
											$ContentBody .= '<a href="https://storage.orveecrm.com/filemanager/index.php?n='.$TicketContent['RecordId'].'&t=ticketoffline" target="_blank" title="Ver Archivo Ticket" style="font-weight:bold;text-decoration:none;">';
											$ContentBody .= '<img src="https://storage.orveecrm.com/filemanager/imageoff.png" style="vertical-align:middle;" /> [+] Cargar Archivo';
											$ContentBody .= '</a>';
											$ContentBody .= '</td>';
											$ContentBody .= '</tr>';
										} // [if ($fileexists == 0 && $fileallowed == 1)]
										
										
									// USER & NOTES
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
										$ContentBody .= 'Comentarios';
										$ContentBody .= '</td>';
										$ContentBody .= '<td style="padding:5px;font-size:8px;">';
										$ContentBody .= $TicketContent['TicketOfflineNotes'];
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
		
										$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
										$ContentBody .= '<td style="background-color:#F0F0F0;border-right:1px solid #ADB1BD;padding:5px;" colspan="2">';
										$ContentBody .= 'Alta ';
										$ContentBody .= '<i>'.$operationusername."";
										$ContentBody .= ' @ '.date('M d, Y H:i:s').'</i>';
										$ContentBody .= '</td>';
										$ContentBody .= '</tr>';
		
									$ContentBody .= '</table>';
						
					
	
							} else {
								
								$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
								$ContentBody .= '<tr><td><span style="font-style:italic;">Sin Informaci&oacute;n del Ticket</span></td></tr>';
								$ContentBody .= '</table><br />';
							}
							
				
				// --------------------
				// SIDEBAR ACTIONS
				// --------------------

						$SidebarSummary .= '<span style="font-weight:bold;">ACTIONS</span>';
						$SidebarSummary .= '<br />';
				
					// VER TARJETA	
						$SidebarSummary .= '<br />';
						$SidebarSummary .= '<span style="line-height:16px;">';
						
						$SidebarSummary .= '<a href="'.$AppCurrentPath.'?m=affiliation&s=items&a=view&q='.$TicketContent['CaseNumber'].'" target="_blank" style="text-decoration:none;">';
						$SidebarSummary .= '&middot; Ver Tarjeta';
						$SidebarSummary .= '</a>';
						$SidebarSummary .= '<br />';

					// VER HISTORIAL	
						$SidebarSummary .= '<a href="http://historial.orbisfarma.com.mx/index.php?action=balance&key=&storeid=0&posid=0&employeeid='.$operationuserid.'&actionauth=0&cardnumber='.$TicketContent['CaseNumber'].'" target="_blank" style="text-decoration:none;">';
						$SidebarSummary .= '&middot; Ver Historial';
						$SidebarSummary .= '</a>';
						$SidebarSummary .= '<br />';
					
					// AUTORIZAR	
						$SidebarSummary .= '<a href="'.$AppCurrentPath.'?m=helpdesk&s=ticketoffline&a=view&cardnumber='.$TicketContent['CardNumber'].'&q='.$TicketContent['RecordId'].'&n='.$TicketContent['RecordId'].'" target="_blank" style="text-decoration:none;">';
						$SidebarSummary .= '&middot; Ver Ticket Offline';
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
				//$EmailMessage['Subject'] = str_replace("|ACTIONDATE|", $TicketToday, $EmailMessage['Subject']);
				$EmailMessage['Subject'] = str_replace("|CASENUMBER|", $TicketToday, $EmailMessage['Subject']);
				// Status at subject
				$EmailMessage['Subject'] = "[".$TicketStatus."] ".$EmailMessage['Subject'];
				
				// EMAIL CONTENT					
				$EmailMessage['Content'] = $InteractionContent;
	
				// REGISTRANT CONTENT
				$EmailMessage['Body'] = $InteractionContentText;
					$EmailMessage['Body'] = str_replace("|CONTENTMESSAGE|", "&nbsp;", $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTTYPE|", "Ticket Offline", $EmailMessage['Body']);
					
					$EmailMessage['Body'] = str_replace("|CONTENT|", $ContentBody, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTSIDEBAR|", $SidebarSummaryEmpty, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTOWNER|", $TicketContent['ItemOwner'], $EmailMessage['Body']);
					
					$EmailMessage['Body'] = str_replace("|CONTENTDATE|", $TicketToday, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|DATE|", $TicketToday, $EmailMessage['Body']);

					
					// Tags en APP & TIME
					//$EmailMessage['Body'] = str_replace("|LOCATION|", $TicketContent['RecordInstance'].' @ '.$TicketContent['RecordLocation'], $EmailMessage['Body']);
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



				// AUTHORIZATION CONTENT
				// To, From & Subject del Email
				$EmailTo 		= $EmailAuthList;
				$EmailCc		= "";
				$EmailBcc 		= "";
		
				// EMAIL FROM & TO
				$EmailMessage['From'] 	  = $InteractionFrom;
				$EmailMessage['FromName'] = $InteractionFromName;
				$EmailMessage['To']   	  = $EmailTo;
				$EmailMessage['ReplyTo']  = $InteractionReplyTo;
				$EmailMessage['Cc']  	  = "";
				$EmailMessage['Bcc']  	  = "";
				
				$EmailMessage['Body'] = $InteractionContentText;

					$EmailMessage['Body'] = str_replace("|CONTENTMESSAGE|", "&nbsp;", $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTTYPE|", "Ticket Offline", $EmailMessage['Body']);
					
					$EmailMessage['Body'] = str_replace("|CONTENT|", $ContentBody, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTSIDEBAR|", $SidebarSummary, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|CONTENTOWNER|", $TicketContent['ItemOwner'], $EmailMessage['Body']);
					
					$EmailMessage['Body'] = str_replace("|CONTENTDATE|", $TicketToday, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|DATE|", $TicketToday, $EmailMessage['Body']);
					
					// Tags en APP & TIME
					//$EmailMessage['Body'] = str_replace("|LOCATION|", $TicketContent['RecordInstance'].' @ '.$TicketContent['RecordLocation'], $EmailMessage['Body']);
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