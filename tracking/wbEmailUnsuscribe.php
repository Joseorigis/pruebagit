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
		// Obtengo el nombre del script en ejecuci�n
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
		
	
	// INCLUDES & REQUIRES 
		include_once('../includes/configuration.php');	// Archivo de configuraci�n
		include_once('../includes/functions.php');	// Librer�a de funciones
		include_once('../includes/database.class.php');	// Class para el manejo de base de datos
		include_once('../includes/databaseconnection.php');	// Conexi�n a base de datos
		
// --------------------
// INICIO CONTENIDO
// --------------------

	// trEmailClick.php
	//		Aplicaci�n que escribe en BD el click generado por el usuario en el email y,
	//  	a partir de la secci�n cliqueada, redirige a un URL especifico.
	//		Esta versi�n, cuenta cada secci�n o click diferente de manera �nica.
	//		v20110905

	// ------------------------------------------------------------
	// INIT
	// ------------------------------------------------------------
			
		// CONTAINER
			$appcontainer = 1;
		
		// ERROR HANDLER
			$actionerrorid = 0;

		// DEFAULT
			$WebsiteHomeDefault = 'default.php'; 
	
		// REFERER ... just in case
			$referer = '';
			if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
	
		
				// SQL Injection Check: BEGIN
					$IsSQLInjection = 0;
					$IsSQLInjection = IsSQLInjection();
					if ($IsSQLInjection > 0) {
							$actionerrorid = 66;			
					}
					// IF SQL Injection, STOP EXECUTION					
					if ($actionerrorid == 66) {
						
						// Redirect to default...
						//header("Refresh: 0;url=$WebsiteHomeDefault");
						echo $WebsiteHomeDefault;
						exit();
						
					}
				// SQL Injection Check: END
				
				
		// QUERYSTRING CHECK
			$QueryStringHeader = '';
			if (isset($_SERVER['QUERY_STRING'])) { $QueryStringHeader = trim(urldecode($_SERVER['QUERY_STRING'])); }
			
			// IF NO QueryString, STOP EXECUTION					
			if ($QueryStringHeader == '') {
							
				// Redirect to default...
				//header("Refresh: 0;url=$WebsiteHomeDefault");
				echo $WebsiteHomeDefault;
				exit();
							
			}



	// ------------------------------------------------------------
	// PARAMETERS [QUERYSTRING]
	// ------------------------------------------------------------

			// VARIABLE INIT
				$AppId			  = '';
				$ActionType		  = 'NA';
				$AffiliationId	  = '0';
				$AffiliationEmail = '';
				$EmailCampaignId  = '0';
				$EmailSentId	  = '0';
				$EmailSectionId   = '1';
				$AppLocalPath 	  = '';
				$AppLocalPath 	  = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
			
			// GET PARAMS
				if (isset($_GET['oid']))	{ $AppId = trim(strtolower($_GET['oid'])); }
				if (isset($_GET['tid']))	{ $ActionType = trim(strtolower($_GET['tid'])); }
				if (isset($_GET['aid']))	{ $AffiliationId = $_GET['aid']; }
				if (isset($_GET['r']))		{ $AffiliationEmail = trim(strtolower($_GET['r'])); }
				if (isset($_GET['cid']))	{ $EmailCampaignId = $_GET['cid']; }
				if (isset($_GET['eid']))	{ $EmailSentId = $_GET['eid']; }
				if (isset($_GET['sid']))	{ $EmailSectionId = trim($_GET['sid']); }
	
			// PARAM CHEK
		  		if ($AffiliationId == '|AID|' || $AffiliationId == '') { $AffiliationId  = '0'; }
			    	if (!is_numeric($AffiliationId)) { $AffiliationId = '0'; }
		   		if ($AffiliationEmail == '|EMAIL|' || $AffiliationEmail == '|email|') { $AffiliationEmail  = ''; }
		   			$AffiliationEmail = str_replace("'", '', $AffiliationEmail);
					
		  		if ($EmailCampaignId == '|CID|' || $EmailCampaignId == '') { $EmailCampaignId  = '0'; }
					if (!is_numeric($EmailCampaignId))	 { $EmailCampaignId = '0'; }
		   		if ($EmailSentId == '|EID|' || $EmailSentId == '') { $EmailSentId  = '0'; }
					if (!is_numeric($EmailSentId))		 { $EmailSentId = '0'; }
		   		if ($EmailSectionId == '|SID|' || $EmailSectionId == '') { $EmailSectionId  = '1'; }
					if (!is_numeric($EmailSectionId)) 	 { $EmailSectionId = '1'; }				



	// ------------------------------------------------------------
	// EMAIL UNSUSCRIBE PROCESS
	// ------------------------------------------------------------

			$EmailVerify = '';
			if (isset($_GET['emailverify'])) { $EmailVerify = $_GET['emailverify']; }
			if (strpos($EmailVerify, "'") !== false) { $EmailVerify = ""; }
			// Si no hay email para verificar...
			if ($EmailVerify == '')  { $EmailVerify = $AffiliationEmail; }
			// Si no hay email inicial...
			if ($AffiliationEmail == '')  { $AffiliationEmail = $EmailVerify; }
	
			$action = '';
			if (isset($_GET['action'])) { $action = strtolower($_GET['action']); }
			
	
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
				$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
									'1', 
									'".$configuration['appkey']."', 
									'view', 
									'crm', 
									'0', 
									'Interactions', 
									'Website';";
				$dbconnection->query($query);
				$items = $dbconnection->count_rows();
				if ($items > 0) {
					$my_row=$dbconnection->get_row();
					$WebsiteHome 	= trim($my_row['ParameterValue']);
					if ($WebsiteHome == '')  { $WebsiteHome = $WebsiteHomeDefault; }
					if ($WebsiteHome == '#') { $WebsiteHome = $WebsiteHomeDefault; }
				}




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo $instancelastname; ?> | Unsuscribe</title>
    <link href="../style.css" rel="stylesheet" type="text/css" />    
        <style type="text/css">
        <!--
		
        .style2 {font-size: 18pt}
        .style3 {font-size: 11pt}
		body { background-color:#F0F0F0; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; }
		td { color: #333333; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 7.5pt; }
		input { color: #292929; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 7.5pt; }

        -->
        </style>
    <link rel="shortcut icon" href="../favicon.ico" />
</head>
<body>
<br>
<table width="80%" border="0" cellspacing="0" cellpadding="0" align="center" style="border:1px solid #ADB1BD;" bgcolor="#FFFFFF">
    <tr> 
      <td bgcolor="#0072C6" valign="top" style="border-bottom:1px solid #ADB1BD;">&nbsp;
      
      </td>
    </tr>  
    <tr> 
      <td bgcolor="#FFFFFF" valign="top">

		<br>

        <table border="0" cellpadding="5" cellspacing="0" width="90%" align="center">
          <tr>
          <td valign="top" width="100%">
        
        
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
              <td><img src="../images/spacer.gif" height="10" width="1" alt=""></td></tr>
              <tr>
              <td align="left">
        <!-- INICIO CUERPO DEL REPORTE -->	  
        
            <?php 
            // Si es INICIO...
            if ($action !== "unsuscribe") {
            ?>
            
                  <br>
                  <span class="style2">Unsuscribe o Baja</span>
                  <br><br><br>
                  En <strong><?php echo $instancelastname; ?></strong> respetamos tu privacidad, tu cuenta de e-mail es usada para enviarte informaci&oacute;n y beneficios del programa.
                  <br><br>
                  Para darte de baja de nuestras comunicaciones, por favor, confirma tu e-mail.
                  <br><br>
                  Darte de baja de nuestras comunicaciones puede tardar hasta 48 horas.
                  <br><br>
                  Gracias por tu confianza en nosotros.
                  <br><br><br>
                  <span class="style3"><strong><?php echo $instancelastname; ?></strong></span>
                  
                  <br><br><br>
                  <table border="0">
                  <tr>
                    <td>
                    <br>
                    <br>
            
                           <form method="get" action="" name="unsuscribeform">
                            <input name="action" type="hidden" value="unsuscribe" />
                            <input name="r" type="hidden" value="<?php echo $AffiliationEmail; ?>" />
                            <input name="aid" type="hidden" value="<?php echo $AffiliationId; ?>" />
                            <input name="eid" type="hidden" value="<?php echo $EmailSentId; ?>" />
                            <input name="cid" type="hidden" value="<?php echo $EmailCampaignId; ?>" />
                               <table border='0' cellpadding='5' cellspacing='1' class='secTableBG'>
                                <tr class="answerCellBG">
                                 <td align="left"><strong>E-mail:</strong></td>
                                </tr>
                                <tr class="answerCellBG">
                                 <td align="left"><input name="emailverify" type="text" size="50" value="<?php echo $EmailVerify; ?>" /></td>
                                </tr>
                                <tr class="answerCellBG">
                                 <td align="right"><input name="submitbutton" type="submit" value="Deseo darme de Baja" /></td>
                                </tr>
                               </table>
                           </form>
        
                    </td>
                  </tr>
                </table>
                  
            <?php
            } else {
            // SI es UNSUSCRIBE...
            
					$items = 0;
					$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_AffiliationItemManage 
											'unsuscribe', 
											'crm', 
											'1', 
											'".$configuration['appkey']."', 
											'".$AffiliationId."', 
											'cardnumber', 
											'password', 
											'name', 
											'lastname', 
											'maidenname', 
											'birthdate', 
											'0', 
											'0', 
											'0', 
											'0', 
											'', 
											'".$EmailVerify."';";
					$dbconnection->query($query);									
					$items = $dbconnection->count_rows();
					if ($items > 0) {
						$my_row=$dbconnection->get_row();
						$AffiliationEmail 		= trim($my_row['Email']);
						$AffiliationCardNumber	= trim($my_row['Tarjeta']);
						$AffiliationFullName 	= trim($my_row['Nombre']);
						$AffiliationName 		= trim($my_row['NombreParcial']);
					}
			
                // EMAIL NOTIFY
                    // Enviamos el email de notificaci�n y confirmaci�n de la baja
                
            ?>
            
                  <br>
                  <span class="style2">Unsuscribe o Baja</span>
                  <br><br><br>
                  El e-mail <strong><?php echo $EmailVerify; ?></strong> est&aacute; siendo procesado para no recibir nuestras comunicaciones.
                  <br><br>
                  Darte de baja de nuestras comunicaciones puede tardar hasta 48 horas.
                  <br><br>
                  Gracias.
                  <br><br><br>
                  <span class="style3"><strong><?php echo $instancelastname; ?></strong></span>
                  
                  <br><br>
            
            <?php 
                }
            ?>
        
              <br>
        <!-- FIN CUERPO DEL REPORTE -->	  
             	</td>
              </tr>
            </table>
        
        
          </td>
          </tr>
          <tr>
              <td align="right">
	            <img src="../images/trademark.png" border="0">
              </td>
          </tr>
          <tr>
              <td>&nbsp;
                
              </td>
          </tr>
        </table>


	  </td>
	</tr>
</table>
<br />
    <table class="headerfooter">
      <tr>
        <td align="center"><?php echo $configuration['appcopyright']; ?></td>
      </tr>
      <tr>
        <td align="center" height="48">
        <img src="../images/FooterLogoOrigis2.png" alt="Copyright" />
        <!--<img src="images/spacer.gif" alt="Spaces" width="40" />
        <img src="images/FooterLogoOrigisLoyalty2.png" alt="Copyright" />-->
        </td>
      </tr>
      <tr>
        <td align="center" class="textInvisible"><br /><?php echo session_id()." - ".date('Ymd His'); ?></td>
      </tr>
    </table>
<br>
<br>
</body>
</html>
