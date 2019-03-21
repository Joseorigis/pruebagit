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
		$dbrecordlog = new database($configuration['db2type'],
							$configuration['db2host'], 
							$configuration['db2name'],
							$configuration['db2username'],
							$configuration['db2password']);


	// --------------------------------------------------
	// SCRIPT PARAMS
	// --------------------------------------------------
	
		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'now';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyText($_GET['t']);
			if ($itemtype == '') { $itemtype = 'now'; }
		}
		$itemtype = strtoupper($itemtype);

		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
				
	
		// INIT OUTPUT
		echo $itemtype." InteractionsTransactions Messages<br />";
		echo "<br />";
		
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// DELETE PREVIOUS
								$query  = "DELETE FROM ".$configuration['instanceprefix']."dbo.AppParameters  
											WHERE (ParameterType = 'Task')
											AND (ParameterName = 'InteractionsTransactionsMessagesSend');";
								$dbconnection->query($query);
								// INSERT NEW EXECUTION
								$query  = "INSERT INTO ".$configuration['instanceprefix']."dbo.AppParameters
											(ParameterType, ParameterName, ParameterValue, ParameterDescription, ParameterLastDate)
											VALUES     
											('Task', 'InteractionsTransactionsMessagesSend', 'Running...', '".$scriptactual."@".$configuration['appkey']."', GETDATE());";
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
		//$InteractionService = 'http://api.levelsms.com:8585/Api/rec.php?APIUser=0r1g15&APIPassword=Or1g1S&txtSMS=|CONTENT|&numSMS=52|TO|&extra=origis';
		$InteractionService = 'https://orbis.orveecrm.com/';


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
			$InteractionListContent	= '';  
				$InteractionListSQL	= ''; 
			
			$InteractionFrom  		= $configuration['adminemail'];
			$InteractionFromName    = 'Orvee Messages';
			$InteractionReplyTo  	= $configuration['adminreplyto'];
			$InteractionSubject 	= '';
			$InteractionContent 	= ''; 
				$InteractionContentText	= '';
				
			$InteractionCode		= '';
			$InteractionCodeAuth	= '';

			$InteractionKey			= '';
			$ApplicationKey			= '';
			$ApplicationPath		= '';

			$Reference01			= '';
			$Reference02			= '';
			$Reference03			= '';
			$Reference04			= '';

				

	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
		
			// WARNING LIST		
				$records = 0;
				$InteractionId = 0;
				
				$query  = "EXEC dbo.usp_app_InteractionsTransactionsMessages
									'0', 
									'".$configuration['appkey']."', 
									'list';";
				$dbtransactions->query($query);
				while($my_row=$dbtransactions->get_row()){	
						$records = $records + 1;
						
						$ApplicationKey 	= $my_row['ApplicationKey'];
						$InteractionKey 	= $my_row['InteractionKey'];
						$InteractionId 		= $my_row['InteractionId'];
						$InteractionType 	= $my_row['InteractionType'];
						$CardNumber 		= $my_row['CardNumber'];
						$RecordId			= $my_row['RecordId'];
						$InteractionContent	= $my_row['InteractionContent'];
						$InteractionDateReward	= $my_row['InteractionRewardDate'];
						$InteractionSentId = $InteractionId;
						$AffiliationId = $CardNumber;
					
						$Reference01 		= $my_row['InteractionReference08'];
						$Reference02 		= $my_row['InteractionReference05'];
						$Reference03 		= $my_row['InteractionReference09'];
						$Reference04 		= $my_row['InteractionReference04'];

						
						echo "Interaction ".$InteractionId.".";
					
						// APPLICATIONKEY
							$ApplicationPath = '';
							$queryapp  = " EXEC dbo.usp_app_UtilityCategoryElements 'UserSwitchJumpTo','".$ApplicationKey."';";
							$dbsecurity->query($queryapp);
							while($my_rowapp=$dbsecurity->get_row()){ 
								$ApplicationPath =  $my_rowapp['ItemPath'];
							}
							$InteractionService = $ApplicationPath;
					

				
//						// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
//							// Contenido
//							$InteractionCode 		= "OrveeCRM.".$InteractionId.".".$InteractionSentId.".".$AffiliationId.".".date("YmdHis");
//							$InteractionCodeAuth 	= md5($InteractionCode);
//							$InteractionCodeUnique  = "-@id:".$InteractionCode."-";
//						
//
//						// CONTENT LIST
//							// Extraemos los resultados de las alarmas
//							$InteractionServiceCustome = $InteractionService;
//		
//							$InteractionServiceCustome = str_replace("|CARDNUMBER|", $CardNumber, $InteractionServiceCustome);
//							$InteractionServiceCustome = str_replace("|TO|", $AffiliationCellPhone, $InteractionServiceCustome);
//							$InteractionServiceCustome = str_replace("|CONTENT|", urlencode($InteractionContent), $InteractionServiceCustome);
//							$InteractionServiceCustome = str_replace("|DATE|", urlencode($InteractionDateReward), $InteractionServiceCustome);
					
				
						// --------------------------------------------------
						// INTERACTION SEND!!!
						// --------------------------------------------------

//								// Interpretar respuesta para el OK		
//								// Reintentos?
								$InteractionMessageSent = 0;	
								$InteractionMessageSentLog = "";
//
//								//$InteractionMessageSentLog = implode('', file($InteractionServiceCustome));
//								$InteractionMessageSentLog = strtolower($InteractionMessageSentLog);
//								if (strpos($InteractionMessageSentLog, "true") !== false) {
//									$InteractionMessageSent = 1;
//								}
//								if (strpos($InteractionMessageSentLog, "error: 0") !== false) {
//									$InteractionMessageSent = 1;
//								}													
//								if (strpos($InteractionMessageSentLog, "error:0") !== false) {
//									$InteractionMessageSent = 1;
//								}													
//								if (strpos($InteractionMessageSentLog, "ok") !== false) {
//									$InteractionMessageSent = 1;
//								}	
					
								$InteractionServiceCustome  = $InteractionService;
								$InteractionServiceCustome .= 'includes/InteractionsTransactionsSend.php?';
								$InteractionServiceCustome .= 'n='.$InteractionId.'&';
								$InteractionServiceCustome .= 't='.$InteractionType.'&';
								$InteractionServiceCustome .= 'cardnumber='.$CardNumber.'&';
								$InteractionServiceCustome .= 'content='.urlencode($InteractionContent).'&';		
								$InteractionServiceCustome .= 'r01='.$Reference01.'&';
								$InteractionServiceCustome .= 'r02='.$Reference02.'&';
								$InteractionServiceCustome .= 'r03='.$Reference03.'&';
								$InteractionServiceCustome .= 'r04='.$Reference04.'';
								$InteractionMessageSentLog = implode('', file($InteractionServiceCustome));
								
								echo $InteractionServiceCustome."<br />";
								echo $InteractionMessageSentLog."<br />";
								echo "<br />";
					
								$querylog  = "EXEC dbo.usp_app_InteractionsTransactionsMessages
													'0', 
													'".$configuration['appkey']."', 
													'update', 
													'".$RecordId."', 
													'".$CardNumber."', 
													'".$InteractionId."', 
													'".$InteractionKey."', 
													'".$InteractionMessageSent."', 
													'".$InteractionMessageSentLog."';";
								$dbrecordlog->query($querylog);		//echo $querylog;						
	
							//echo $InteractionServiceCustome."<br />";
							echo "... Sent!<br />";
	
	
				} // while($my_row=$dbtransactions->get_row()){	
	
	
	
							// ------------------------------
							// PARAMETERS LOG:begin
							// ------------------------------
								// UPDATE CURRENT EXECUTION
								$query  = "UPDATE    ".$configuration['instanceprefix']."dbo.AppParameters
											SET              ParameterValue = 'FINISHED', ParameterLastDate = GETDATE(),
																ParameterDescription = '".$scriptactual."@".$configuration['appkey']."'
											WHERE     (ParameterType = 'Task') AND (ParameterName = 'InteractionsTransactionsMessagesSend');";
								$dbconnection->query($query);
							// ------------------------------
							// PARAMETERS LOG:end
							// ------------------------------
						
						
		include_once('../includes/databaseconnectionrelease.php');	
		$dbrecordlog->disconnect();
	

	// FINAL OUTPUT
	echo "<br />";
	echo "ALL Messages Sent!<br />";
	echo date('Ymd H:i:s');



?>