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
		session_start();
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

			// Connecting to database CATALOGs
			$db2 = new database($configuration['db1type'],
								$configuration['db1host'], 
								$configuration['db1name'],
								$configuration['db1username'],
								$configuration['db1password']);		

						// Seleccionamos todas las interacciones
						$query  = "EXEC [dbo].[usp_tsk_InteractionsTransactionsTry];";
						$dbtransactions->query($query);
						

	// DATABASE CONNECTION CLOSE
		include_once('../includes/databaseconnectionrelease.php');	

?>
<?php $db2->disconnect(); ?>
