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
		$itemtype = 'today';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyText($_GET['t']);
			if ($itemtype == '') { $itemtype = 'today'; }
		}
		$itemtype = strtoupper($itemtype);

		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
				
	
		// INIT OUTPUT
		echo $itemtype." Rules Warnings Notifications<br />";
		echo "<br />";
		
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// DELETE PREVIOUS
								$query  = "DELETE FROM ".$configuration['instanceprefix']."dbo.AppParameters  
											WHERE (ParameterType = 'Task')
											AND (ParameterName = 'RulesWarningsSend');";
								$dbconnection->query($query);
								// INSERT NEW EXECUTION
								$query  = "INSERT INTO ".$configuration['instanceprefix']."dbo.AppParameters
											(ParameterType, ParameterName, ParameterValue, ParameterDescription, ParameterLastDate)
											VALUES     
											('Task', 'RulesWarningsSend', 'Running...', '".$scriptactual."@".$configuration['appkey']."', GETDATE());";
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
			$InteractionListContent	= '../templates/RulesWarningListItemTemplate.html';  
				$InteractionListSQL	= implode('', file($InteractionListContent)); 
			
			$InteractionFrom  		= $configuration['adminemail'];
			$InteractionFromName    = 'Orvee Warnings';
			$InteractionReplyTo  	= $configuration['adminreplyto'];
			$InteractionSubject 	= 'Alarmas |RULENAME| de |RULEDATE| @ '.$configuration['instancelastname'];
			$InteractionContent 	= '../templates/RulesWarningListTemplate.html'; 
				$InteractionContentText	= implode('', file($InteractionContent));
				
			$InteractionCode		= '';
			$InteractionCodeAuth	= '';

				
					// INTERACTION SENDER 
						$items = 0;
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
											'0', 
											'".$configuration['appkey']."', 
											'view', 
											'crm', 
											'0', 
											'Rules', 
											'WarningEmailFrom';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						if ($items > 0) {
								$my_row=$dbconnection->get_row();
								$InteractionFrom = trim($my_row['ParameterValue']);
						} 
						if ($InteractionFrom == '' || isValidEmail($InteractionFrom) == 0) {
							$InteractionFrom = $configuration['adminemail'];
						}
		
						$items = 0;
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
											'0', 
											'".$configuration['appkey']."', 
											'view', 
											'crm', 
											'0', 
											'Rules', 
											'WarningEmailFromName';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						if ($items > 0) {
								$my_row=$dbconnection->get_row();
								$InteractionFromName = trim($my_row['ParameterValue']);
								if ($InteractionFromName == '') {
									$InteractionFromName = 'Orvee Warnings';
								}
						} 
		
		
	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
		
			// WARNING LIST		
				$records = 0;
				$RuleId = 0;
				$RuleList = '';
				
				$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_RulesWarningsManage
									'1', 
									'".$configuration['appkey']."', 
									'scheduletodayforteo19', 
									'warnings', 
									'0', 
									'".$itemtype."';";
				$dbtransactions->query($query);
				while($my_row=$dbtransactions->get_row()){	
						$records = $records + 1;
						
						$RuleId 	= $my_row['RuleWarningId'];
						$RuleName 	= $my_row['RuleName'];
						$RuleDate	= $my_row['WarningDate'];
						$RuleToday	= $my_row['WarningToday'];
						$RuleColor	= $my_row['WarningColor'];
						$RuleTitleColor = 'F78F1E';
						$RuleTitleColor = $RuleColor;
						$RuleList 	= $my_row['WarningDistributionList'];
						//$RuleTitle	= $configuration['instancefirstname'].' '.$configuration['instancelastname'];
						$RuleTitle	= 'Lilly Forteo Platinum';
						$InteractionId = $RuleId;
						$InteractionSentId = $RuleId;
						
						echo "RuleWarning ".$RuleId.".";

				
						// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
							// Contenido
							$InteractionCode 		= "OrveeCRM.".$InteractionId.".".$InteractionSentId.".".$AffiliationId.".".date("YmdHis");
							$InteractionCodeAuth 	= md5($InteractionCode);
							$InteractionCodeUnique  = "-@id:".$InteractionCode."-";
						

						// CONTENT LIST
							// Extraemos los resultados de las alarmas
							$WarningLink 	= strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
							$WarningLink 	= str_replace('includes/', '', $WarningLink);
							$WarningLink   .= 'index.php?m=affiliation&s=items&a=view&q=';
							$WarningLink    = '#';
							$WarningDate 	= getDateFromDays(1);
							$WarningToday 	= date('M d, Y');
							$WarningColor	= $RuleColor;
							$WarningContent	= '';
							$WarningContentItem = '';
							$WarningResume	= '';
							$WarningItem = '';
							$WarningItem = $InteractionListSQL; 
							
							// GET LIST
							$items = 0;
							$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_RulesWarningsManage
												'1', 
												'".$configuration['appkey']."', 
												'listtoday', 
												'warnings', 
												'".$RuleId."',
												'".$itemtype."';";
							$dbnotificationslist->query($query);
							$items = $dbnotificationslist->count_rows();
							if ($items > 0) {
								while($mylist=$dbnotificationslist->get_row()){
									$WarningToday = $mylist['WarningDate'];
									$WarningDate = $mylist['WarningDateBegin'];
									
									$WarningContentItem = $WarningItem."<br />";
									
									$WarningLinkAffiliated  = '';
									$WarningLinkAffiliated .= $WarningLink.$mylist['CardNumber'];
			
									// ITEM:begin
									$WarningContentItem = str_replace("CCCCCC", $mylist['WarningColor'], $WarningContentItem);
									$WarningContentItem = str_replace("|WID|", $mylist['RuleWarningId'], $WarningContentItem);
									
									$WarningWho = '';
									$WarningWho .= '<a href="'.$WarningLinkAffiliated.'" target="_BLANK" ';
									$WarningWho .= 'title="Ver Afiliado" style="text-decoration:none;">';
									$WarningWho .= '<span style="font-size:12px;">';
									$WarningWho .= ''.$mylist['CardNumber'].'';
									$WarningWho .= '</span><br />';
									//$WarningWho .= ''.$mylist['CardName'].'';
									$WarningWho .= '';
									$WarningWho .= '</a>';
									if (trim($mylist['CardStatus']) !== '' || trim($mylist['CardStatus']) !== '') {
										$WarningWho .= '<br />';
										$WarningWho .= '<span style="color:#FF0000;font-size:8px;font-weight:bold;">';
										$WarningWho .= ''.$mylist['CardStatus'].'';
										$WarningWho .= '</span>&nbsp;';
										$WarningWho .= '<span style="color:#F78F1E;font-size:8px;font-weight:bold;">';
										$WarningWho .= ''.$mylist['CardRecurrent'].'';
										$WarningWho .= '</span>';
									}
									$WarningContentItem = str_replace("|WARNINGWHO|", $WarningWho, $WarningContentItem);
			
									$WarningWhat  = '';
									$WarningWhat .= '<span style="font-size:8px;">';
									$WarningWhat .= $mylist['RuleName'].'<br />';
									$WarningWhat .= '@ '.number_format($mylist['WarningOperationUnits'],0).' '.$mylist['WarningObject'];
									$WarningWhat .= '<br />';
									$WarningWhat .= 'Ref '.$mylist['RulesWarningsLogId'];
									$WarningWhat .= '</span>';
									$WarningContentItem = str_replace("|WARNINGWHAT|", $WarningWhat, $WarningContentItem);
			
									$WarningWhen  = '';
									$WarningWhen .= '<span style="font-size:8px;">';
									$WarningWhen .= $mylist['WarningDateEnd'].'<br />';
									$WarningWhen .= '@ '.$mylist['ConnectionReference'].' ';
									$WarningWhen .= $mylist['WarningPlace'];
									$WarningWhen .= '</span>';
									$WarningContentItem = str_replace("|WARNINGWHEN|", $WarningWhen, $WarningContentItem);
									// ITEM:end
									
									$WarningContent .= $WarningContentItem."<br />";
			
								}
							}
							if ($items == 0) {
								
								$WarningContent	= '<br /><span style="font-style:italic;font-size:16px;">Sin Ventas</span><br />';
								
							}
		
		
						// RESUME LIST
							// Extraemos los resultados de las alarmas
							$items = 0;
							$WarningResume  = '';
							$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_RulesWarningsManage
												'1', 
												'".$configuration['appkey']."', 
												'resumetoday', 
												'warnings', 
												'".$RuleId."',
												'".$itemtype."';";
							$dbnotificationslist->query($query);
							$items = $dbnotificationslist->count_rows();
							if ($items > 0) {
								while($mylist=$dbnotificationslist->get_row()){
									$WarningColor = 'CCCCCC';
									$WarningColor = $mylist['WarningColor'];
									if ($WarningColor == '') { $WarningColor = 'CCCCCC'; }
		
									$WarningResume .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
		
									$WarningResume .= '<tr>';
		
									$WarningResume .= '<td colspan="2" style="text-align:center;background-color:#FFFFFF;padding:5px;border-bottom:1px solid #ADB1BD;font-weight:bold;font-size:9px;">';
									$WarningResume .= $mylist['RuleName'];
									$WarningResume .= '</td>';
									
									$WarningResume .= '</tr>';
									
									$WarningResume .= '<tr>';
		
									$WarningResume .= '<td style="width:30%;text-align:center;background-color:#'.$WarningColor.';padding:5px;font-size:14px;font-weight:bold;color:#FFFFFF;">';
									$WarningResume .= $mylist['RuleWarningId'];
									$WarningResume .= '</td>';
		
									$WarningResume .= '<td style="width:70%;text-align:center;background-color:#F0F0F0;padding:5px;border-left:1px solid #ADB1BD;">';
									$WarningResume .= number_format($mylist['WarningItems'],0).' casos';
									$WarningResume .= '</td>';
		
									$WarningResume .= '</tr>';
									$WarningResume .= '</table><br />';
			
								}
							}
							if ($items == 0) {
								
								$WarningResume .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
								$WarningResume .= '<tr><td><span style="font-style:italic;">Sin Ejecuciones</span></td></tr>';
								$WarningResume .= '</table><br />';
							}
							
							
							
					// EMAIL HEADERS
					$EmailMessage['Headers'] = "";
					$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
					$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
					$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
			
					// To, From & Subject del Email
					$EmailTo 		= $RuleList;
					$EmailCc		= "";
					$EmailBcc 		= "";
			
					// EMAIL FROM & TO
					$EmailMessage['From'] 	  = $InteractionFrom;
					$EmailMessage['FromName'] = $InteractionFromName;
					$EmailMessage['To']   	  = $EmailTo;
					//$EmailMessage['To']   	  = "linea.lilly@lilly.com";
					$EmailMessage['ReplyTo']  = $InteractionReplyTo;
					$EmailMessage['Cc']  	  = $EmailTo;
					$EmailMessage['Bcc']  	  = "";
					//linea.lilly@lilly.com
		
					// EMAIL SUBJECT					
					$EmailMessage['Subject'] = $InteractionSubject;		
					$EmailMessage['Subject'] = str_replace("|RULENAME|", $RuleName, $EmailMessage['Subject']);
					$EmailMessage['Subject'] = str_replace("|RULEDATE|", $RuleDate, $EmailMessage['Subject']);
					
					// EMAIL CONTENT					
					$EmailMessage['Content'] = $InteractionContent;
					$EmailMessage['Body'] = $InteractionContentText;
					
		
					$EmailMessage['Body'] = str_replace("|WARNINGTITLE|", $RuleTitle, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|WARNINGNAME|", $RuleName.' ['.$RuleId.']', $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|WARNINGDATE|", $RuleDate, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|WARNINGLIST|", $WarningContent, $EmailMessage['Body']);
					//$EmailMessage['Body'] = str_replace("|WARNINGRESUME|", $WarningResume, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|WARNINGRESUME|", "", $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("CCCCCC", $RuleTitleColor, $EmailMessage['Body']);
					$EmailMessage['Body'] = str_replace("|DATE|", $RuleToday, $EmailMessage['Body']);
					
					// Tags en APP & TIME
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
	
	
				} // while($my_row=$dbtransactions->get_row()){	
	
	
	
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// UPDATE CURRENT EXECUTION
								$query  = "UPDATE    ".$configuration['instanceprefix']."dbo.AppParameters
											SET              ParameterValue = 'FINISHED', ParameterLastDate = GETDATE(),
																ParameterDescription = '".$scriptactual."@".$configuration['appkey']."'
											WHERE     (ParameterType = 'Task') AND (ParameterName = 'RulesWarningsSend');";
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