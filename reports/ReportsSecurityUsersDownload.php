<?php

// https://enlace.orveecrm.com/index.php?m=reports&s=items&a=download&t=quejaproducto&d=201609

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
		
			// TRANSACTIONS DATABASE
				include_once('../includes/databaseconnectiontransactions.php');

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
	
			$filter = " ";
		
		// filename
			$filename = $configuration['appkey']."_users_";
			$filename = $filename.$_SESSION[$configuration['appkey']]['username']."_".date('YmdHis').".csv";

	
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename);
		//header('Content-Disposition: attachment; filename=\'$filename\'');
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');
		
		// output the column headings
		fputcsv($output, array('UserId',
							    'Username','Email','Name',
							    'UserStatus','UserProfile','CreationDate',
								'ModifiedDate','LastAccess', 'ExportUser',
							    'OperationDate'));
		
		// fetch the data
		$query  = "EXEC dbo.usp_app_SecurityUserManage
						'".$_SESSION[$configuration['appkey']]['userid']."', 
						'".$configuration['appkey']."', 
						'list';";
		$dbsecurity->query($query);

		// loop over the rows, outputting them
		while($my_row=$dbsecurity->get_row()) {
			
			 $row  = "";
			 $row .= $my_row["UserId"].",";
			 $row .= $my_row["Username"].",".$my_row["Email"].",".$my_row["Name"].",";
			 $row .= $my_row["UserStatus"].",".$my_row["UserProfile"].",".$my_row["CreationDate"].",";
			 $row .= $my_row["ModifiedDate"].",".$my_row["LastAccess"].",".$my_row["ExportUser"].",";
			 $row .= date('YmdHis');			
			
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