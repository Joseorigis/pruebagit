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

		// itemtype
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);

		// item
			$item = '';
			if (isset($_GET['item'])) {
				$item = setOnlyNumbers($_GET['item']);
			}

		// itembrand
			$itembrand = '';
			if (isset($_GET['itembrand'])) { 
				$itembrand = setOnlyText($_GET['itembrand']);
				//if ($itembrand == '') 
				//	{ $actionerrorid = 2; }
			} 

		// connection
			$connection = 0;
			if (isset($_GET['connection'])) {
				$connection = setOnlyNumbers($_GET['connection']);
				if ($connection == '') { $connection = 0; }
			}
			if ($connection == 0) {
				$connection = 1;
			}

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();

		if(WithoutContent(document.orveefrmrule.itemsku.value))
			{ errormessage += "\n- Ingresa el código del artículo!."; }

		if(WithoutContent(document.orveefrmrule.itemname.value))
			{ errormessage += "\n- Ingresa la descripción del artículo!."; }

		if(WithoutContent(document.orveefrmrule.itembrand.value))
			{ errormessage += "\n- Ingresa la marca del artículo!."; }

			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para agregar el articulo, por favor: ' + errormessage);
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

                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="bonusitem" />
                <input name="a" type="hidden" value="add" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagerules.png" alt="Rules Status" title="Rules Status" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Nueva Art&iacute;culo [Regla Bonificaci&oacute;n]
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culo C&oacute;digo<br />
                    <input name="itemsku" id="itemsku" type="text" class="inputtextrequired" max="13" value="<?php echo $item; ?>" /><br />
                    <span class="textHint"> 
                        &middot; SKU o c&oacute;digo del art&iacute;culo.<br />
                        </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Art&iacute;culo Descripci&oacute;n<br />
                    <input name="itemname" id="itemname" type="text" class="inputtextrequired" size="50" max="250" value="" /><br />
                    <span class="textHint"> 
                   		&middot; Descripci&oacute;n o nombre del art&iacute;culo.<br />
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culo Marca<br />
                    <input name="itembrand" id="itembrand" type="text" class="inputtextrequired" value="<?php echo $itembrand; ?>" /><br />
                    <span class="textHint"> 
                   		&middot; Marca del art&iacute;culo.<br />
                   		&middot; e.g. SANOFI, LILLY, LIOMONT, etc.<br />
                        </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Art&iacute;culo Grupo<br />
                    <select name="connection" id="connection" class="selectrequired">
                        <?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'BonusItemsOwnersFilter','".$connection."';";
                            $dbtransactions->query($query);
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
                    </select><br />
                    <span class="textHint"> 
                    &middot; Grupo o Laboratorio para el art&iacute;culo.<br />
						<span style="color:#FC0A0E">
						&middot; Art&iacute;culos FAHORRO (NO MARCA PROPIA) poner HEB [20].<br />
						&middot; Art&iacute;culos MARZAM poner HEB [20].<br />
						</span>
                    </span>
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

