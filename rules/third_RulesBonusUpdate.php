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
		// MESSAGE & KEY
		$message = "";
		$requiredkey  = "52447f516f7bc17940f7ffb156fd679a";
		$webservice = str_replace(getCurrentPageScript(), '', getCurrentPageURL());

	
	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}


	// PARAMETER VALIDATION
		// key
			$key = '0';
			if (isset($_GET['k'])) { 
				$key = setOnlyText($_GET['k']); 
			}
			if ($key !== $requiredkey) { $actionerrorid = 10; }
			if ($key == '') { $actionerrorid = 2; }

		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}	

		// itemtype
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);

//		// actionauth 
//			$actionauth = '';
//			if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
//			if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
//			if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio
		
		// item variables set
			// itemsku
				$ruleitem = '';
				if (isset($_GET['itemsku'])) { 
					$ruleitem = setOnlyCharactersValid($_GET['itemsku']);
					if ($ruleitem == '') 
						{ $actionerrorid = 2; }
				} else {
					$actionerrorid = 1;
				}
		
			// ruleexpiration
				$ruleexpiration = '';
				if (isset($_GET['expiration'])) { 
					$ruleexpiration = setOnlyNumbers($_GET['expiration']);
					//if ($itembrand == '') 
					//	{ $actionerrorid = 2; }
				} else {
					$actionerrorid = 1;
				}

			// ruleexpirationdate
				$ruleexpirationdate = '';
				if (isset($_GET['date'])) { 
					$ruleexpirationdate = setOnlyNumbers($_GET['date']);
					//if ($itembrand == '') 
					//	{ $actionerrorid = 2; }
				} else {
					$actionerrorid = 1;
				}
		
			// connectionid
				$connectionid = '1';
				if (isset($_GET['cid'])) { 
					$connectionid = setOnlyNumbers($_GET['cid']); 
				}
				if ($connectionid == '') { $connectionid = '1'; }

			// rulecode
				$rulecode = '';
				if (isset($_GET['code'])) { 
					$rulecode = setOnlyCharactersValid($_GET['code']);
					//if ($itembrand == '') 
					//	{ $actionerrorid = 2; }
				} else {
					$actionerrorid = 1;
				}


	// RECORD PROCESS...	
		// Si no hay error hasta aquí, agregamos...
		$operation = "updateexpiration";
		if ($actionerrorid == 0) {
	

					$records = 0;
					$query  = "EXEC dbo.usp_app_RulesBonusManage
										'0', 
										'".$configuration['appkey']."',
										'".$operation."', 
										'".$itemtype."', 
										'".$itemid."', 
										'',
										'',
										'".$rulecode."',
										'".$connectionid."',
										'0',
										'".$ruleexpiration."',
										'".$ruleexpirationdate."',
										'',
										'".$ruleitem."';";//echo $query;
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						$my_row=$dbtransactions->get_row();
						
						$actionerrorid 		= $my_row['Error']; 

					} else {
						$actionerrorid = 66;
					}

		} // if ($actionerrorid == 0)


						
		include_once('../includes/databaseconnectionrelease.php');	
	
	//https://orbis.orveecrm.com/rules/third_RulesBonusItemAdd.php?k=0&connection=10&itemsku=0&itemname=0&itembrand=0

	// FINAL OUTPUT
	echo $actionerrorid;

?>