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
		
		$total = 100;

			// Connecting to database CATALOGs
			$db2 = new database($configuration['db0type'],
								$configuration['db0host'], 
								$configuration['db0name'],
								$configuration['db0username'],
								$configuration['db0password']);		

						// Seleccionamos todas las interacciones
						$items = 0;
						$indice = 0;
						$query  = "SELECT   TOP ".$total." * 
									FROM InteractionsEmailReboundLog WITH(NOLOCK)
									WHERE (ReboundInbox = 'monederodelahorro@fahorro.com.mx')
									ORDER BY id_EmailReboundLog;";
						$dbsecurity->query($query);
						
						while($my_row=$dbsecurity->get_row()){ 
						
								$items = $items + 1;
								
							   $indice	 = $my_row['id_EmailReboundLog'];
						
								// Inicializo
							   $AffiliationId	 = $my_row['id_UsuarioWeb'];
							   $AffiliationEmail = $my_row['Email'];
							   $EmailSentId		 = $my_row['id_EnvioEmail'];
							   $EmailCampaignId	 = $my_row['id_CampaniaEmail'];
							   $EmailSectionId   = '0';
							   $EmailSubject	 = $my_row['ReboundSubject'];
							   $EmailReboundCode = $my_row['ReboundCode'];
							   
							   $Content  = 'https://apps.monederodelahorro.net:444/crm/tracking/trackEmailRebound.php?';
							   $Content .= 'aid='.$AffiliationId.'&';
							   $Content .= 'eid='.$EmailSentId.'&';
							   $Content .= 'sid='.$EmailSectionId.'&';
							   $Content .= 'cid='.$EmailCampaignId.'&';
							   $Content .= 'r='.$AffiliationEmail.'&';
							   $Content .= 'subject='.$EmailSubject.'&';
							   $Content .= 'error='.$EmailReboundCode.'';
							   $Content = str_replace(" ", "%20", $Content);

								echo $items.". ";
								echo $my_row['id_EmailReboundLog']."... ";
								//echo $Content;
								
									$filecontent = file($Content);
									$ContentText = implode('', $filecontent);
									//$ContentText = str_replace(chr(39),'"',$ContentText);
									//$ContentText = urlencode($ContentText);
									
									$querydelete  = "DELETE
												FROM InteractionsEmailReboundLog 
												WHERE  id_EmailReboundLog = '".$indice."';";
									$db2->query($querydelete);
									
								
								echo $ContentText."<br />";	
						
						}
	

						$query  = "UPDATE    AppParameters
									SET              ParameterValue = 'FINISHED', ParameterLastDate = GETDATE(),
													 ParameterDescription = '".$scriptactual."'
									WHERE     (ParameterType = 'Task') AND (ParameterName = 'InteractionsReboundExport');";
						$dbsecurity->query($query);
						

	// DATABASE CONNECTION CLOSE
		include_once('../includes/databaseconnectionrelease.php');
		
		$db2->disconnect();	
		
		if ($total <= $items) {
			echo "<meta http-equiv='refresh' content='0' >";
			echo "Task Refreshing...";
		} else {
			echo "Task End.";
		}

?>

