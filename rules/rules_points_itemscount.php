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


		$itemid01 = '';
		$itemid02 = '';
		$itemid03 = '';
		$itemid04 = '';
		$itemid05 = '';
		$itemid06 = '';

		if (isset($_GET['ItemType1'])) { $itemid01 = setOnlyText($_GET['ItemType1']); }
		if (isset($_GET['ItemType2'])) { $itemid02 = setOnlyText($_GET['ItemType2']); }
		if (isset($_GET['ItemType3'])) { $itemid03 = setOnlyText($_GET['ItemType3']); }
		if (isset($_GET['ItemType4'])) { $itemid04 = setOnlyText($_GET['ItemType4']); }
		if (isset($_GET['ItemType5'])) { $itemid05 = setOnlyText($_GET['ItemType5']); }
		if (isset($_GET['ItemType6'])) { $itemid06 = setOnlyText($_GET['ItemType6']); }

		$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsItemsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."',
							'count', 
							'points', 
							'0',
							'',
							'".$itemid01."',
							'".$itemid02."',
							'".$itemid03."',
							'".$itemid04."',
							'".$itemid05."',
							'".$itemid06."';"; //echo $query;
		$dbtransactions->query($query);
		$my_row=$dbtransactions->get_row();
		echo "<span style='font-size:36px;font-weight:bold;'>".number_format($my_row['Items'])."</span>";
		//echo "<span style='font-size:36px;font-weight:bold;'>".$query."</span>";
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
