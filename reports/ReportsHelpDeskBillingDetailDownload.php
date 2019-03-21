<?php

// index.php?m=reports&s=items&a=download&t=settlementindex&d=201609&n=5&q=sanofi

	// WARNINGS & ERRORS
		//ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

	// INIT
		// Iniciamos el controlador de SESSIONs de PHP
		session_start();
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];

	// INCLUDES & REQUIRES
		include_once('../includes/configuration.php');	// Archivo de configuración
		include_once('../includes/database.class.php');	// Class para el manejo de base de datos
		include_once('../includes/databaseconnection.php');	// Conexión a base de datos
		include_once('../includes/functions.php');	// Librería de funciones

		include_once('../includes/databaseconnectiontransactions.php');	// Conexión a base de datos
		

// --------------------
// INICIO CONTENIDO
// --------------------

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();
		// ERROR MESSAGE
		$errormessage = "";


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
			$actionerrorid = 10;
//			include_once("../accessdenied.php"); 
//			exit();
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
			
		}

// --------------------
// INICIO REPORTE
// --------------------


// IF SESSION...
	if (isset($_SESSION[$configuration['appkey']])) {
	
			$date = "";
			$itemid = "0";
			$itemname = "0";
			if (isset($_GET['d'])) {
				$date = setOnlyNumbers($_GET['d']);
			}

			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == "") { $itemid = 0; }
			}
			if (isset($_GET['q'])) {
				$itemname = setOnlyLetters($_GET['q']);
				$itemname = strtolower($itemname);
			}
		
		
		// filename
			//$filename  = $configuration['appkey']."_";
			$filename  = "";
			$filename .= "billingdetail_";

				if ($itemname !== "") {
					$filename = $filename.$itemname."_";
				}
		
				if ($date !== "") {
					$filename = $filename.$date."_";
				}

			$filename = $filename.$_SESSION[$configuration['appkey']]['username']."_".date('YmdHis').".csv";

	
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename);
		//header('Content-Disposition: attachment; filename=\'$filename\'');
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');
		
		// output the column headings
		fputcsv($output, array('ItemOwnerId','ItemOwnerName',
							   'BillingDate','BillingTable',
								'ConnectionId','ConnectionName',
								'Transactions'));
		
		// fetch the data
		$query = "EXEC dbo.usp_app_TransactionsBillingReport
							'0',
							'".$configuration['appkey']."',
							'report',
							'',
							'".$itemid."',
							'',
							'".$date."';";
		$dbtransactions->query($query);
		
		// loop over the rows, outputting them
		while($my_row=$dbtransactions->get_row()) {
			
			 $row  = "";
			 $row .= $my_row["ItemOwnerId"].",".$my_row["ItemOwnerName"].",";
			 $row .= $my_row["BillingDate"].",".$my_row["BillingTable"].",";
			 $row .= $my_row["ConnectionId"].",".$my_row["ConnectionName"].",";
			 $row .= $my_row["Transactions"];
			
			 fputcsv($output, explode(",",$row));
			 //fputcsv($output, $my_row);
			 
		}
	
	} else {
		
			//header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			// Redirigimos hacia el URL de la sección
		   header("Refresh: 0;url=../index.php");
		   exit();
			
	}
?>