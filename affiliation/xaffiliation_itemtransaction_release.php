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
			
			
		// Inicializamos	
		$ErrorId = '99';	


// SCRIPT
	// Obtengo el nombre del script en ejecución
	$script = __FILE__;
	$scriptactual = $camino[count($camino)-1];

	// CURRENT PAGE SCRIPT
		$listscriptparts = explode(chr(92), $scriptactual);
		$listscript = $listscriptparts[count($listscriptparts)-1];

	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", $listscript);
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		setNavigationLog('navigation', 0, $listmodule.'/'.$listscript);

		// Obtenemos parametros...
		$connectionid 	= '10';
		$cardnumber		= '';
		$action 		= '';
		if (isset($_GET['cardnumber'])) {
			$cardnumber 	= $_GET['cardnumber'];
			 if (!is_numeric($cardnumber)) { $cardnumber = '0'; }
		}
		if (isset($_GET['transaction'])) {
			$transaction 	= $_GET['transaction'];
			 if (!is_numeric($transaction)) { $transaction = '0'; }
		}
		if (isset($_GET['saleauthnumber'])) {
			$saleauthnumber = $_GET['saleauthnumber'];
			 if (!is_numeric($saleauthnumber)) { $saleauthnumber = '0'; }
		}
		if (isset($_GET['action'])) {
			$action = $_GET['action'];
		}
		$cardaffiliationid = '0';
		$transactionid	= '0';
		if (strlen($transaction) > 2) {
			$transactionid	= substr($transaction,2,strlen($transaction)-2);
		}
		
		// Obtenemos el AfiliadoId
		$items = 0;
		$query  = " EXEC usp_pos_CardStatus '".$cardnumber."', '".$connectionid."';";
		$dbconnection->query($query);
		$items = $dbconnection->count_rows();
		if ($items > 0) {
			$my_row=$dbconnection->get_row();
			$cardaffiliationid = $my_row['id_UsuarioWeb'];
		}

		// SI hay datos, procedemos...
        //echo $cardaffiliationid.'-'.$transactionid.'-'.$saleauthnumber;
		if ($cardaffiliationid != '0' && $transactionid != '0' && $saleauthnumber != '0') {

			// Status de la venta para ver si es viable reversar...
				$items = 0;
				$saleauthnumberlast = '0';
				$query  = " EXEC usp_app_TransactionSaleStatus '".$transactionid."', '".$saleauthnumber."';";
				$dbconnection->query($query);
				//echo $query;
				$items = $dbconnection->count_rows();
				if ($items > 0) {
					$my_row=$dbconnection->get_row();
					$saleauthnumberlast = trim($my_row['id_Venta']);
					$ErrorId = trim($my_row['Error']);
					if ($saleauthnumberlast !== $saleauthnumber) {
						$ErrorId = '23';
					}
				}
				
	
				// OK reversamos...
				if ($ErrorId == '0' && $action == 'release') {
					$query  = " EXEC usp_pos_TransactionSaleReverse '".$transactionid."', '".$saleauthnumber."', '".$cardaffiliationid."';";
					//echo $query;
					$dbconnection->query($query);
					//$my_row=$dbconnection->get_row();
					$ErrorId = '0';
				}
			
		} else {
				$ErrorId = '66';
		}
		
		// FIN
		echo $ErrorId;
		
?>
