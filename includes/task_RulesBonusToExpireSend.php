<?php

// ----------------------------------------------------------------------------------------------------
// RULES BONUS TO EXPIRE
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
		$itemtype = 'rulesbonus';
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
											AND (ParameterName = 'RulesBonusToExpireSend');";
								$dbconnection->query($query);
								// INSERT NEW EXECUTION
								$query  = "INSERT INTO ".$configuration['instanceprefix']."dbo.AppParameters
											(ParameterType, ParameterName, ParameterValue, ParameterDescription, ParameterLastDate)
											VALUES     
											('Task', 'RulesBonusToExpireSend', 'Running...', '".$scriptactual."@".$configuration['appkey']."', GETDATE());";
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
			//$InteractionSubject 	= 'Ticket Offline '.$configuration['instancelastname'].' de |ACTIONDATE|';
			$InteractionSubject 	= 'Vencimientos Proximos |ITEMOWNER| @ |ACTIONDATE|';
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
				$InteractionAuthorized = 0;
				
				$query  = "EXEC dbo.usp_app_RulesBonusManage
									'0',
									'".$configuration['appkey']."',
									'listtoexpireindex',
									'".$actionauth."';";
				$dbtransactions->query($query);
				while($my_row=$dbtransactions->get_row()){	
							$records = $records + 1;

							$OperationContent = $my_row;
							$OperationAction = "";
							
							$EmailDistributionList = $OperationContent['RuleContact'];
							$InteractionAuthorized = $OperationContent['RuleSend'];
							echo $OperationContent['ConnectionName'];
							
							//require_once('../includes/HelpDeskTicketOfflineSend.php');	
							
							$ContentBody			= '';
							$SidebarSummary 		= '';
							$SidebarSummaryEmpty	= '';
							
						
							// ------------------------------
							// OPERATION CONTENT
							// ------------------------------
								$OperationStatus	= "INICIO";
								$OperationToday	= date('M d, Y');
		
			
									$InteractionId = $OperationContent['ConnectionId'];
									$InteractionSentId = $OperationContent['ConnectionId'];
									
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
											
										// STATUS
											$StatusColor = "FFCC00";
		
											switch ($OperationStatus) {
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
												default:
												   $StatusColor = "00CC00";
											}									
											
										
				
										// CONTENT LIST
											// Extraemos los resultados de las tareas...
											$ContentBody = "";

		
											$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
											
											// TICKET ID
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:12px;">';
												$ContentBody .= '<td style="background-color:#0072C6;border-right:1px solid #ADB1BD;padding:5px;color:#FFFFFF;" colspan="2">';
												$ContentBody .= 'Vencimientos Reglas<br />';
												$ContentBody .= '<span style="color:#FFFF00;font-weight:bold;">'.$OperationContent['ConnectionName'].'</span>';
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';
											// PERIODO
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
												$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;">';
												$ContentBody .= 'Periodo';
												$ContentBody .= '</td>';
												$ContentBody .= '<td style="padding:5px;font-size:11px;">';
												$ContentBody .= "Pr&oacute;ximos ".$OperationContent['RuleExpirationRange']." d&iacute;as";
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';
												

												// OWNER CONNECTIONS LIST												
													$items = 0;
													$OwnerRulesList = "";
													$OwnerRulesList .= '<br />';
													$OwnerRulesList .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
													
													$OwnerRulesList .= '<tr style="border-bottom:1px solid #ADB1BD;background-color:#F0F6FB;font-size:9px;font-weight:bold;">';

													$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;font-size:9px;font-weight:bold;">';
													$OwnerRulesList .= 'Art&iacute;culo';
													$OwnerRulesList .= '</td>';
													$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;font-size:9px;font-weight:bold;">';
													$OwnerRulesList .= 'Marca';
													$OwnerRulesList .= '</td>';
													$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;font-size:9px;font-weight:bold;">';
													$OwnerRulesList .= 'Regla';
													$OwnerRulesList .= '</td>';
													$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;font-size:9px;font-weight:bold;">';
													$OwnerRulesList .= 'Vencimiento';
													$OwnerRulesList .= '</td>';
													
													$OwnerRulesList .= '</tr>';
																											
													$querylist  = "EXEC dbo.usp_app_RulesBonusManage
																		'0',
																		'".$configuration['appkey']."',
																		'listtoexpire',
																		'".$actionauth."',
																		'".$OperationContent['ConnectionId']."';";
													$dbnotificationslist->query($querylist);
													while($my_list=$dbnotificationslist->get_row()){	
													
														$items = $items + 1;
														
														$bordercolor = "FFCC00";
														if ($my_list['RuleExpirationFlag'] == 'red')
															{ $bordercolor = "FF3300"; }
														
														$OwnerRulesList .= '<tr style="border-bottom:1px solid #ADB1BD;">';

														//$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;">';
														$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;border-left:3px solid #'.$bordercolor.';">';
														$OwnerRulesList .= $my_list['ItemSKU']."<br />";
														$OwnerRulesList .= '<span style="font-size:9px;">';
														$OwnerRulesList .= $my_list['ItemName'];
														$OwnerRulesList .= '</span>';
														$OwnerRulesList .= '</td>';
														$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;">';
														$OwnerRulesList .= '<span style="font-size:9px;">';
														$OwnerRulesList .= $my_list['ItemBrand'];
														$OwnerRulesList .= '</span>';
														$OwnerRulesList .= '</td>';
														$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;">';
														$OwnerRulesList .= $my_list['RuleDescription'];
														$OwnerRulesList .= '</td>';
														$OwnerRulesList .= '<td style="border-right:1px solid #ADB1BD;padding:5px;">';
														$OwnerRulesList .= $my_list['RuleExpiration']."<br />";
														$OwnerRulesList .= '<span style="font-size:8px;font-style:italic;">';
														$OwnerRulesList .= '* En '.$my_list['RuleExpirationDays'].' dias';
														$OwnerRulesList .= '</span>';
														$OwnerRulesList .= '</td>';
														
														$OwnerRulesList .= '</tr>';
														
													} 
													
													if ($OwnerRulesList == "") {

														$OwnerRulesList .= '<td>';
														$OwnerRulesList = "<i>[Sin Reglas / Art&iacute;culos]</i>";
														$OwnerRulesList .= '</td>';

													}
																						
													$OwnerRulesList .= '</table>';
													$OwnerRulesList .= '<br />';
													$OwnerRulesList .= '<br />';
												
												
											// RULES ITEMS
												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
												$ContentBody .= '<td style="background-color:#F0F6FB;border-right:1px solid #ADB1BD;padding:5px;font-weight:bold;" colspan="2">';
												$ContentBody .= 'Reglas / Art&iacute;culos';
												$ContentBody .= '</td>';
												$ContentBody .= '</tr>';

												$ContentBody .= '<tr style="border-bottom:1px solid #ADB1BD;font-size:9px;">';
												$ContentBody .= '<td style="padding:5px;font-size:11px;" colspan="2">';
												$ContentBody .= $OwnerRulesList;
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
								
							
			
									} else {
										
										$ContentBody .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
										$ContentBody .= '<tr><td><span style="font-style:italic;">Sin Informaci&oacute;n</span></td></tr>';
										$ContentBody .= '</table><br />';
									}
									
						
						// --------------------
						// SIDEBAR ACTIONS
						// --------------------
		
								$SidebarSummary .= '<span style="font-weight:bold;">ACTIONS</span>';
								$SidebarSummary .= '<br />';
								$SidebarSummary .= '<br />';
						
							// DESCARGAR	
								//$SidebarSummary .= '<a href="'.$AppCurrentPath.'index.php?m=reports&s=items&a=download&t=settlementindex&d='.$OperationContent['SettlementPeriodISO'].'&n='.$OperationContent['ItemOwnerId'].'&q='.$OperationContent['ItemOwner'].'" target="_blank" style="text-decoration:none;">';
								$SidebarSummary .= '<a href="'.$AppCurrentPath.'index.php" target="_blank" style="text-decoration:none;">';
								$SidebarSummary .= '&middot; Descargar Listado';
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
						$EmailMessage['Subject'] = str_replace("|ITEMOWNER|", $OperationContent['ConnectionName'], $EmailMessage['Subject']);
						$EmailMessage['Subject'] = str_replace("|ACTIONDATE|", $OperationToday, $EmailMessage['Subject']);
						// Status at subject
						//$EmailMessage['Subject'] = "[".$OperationStatus."] ".$EmailMessage['Subject'];
						
						// EMAIL CONTENT					
						$EmailMessage['Content'] = $InteractionContent;
			
						// REGISTRANT CONTENT
						$EmailMessage['Body'] = $InteractionContentText;
							$EmailMessage['Body'] = str_replace("|CONTENTMESSAGE|", "&nbsp;", $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTTYPE|", "Vencimientos Reglas", $EmailMessage['Body']);
							
							$EmailMessage['Body'] = str_replace("|CONTENT|", $ContentBody, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTSIDEBAR|", $SidebarSummary, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|CONTENTOWNER|", $OperationContent['ConnectionName'], $EmailMessage['Body']);
							
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
	
	
				} // while($my_row=$dbtransactions->get_row()){	
	
	
	
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// UPDATE CURRENT EXECUTION
								$query  = "UPDATE    ".$configuration['instanceprefix']."dbo.AppParameters
											SET              ParameterValue = 'FINISHED', ParameterLastDate = GETDATE(),
																ParameterDescription = '".$scriptactual."@".$configuration['appkey']."'
											WHERE     (ParameterType = 'Task') AND (ParameterName = 'RulesBonusToExpireSend');";
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