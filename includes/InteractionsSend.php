<?php
/**
*
* InteractionsSend.php
*
* Envia una interacción a un afiliado.
*	OPERACION: funciona vía REQUIRE o INCLUDE.
*	PARAMETROS: se manda interactionid & affiliationid & $interactiontype
*	+ Modificaciones 20170915. raulbg. Implementación Inicial.
*
* @version 		20180116.orvee
* @category 	interactions
* @package 		orvee
* @author 		raulbg <raulbg@origis.com>
* @deprecated 	none
*
*/

// ----------------------------------------------------------------------------------------------------
// INTERACTION MESSAGE AFFILIATED
// ----------------------------------------------------------------------------------------------------

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
	
		// Connecting to database ALTERNATE
		$dbconnectionalternate = new database($configuration['db1type'],
							$configuration['db1host'], 
							$configuration['db1name'],
							$configuration['db1username'],
							$configuration['db1password']);		

	
	// --------------------------------------------------
	// SCRIPT PARAMS
	// --------------------------------------------------

		// Estas 3 variables deben llegar desde afuera del script....	
		if (!isset($affiliationid)) 	{ $affiliationid = '0'; }
		if (!isset($interactionid)) 	{ $interactionid = '0'; }
		if (!isset($interactiontype)) 	{ $interactiontype = 'NA'; }

