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
			
			$cardnumber = "";
			if (isset($_GET['cardnumber'])) {
				$cardnumber = setOnlyText($_GET['cardnumber']);
				//$cardnumber = setOnlyNumbers($_GET['cardnumber']);
				//if (isValidNumber($cardnumber, "EAN13") == 0) {
				//	$actionerrorid = 2;
				//	$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
				//}
				if ($cardnumber == "") {
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
				}
	//		} else {
	//			$actionerrorid = 1;
			}
	
		// itemtype
			$itemtype = 'bonusfree';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonusfree'; }
			}
			$itemtype = strtolower($itemtype);

		// ticket params
			$connectionid = "0";
			if (isset($_GET['connectionid'])) {
				$connectionid = setOnlyNumbers($_GET['connectionid']);
				if ($connectionid == "") { $connectionid = "0"; }
			}
			$itemsku = "";
			if (isset($_GET['itemsku'])) {
				$itemsku = setOnlyNumbers($_GET['itemsku']);
			}
	
			$casemonitor = "";
			if (isset($_SESSION[$configuration['appkey']]['email'])) {
				$casemonitor = $_SESSION[$configuration['appkey']]['email'];			
			}
		
		

		// AFFILIATIONSEARCH
				// Obtengo el registro del afiliado
				$items = 0;
				$query  = "EXEC dbo.usp_app_AffiliationItem 
									'0', '".$cardnumber."';";
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows();
				if ($items > 0) {
					$my_row=$dbtransactions->get_row();
					$actionerrorid 		= $my_row['Error']; 
				} else {
					$actionerrorid = 2;
				}


	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();

		if(WithoutSelectionValue(document.orveefrmhelpdesk.connectionid))
			{ errormessage += "\n- Selecciona la cadena para actualizar!."; }

		if(WithoutSelectionValue(document.orveefrmhelpdesk.itemsku))
			{ errormessage += "\n- Selecciona el artículo a actualizar!."; }

		if(WithoutContent(document.orveefrmhelpdesk.units.value))
			{ errormessage += "\n- Selecciona las unidades o cajas a actualizar!."; }
			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para enviar la información, por favor: ' + errormessage);
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
                <input name="s" type="hidden" value="bonusrecord" />
                <input name="a" type="hidden" value="update" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="n" type="hidden" value="0" />
                <input name="cardnumber" type="hidden" value="<?php echo $cardnumber; ?>" />
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
                                Historial Actualizaci&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Tarjeta<br />
                    <span class="textMedium"> 
                    <?php echo $cardnumber; ?>
                    </span><br />
                    <span class="textHint"> &middot; Tarjeta a la cual se agregar&aacute; el ticket.</span>
                    </td>
                  </tr>
 
                   <tr>
                    <td>
                    Actualizaci&oacute;n<br />
                    <input name="itemtype" type="radio" value="freeze"  checked="checked" />&nbsp;Quitar
                    	<span style="font-style:italic;font-size:9px;">
                    	&nbsp;[Las cajas o el historial ser&aacute;n bloqueados]
                        </span><br />
					<input name="itemtype" type="radio" value="release" />&nbsp;Liberar
                    	<span style="font-style:italic;font-size:9px;">
                   		&nbsp;[Las cajas o el historial ser&aacute;n liberados para obtener una nueva bonificaci&oacute;n]
                        </span><br />
                    <span class="textHint"> &middot; Tipo de actualizaci&oacute;n al historial.</span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Cadena<br />
                    <select name="connectionid" id="connectionid" class="selectrequired">
 	                    <option value="">[Seleccione Cadena]</option>
                       <?php
					   		$items = 0;
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'HelpDeskConnectionsCardNumber', '".$connectionid."', '".$cardnumber."';";
                            $dbtransactions->query($query);
							$items = $dbtransactions->count_rows();
                            while($my_row=$dbtransactions->get_row()){ 
                                if ($my_row['ItemIsSelected'] == 1) {
                                    echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
                                } else {
                                    echo "<option value='".$my_row['ItemId']."'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
                                }
                            }
                        ?>
                    </select>
                    <?php if ($items == 0) { ?>
                    	<span style="color:#F90920;font-style:italic;">
                        	&middot; Sin Cadenas !!!
                        </span>
					<?php } ?>
                    <br />
                    <span class="textHint"> 
                    &middot; Cadena o conexi&oacute;n de la actualizaci&oacute;n.<br />
                    </span>
                    </td>
                  </tr>
 
                  <tr>
                    <td>
                    Art&iacute;culo<br />
                    <select name="itemsku" id="itemsku" class="selectrequired" style="font-size:9px;" >
                        <option value="">[Seleccione Artículo]</option>
                        <?php
							$items = 0;
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'HelpDeskItemsCardNumber', '".$itemsku."', '".$cardnumber."';";
                            $dbtransactions->query($query);
							$items = $dbtransactions->count_rows();
                            while($my_row=$dbtransactions->get_row()){ 
                                if ($my_row['ItemIsSelected'] == 1) {
                                    echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                    echo "&nbsp;".$my_row['Item']."&nbsp;".$my_row['ItemDetail']."</option>";
                                } else {
                                    echo "<option value='".$my_row['ItemId']."'>";
                                    echo "&nbsp;".$my_row['Item']."&nbsp;".$my_row['ItemDetail']."</option>";
                                }
                            }
                        ?>
                    </select>
                    <?php if ($items == 0) { ?>
                    	<span style="color:#F90920;font-style:italic;">
                        	&middot; Sin Art&iacute;culos !!!
                        </span>
					<?php } ?>
                    <br />
                    <span class="textHint"> &middot; SKU o Art&iacute;culo a actualizar.</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Unidades<br />
                    <select name="units" id="units" class="selectrequired">
	                    <option value="">[Seleccione Unidades]</option>
						<?php
							for ($i=1;$i<10;$i++) {
								if ($i == 1) {
                                    echo "<option value='".$i."' selected>";
									echo "".$i." unidad o caja</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." unidades o cajas</option>";
								}
							}
                        ?>
	                    <option value="99">Un Ciclo Bonificaci&oacute;n</option>
	                    <option value="999">TODO</option>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Cantidad de art&iacute;culos a actualizar.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Monitor<br />
					<input name="casemonitor" id="casemonitor" type="text" class="inputtextrequired" size="80" value="<?php echo $casemonitor; ?>" /><br />
                        <span class="textHint">
                        &middot; Emails para dar seguimiento al caso.<br />
                        &middot; Separado por comas [email@dominio.com, email@dominio.com,...].<br />
                        </span>
                    </td>
                  </tr>

                   <tr>
                    <td>
                    Notas<br />
                    <textarea name="casenotes" id="casenotes" cols="80" rows="5" title="Notas" maxlength="250" style="font-size:10px;"></textarea><br />
                    <span class="textHint"> &middot; Notas u observaciones de la actualizaci&oacute;n.</span>                            
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

