<?php

// ----------------------------------------------------------------------------------------------------
// PARAMETERS TASK LOG
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
		$itemtype = 'local';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'local'; }
		}
		$itemtype = strtolower($itemtype);
		

		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
				
	
		// INIT OUTPUT
		echo strtoupper($itemtype)." Parameters Task Log Notifications<br />";
		echo "<br />";
		
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// DELETE PREVIOUS
								$query  = "DELETE FROM ".$configuration['instanceprefix']."dbo.AppParameters  
											WHERE (ParameterType = 'Task')
											AND (ParameterName = 'ParametersTaskLog');";
								$dbsecurity->query($query);
								// INSERT NEW EXECUTION
								$query  = "INSERT INTO ".$configuration['instanceprefix']."dbo.AppParameters
											(ParameterType, ParameterName, ParameterValue, ParameterDescription, ParameterLastDate)
											VALUES     
											('Task', 'ParametersTaskLog', 'Running...', '".$scriptactual."@".$configuration['appkey']."', GETDATE());";
								$dbsecurity->query($query);
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
		
		$InteractionTo 		= '';
		$ParameterEmailList = 'raulbg@origis.com';


					// INTERACTION SEND TO
						$items = 0;
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
											'0', 
											'".$configuration['appkey']."', 
											'view', 
											'crm', 
											'0', 
											'Parameters', 
											'TaskLogTo';";
						$dbsecurity->query($query);
						$items = $dbsecurity->count_rows();
						if ($items > 0) {
								$my_row=$dbsecurity->get_row();
								$InteractionTo = trim($my_row['ParameterValue']);
						} 
						if ($InteractionTo == '') {
							$InteractionTo = $ParameterEmailList;
						}


	// --------------------------------------------------
	// INTERACTION CONTENT
	// --------------------------------------------------
	
			// Extraemos los datos de la campaña
			$InteractionId  		= 0;
			$InteractionSentId 		= 0;
			$InteractionType  		= $itemtype;
			$InteractionName  		= '';
			$InteractionStatusId  	= 0;
			$InteractionMonitor 	= ''; 
			$InteractionListId		= 0; 
			$InteractionListContent	= '../templates/ParametersTaskLogTemplate.html';  
				$InteractionListSQL	= implode('', file($InteractionListContent)); 
			
			$InteractionFrom  		= $configuration['adminemail'];
			$InteractionFromName    = 'Orvee Tasks';
			$InteractionReplyTo  	= $configuration['adminreplyto'];
			$InteractionSubject 	= 'Tareas '.$configuration['instancelastname'].' de |PARAMETERDATE|';
			$InteractionContent 	= '../templates/ParametersTaskLogTemplate.html'; 
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
											'Parameters', 
											'TaskLogFrom';";
						$dbsecurity->query($query);
						$items = $dbsecurity->count_rows();
						if ($items > 0) {
								$my_row=$dbsecurity->get_row();
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
											'Parameters', 
											'TaskLogFromName';";
						$dbsecurity->query($query);
						$items = $dbsecurity->count_rows();
						if ($items > 0) {
								$my_row=$dbsecurity->get_row();
								$InteractionFromName = trim($my_row['ParameterValue']);
								if ($InteractionFromName == '') {
									$InteractionFromName = 'Orvee Tasks';
								}
						} 
		
		
	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
		
			// NOTIFICATIONS LIST		
				$records 		= 0;
				$ParameterId 	= 0;
				$ParameterList 	= '';
				$ParameterSummary = '';
				
				$TaskAlert		= 0;	// Task FAILED Flag
				$TaskCount 		= 0;	// Task Count
				$TaskExecuted 	= 0;	// Task Count Executed
				$TaskWithErrors	= 0;	// Task Count Minus Executed
				$TaskFailed 	= 0;	// Task Count Failed During Execution
				$TaskNotExecuted= 0;	// Task Count Not Executed
				
				// TASK LOG for integrity
				$TaskLogCount	= 0;
				
				if ($itemtype == 'global') {
	
						// ------------------------------
						// GLOBAL TASK LOG
						// ------------------------------
							$query  = "EXEC dbo.usp_app_ParametersManage
														'0', 
														'".$configuration['appkey']."', 
														'tasklog', 
														'crm';";
							$dbtransactions->query($query);
							while($my_row=$dbtransactions->get_row()){	
									$records = $records + 1;
									$TaskCount = $TaskCount + 1;
									
									$ParameterId 	= $my_row['ParameterId'];
									$ParameterType 	= $my_row['ParameterType'];
									$ParameterName 	= $my_row['ParameterName'];
									$ParameterValue	= $my_row['ParameterValue'];
									$ParameterDesc 	= $my_row['ParameterDescription'];
									$ParameterDate	= $my_row['ParameterLastExecution'];
									$ParameterToday	= $my_row['ParameterToday'];
									$ParameterInstance  = $my_row['ParameterInstance'];
									$ParameterLocation	= $my_row['ParameterLocation'];
									//$ParameterOwner	= $my_row['ParameterOwner'];
									$ParameterOwner	= $configuration['appkey'];
			
									$ParameterTaskDays		= $my_row['TaskDays'];
									$ParameterTaskExecuted	= $my_row['TaskExecuted'];
									$ParameterTaskCompleted	= $my_row['TaskCompleted'];
			
			
									$InteractionId = $ParameterId;
									$InteractionSentId = $ParameterId;
									
									//echo "Parameter ".$ParameterId.".";
			
							
									// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
										// Contenido
										$InteractionCode 		= "OrveeCRM.".$InteractionId.".".$InteractionSentId.".0.".date("YmdHis");
										$InteractionCodeAuth 	= md5($InteractionCode);
										$InteractionCodeUnique  = "-@id:".$InteractionCode."-";
									
			
									// CONTENT LIST
										// Extraemos los resultados de las tareas...
										//$ParameterList = "";
										
												$ColourBase		= "#ffffff";
												$ColourOK		= "#f0f6fB";
												$ColourWarning 	= "#fff6bf";
												$ColourError   	= "#ffcfca";
			
												
													$ColourSelected = "#f0f0f0";
													if ($ParameterTaskExecuted == 1 && $ParameterTaskCompleted == 1) {
														$ColourSelected = $ColourOK; 
														$TaskExecuted = $TaskExecuted + 1;
													}
													if ($ParameterTaskExecuted == 1 && $ParameterTaskCompleted == 0) {
														$ColourSelected = $ColourWarning; 
														$TaskFailed = $TaskFailed + 1;
													}
													if ($ParameterTaskExecuted == 0) {
														$ColourSelected = $ColourError; 
														$TaskNotExecuted = $TaskNotExecuted + 1;
													}
					
			
												$ParameterList .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
					
												$ParameterList .= '<tr>';
					
												$ParameterList .= '<td style="text-align:left;background-color:'.$ColourSelected.';padding:5px;border-bottom:1px solid #ADB1BD;font-weight:bold;font-size:9px;">';
												$ParameterList .= $ParameterName;
												$ParameterList .= '</td>';
												
												$ParameterList .= '</tr>';
												
												$ParameterList .= '<tr>';
												$ParameterList .= '<td style="text-align:left;background-color:'.$ColourBase.';padding:5px;border-left:1px solid #ADB1BD;">';
												$ParameterList .= "<span style='font-size:8px;'>";
												$ParameterList .= $ParameterValue." @ ".$ParameterDate."<br />";
												$ParameterList .= $ParameterDesc."<br />";
												$ParameterList .= "</span>";
												$ParameterList .= '</td>';
					
												$ParameterList .= '</tr>';
												$ParameterList .= '</table><br />';
						
				
							} // while($my_row=$dbtransactions->get_row()){	
							

							// TASK LOG INTEGRITY
							$ParameterListLogs = '';
							$query  = "EXEC dbo.usp_app_ParametersManage
														'0', 
														'".$configuration['appkey']."', 
														'tasklogintegrity', 
														'crm';";
							$dbtransactions->query($query);
							$TaskLogCount = $dbtransactions->count_rows();
							while($my_row=$dbtransactions->get_row()){	
						
									$ParameterId 	= $my_row['ParameterId'];
									$ParameterType 	= $my_row['ParameterType'];
									$ParameterName 	= $my_row['ParameterName'];
									$ParameterValue	= $my_row['ParameterValue'];
									$ParameterDesc 	= $my_row['ParameterDescription'];
									$ParameterDate	= $my_row['ParameterLastExecution'];
									$ParameterToday	= $my_row['ParameterToday'];
									$ParameterInstance  = $my_row['ParameterInstance'];
									$ParameterLocation	= $my_row['ParameterLocation'];
									//$ParameterOwner	= $my_row['ParameterOwner'];
									$ParameterOwner	= $configuration['appkey'];
			
									$ParameterTaskDays		= $my_row['TaskDays'];
									$ParameterTaskExecuted	= $my_row['TaskExecuted'];
									$ParameterTaskCompleted	= $my_row['TaskCompleted'];
						
									$ParameterListLogs .= '&middot;&nbsp;'.$ParameterValue.'<br />';
						
							} // while($my_row=$dbtransactions->get_row()){	
							
							if ($ParameterListLogs !== '') {
							
									$ParameterList .= '<br />';
									$ParameterList .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
		
									$ParameterList .= '<tr>';
		
									$ParameterList .= '<td style="text-align:left;background-color:#ffcc00;padding:5px;border-bottom:1px solid #ADB1BD;font-weight:bold;font-size:9px;">';
									$ParameterList .= 'InstanceIntegrity';
									$ParameterList .= '</td>';
									
									$ParameterList .= '</tr>';
									
									$ParameterList .= '<tr>';
									$ParameterList .= '<td style="text-align:left;background-color:#FFFFE6;padding:5px;border-left:1px solid #ADB1BD;">';
									$ParameterList .= "<span style='font-size:9px;font-weight:bold;'>";
									$ParameterList .= $ParameterListLogs;
									$ParameterList .= "</span>";
									$ParameterList .= '</td>';
		
									$ParameterList .= '</tr>';
									$ParameterList .= '</table><br />';	
												
							} // if ($ParameterListLogs !== '')
																																					
					
				} else {
				
						// ------------------------------
						// LOCAL TASK LOG
						// ------------------------------
							$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
														'0', 
														'".$configuration['appkey']."', 
														'tasklog', 
														'crm';";
							$dbsecurity->query($query);
							while($my_row=$dbsecurity->get_row()){	
									$records = $records + 1;
									$TaskCount = $TaskCount + 1;
									
									$ParameterId 	= $my_row['ParameterId'];
									$ParameterType 	= $my_row['ParameterType'];
									$ParameterName 	= $my_row['ParameterName'];
									$ParameterValue	= $my_row['ParameterValue'];
									$ParameterDesc 	= $my_row['ParameterDescription'];
									$ParameterDate	= $my_row['ParameterLastExecution'];
									$ParameterToday	= $my_row['ParameterToday'];
									$ParameterInstance  = $my_row['ParameterInstance'];
									$ParameterLocation	= $my_row['ParameterLocation'];
									//$ParameterOwner	= $my_row['ParameterOwner'];
									$ParameterOwner	= $configuration['appkey'];
			
									$ParameterTaskDays		= $my_row['TaskDays'];
									$ParameterTaskExecuted	= $my_row['TaskExecuted'];
									$ParameterTaskCompleted	= $my_row['TaskCompleted'];
			
			
									$InteractionId = $ParameterId;
									$InteractionSentId = $ParameterId;
									
									//echo "Parameter ".$ParameterId.".";
			
							
									// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
										// Contenido
										$InteractionCode 		= "OrveeCRM.".$InteractionId.".".$InteractionSentId.".0.".date("YmdHis");
										$InteractionCodeAuth 	= md5($InteractionCode);
										$InteractionCodeUnique  = "-@id:".$InteractionCode."-";
									
			
									// CONTENT LIST
										// Extraemos los resultados de las tareas...
										//$ParameterList = "";
										
												$ColourBase		= "#ffffff";
												$ColourOK		= "#f0f6fB";
												$ColourWarning 	= "#fff6bf";
												$ColourError   	= "#ffcfca";
			
												
													$ColourSelected = "#f0f0f0";
													if ($ParameterTaskExecuted == 1 && $ParameterTaskCompleted == 1) {
														$ColourSelected = $ColourOK; 
														$TaskExecuted = $TaskExecuted + 1;
													}
													if ($ParameterTaskExecuted == 1 && $ParameterTaskCompleted == 0) {
														$ColourSelected = $ColourWarning; 
														$TaskFailed = $TaskFailed + 1;
													}
													if ($ParameterTaskExecuted == 0) {
														$ColourSelected = $ColourError; 
														$TaskNotExecuted = $TaskNotExecuted + 1;
													}
					
			
												$ParameterList .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
					
												$ParameterList .= '<tr>';
					
												$ParameterList .= '<td style="text-align:left;background-color:'.$ColourSelected.';padding:5px;border-bottom:1px solid #ADB1BD;font-weight:bold;font-size:9px;">';
												$ParameterList .= $ParameterName;
												$ParameterList .= '</td>';
												
												$ParameterList .= '</tr>';
												
												$ParameterList .= '<tr>';
												$ParameterList .= '<td style="text-align:left;background-color:'.$ColourBase.';padding:5px;border-left:1px solid #ADB1BD;">';
												$ParameterList .= "<span style='font-size:8px;'>";
												$ParameterList .= $ParameterValue." @ ".$ParameterDate."<br />";
												$ParameterList .= $ParameterDesc."<br />";
												$ParameterList .= "</span>";
												$ParameterList .= '</td>';
					
												$ParameterList .= '</tr>';
												$ParameterList .= '</table><br />';
						
				
							} // while($my_row=$dbsecurity->get_row()){	
							
							// TASK LOG INTEGRITY
							$ParameterListLogs = '';
							$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
														'0', 
														'".$configuration['appkey']."', 
														'tasklogintegrity', 
														'crm';";
							$dbsecurity->query($query);
							$TaskLogCount = $dbsecurity->count_rows();
							while($my_row=$dbsecurity->get_row()){	

									$ParameterId 	= $my_row['ParameterId'];
									$ParameterType 	= $my_row['ParameterType'];
									$ParameterName 	= $my_row['ParameterName'];
									$ParameterValue	= $my_row['ParameterValue'];
									$ParameterDesc 	= $my_row['ParameterDescription'];
									$ParameterDate	= $my_row['ParameterLastExecution'];
									$ParameterToday	= $my_row['ParameterToday'];
									$ParameterInstance  = $my_row['ParameterInstance'];
									$ParameterLocation	= $my_row['ParameterLocation'];
									//$ParameterOwner	= $my_row['ParameterOwner'];
									$ParameterOwner	= $configuration['appkey'];
			
									$ParameterTaskDays		= $my_row['TaskDays'];
									$ParameterTaskExecuted	= $my_row['TaskExecuted'];
									$ParameterTaskCompleted	= $my_row['TaskCompleted'];
					
									$ParameterListLogs .= '&middot;&nbsp;'.$ParameterValue.'<br />';
						
							} // while($my_row=$dbtransactions->get_row()){	
							
							if ($ParameterListLogs !== '') {
							
									$ParameterList .= '<br />';
									$ParameterList .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
		
									$ParameterList .= '<tr>';
		
									$ParameterList .= '<td style="text-align:left;background-color:#ffcc00;padding:5px;border-bottom:1px solid #ADB1BD;font-weight:bold;font-size:9px;">';
									$ParameterList .= 'InstanceIntegrity';
									$ParameterList .= '</td>';
									
									$ParameterList .= '</tr>';
									
									$ParameterList .= '<tr>';
									$ParameterList .= '<td style="text-align:left;background-color:#FFFFE6;padding:5px;border-left:1px solid #ADB1BD;">';
									$ParameterList .= "<span style='font-size:9px;font-weight:bold;'>";
									$ParameterList .= $ParameterListLogs;
									$ParameterList .= "</span>";
									$ParameterList .= '</td>';
		
									$ParameterList .= '</tr>';
									$ParameterList .= '</table><br />';	
												
							} // if ($ParameterListLogs !== '')
							
	
				} // if ($itemtype == 'global') {
	
	
				if ($records == 0) {
					
					$ParameterList .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:100%;">';
					$ParameterList .= '<tr><td><span style="font-style:italic;">Sin Tareas</span></td></tr>';
					$ParameterList .= '</table><br />';
				}
				
				// TASK LOG SUMMARY
					// Tareas con alguna falla
					$TaskWithErrors = $TaskCount - $TaskExecuted;
					// Checamos si hubo fallas
					if ($TaskCount !== $TaskExecuted) {
						$TaskAlert = 1;
					}

					$ParameterSummary .= '<table style="border:1px solid #ADB1BD;border-collapse:collapse;width:90%;">';
					$ParameterSummary .= '<tr>';
					$ParameterSummary .= '<td colspan="2" style="text-align:center;background-color:#f0f6fB;padding:5px;border-bottom:1px solid #ADB1BD;font-weight:bold;font-size:9px;">';
					$ParameterSummary .= 'Tareas';
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '</tr>';
					$ParameterSummary .= '<tr>';
					$ParameterSummary .= '<td colspan="2" style="text-align:center;background-color:#FFFFFF;padding:5px;border-bottom:1px solid #ADB1BD;font-size:24px;">';
					$ParameterSummary .= $TaskCount;
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '</tr>';
					$ParameterSummary .= '<tr>';
					$ParameterSummary .= '<td style="width:30%;text-align:center;background-color:#f0f6fB;padding:5px;border-bottom:1px solid #ADB1BD;">';
					$ParameterSummary .= 'OK';
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '<td style="width:70%;text-align:center;background-color:#f0f6fB;padding:5px;border-bottom:1px solid #ADB1BD;border-left:1px solid #ADB1BD;font-size:14px;">';
					$ParameterSummary .= $TaskExecuted;
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '</tr>';
					$ParameterSummary .= '<tr>';
					$ParameterSummary .= '<td style="width:30%;text-align:center;background-color:#fff6bf;padding:5px;border-bottom:1px solid #ADB1BD;">';
					$ParameterSummary .= 'WARN';
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '<td style="width:70%;text-align:center;background-color:#fff6bf;padding:5px;border-bottom:1px solid #ADB1BD;border-left:1px solid #ADB1BD;font-size:14px;">';
					$ParameterSummary .= $TaskFailed;
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '</tr>';
					$ParameterSummary .= '<tr>';
					$ParameterSummary .= '<td style="width:30%;text-align:center;background-color:#ffcfca;padding:5px;">';
					$ParameterSummary .= 'ERR';
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '<td style="width:70%;text-align:center;background-color:#ffcfca;padding:5px;border-left:1px solid #ADB1BD;font-size:14px;">';
					$ParameterSummary .= $TaskNotExecuted;
					$ParameterSummary .= '</td>';
					$ParameterSummary .= '</tr>';
					$ParameterSummary .= '</table><br />';				
				
						
						
				// EMAIL HEADERS
				$EmailMessage['Headers'] = "";
				$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
				$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
				$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
		
				// To, From & Subject del Email
				$EmailTo 		= $InteractionTo;
				$EmailCc		= "";
				$EmailBcc 		= "";
		
				// EMAIL FROM & TO
				$EmailMessage['From'] 	  = $InteractionFrom;
				$EmailMessage['FromName'] = $InteractionFromName;
				$EmailMessage['To']   	  = $EmailTo;
				$EmailMessage['ReplyTo']  = $InteractionReplyTo;
				$EmailMessage['Cc']  	  = "";
				$EmailMessage['Bcc']  	  = "";
	
				// EMAIL SUBJECT					
				$EmailMessage['Subject'] = $InteractionSubject;		
				//$EmailMessage['Subject'] = str_replace("|PARAMETEROWNER|", $ParameterOwner, $EmailMessage['Subject']);
				$EmailMessage['Subject'] = str_replace("|PARAMETERDATE|", $ParameterToday, $EmailMessage['Subject']);
				// If something failed at integrity!!!
				if ($TaskLogCount > 0) {
					$EmailMessage['Subject'] = "[INTEGRITY] ".$EmailMessage['Subject'];
				}
				// If something failed!!!
				if ($TaskAlert == 1) {
					$EmailMessage['Subject'] = "[WARNING] ".$EmailMessage['Subject'];
				}
				
				// EMAIL CONTENT					
				$EmailMessage['Content'] = $InteractionContent;
				$EmailMessage['Body'] = $InteractionContentText;
	
				$EmailMessage['Body'] = str_replace("|PARAMETERLIST|", $ParameterList, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|PARAMETERRESUME|", $ParameterSummary, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|PARAMETEROWNER|", $configuration['instancelastname'].' @ '.$ParameterInstance, $EmailMessage['Body']);
				
				$EmailMessage['Body'] = str_replace("|PARAMETERTODAY|", $ParameterToday, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|DATE|", $ParameterToday, $EmailMessage['Body']);
				
				// Tags en APP & TIME
				$EmailMessage['Body'] = str_replace("|LOCATION|", $ParameterInstance.' @ '.$ParameterLocation, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|APP|", $script, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|TIME|", date('d/m/Y')." ".date('H:i:s'), $EmailMessage['Body']);
				
	
				// If something failed!!!
				$TaskMessage = "";
				if ($TaskAlert == 1) {
					$TaskMessage .= '<span style="color:#ff0000;font-weight:bold;font-size:12px;">';
					$TaskMessage .= '&middot; Errores en '.$TaskWithErrors.' tareas!<br />';
					$TaskMessage .= '</span><br />';
				} 
				$EmailMessage['Body'] = str_replace("|PARAMETERMESSAGE|", $TaskMessage, $EmailMessage['Body']);
			
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
										WHERE     (ParameterType = 'Task') AND (ParameterName = 'ParametersTaskLog');";
							$dbsecurity->query($query);
						// ------------------------------
						// PARAMETERS LOG:end
						// ------------------------------
						
						
		include_once('../includes/databaseconnectionrelease.php');	
	

	// FINAL OUTPUT
	echo "<br />";
	echo "ALL Notifications Sent!<br />";
	echo date('Ymd H:i:s');

	
	
// -------------------------------------------------	
// FUNCTIONS
// -------------------------------------------------

?>