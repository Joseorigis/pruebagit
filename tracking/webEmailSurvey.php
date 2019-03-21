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
		if(!session_id()){
			// Session
			session_start();
		}
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
		
	
	// INCLUDES & REQUIRES 
		include_once('../includes/configuration.php');	// Archivo de configuración
		include_once('../includes/functions.php');	// Librería de funciones
		include_once('../includes/database.class.php');	// Class para el manejo de base de datos
		include_once('../includes/databaseconnection.php');	// Conexión a base de datos
		
// --------------------
// INICIO CONTENIDO
// --------------------

	// REFERER
		$referer = '';
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }

	// ACTIONERRORID
		$actionerrorid = 0;
		

	// SQL INJECTION
			// SQL Injection Check: BEGIN
				// GET
					// Obtenemos el query string
					$QueryStringHeader = "";
					if (isset($_SERVER['QUERY_STRING'])) { $QueryStringHeader = urldecode($_SERVER['QUERY_STRING']); }
						// Si hay comillas, redirigimos la ejecución
						if (strpos($QueryStringHeader, "'") !== false) { 
							$actionerrorid = 66;
							$QueryStringHeader = str_replace("'", '', $QueryStringHeader);
						}
				// POST
					// Cada variable de POST
					$CharacterFound = 0;
					foreach($_POST as $key => $value) {
					  	//echo "POST parameter '$key' has '$value'";
						// Si hay comillas, redirigimos la ejecución
						if (strpos($value, "'") !== false) { 
							$CharacterFound = 1;
							unset($_POST[$key]);
							$_POST[$key] = "";
						}
					}	
					// Si encontramos caracteres raros, detenemos la ejecución...
					if ($CharacterFound == 1) {
						unset($_POST);
						$actionerrorid = 66;
					}						
			// SQL Injection Check: END		
		

	// PARAMS
		// Inicializo
		$AffiliationId	 = 0;
		$AffiliationEmail = '';
		$EmailSentId	  = 0;
	    $EmailCampaignId	 = 0;
		$EmailSectionId   = 1;
		
		// Obtengo los parametros enviados
		if (isset($_GET['idUser']))		{ $AffiliationId = $_GET['idUser']; }
		if (isset($_GET['idEnvio']))	{ $EmailSentId = $_GET['idEnvio']; }
		if (isset($_GET['idSection'])) 	{ $EmailSectionId = $_GET['idSection']; }
		if (isset($_GET['email']))		{ $AffiliationEmail = strtolower(trim($_GET['email'])); }

		if (isset($_GET['aid']))		{ $AffiliationId = $_GET['aid']; }
		if (isset($_GET['eid']))		{ $EmailSentId = $_GET['eid']; }
		if (isset($_GET['sid'])) 		{ $EmailSectionId = $_GET['sid']; }
		if (isset($_GET['cid'])) 		{ $EmailCampaignId = $_GET['cid']; }
		if (isset($_GET['r']))			{ $AffiliationEmail = strtolower(trim($_GET['r'])); }

		// Validamos
		// Si no enviaron parametros, pongo como si fueran CERO
		if ($AffiliationId  == '|USERID|' || $AffiliationId == '') { $AffiliationId  = 0; }
		if ($AffiliationEmail  == '|EMAIL|' || $AffiliationEmail == '|email|') { $AffiliationEmail  = ''; }
		$AffiliationEmail = str_replace("'", '', $AffiliationEmail);
		if ($EmailSentId == '|ENVIOID|' || $EmailSentId == '') { $EmailSentId  = 0; }
	    if ($EmailCampaignId == '|CID|' || $EmailCampaignId == '') { $EmailCampaignId  = 0; }
		if ($EmailSectionId == '|SECCIONID|' || $EmailSectionId == '') { $EmailSectionId  = 1; }
		// Si no son números
		if (!is_numeric($AffiliationId))		 { $AffiliationId = 0; }
		if (!is_numeric($EmailSentId))		 { $EmailSentId = 0; }
	    if (!is_numeric($EmailCampaignId))	 { $EmailCampaignId = 0; }
		if (!is_numeric($EmailSectionId)) 	 { $EmailSectionId = 1; }
	

	// ---------------------------------------------
	// CAMPAIGN DATA

		// INSTANCE...
		$instancename  		= $configuration['instancefirstname'];
		if ($instancename == '') { $instancename = 'Origis'; }
		$instancelastname 	= $configuration['instancelastname'];
		if ($instancelastname == '') { $instancelastname = 'OrveeCRM'; }
	 
  

		// HOME	
				$WebsiteHomeDefault = 'default.php'; // DEFAULT
				$WebsiteHome = '';
				$items = 0;
				$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_InteractionsTrackSectionManage
									'1', 
									'".$configuration['appkey']."', 
									'view', 
									'emailsection', 
									'".$EmailSectionId."';";
				$dbconnection->query($query);
				$items = $dbconnection->count_rows();
				if ($items > 0) {
					$my_row=$dbconnection->get_row();
					$WebsiteHome 	= trim($my_row['InteractionSectionRedirect']);
					if ($WebsiteHome == '')  { $WebsiteHome = $WebsiteHomeDefault; }
					if ($WebsiteHome == '#') { $WebsiteHome = $WebsiteHomeDefault; }
				}


		// SURVEY PARAMS	
			$SurveyLinkFound = strpos($WebsiteHome, 'surveymonkey.com');
			$SurverLink		 = "";
			
			if ($SurveyLinkFound !== false) {
				
					$SurverParams 	 = "&c=".$AffiliationId;
					//$SurverLink		.= "https://es.surveymonkey.com/jsEmbed.aspx?sm=Aop71hG1NUBwSf_2fdPb7ISg_3d_3d";
					$SurverLink		.= $WebsiteHome;
					$SurverLink		.= $SurverParams;
					
			} else {

					header("Refresh: 0;url=$WebsiteHomeDefault");
					exit();

			}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo $instancelastname; ?> | Encuesta</title>
    <link href="../style.css" rel="stylesheet" type="text/css" />    
    <link rel="shortcut icon" href="../favicon.ico" />
</head>

<body style="background-color:#F0F0F0">

<table style="background-color:#FFFFFF;text-align:center;border-collapse:collapse;margin:auto;border:1px solid #ADB1BD;">
  <tr>
    <td style="text-align:center;">
    
        <div id="surveyMonkeyInfo">
        <div>
        <script src="<?php echo $SurverLink; ?>"> </script>
        </div>
        </div>     
            
    </td>
  </tr>
</table>

</body>
</html>
