<?php
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
* 	Descripción de la función.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la página es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=ISO-8859-15');
		// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
	}

// SCRIPT
	// Obtengo el nombre del script en ejecución
	$script = __FILE__;
	$camino = get_included_files();
	$scriptactual = $camino[count($camino)-1];
	

// CONTAINER CHECK
	// Si el llamado no viene del index o contenedor principal ...PAGE NOT FOUND
	if (!isset($appcontainer)) {
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
	} 


// --------------------
// INICIO CONTENIDO
// --------------------

			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');

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
			include_once("accessdenied.php"); 
			exit();
		}



	// PARAMETER VALIDATION
		// actionauth 
			if (isset($_GET['operation'])) {
				$actionauth = '';
				if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
				if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
				if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio
			}

		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}
			
		// itemtype
			$itemtype = 'connections';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'connections'; }
			}
			$itemtype = strtolower($itemtype);

		// action
			$operation = 'none';
			if (isset($_GET['operation'])) {
				$operation = setOnlyLetters($_GET['operation']);
				if ($operation == '') { $operation = 'none'; }
			}
			$operation = strtolower($operation);
			
		//settings
				$filessource = "";
				$ftpsource = "";
				$ftpprotocol = "";
				$ftpmultiple = "";
				$fileslayout = "";
				$filesfrequency = "";
				$filesformat = "";
				$filesmultiple = "";

			
		// CONNECTION SEARCH
			$connectionid  			= $itemid;
			$connectionname			= '';
			$connectionapp			= '';
			$connectionservice		= 'https://orbisws01.orbisfarma.com.mx/';
			$connectionlicenses		= 1;

		// SET ACTION...
			$items = 0;
			if ($operation !== 'none' && $actionerrorid == 0) {

					// SET RECORDS...
					$items = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."', 
							'edit".$operation."', 
							'".$itemtype."', 
							'".$itemid."',
							'".$actionauth."';";
					$dbtransactions->query($query);
					$items = $dbtransactions->count_rows(); 	// Total de elementos
					if ($items > 0) {
						$my_row=$dbtransactions->get_row();
						$actionerrorid 		= $my_row['Error']; 
						if ($actionerrorid == 0) {
							$connectionname 	= $my_row['ConnectionName']; 
						}
						
					} else {
						$actionerrorid = 2;
					}
				
			} else {

					// GET RECORDS...
					$items = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."', 
							'view', 
							'".$itemtype."', 
							'".$itemid."';";
					$dbtransactions->query($query);
					$items = $dbtransactions->count_rows(); 	// Total de elementos
					if ($items > 0) {
						$my_row=$dbtransactions->get_row();
						//$actionerrorid 		= $my_row['Error']; 
						
						$connectionname 	= $my_row['ConnectionName']; 
						$connectioncode 	= $my_row['ConnectionCode']; 
						$connectionapp		= $my_row['ConnectionApp']; 
						$connectionservice	= $my_row['ConnectionService']; 
						$connectionlicenses	= $my_row['ConnectionLicenses']; 

					} else {
						$actionerrorid = 2;
					}
					
			} // [if ($operation !== 'none' && $actionerrorid == 0)]
				

?>

<SCRIPT type="text/javascript">
<!--
	function setConnOrbisApp() {
	
		var isorbisapp = document.getElementById("orbisapp").checked;
		var licenses = document.getElementById("connectionlicenses").value;
		
		if (isorbisapp) {
			document.getElementById("connectionwebservice").value = 'https://orbiswsapp01.orbisfarma.com.mx/';
			if (licenses == 1)
				{ document.getElementById("connectionlicenses").value = '3'; }
		} else {
			document.getElementById("connectionwebservice").value = 'https://orbisws01.orbisfarma.com.mx/';
			document.getElementById("connectionlicenses").value = '1';
		}
		
		
	}

	function CheckRequiredFields() {
		var errormessage = new String();
		
		var isorbisapp = document.getElementById("orbisapp").checked;
		var licenses = document.getElementById("connectionlicenses").value;
		
		if(NoneWithCheck(document.orveefrmhelpdesk.connectionapp))
			{ errormessage += "\n- Seleccione la aplicación para la conexión!."; }

		if(WithoutContent(document.orveefrmhelpdesk.connectionwebservice.value))
			{ errormessage += "\n- Ingrese un web service para la conexión!."; }

			if  (isorbisapp == false && licenses > 1)
				{ errormessage += "\n- La aplicación para la conexión seleccionada solo puede usar una licencia!."; }
		
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para actualizar la conexion, por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			//document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Afiliación en proceso, por favor, espere un momento...</em>";
			
			return false;
			}
		//document.orveefrmuser.submit();
		return true;
	} // end of function CheckRequiredFields()

