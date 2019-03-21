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
		$itemtype = 'ticketoffline';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyText($_GET['t']);
			if ($itemtype == '') { $itemtype = 'today'; }
		}
		$itemtype = strtoupper($itemtype);

		// not a list, only a record
		$itemid = '0';
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = '0'; }
		}

		// listtype
		$itemquestion = 'pending';
		if (isset($_GET['q'])) {
			$itemquestion = setOnlyText($_GET['q']);
			if ($itemquestion == '') { $itemquestion = 'pending'; }
		}
		$itemquestion = strtolower($itemquestion);


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
											AND (ParameterName = 'HelpDeskTicketsOfflineSend');";
								$dbconnection->query($query);
								// INSERT NEW EXECUTION
								$query  = "INSERT INTO ".$configuration['instanceprefix']."dbo.AppParameters
											(ParameterType, ParameterName, ParameterValue, ParameterDescription, ParameterLastDate)
											VALUES     
											('Task', 'HelpDeskTicketsOfflineSend', 'Running...', '".$scriptactual."@".$configuration['appkey']."', GETDATE());";
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
			$InteractionListContent	= 'https://orbis.orveecrm.com/templates/HelpDeskTicketOfflineTemplate.html';  
				$InteractionListSQL	= implode('', file($InteractionListContent)); 
			
			$InteractionFrom  		= $configuration['adminemail'];
			$InteractionFromName    = 'Orvee HelpDesk';
			$InteractionReplyTo  	= $configuration['adminreplyto'];
			$InteractionSubject 	= 'Ticket Offline '.$configuration['instancelastname'].' de |ACTIONDATE|';
			$InteractionContent 	= 'https://orbis.orveecrm.com/templates/HelpDeskTicketOfflineTemplate.html'; 
				$InteractionContentText	= implode('', file($InteractionContent));
				
			$InteractionCode		= '';
			$InteractionCodeAuth	= '';

				
		
	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
	
				$TicketToday	= date('M d, Y');
				$TicketStatus	= '';
				$TicketAction 	= '';
				$operation		= 'listpending';
		
			// NOTIFICATIONS LIST		
				$records = 0;
				$RecordId = 0;
				$DistributionList = '';
				$ContentBody			= '';
				$SidebarSummary 		= '';
				$SidebarSummaryEmpty	= '';
							
				$query  = "EXEC dbo.usp_app_HelpDeskTicketsOfflineManage
									'0',
									'".$configuration['appkey']."',
									'listpending';";
				$dbtransactions->query($query);
				while($my_row=$dbtransactions->get_row()){	
							$records = $records + 1;
						
							$invoicemonitor = "";
							$ticketmonitor = "";
							
							$TicketContent = $my_row;
							
							$EmailDistributionList = $TicketContent['TicketOfflineAdministrator'];
							
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
											
										
				
										// CONTENT LIST
											// Extraemos los resultados de las tareas...
					
												$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
					
												$ContentBody .= '<tr>';
					
												$ContentBody .= '<td style="text-align:left;background-color:#0072C6;padding:5px;border-bottom:1px solid #ADB1BD;font-size:9px;color:#FFFFFF;">';
												$ContentBody .= 'Ticket Offline ';
												$ContentBody .= '<span style="color:#FFFF00;font-weight:bold;">'.$TicketContent['CaseNumber'].'</span>';
												$ContentBody .= '</td>';
												
												$ContentBody .= '</tr>';
												
												$ContentBody .= '<tr>';
												$ContentBody .= '<td style="text-align:left;background-color:#FFFFFF;padding:5px;border-left:1px solid #ADB1BD;">';
												$ContentBody .= "<span style='font-size:8px;'>";
												$ContentBody .= $TicketContent['CardNumber']." @ ".$TicketContent['RecordDate']."<br />";
												$ContentBody .= $TicketContent['ItemSKU']." ".$TicketContent['ItemName']."<br />";
												$ContentBody .= "<a href='".$AppCurrentPath."?m=helpdesk&s=ticketoffline&a=view&cardnumber=".$TicketContent['CaseNumber']."&q=".$TicketContent['RecordId']."&n=".$TicketContent['RecordId']."'>Ver detalle</a>"."<br />";
												$ContentBody .= "</span>";
												$ContentBody .= '</td>';
					
												$ContentBody .= '</tr>';
												$ContentBody .= '</table><br />';
			
									} else {
										
										$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
										$ContentBody .= '<tr><td><span style="font-style:italic;">Sin Tickets Offline</span></td></tr>';
										$ContentBody .= '</table><br />';
									}
									
	
				} // while($my_row=$dbtransactions->get_row()){	
	

				// if no records...
				if ($records == 0) {

						$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
						$ContentBody .= '<tr><td><span style="font-style:italic;">Sin Tickets Offline</span></td></tr>';
						$ContentBody .= '</table><br />';
					
				}
	
						
						// --------------------
						// SIDEBAR ACTIONS
						// --------------------
		
								$SidebarSummary .= '<br />';
							
								$SidebarSummary .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
								$SidebarSummary .= '<tr>';
								$SidebarSummary .= '<td colspan="2" style="text-align:center;background-color:#f0f6fB;padding:5px;border-bottom:1px solid #ADB1BD;font-weight:bold;font-size:9px;">';
								$SidebarSummary .= 'Tickets Offline';
								$SidebarSummary .= '</td>';
								$SidebarSummary .= '</tr>';
								$SidebarSummary .= '<tr>';
								$SidebarSummary .= '<td colspan="2" style="text-align:center;background-color:#FFFFFF;padding:5px;border-bottom:1px solid #ADB1BD;font-size:24px;">';
								$SidebarSummary .= $records;
								$SidebarSummary .= '</td>';
								$SidebarSummary .= '</tr>';
								$SidebarSummary .= '</table><br />';		
					
												
								
							// EMAIL HEADERS
						$EmailMessage['Headers'] = "";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
						$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
				
						// To, From & Subject del Email
						$EmailTo 		= $EmailDistributionList;
						//$EmailTo 		= "raulbg@origis.com";
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
						$EmailMessage['Subject'] = str_replace("|ACTIONDATE|", $TicketToday, $EmailMessage['Subject']);
						// Status at subject
						if ($TicketStatus == 'NEW') {
							$EmailMessage['Subject'] = "[NEW] ".$EmailMessage['Subject'];
						}
						
						// EMAIL CONTENT					
						$EmailMessage['Content'] = $InteractionContent;
			
						// REGISTRANT CONTENT
						$EmailMessage['Body'] = $InteractionContentText;
							$EmailMessage['Body'] = str_replace("|CONTENTMESSAGE|", "&nbsp;", $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTTYPE|", "Tickets Offline", $EmailMessage['Body']);
							
							$EmailMessage['Body'] = str_replace("|CONTENT|", $ContentBody, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTSIDEBAR|", $SidebarSummary, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTOWNER|", "HELP DESK", $EmailMessage['Body']);
							
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
							
							echo "... Sent!<br />";
	
	
	
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// UPDATE CURRENT EXECUTION
								$query  = "UPDATE    ".$configuration['instanceprefix']."dbo.AppParameters
											SET              ParameterValue = 'FINISHED', ParameterLastDate = GETDATE(),
																ParameterDescription = '".$scriptactual."@".$configuration['appkey']."'
											WHERE     (ParameterType = 'Task') AND (ParameterName = 'HelpDeskTicketsOfflineSend');";
								$dbconnection->query($query);
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