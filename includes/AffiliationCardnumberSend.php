<?php

// ----------------------------------------------------------------------------------------------------
// AFFILIATION CARDNUMBER SEND
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
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();
	
//	// REQUEST SOURCE VALIDATION
//		$requestsource = getRequestSource();
//		if ($requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}	
		
	

			// TRANSACTIONS DATABASE
				include_once('../includes/databaseconnectiontransactions.php');


		$RegistrantUserId = 0;
		if (isset($_SESSION[$configuration['appkey']]['userid'])) { $RegistrantUserId = $_SESSION[$configuration['appkey']]['userid']; }

	
	// --------------------------------------------------
	// SCRIPT PARAMS
	// --------------------------------------------------

	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
//		if (isset($affiliationid)) {
//			$affiliationid = setOnlyNumbers($affiliationid);
//			if ($affiliationid == '') { $affiliationid = 0; }
//			if (!is_numeric($affiliationid)) { $affiliationid = 0; }
//		}	
		
		$affiliationid = 0;
		if (isset($_GET['n'])) {
			$affiliationid = setOnlyNumbers($_GET['n']);
			if ($affiliationid == '') { $affiliationid = 0; }
			if (!is_numeric($affiliationid)) { $affiliationid = 0; }
		}
		$cardnumber = '0';
		if (isset($_GET['cardnumber'])) {
			$cardnumber = setOnlyNumbers($_GET['cardnumber']);
			if ($cardnumber == '') { $cardnumber = '0'; }
			if (!is_numeric($cardnumber)) { $cardnumber = '0'; }
		}
		// Obtenemos el itemtype, el tipo de elemento a consultaar
		$itemtype = 'EMAIL';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'EMAIL'; }
		}
		$itemtype = strtoupper($itemtype);
				
		$itemid   	= '0'; // EDIT!!!! InteractionId
		$itemsentid = '0'; // InteractionSentId
		
		
		// Application Current Path 
		$AppCurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));


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
					if ($itemtype == 'SMS') {
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
							} else {
									$InteractionResult 	= 'INTERACTIONSERVICENOTFOUND';
									$InteractionSent 	= 0;
							}
					}
					// EMAIL
					if ($itemtype == 'EMAIL') {
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
			$InteractionId  		= $itemid;
			$InteractionSentId 		= '0';
			$InteractionType  		= $itemtype;
			$InteractionName  		= '';
			$InteractionStatusId  	= 0;
			$InteractionMonitor 	= ''; 
			$InteractionListId		= 0; 
			$InteractionListContent	= ''; 
				$InteractionListSQL		= ''; 
			
			$InteractionFrom  		= 'noreply@orveecrm.com';
			$InteractionFromName    = 'OrveeCRM';
			$InteractionReplyTo  	= '';
			$InteractionSubject 	= 'Tu Tarjeta'; 
			$InteractionContent 	= '../templates/AffiliationCardnumberTemplate.html'; 
			
			if ($itemtype == 'SMS') {
				$InteractionContent 	= 'Tu tarjeta |PROGRAMNAME| es |CARDNUMBER|'; 
			}

					// Interaction Sender & ReplyTo
						$items = 0;
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
											'".$RegistrantUserId."', 
											'".$script."', 
											'view', 
											'crm', 
											'0', 
											'Interactions', 
											'EmailFrom';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						if ($items > 0) {
							$my_row=$dbconnection->get_row();
							$InteractionFrom 	= trim($my_row['ParameterValue']);
							if ($InteractionFrom == '') { $InteractionFrom = 'noreply@orveecrm.com'; }
						}
	
						$items = 0;
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
											'".$RegistrantUserId."', 
											'".$script."', 
											'view', 
											'crm', 
											'0', 
											'Interactions', 
											'EmailFromName';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						if ($items > 0) {
							$my_row=$dbconnection->get_row();
							$InteractionFromName 	= trim($my_row['ParameterValue']);
							if ($InteractionFromName == '') { $InteractionFromName = 'OrveeCRM'; }
						}
	
						$items = 0;
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
											'".$RegistrantUserId."', 
											'".$script."', 
											'view', 
											'crm', 
											'0', 
											'Interactions', 
											'EmailReplyTo';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						if ($items > 0) {
							$my_row=$dbconnection->get_row();
							$InteractionReplyTo 	= trim($my_row['ParameterValue']);
							if ($InteractionReplyTo == '') { $InteractionReplyTo = ''; }
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
				// CARDNUMBER PARAMS
				// --------------------------------------------------
	
					// CARDNUMBER PARAMS INIT		
						//$AffiliatedId 			 	= '218';
						$AffiliatedId				= $affiliationid;
						$AffiliatedContactElement 	= '';

						$CardNumber 		= $cardnumber;
						$CardNumberName		= '';
						$CardNumberExpiry	= '';
						
						$CardProgramOwner 	= 'Origis';
						$CardProgramName 	= 'Orbis';
						$CardProgramColor 	= '0072C6';
						$CardProgramImage 	= 'https://storage.orveecrm.com/app/images/origis/programlogo.png';

						$CardProgramLinkPhone		= '01 800 0870 788';
						$CardProgramLinkWebsite 	= 'http://www.orbisfarma.com.mx/';
						$CardProgramLinkPrivacy 	= 'https://afiliacion.orbisfarma.com.mx/avisoprivacidad.php';
						$CardProgramLinkContact 	= 'http://www.orbisfarma.com.mx/';
						$CardProgramLinkBalance 	= 'https://historial.orbisfarma.com.mx/';
						$CardProgramLinkStores	 	= 'http://www.orbisfarma.com.mx/';


					// CARD OWNER DATA
						$query = " EXEC ".$configuration['instanceprefix']."dbo.usp_app_AffiliationItemManage
												'cardowner', 
												'crm',
												'".$RegistrantUserId."',
												'".$script."',
												'0',
												'".$CardNumber."';";	
						$dbtransactions->query($query);
						$items = $dbtransactions->count_rows();
						if ($items > 0) {
								$my_row=$dbtransactions->get_row();
							
								$CardNumberName		= $my_row['CardNameSearch'];
								$CardNumberExpiry	= $my_row['CardExpiration'];
								
								$CardProgramOwner 	= $my_row['ItemOwner'];
								$CardProgramName 	= $my_row['ItemOwnerProgram'];
								$CardProgramColor 	= $my_row['ItemOwnerProgramColor'];
								$CardProgramImage 	= $my_row['ItemOwnerProgramImage'];
		
								$CardProgramLinkPhone		= $my_row['ItemOwnerProgramPhone'];
								$CardProgramLinkWebsite 	= $my_row['ItemOwnerProgramWebsite'];
								$CardProgramLinkPrivacy 	= $my_row['ItemOwnerProgramPrivacy'];
								$CardProgramLinkContact 	= $my_row['ItemOwnerProgramContact'];
								$CardProgramLinkBalance 	= $my_row['ItemOwnerProgramBalance'];
								$CardProgramLinkStores	 	= $my_row['ItemOwnerProgramStores'];
						}


						if ($CardProgramLinkPrivacy !== '') {
							$CardProgramLinkPrivacy 	= '<a href="'.$CardProgramLinkPrivacy.'" target="_blank">Aviso Privacidad</a>&nbsp;|&nbsp;';
						}
						if ($CardProgramLinkContact !== '') {
							$CardProgramLinkContact 	= '<a href="'.$CardProgramLinkContact.'" target="_blank">Contacto</a>&nbsp;|&nbsp;';
						}
						if ($CardProgramLinkBalance !== '') {
							$CardProgramLinkBalance 	= '<a href="'.$CardProgramLinkBalance.'" target="_blank">Estado Cuenta</a>&nbsp;|&nbsp;';
						}
						if ($CardProgramLinkStores !== '') {
							$CardProgramLinkStores 	= '<a href="'.$CardProgramLinkStores.'" target="_blank">Localiza Sucursal</a>&nbsp;|&nbsp;';
						}
	
					// AFFILIATED DATA LIST
						$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_AffiliationItem
													'".$AffiliatedId."',
													'".$CardNumber."';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						if ($items > 0) {
								$my_row=$dbconnection->get_row();
								
								$InteractionElementsCount = $InteractionElementsCount + 1;
								
								$AffiliatedId 		= $my_row['CardAffiliationId'];
								$CardNumber			= $my_row['CardNumber'];
								$CardNumberName		= $my_row['CardName'];
								$CardNumberExpiry	= $my_row['CardNumberExpiry'];
								
								$AffiliatedEmail		= $my_row['CardEmail'];
								$AffiliatedCellphone 	= $my_row['CardCellularPhone'];
								$AffiliatedContactElement = '';
									
								if ($itemtype == 'EMAIL') {
									$AffiliatedContactElement = trim($AffiliatedEmail);
								}
								if ($itemtype == 'SMS') {
									$AffiliatedContactElement = setOnlyNumbers($AffiliatedCellphone);
								}
								
//$AffiliatedContactElement = 'raulbg@origis.com';
								
								// If No Data Error
								if ($AffiliatedContactElement == '') {
										$InteractionResult	= 'AFFILIATEDDATAEMPTY';
										$InteractionSent 	= 0;
								}
						} else {
								$InteractionResult	= 'AFFILIATEDDATANOTFOUND';
								$InteractionSent 	= 0;
						}
						
						// CARD AUTH
						$CardNumberAuth = md5($CardNumber);

		
						// --------------------------------------------------
						// INTERACTION CONTENT CUSTOMIZATION
						// --------------------------------------------------
		
									// INTERACTION CODES
										$InteractionCode		= '';
										$InteractionCodeAuth	= '';
								
						
									// IF NO ERROR SO FAR... AGAIN
									if ($InteractionSent !== 0) {
									
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
												//$InteractionMessage['FromName']   = $InteractionFromName;
												$InteractionMessage['FromName']   = $CardProgramOwner.' '.$CardProgramName;
												$InteractionMessage['To']   	  = $AffiliatedContactElement;
												$InteractionMessage['ReplyTo']    = $InteractionReplyTo;
												$InteractionMessage['Cc']  	 	  = "";
												$InteractionMessage['Bcc']  	  = "";
								
											// INTERACTION CONTENT					
												$InteractionMessage['Subject'] = $InteractionSubject;		
												$InteractionMessage['Content'] = $InteractionContent;
													if ($itemtype == 'EMAIL') {
														$InteractionMessage['Body'] = implode('', file($InteractionMessage['Content']));
													}
													if ($itemtype == 'SMS') {
														$InteractionMessage['Body'] = $InteractionMessage['Content'];
													}
				

											// INTERACTION CUSTOMIZATION					
												// SUBJECT
												$InteractionMessage['Subject'] = str_replace("|CARDNUMBER|", $CardNumber, $InteractionMessage['Subject']);
												
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
												// BODY: CARDNUMBER Tags
												$InteractionMessage['Body'] = str_replace("|CARDNUMBER|", $CardNumber, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|CARDNUMBERNAME|", $CardNumberName, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|CARDNUMBEREXPIRY|", $CardNumberExpiry, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|CARDNUMBERAUTH|", $CardNumberAuth, $InteractionMessage['Body']);
												// BODY: PROGRAM Tags
												$InteractionMessage['Body'] = str_replace("|PROGRAMOWNER|", $CardProgramOwner, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMNAME|", $CardProgramName, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMCOLOR|", $CardProgramColor, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMIMAGE|", $CardProgramImage, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMWEBSITE|", $CardProgramLinkWebsite, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMPRIVACY|", $CardProgramLinkPrivacy, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMPHONE|", $CardProgramLinkPhone, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMCONTACT|", $CardProgramLinkContact, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMBALANCE|", $CardProgramLinkBalance, $InteractionMessage['Body']);
												$InteractionMessage['Body'] = str_replace("|PROGRAMSTORES|", $CardProgramLinkStores, $InteractionMessage['Body']);
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
											
												if ($itemtype == 'SMS') {
													$InteractionServiceCustome = str_replace("|FROM|", $InteractionFrom, $InteractionServiceCustome);
													$InteractionServiceCustome = str_replace("|TO|", $AffiliatedContactElement, $InteractionServiceCustome);
													$InteractionServiceCustome = str_replace("|MESSAGE|", urlencode($InteractionMessage['Body']), $InteractionServiceCustome);
													$InteractionContentCustome = $InteractionMessage['Body'];
												}
											
								
												// Interaction Content Send
												if ($itemtype == 'SMS') {
													$InteractionMessageSentLog = implode('', file($InteractionServiceCustome));
													$InteractionMessageSentLog = strtolower($InteractionMessageSentLog);
													if (strpos($InteractionMessageSentLog, "true") !== false) {
														$InteractionMessageSent = 1;
													}
													if (strpos($InteractionMessageSentLog, "status code: 0") !== false) {
														$InteractionMessageSent = 1;
													}													}
												if ($itemtype == 'EMAIL') {
													$InteractionMessageSent = sendEmailMessage($InteractionMessage);
												}
														
												// Interaction Result Process...				
												if ($InteractionMessageSent == 1) {
													$InteractionResult = 'OK;';
													$InteractionSent = 1;
												} else {
													$InteractionResult = 'ScriptError;';
													$InteractionSent = 0;
												}
								
								
										// INTERACTION RESULT MANAGE		
											// Interaction Track Manage
											$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_InteractionsTrackManage
															'0', 
															'".$configuration['appkey']."', 
															'add', 
															'".$itemtype."', 
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
											$dbconnection->query($query);
								
								
								
							} // if ($InteractionSent !== 0) {	 ... AGAIN
							
														
		} // if ($InteractionSent !== 0) {	

	echo $InteractionResult."<br />";
	//echo $InteractionSent."<br />";
	//echo "FIN";

?>