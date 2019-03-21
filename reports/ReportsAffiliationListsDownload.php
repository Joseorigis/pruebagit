<?php
/**
*
* ReportsAffiliationListsDownload.php
*
* Reporte descargable de una lista de afiliados (AffiliationList).
*	+ Modificaciones 20170914. raulbg. Implementación Inicial.
*
* @version 		20170914.orvee
* @category 	reports
* @package 		orvee
* @author 		raulbg <raulbg@origis.com>
* @deprecated 	none
*
*/

// -----------------------------------------------------------------------------
// TBD en usp_app_AffiliationListsItemsList 'listdownload' ... 'listdownloadheader?'
// TBD si es csv o xls??? en itemtype?
// Listname?? only first 50 chars?
// -----------------------------------------------------------------------------


// index.php?m=reports&s=items&a=download&t=farmacovigilancia&d=201609

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

	// PARAMETER VALIDATION
		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}
				
		// itemtype
			$itemtype = 'list';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'list'; }
			}
			$itemtype = strtolower($itemtype);

		// itemquery
			$itemquery = '';
			if (isset($_GET['q'])) {
				$itemquery = setOnlyLetters($_GET['q']);
			}

		// filename...
			$fname = '';
			if (isset($_GET['fn'])) {
				$fname = setOnlyCharactersValid($_GET['fn']);
				// replace blank spaces...
				$fname = str_replace(" ", "_", $fname);
			}

		// filetype
			$ftype = '';
			if (isset($_GET['ft'])) {
				$ftype = setOnlyLetters($_GET['ft']);
			}
			$ftype = strtolower($ftype);


		// itemscount
			$items = 0;
			

	// SESSION CHECK
		// if session...
			if (isset($_SESSION[$configuration['appkey']])) {
			
				// get records...
				$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_AffiliationListsItemsList
								'0','".$configuration['appkey']."',
								'".$itemid."', '0', '',
								'list', '0', '0', '', '';";
				$dbconnection->query($query);
				$items = $dbconnection->count_rows();
				
				// set filename...
				$filename  = str_replace("main", "", $configuration['appkey']);
				if ($fname !== '') {
					$filename .= "_".$fname;
				}
				$filename .= "_".$itemid;
				$filename .= "_"."list";
				$filename .= "_".$_SESSION[$configuration['appkey']]['username'];
				$filename .= "_".date('YmdHis').".csv";
		
				// output headers so that the file is downloaded rather than displayed
				header('Content-Type: text/csv; charset=utf-8');
				header('Content-Disposition: attachment; filename='.$filename);
				//header('Content-Disposition: attachment; filename=\'$filename\'');
				
				// create a file pointer connected to the output stream
				$output = fopen('php://output', 'w');
				
				// output the column headings
				fputcsv($output, array('AffiliationId',
										'CardNumber',
										'OperationDate'));
				
				// loop over the rows, outputting them
				while($my_row=$dbconnection->get_row()) {
					
						 $row  = "";
						 $row .= $my_row["AffiliationId"].",";
						 $row .= $my_row["CardNumber"].",";
						 $row .= date('Ymd');			
					
						 fputcsv($output, explode(",",$row));
						 //fputcsv($output, $my_row);
					 
				}
			
			} else {
				
					//header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
					// Redirigimos hacia el URL de la sección
				   header("Refresh: 0;url=../index.php");
				   exit();
					
			} // [if (isset($_SESSION[$configuration['appkey']])) {]
			
?>