//-->
</SCRIPT>

<!-- MODULO: begin -->
<table class="template">
  <tr>
  	<td>

        <!-- MODULO HEADER:begin -->
			<?php require_once('headertitle.php') ; ?>
        <!-- MODULO HEADER:end -->

    <!-- MODULO CONTENIDO: begin -->
    <table class="template">
      <tr>


		    <!-- MODULO BODY: begin -->
        <td class="templatemainbody">
                <br />

                <form action="index.php" method="get" name="orveefrmhelpdesk" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="helpdesk" />
                <input name="s" type="hidden" value="connections" />
                <input name="a" type="hidden" value="updateapp" />
                <input name="n" id="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="t" id="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagesettings.png" alt="Help Desk" title="Help Desk" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Help Desk<br />
                                Editar Conexi&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Conexi&oacute;n<br />
                    <span class="textMedium"> 
                    <?php echo $connectionname; ?> [<?php echo $connectionid; ?>]
                    </span><br />
                    </td>
                  </tr>


                 <tr>
                    <td>
                    APP<br />
                    <div class="fieldrequired">
                    <input name="connectionapp" id="orbisapp" type="radio" value="orbisapp" onClick="setConnOrbisApp(this);" <?php if ($connectionapp == 'orbisapp') { echo "checked='checked'"; } ?> />&nbsp;OrbisApp<br />
					<input name="connectionapp" id="orbiswebservice" type="radio" value="orbiswebservice" onClick="setConnOrbisApp(this);" <?php if ($connectionapp == 'orbiswebservice') { echo "checked='checked'"; } ?> />&nbsp;Web Service<br />
					<input name="connectionapp" id="orbisoffline" type="radio" value="orbisoffline" onClick="setConnOrbisApp(this);" <?php if ($connectionapp == 'orbisoffline') { echo "checked='checked'"; } ?> />&nbsp;Offline<br />
                    </div>
                        <span class="textHint">
                        &middot; Aplicaci&oacute;n o forma de conexi&oacute;n.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Web Service<br />
                    <input name="connectionwebservice" id="connectionwebservice" type="text" class="inputtextrequired" size="80" value="<?php echo $connectionservice; ?>" />
                        <span class="textHint">
                        &middot; Servicio web o aplicaci&oacute;n de la conexi&oacute;n.<br />
                        </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Licencias<br />
                    <select name="connectionlicenses" id="connectionlicenses" class="selectrequired">
	                    <option value="">[Seleccione Licencias]</option>
						<?php
							for ($i=1;$i<10;$i++) {
								if ($i == $connectionlicenses) {
                                    echo "<option value='".$i."' selected>";
									echo "".$i." licencia</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." licencias</option>";
								}
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Cantidad de licencias disponibles para la conexi&oacute;n [ORBISAPP].<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Actualizar" />
                    </div>
                    </td>
                  </tr>
 
                </table>
				</form>
                
        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
					<!-- Incluimos el sidebar del modulo-->
                    <?php 
					// Armamos dinamicamente el nombre del sidebar
					$sidebarfile = $module."_sidebar.php";
					include($sidebarfile);
					?>
            
        </td>
            <!-- MODULO TOOLBAR: end -->

      </tr>
    </table>
    <!-- MODULO CONTENIDO: end -->

    
	<br />
	</td>
  </tr>
</table>
<!-- MODULO: end -->