//		$affiliationid   = '0'; // EDIT!!!! InteractionId
//		$interactionid   = '38'; // EDIT!!!! InteractionId
//		$interactiontype = 'SMS'; // InteractionType
		$interactionsentid = '0'; // InteractionSentId
		
		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));


		// DEFINIR COMO DAEMON
		// MONITOR
		// RECURSIVIDAD
		// SENT - ID



	// --------------------------------------------------
	// INTERACTION PARAMS
	// --------------------------------------------------

		// Get Local SMTP Host
		$InteractionSMTPHost = ini_get('SMTP');

		$InteractionResult  = '';
		$InteractionSent 	= 99;
		$InteractionService	= '';
		$InteractionServiceCustome = '';
		
				// INTERACTION SERVICE
					// SMS
					if ($interactiontype == 'SMS') {
							$items = 0;
							$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
												'0', 
												'".$configuration['appkey']."', 
												'view', 
												'crm', 
												'0', 
												'Interactions', 
												'SMSService';";
							$dbconnection->query($query);
							$items = $dbconnection->count_rows();
							if ($items > 0) {
									$my_row=$dbconnection->get_row();
									$InteractionService = trim($my_row['ParameterValue']);
								
									if ($InteractionService == 'NA' || $InteractionService == '') {
										$InteractionResult 	= 'INTERACTIONSERVICEINVALID';
										$InteractionSent 	= 0;										
									}
								
							} else {
									$InteractionResult 	= 'INTERACTIONSERVICENOTFOUND';
									$InteractionSent 	= 0;
							}
					}
					// EMAIL
					if ($interactiontype == 'EMAIL') {
							$InteractionService = $InteractionSMTPHost;
					}
			
					// NONE
					if ($InteractionService == '') {
							$InteractionResult 	= 'INTERACTIONSERVICENOTFOUND';
							$InteractionSent 	= 0;
					}



	// --------------------------------------------------
	// INTERACTION CONTENT
	// --------------------------------------------------
			// Extraemos los datos de la campaña
			$InteractionId  		= $interactionid;
			$InteractionSentId 		= $interactionsentid;
			$InteractionType  		= $interactiontype;
			$InteractionName  		= '';
			$InteractionStatusId  	= 0;
			$InteractionMonitor 	= ''; 
			$InteractionListId		= 0; 
			$InteractionListContent	= ''; 
				$InteractionListSQL		= ''; 
			
			$InteractionFrom  		= '';
			$InteractionFromName    = '';
			$InteractionReplyTo  	= '';
			$InteractionSubject 	= ''; 
			$InteractionContent 	= ''; 

			$AffiliatedId	 = '0';
			
			$items = 0;
			$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_Interactions".$interactiontype."Manage
							'0', 
							'".$configuration['appkey']."', 
							'view', 
							'".$interactiontype."', 
							'".$interactionid."';";
			$dbconnection->query($query);
			$items = $dbconnection->count_rows();
			if ($items > 0) {
					$my_row=$dbconnection->get_row();
					
					$InteractionId  		= $my_row['InteractionId'];
					$InteractionSentId 		= $my_row['InteractionSentId'];
					$InteractionType  		= $my_row['InteractionType'];
					$InteractionName  		= $my_row['InteractionName'];
					$InteractionStatusId  	= $my_row['InteractionStatusId'];
					$InteractionMonitor 	= $my_row['InteractionMonitor']; 
					$InteractionListId		= $my_row['InteractionListId']; 
					$InteractionListContent	= $my_row['InteractionListContent']; 
						$InteractionListSQL		= urldecode($InteractionListContent); 
					
					$InteractionFrom  		= $my_row['InteractionFrom'];
					$InteractionFromName    = $my_row['InteractionFromName'];
					$InteractionReplyTo  	= $my_row['InteractionReplyTo'];
					$InteractionSubject 	= $my_row['InteractionSubject']; 
					$InteractionContent 	= $my_row['InteractionContent']; 
		
					if ($InteractionStatusId !== 2 && $InteractionStatusId !== 3) {
						$InteractionResult 	= 'INTERACTIONINACTIVE';
						$InteractionSent 	= 0;
					}
	
			} else {
				
					$InteractionResult 	= 'INTERACTIONNOTFOUND';
					$InteractionSent 	= 0;
			}
		


	// --------------------------------------------------
	// INTERACTION CONTENT CUSTOMIZATION
	// --------------------------------------------------
		
		$InteractionCode		= '';
		$InteractionCodeAuth	= '';

		
		// IF NO ERROR SO FAR...
		if ($InteractionSent !== 0) {			
		
		
						$InteractionElementsCount = 0;
						
				// --------------------------------------------------
				// AFFILIATED PARAMS
				// --------------------------------------------------
	
					// AFFILIATED PARAMS INIT		
						$AffiliatedId 		= '0';
						$AffiliatedNumber 	= '';
						$AffiliatedPassword = '';
						
						$AffiliatedName 	= '';
						$AffiliatedLastName = '';
						$AffiliatedFullName = '';
						
						$AffiliatedPermission 	= 1;
						$AffiliatedEmail		= '';
						$AffiliatedEmailBlocked = 0;
						$AffiliatedPhone 		= '';
						$AffiliatedCellphone 	= '';
						$AffiliatedContactElement = '';

//						$AffiliatedId = '5489';
//						$AffiliatedNumber = '4600110059996';
//						$AffiliatedPassword = '';
//						
//						$AffiliatedName = 'Raul';
//						$AffiliatedLastName = 'Raul';
//						$AffiliatedFullName = 'Raul';
//						
//						$AffiliatedPermission = 1;
//						$AffiliatedEmail = 'raulbg@origis.com';
//						$AffiliatedEmailBlocked = 0;
//						$AffiliatedPhone = '5554055044';
//						$AffiliatedCellphone = '5554055044';
//						$AffiliatedContactElement = '';
			
					// AFFILIATED DATA
						// If data set...
						if (isset($affiliationid)) 	{ $AffiliatedId = $affiliationid; }
						if (isset($password)) 		{ $AffiliatedPassword = $password; }
						if (isset($cardnumber)) 	{ $AffiliatedNumber = $cardnumber; }

	
					// AFFILIATED DATA LIST
						$items = 0;
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_AffiliationListsItemsList
										'0','".$configuration['appkey']."',
										'".$InteractionListId."', '".$AffiliatedId."', '".$AffiliatedNumber."',
										'list', '0', '0', '', '';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						while ($affiliatedrow=$dbconnection->get_row()) {
							
								$InteractionElementsCount = $InteractionElementsCount + 1;
								
								$AffiliatedId 		= $affiliatedrow['AffiliationId'];
								$AffiliatedNumber	= $affiliatedrow['CardNumber'];
								$AffiliatedPassword	= '';
								
								$AffiliatedName 	= $affiliatedrow['CardName'];
								$AffiliatedLastName = $affiliatedrow['CardLastName'];
								$AffiliatedFullName = $affiliatedrow['CardFullName'];
								
								$AffiliatedPermission 	= $affiliatedrow['PermissionMarketing'];
								$AffiliatedEmail		= $affiliatedrow['CardEmail'];
								$AffiliatedEmailBlocked = $affiliatedrow['EmailBlocked'];
								$AffiliatedPhone 		= $affiliatedrow['CardPhone'];
								$AffiliatedCellphone 	= $affiliatedrow['CardCellPhone'];
								$AffiliatedContactElement = '';
									
								if ($interactiontype == 'EMAIL') {
									$AffiliatedContactElement = trim($AffiliatedEmail);
									//$AffiliatedContactElement = "raulbg@origis.com";
								}
								if ($interactiontype == 'SMS') {
									$AffiliatedContactElement = setOnlyNumbers($AffiliatedCellphone);
									//$AffiliatedContactElement = "5554055044";
								}
								
								// If No Data Error
								if ($AffiliatedContactElement == '') {
										$InteractionResult	= 'AFFILIATEDDATANOTFOUND';
										$InteractionSent 	= 0;
								}
								
		
						// --------------------------------------------------
						// INTERACTION CONTENT CUSTOMIZATION
						// --------------------------------------------------
		
									// INTERACTION CODES
										$InteractionCode		= '';
										$InteractionCodeAuth	= '';
								
						
									// IF NO ERROR SO FAR... AGAIN
									if ($InteractionSent !== 0) {
										
												$InteractionSent		= 99;
									
											// INTERACTION CODE AUTH
												$InteractionCode 		= $configuration['appkey'].".".$InteractionId.".".$InteractionSentId.".".$AffiliatedId.".".date("YmdHis");
												$InteractionCodeAuth 	= md5($InteractionCode);
												$InteractionCodeUnique  = "-@id:".$InteractionCode."-";
										
											// INTERACTION HEADERS
												$InteractionMessage['Headers'] = "";
												$InteractionMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$configuration['appkey']."\r\n";
												$InteractionMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
												$InteractionMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";
										
											// INTERACTION FROM & TO
												$InteractionMessage['From'] 	  = $InteractionFrom;
												$InteractionMessage['FromName']   = $InteractionFromName;
												$InteractionMessage['To']   	  = $AffiliatedContactElement;
												$InteractionMessage['ReplyTo']    = $InteractionReplyTo;
												$InteractionMessage['Cc']  	 	  = "";
												$InteractionMessage['Bcc']  	  = "";
								
											// INTERACTION CONTENT					
												$InteractionMessage['Subject'] = $InteractionSubject;		
												$InteractionMessage['Content'] = $InteractionContent;
													if ($interactiontype == 'EMAIL') {
														$InteractionMessage['Body'] = implode('', file($InteractionMessage['Content']));
													}
													if ($interactiontype == 'SMS') {
														$InteractionMessage['Body'] = $InteractionMessage['Content'];
													}
				
		
											// INTERACTION AFFILIATED CUSTOMIZATION					
													// Get Affiliated Adhoc Content
													$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_InteractionsTrackParamsManage
																	'0', 
																	'".$configuration['appkey']."', 
																	'params', 
																	'".$interactiontype."', 
																	'sent', 
																	'".$InteractionId."', 
																	'".$InteractionSentId."', 
																	'".$AffiliatedId."', 
																	'".$AffiliatedContactElement."', 
																	'".date('Ymd')."', 
																	'".$InteractionService."', 
																	'".$InteractionResult."', 
																	'".$InteractionCode."', 
																	'".$InteractionCodeAuth."', 
																	'".$InteractionSent."', 
																	'".$InteractionFrom."';";
													$dbconnectionalternate->query($query);
													while ($paramsdatarow=$dbconnectionalternate->get_row()) {
														
															$ParameterType 	= '';
															$ParameterName 	= '';
															$ParameterValue = '';
				
															$ParameterType 	= $paramsdatarow['ParameterType'];
															$ParameterName 	= $paramsdatarow['ParameterName'];
															$ParameterValue = $paramsdatarow['ParameterValue'];
															
															// SUBJECT
															if ($ParameterType == 'SUBJECT') {
																$InteractionMessage['Subject'] = str_replace($ParameterName, $ParameterValue, $InteractionMessage['Subject']);
															}
														
															// CONTENT or BODY
															if ($ParameterType == 'CONTENT') {
																$InteractionMessage['Body'] = str_replace($ParameterName, $ParameterValue, $InteractionMessage['Body']);
															}												
															if ($ParameterType == 'BODY') {
																$InteractionMessage['Body'] = str_replace($ParameterName, $ParameterValue, $InteractionMessage['Body']);
															}												
		
													} // while ($paramsdatarow=$dbconnectionalternate->get_row()) {
																								
				
											// INTERACTION CUSTOMIZATION					
												// SUBJECT
												$InteractionMessage['Subject'] = str_replace("|NOMBRE|", '', $InteractionMessage['Subject']);
												$InteractionMessage['Subject'] = str_replace("|NAME|", '', $InteractionMessage['Subject']);
												
												// BODY: GENERAL Tags
												$InteractionMessage['Body'] = str_replace("|EMAILID|", $InteractionCodeUnique, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|SECCIONID|", "0", $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|SID|", "0", $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|USERID|", $AffiliatedId, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|AID|", $AffiliatedId, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|ENVIOID|", $InteractionSentId, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|EID|", $InteractionSentId, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|CAMPANIAID|", $InteractionId, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|CID|", $InteractionId, $InteractionMessage['Body']);
												// BODY: AFFILIATED Tags
												$InteractionMessage['Body'] = str_replace("|TARJETA|", $AffiliatedNumber, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|CARDNUMBER|", $AffiliatedNumber, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PASSWORD|", $AffiliatedPassword, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|NOMBRE|", $AffiliatedName, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|NAME|", $AffiliatedName, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|USERNAME|", $AffiliatedEmail, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|EMAIL|", $AffiliatedEmail, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|CELLPHONE|", $AffiliatedCellphone, $InteractionMessage['Body']);
												// BODY: APP & TIME Tags
												$InteractionMessage['Body'] = str_replace("|APP|", $configuration['appkey'], $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|TIME|", date('d/m/Y')." ".date('H:i:s'), $InteractionMessage['Body']);
								
					
						// --------------------------------------------------
						// INTERACTION SEND!!!
						// --------------------------------------------------
							
											// Interpretar respuesta para el OK		
											// Reintentos?
											$InteractionMessageSent = 0;	
											$InteractionMessageSentLog = "";
											$InteractionServiceCustome = $InteractionService;
											$InteractionContentCustome = '';
											
												if ($interactiontype == 'SMS') {
													$InteractionServiceCustome = str_replace("|CID|", $InteractionId, $InteractionServiceCustome);
													$InteractionServiceCustome = str_replace("|FROM|", $InteractionFrom, $InteractionServiceCustome);
													$InteractionServiceCustome = str_replace("|TO|", $AffiliatedContactElement, $InteractionServiceCustome);
													$InteractionServiceCustome = str_replace("|MESSAGE|", urlencode($InteractionMessage['Body']), $InteractionServiceCustome);
													$InteractionContentCustome = $InteractionMessage['Body'];
												}
											
								
											// Check If Interaction Is Permitted...
											if ($AffiliatedPermission == 1) {
								
													// Interaction Content Send
													if ($interactiontype == 'SMS') {
														$InteractionMessageSentLog = implode('', file($InteractionServiceCustome));
														$InteractionMessageSentLog = strtolower($InteractionMessageSentLog);
														if (strpos($InteractionMessageSentLog, "true") !== false) {
															$InteractionMessageSent = 1;
														}
														if (strpos($InteractionMessageSentLog, "status code: 0") !== false) {
															$InteractionMessageSent = 1;
														}												
														if (strpos($InteractionMessageSentLog, "ok") !== false) {
															$InteractionMessageSent = 1;
														}													
													}
													if ($interactiontype == 'EMAIL') {
														$InteractionMessageSent = sendEmailMessage($InteractionMessage);
													}
															
													// Interaction Result Process...				
													if ($InteractionMessageSent == 1) {
														$InteractionResult = 'OK;';
														$InteractionResult = substr($InteractionMessageSentLog,0,100);
														$InteractionSent = 1;
													} else {
														$InteractionResult = 'ScriptError;';
														$InteractionResult = substr($InteractionMessageSentLog,0,100);
														$InteractionSent = 0;
													}
								
											} else { // No Permission...
											
												   $InteractionResult = 'InteractionWithoutPermission;';
												   $InteractionSent = 0;
											}				
								
										// INTERACTION RESULT MANAGE		
											// Interaction Track Manage
											$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_InteractionsTrackManage
															'0', 
															'".$configuration['appkey']."', 
															'add', 
															'".$interactiontype."', 
															'sent', 
															'".$InteractionId."', 
															'".$InteractionSentId."', 
															'".$AffiliatedId."', 
															'".$AffiliatedContactElement."', 
															'".date('Ymd')."', 
															'".$InteractionServiceCustome."', 
															'".$InteractionResult."', 
															'".$InteractionCode."', 
															'".$InteractionCodeAuth."', 
															'".$InteractionSent."', 
															'".$InteractionFrom."', 
															'".$InteractionContentCustome."';";
											$dbconnectionalternate->query($query);
								
								
								
							} // if ($InteractionSent !== 0) {	 ... AGAIN
							
						} // while ($affiliatedrow=$dbconnection->get_row()) {
														
		} // if ($InteractionSent !== 0) {	


	$dbconnectionalternate->disconnect();	
	//echo $InteractionServiceCustome."<br />";
	//echo $InteractionResult."<br />";
	//echo $InteractionSent."<br />";
	//echo "FIN";
				
?>