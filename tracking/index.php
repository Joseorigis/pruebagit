<?php

//ALTER TABLE track_EmailViews
//ADD [id_CampaniaEmail] [bigint] DEFAULT ((0))
//;
//ALTER TABLE track_EmailClicks
//ADD [id_CampaniaEmail] [bigint] DEFAULT ((0))
//;

// DEFAULT inserts en Sections com 6 = unsuscribe?

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

	// index.php
	//		Script de administración del tracking de Interactions @ OrveeCRM.
	//		Este script es solo el handler de los tracking, recibe parametros y canaliza al orvee correspondiente.
	//		La lógica de la acción (view, click, etc) es del script que referenciemos.
	//		v20140420
	//		index.php?oid=orveecrm&tid=view&aid=0&cid=1&eid=1&sid=6&r=raulbg@origis.com.mx
	
// TBD: Only works for email!!!	
	
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
						header("Refresh: 0;url=$WebsiteHomeDefault");
						//echo $WebsiteHomeDefault;
						exit();
						
					}
				// SQL Injection Check: END
				
				
		// QUERYSTRING CHECK
			$QueryStringHeader = '';
			if (isset($_SERVER['QUERY_STRING'])) { $QueryStringHeader = trim(urldecode($_SERVER['QUERY_STRING'])); }
			
			// IF NO QueryString, STOP EXECUTION					
			if ($QueryStringHeader == '') {
							
				// Redirect to default...
				header("Refresh: 0;url=$WebsiteHomeDefault");
				//echo $WebsiteHomeDefault;
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
	// ACTION PROCESS
	// ------------------------------------------------------------

			// IMAGE SHOW CHECK
				$ImageShow = 0;
// TBD: pasar a folder local?				
				//$ImageFile = '../images/trademark.png';
				$ImageFile = '../images/spacer.gif';
				
				if ($ActionType == 'view') {
					$ImageShow = 1;
				}


			// ACTION APPID PATH
// TBD: Get this path from store thru applicationkey	
				$AppPath = '';
				$AppPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
				//$AppPath = 'https://apps.monederodelahorro.net:444/crm/tracking/';
				//if ($AppLocalPath == $AppPath) { $AppPath = ''; } // Por si estamos en local
				$AppPathScript = '';
				
				
			// ACTION LOCAL LOG
				// Maybe al final?, ya con a donde jaló..., ojo, porque si es view se va a detener



			// --------------------
			// ACTION EMAIL VIEW: begin
			// --------------------
				// ALWAYS REQUIRED
					// VIEW SCRIPT
						$AppPathScript  = $AppPath;
						$AppPathScript .= 'trEmailView.php?';
						$AppPathScript .= 'oid='.$AppId.'&';
						$AppPathScript .= 'aid='.$AffiliationId.'&';
						$AppPathScript .= 'cid='.$EmailCampaignId.'&';
						$AppPathScript .= 'eid='.$EmailSentId.'&';
						$AppPathScript .= 'sid='.$EmailSectionId.'';
						
						$AppPathResponse = '';
						$AppPathResponse = implode('', file($AppPathScript));
						
					// SHOW IMAGE
						// Just when is a VIEW only
						if ($ImageShow == 1) {
						   header('Content-type: image/gif');
						   header('Content-transfer-encoding: binary');
						   header('Content-length: '.filesize($ImageFile));
						   readfile($ImageFile);
						   exit();
						}
			// --------------------
			// ACTION EMAIL VIEW: end
			// --------------------


			// --------------------
			// ACTION EMAIL NOVIEW: begin
			// --------------------
				// El script a referenciar, debe procesar el click y enviarnos el link a donde redirigirnos.
				if ($ActionType == 'noview') {
					// NOVIEW SCRIPT
						$AppPathScript  = $AppPath;
						$AppPathScript .= 'trEmailNoView.php?';
						$AppPathScript .= 'oid='.$AppId.'&';
						$AppPathScript .= 'aid='.$AffiliationId.'&';
						$AppPathScript .= 'cid='.$EmailCampaignId.'&';
						$AppPathScript .= 'eid='.$EmailSentId.'&';
						$AppPathScript .= 'sid='.$EmailSectionId.'';
						//$AppPathScript .= 'r='.$AffiliationEmail.'';
						
						$AppPathResponse = '';
						$AppPathResponse = implode('', file($AppPathScript));
						$AppPathResponse = trim($AppPathResponse);
						
						//echo $AppPathScript."<br>";
						//echo $AppPathResponse."<br>";
						
						// IF valid reponse				
						if (filter_var($AppPathResponse, FILTER_VALIDATE_URL)) {
										
							// Redirect to response...
							header("Refresh: 0;url=$AppPathResponse");
							//echo "OK";
										
						} else {
							
							// Redirect to response...
							header("Refresh: 0;url=$WebsiteHomeDefault");
							//echo "ERR";
							
						}
						
						exit();
				}
			// --------------------
			// ACTION EMAIL NOVIEW: end
			// --------------------

			// --------------------
			// ACTION EMAIL CLICK: begin
			// --------------------
				// El script a referenciar, debe procesar el click y enviarnos el link a donde redirigirnos.
				if ($ActionType == 'click') {
					// CLICK SCRIPT
						$AppPathScript  = $AppPath;
						$AppPathScript .= 'trEmailClick.php?';
						$AppPathScript .= 'oid='.$AppId.'&';
						$AppPathScript .= 'aid='.$AffiliationId.'&';
						$AppPathScript .= 'cid='.$EmailCampaignId.'&';
						$AppPathScript .= 'eid='.$EmailSentId.'&';
						$AppPathScript .= 'sid='.$EmailSectionId.'';
						//$AppPathScript .= 'r='.$AffiliationEmail.'';
						
						$AppPathResponse = '';
						$AppPathResponse = implode('', file($AppPathScript));
						$AppPathResponse = trim($AppPathResponse);
						
						//echo $AppPathScript."<br>";
						//echo $AppPathResponse."<br>";
						
						// IF valid reponse				
						if (filter_var($AppPathResponse, FILTER_VALIDATE_URL)) {
										
							// Redirect to response...
							header("Refresh: 0;url=$AppPathResponse");
							//echo "OK";
										
						} else {
							
							// Redirect to response...
							header("Refresh: 0;url=$WebsiteHomeDefault");
							//echo "ERR";
							
						}
						
						exit();
				}
			// --------------------
			// ACTION EMAIL CLICK: end
			// --------------------


			// --------------------
			// ACTION EMAIL UNSUSCRIBE: begin
			// --------------------
				// El script a referenciar, debe procesar el click y enviarnos el link a donde redirigirnos.
				if ($ActionType == 'unsuscribe') {
					// UNSUSCRIBE SCRIPT
// TBD: Porque el email?, si unsus lo puede extraer					
						$AppPathScript  = $AppPath;
						$AppPathScript .= 'trEmailUnsuscribe.php?';
						$AppPathScript .= 'oid='.$AppId.'&';
						$AppPathScript .= 'aid='.$AffiliationId.'&';
						$AppPathScript .= 'cid='.$EmailCampaignId.'&';
						$AppPathScript .= 'eid='.$EmailSentId.'&';
						$AppPathScript .= 'sid='.$EmailSectionId.'&';
						$AppPathScript .= 'r='.$AffiliationEmail.'';
						
						$AppPathResponse = '';
						$AppPathResponse = implode('', file($AppPathScript));
						$AppPathResponse = trim($AppPathResponse);
						
						//echo $AppPathScript."<br>";
						//echo $AppPathResponse."<br>";
						
						// IF valid reponse				
						if (filter_var($AppPathResponse, FILTER_VALIDATE_URL)) {
										
							// Redirect to response...
							header("Refresh: 0;url=$AppPathResponse");
							//echo "OK";
										
						} else {
							
							// Redirect to response...
							header("Refresh: 0;url=$WebsiteHomeDefault");
							//echo "ERR";
							
						}
						
						exit();
				}
			// --------------------
			// ACTION EMAIL UNSUSCRIBE: end
			// --------------------



	// DATABASE CONNECTION CLOSE
		include_once('../includes/databaseconnectionrelease.php');	
		

	// END
		// Redirect to default...
		header("Refresh: 0;url=$WebsiteHomeDefault");
		//echo $WebsiteHomeDefault;
		exit();


?>
