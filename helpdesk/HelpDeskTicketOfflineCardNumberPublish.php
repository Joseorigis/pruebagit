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
		// ConnectionId
		if (!isset($connectionid)) {
			$connectionid = "1";
		}
		

	// --------------------------------------------------
	// RE PUBLISH
	// --------------------------------------------------		
	
			$cardprefix = "00";
			$cardprefix = substr($cardnumber, 0, 2);
			$linkpage = "empty";
			$linkresult = "empty";

			// LILLY ENLANCE
			if ($cardprefix == "50") {

					// Publicamos en BENAVIDES
					//$CurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
					//$linkpage  = $CurrentPath."affiliation/setAffiliationBenavidesExport.php";
					$linkpage  = "https://storage.orveecrm.com/lillyenlace/fbenavides/setAffiliationBenavidesExport.php";
					$linkpage .= "?n=".$cardnumber."";
					$linkresult = implode('', file($linkpage));		

			}

			// ASOFARMA ACTUA
			if ($cardprefix == "56") {

					// Publicamos en BENAVIDES
					//$CurrentPath = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
					//$linkpage  = $CurrentPath."affiliation/setAffiliationBenavidesExport.php";
					$linkpage  = "https://storage.orveecrm.com/asofarmaactua/setAffiliationBenavidesExport.php";
					$linkpage .= "?n=".$cardnumber."";
					$linkresult = implode('', file($linkpage));		

			}		

	
		// Disconnect to databases &  ALTERNATE
		//include_once('includes/databaseconnectionrelease.php');	
		$dbconnectionalternate->disconnect();	

?>