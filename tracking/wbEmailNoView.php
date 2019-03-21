<?php

// HTML headers
	header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
	header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
	header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
	header ('Pragma: no-cache');	// HTTP/1.0


	// WARNINGS & ERRORS
		//ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');


	// INIT
		// Iniciamos el controlador de SESSIONs de PHP
		if(!session_id()){
			// Session
			session_start();
		}
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
		
	
	// INCLUDES & REQUIRES 
		include_once('../includes/configuration.php');	// Archivo de configuración
		include_once('../includes/functions.php');	// Librería de funciones
		include_once('../includes/database.class.php');	// Class para el manejo de base de datos
		include_once('../includes/databaseconnection.php');	// Conexión a base de datos
		
// --------------------
// INICIO CONTENIDO
// --------------------

	// trEmailClick.php
	//		Aplicación que escribe en BD el click generado por el usuario en el email y,
	//  	a partir de la sección cliqueada, redirige a un URL especifico.
	//		Esta versión, cuenta cada sección o click diferente de manera única.
	//		v20110905

	// ------------------------------------------------------------
	// INIT
	// ------------------------------------------------------------
			
		// CONTAINER
			$appcontainer = 1;
		
		// ERROR HANDLER
			$actionerrorid = 0;

		// DEFAULT
			$WebsiteHomeDefault = 'default.php'; 
	
		// REFERER ... just in case
			$referer = '';
			if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
	
		
				// SQL Injection Check: BEGIN
					$IsSQLInjection = 0;
					$IsSQLInjection = IsSQLInjection();
					if ($IsSQLInjection > 0) {
							$actionerrorid = 66;			
					}
					// IF SQL Injection, STOP EXECUTION					
					if ($actionerrorid == 66) {
						
						// Redirect to default...
						//header("Refresh: 0;url=$WebsiteHomeDefault");
						echo $WebsiteHomeDefault;
						exit();
						
					}
				// SQL Injection Check: END
				
				
		// QUERYSTRING CHECK
			$QueryStringHeader = '';
			if (isset($_SERVER['QUERY_STRING'])) { $QueryStringHeader = trim(urldecode($_SERVER['QUERY_STRING'])); }
			
			// IF NO QueryString, STOP EXECUTION					
			if ($QueryStringHeader == '') {
							
				// Redirect to default...
				//header("Refresh: 0;url=$WebsiteHomeDefault");
				echo $WebsiteHomeDefault;
				exit();
							
			}



	// ------------------------------------------------------------
	// PARAMETERS [QUERYSTRING]
	// ------------------------------------------------------------

			// VARIABLE INIT
				$AppId			  = '';
				$ActionType		  = 'NA';
				$AffiliationId	  = '0';
				$AffiliationEmail = '';
				$EmailCampaignId  = '0';
				$EmailSentId	  = '0';
				$EmailSectionId   = '1';
				$AppLocalPath 	  = '';
				$AppLocalPath 	  = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
			
			// GET PARAMS
				if (isset($_GET['oid']))	{ $AppId = trim(strtolower($_GET['oid'])); }
				if (isset($_GET['tid']))	{ $ActionType = trim(strtolower($_GET['tid'])); }
				if (isset($_GET['aid']))	{ $AffiliationId = $_GET['aid']; }
				if (isset($_GET['r']))		{ $AffiliationEmail = trim(strtolower($_GET['r'])); }
				if (isset($_GET['cid']))	{ $EmailCampaignId = $_GET['cid']; }
				if (isset($_GET['eid']))	{ $EmailSentId = $_GET['eid']; }
				if (isset($_GET['sid']))	{ $EmailSectionId = trim($_GET['sid']); }
	
			// PARAM CHEK
		  		if ($AffiliationId == '|AID|' || $AffiliationId == '') { $AffiliationId  = '0'; }
			    	if (!is_numeric($AffiliationId)) { $AffiliationId = '0'; }
		   		if ($AffiliationEmail == '|EMAIL|' || $AffiliationEmail == '|email|') { $AffiliationEmail  = ''; }
		   			$AffiliationEmail = str_replace("'", '', $AffiliationEmail);
					
		  		if ($EmailCampaignId == '|CID|' || $EmailCampaignId == '') { $EmailCampaignId  = '0'; }
					if (!is_numeric($EmailCampaignId))	 { $EmailCampaignId = '0'; }
		   		if ($EmailSentId == '|EID|' || $EmailSentId == '') { $EmailSentId  = '0'; }
					if (!is_numeric($EmailSentId))		 { $EmailSentId = '0'; }
		   		if ($EmailSectionId == '|SID|' || $EmailSectionId == '') { $EmailSectionId  = '1'; }
					if (!is_numeric($EmailSectionId)) 	 { $EmailSectionId = '1'; }				



	// ------------------------------------------------------------
	// EMAIL NO VIEW PROCESS
	// ------------------------------------------------------------


		// ---------------------------------------------
		// CAMPAIGN DATA
				// Obtengo el contenido del Item
				$WebsiteHomeDefault = 'default.php'; // DEFAULT
				$WebsiteHome = '';
				$WebsiteHomeRedirect = 0;
				$items = 0;
				$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_InteractionsEmailManage
								'1', 
								'".$configuration['appkey']."', 
								'noview', 
								'email', 
								'".$EmailCampaignId."',
								'".$EmailSentId."';";
				$dbconnection->query($query);
				$items = $dbconnection->count_rows();
				if ($items > 0) {
					$my_row=$dbconnection->get_row();
					$WebsiteHome 	= trim($my_row['InteractionContent']);
					if ($WebsiteHome == '#') { $WebsiteHome = ''; }
				}

				// SI seguimos con el DEFAULT, intentamos el HOME Section...
				if ($WebsiteHome == '') {
						// Consultamos...
							$items = 0;
							$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_InteractionsTrackSectionManage
												'1', 
												'".$configuration['appkey']."', 
												'view', 
												'emailsection', 
												'1';";
							$dbconnection->query($query);
							$items = $dbconnection->count_rows();
							if ($items > 0) {
								$my_row=$dbconnection->get_row();
								$WebsiteHome 	 = trim($my_row['InteractionSectionRedirect']);
								$WebsiteHomeRedirect = 1;
								if ($WebsiteHome == '#') { $WebsiteHome = ''; }
							}
				}
				
				// SI seguimos con el DEFAULT, intentamos el HOME principal...
				if ($WebsiteHome == '') {
						// Consultamos...
							$items = 0;
							$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
												'1', 
												'".$configuration['appkey']."', 
												'view', 
												'crm', 
												'0', 
												'Interactions', 
												'Website';";
							$dbconnection->query($query);
							$items = $dbconnection->count_rows();
							if ($items > 0) {
								$my_row=$dbconnection->get_row();
								$WebsiteHome 	 = trim($my_row['ParameterValue']);
								$WebsiteHomeRedirect = 1;
								if ($WebsiteHome == '#') { $WebsiteHome = ''; }
							}
				}
				
				// DEFAULT...
				if ($WebsiteHome == '') { $WebsiteHome = $WebsiteHomeDefault; }



	// ---------------------------------------------
	// AFFILIATED DATA
			$AffiliationEmail 		= '';
			$AffiliationCardNumber 	= '0';
			$AffiliationFullName 	= '';
			$AffiliationName		= '';
			$AffiliationLastName 	= '';
			$AffiliationMaidenName 	= '';
			$items = 0;
			$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_AffiliationItemManage 
									'view', 
									'crm', 
									'1', 
									'".$configuration['appkey']."', 
									'".$AffiliationId."';";
			$dbconnection->query($query);									
			$items = $dbconnection->count_rows();
			if ($items > 0) {
				$my_row=$dbconnection->get_row();
				$AffiliationEmail 		= trim($my_row['Email']);
				$AffiliationCardNumber	= trim($my_row['Tarjeta']);
				$AffiliationFullName 	= trim($my_row['Nombre']);
				$AffiliationName 		= trim($my_row['NombreParcial']);
			}



	// ---------------------------------------------
	// OUTPUT
			// Descargo el HTML enviado y que mostraremos en web
			
				// Si es el DEFAULT hasta aquí, ponemos el folder para que se lea como HTML y como código
				if ($WebsiteHome == $WebsiteHomeDefault || ($EmailSentId == 0 && $EmailCampaignId == 0)) {
					header("Refresh: 0;url=$WebsiteHome");
					exit();
				}
				// Si es el DEFAULT hasta aquí, ponemos el folder para que se lea como HTML y como código
				if ($WebsiteHomeRedirect == 1) {
					header("Refresh: 0;url=$WebsiteHome");
					exit();
				}
				// SQL Injection
				if ($actionerrorid == 66) {
					$WebsiteHomeDefault = 'default.php';
					header("Refresh: 0;url=$WebsiteHomeDefault");
					exit();
				}
	
	
			$HTMLFile = file($WebsiteHome);
			$HTMLContent = '';
			$FileLines = count($HTMLFile);
			$i = 0;
			while ($i<$FileLines) {
			  $HTMLContent .= chop($HTMLFile[$i])."\r\n";
			  $i++;
			}
		
				// TAGS vía SP
				$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_InteractionsTrackParamsManage 
										'1', 
										'".$configuration['appkey']."', 
										'params', 
										'email', 
										'sent', 
										'".$EmailCampaignId."', 
										'".$EmailSentId."', 
										'".$AffiliationId."', 
										'".$AffiliationEmail."', 
										'".date('Ymd')."';";
				$dbconnection->query($query);									
				while($my_row=$dbconnection->get_row()){
					
						//if (trim($my_row['ParameterType']) == 'SUBJECT') {
						//	$HTMLContent = str_replace(trim($my_row['ParameterName']), trim($my_row['ParameterValue']),$HTMLContent);
						//}
					
						if (trim($my_row['ParameterType']) == 'CONTENT') {
							$HTMLContent = str_replace(trim($my_row['ParameterName']), trim($my_row['ParameterValue']),$HTMLContent);
						}
					
				}
		
			// Reemplazo los tags, para generar una impresión única
			$HTMLContent = str_replace('|CAMPANIAID|', $EmailCampaignId, $HTMLContent);
			$HTMLContent = str_replace('|ENVIOID|', $EmailSentId, $HTMLContent);
			$HTMLContent = str_replace('|EMAILID|', '0', $HTMLContent);

			$HTMLContent = str_replace('|CID|', $EmailCampaignId, $HTMLContent);
			$HTMLContent = str_replace('|EID|', $EmailSentId, $HTMLContent);
			$HTMLContent = str_replace('|SID|', '0', $HTMLContent);
			$HTMLContent = str_replace('|AID|', $AffiliationId, $HTMLContent);
			$HTMLContent = str_replace('|AUTH|', '0', $HTMLContent);
			$HTMLContent = str_replace('|KEY|', '0', $HTMLContent);
			
			$HTMLContent = str_replace('|USERID|', $AffiliationId, $HTMLContent);
			$HTMLContent = str_replace('|EMAIL|', $AffiliationEmail, $HTMLContent);
			
			$HTMLContent = str_replace('|AFFILIATIONID|', $AffiliationId, $HTMLContent);
			$HTMLContent = str_replace('|CARDNUMBER|', $AffiliationCardNumber, $HTMLContent);
			$HTMLContent = str_replace('|NAME|', $AffiliationFullName, $HTMLContent);
			$HTMLContent = str_replace('|LASTNAME|', '', $HTMLContent);
			$HTMLContent = str_replace('|DATE|', date('d/m/Y'), $HTMLContent);						
			
			$HTMLContent = str_replace('|TARJETA|', $AffiliationCardNumber, $HTMLContent);
			$HTMLContent = str_replace('|NOMBRE|', $AffiliationFullName, $HTMLContent);
			$HTMLContent = str_replace('|PATERNO|', '', $HTMLContent);
			$HTMLContent = str_replace('|MATERNO|', '', $HTMLContent);
			$HTMLContent = str_replace('|FECHA|', date('d/m/Y'), $HTMLContent);						
			
			$HTMLContent = str_replace('|PASSWORD|', '[NO DISPONIBLE]', $HTMLContent);

			// Imprimo la pieza personalizada en el browser
			print $HTMLContent;

?>
