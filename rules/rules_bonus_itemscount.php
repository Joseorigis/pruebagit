<?php
	// Verificamos si la página es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=ISO-8859-15');
		// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
	}
	
	// INIT
		// Iniciamos el controlador de SESSIONs de PHP
		session_start();
			
			// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexión a base de datos
				include_once('../includes/functions.php');	// Librería de funciones
			
			// TRANSACTIONS DATABASE
				include_once('../includes/databaseconnectiontransactions.php');


		$ItemId0 = setOnlyText($_GET['ItemType0']);
		$ItemId1 = setOnlyText($_GET['ItemType1']);
		
		$ItemFilter = "";
		if ($ItemId0 != "") { $ItemFilter .= " AND (ItemOwnerId = '".$ItemId0."') "; }
		if ($ItemId1 != "") { $ItemFilter .= " AND (ItemSKU = '".$ItemId1."') "; }
		
		
		$query  = " SELECT COUNT(ItemId) AS Items FROM ItemsList WITH(NOLOCK)
					WHERE (ItemId > 0) ".$ItemFilter ;
		$dbtransactions->query($query);
		$my_row=$dbtransactions->get_row();
		echo "<span style='font-size:36px;font-weight:bold;'>".number_format($my_row['Items'])."</span>";
		exit();
//		$items = 99;
//		if ($items > 0) {
//			//$my_row=$db->get_row();
//			//echo "<strong>".$my_row['Casa']."</strong><br />";
//			//echo "".$my_row['direccion'];
//			echo "<span style='font-size:36px;font-weight:bold;'>".number_format(rand(1,2000))."</span>";
//			
//		} else {
//			echo "<span style='font-size:36px;font-weight:bold;'>0</span>";
//		}
		
?>
