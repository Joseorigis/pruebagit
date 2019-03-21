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
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}
	

	// PARAMETER VALIDATION
		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}
			
		// itemtype
			$itemtype = 'connectionscontact';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'connectionscontact'; }
			}
			$itemtype = strtolower($itemtype);

		// connectionid ... in case off
			$connectionid = 0;
			if (isset($_GET['connectionid'])) {
				$connectionid = setOnlyNumbers($_GET['connectionid']);
				if ($connectionid == '') { $connectionid = 0; }
				if (!is_numeric($connectionid)) { $connectionid = 0; }
			}
			

		// CONNECTION SEARCH
			$connectionname			= '';


					// GET RECORDS...
					$items = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."', 
							'view', 
							'".$itemtype."', 
							'".$connectionid."';";
					$dbtransactions->query($query);
					$items = $dbtransactions->count_rows(); 	// Total de elementos
					if ($items > 0) {
						$my_row=$dbtransactions->get_row();
						//$actionerrorid 		= $my_row['Error']; 
						
						$connectionname 	= $my_row['ConnectionName']; 
						
					} else {
						$actionerrorid = 2;
					}


	// REFERER
		// Identificamos de donde viene... para regresarlo en caso de error
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

?>

<SCRIPT type="text/javascript">
<!--
	function CheckRequiredFields() {
		var errormessage = new String();
		
		if(WithoutContent(document.orveefrmhelpdesk.addressname.value))
			{ errormessage += "\n- Ingrese un nombre del contacto!."; }
			
		if(NoneWithCheck(document.orveefrmhelpdesk.addresssubtype))
			{ errormessage += "\n- Seleccione un tipo de contacto!."; }

		if(WithoutContent(document.orveefrmhelpdesk.addressemail.value) && 
			WithoutContent(document.orveefrmhelpdesk.addressphone.value) &&
			WithoutContent(document.orveefrmhelpdesk.addresscellphone.value))
			{ errormessage += "\n- Ingrese al menos un dato de contacto!."; }
		
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para agregar el contacto, por favor: ' + errormessage);
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
                <input name="s" type="hidden" value="connectionscontact" />
                <input name="a" type="hidden" value="add" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="connectionid" type="hidden" value="<?php echo $connectionid; ?>" />
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
                                Nuevo Contacto
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
                     Nombre<br/>
                    <input name="addressname" id="addressname" type="text" size="50" class="inputtextrequired" /><br />
                    <span class="textHint">
                    &middot; Nombre del Contacto.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                     Email<br/>
                    <input name="addressemail" id="addressemail" type="text" size="50" class="inputtextrequired" /><br />
                    <span class="textHint">
                    &middot; Email del Contacto.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                     Tel&eacute;fono<br/>
                    <input name="addressphone" id="addressphone" type="text" class="inputtextrequired" /><br />
                    <span class="textHint">
                    &middot; Tel&eacute;fono del Contacto.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                     Celular<br/>
                    <input name="addresscellphone" id="addresscellphone" type="text" class="inputtextrequired" /><br />
                    <span class="textHint">
                    &middot; Celular del Contacto.<br />
                    </span>
                    </td>
                  </tr>
                                   
                  <tr>
                    <td>
                    Tipo<br />
                    <div class="fieldrequired">
                    <input name="addresssubtype" id="addresssubtype" type="radio" value="settlementfiles" />&nbsp;Conciliaci&oacute;n<br />
					<input name="addresssubtype" id="addresssubtype" type="radio" value="soporte" />&nbsp;Soporte<br />
					<input name="addresssubtype" id="addresssubtype" type="radio" value="contacto" />&nbsp;Otro<br />
                    </div>
                        <span class="textHint">
                        &middot; Tipo de Contacto.<br />
                        </span>
                    </td>
                  </tr>
                  
                       <tr>
                        <td>
                        Notas<br />
                        <textarea name="addressnotes" id="addressnotes" cols="80" rows="3" title="Notas del Contacto" maxlength="250" style="font-size:10px;"></textarea><br />
                        <span class="textHint"> &middot; Notas u observaciones del contacto.</span>                            
                        </td>
                      </tr>
                                        
                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
                    </div>
                    </td>
                  </tr>
                </table>
				</form>
                
     
                    <br /><br />
                    
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

