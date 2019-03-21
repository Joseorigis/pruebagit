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

					} else {
						$actionerrorid = 2;
					}
					
			} // [if ($operation !== 'none' && $actionerrorid == 0)]
				

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		if(WithoutSelectionValue(document.orveefrmhelpdesk.licenses))
			{ errormessage += "\n- Seleccione la cantidad de instalaciones!."; }
		
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
                <input name="a" type="hidden" value="updatecode" />
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
                      C&oacute;digo<br/>
                    <input name="connectioncode" id="connectioncode" type="text" class="inputtext" value="<?php echo $connectioncode; ?>" /><br />
                    <span class="textHint">
                    &middot; C&oacute;digo de referencia de la conexi&oacute;n.<br />
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

