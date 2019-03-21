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
			$itemtype = 'connectionsstore';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'connectionsstore'; }
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

			// Connecting to database CATALOGs
			$dbconnectionalternate = new database($configuration['db1type'],
								$configuration['db1host'], 
								$configuration['db1name'],
								$configuration['db1username'],
								$configuration['db1password']);		


					// GET RECORDS...
					$items = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsAddressManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'view', 
										'".$itemtype."', 
										'".$connectionid."',
										'stores',
										'',
										'".$actionauth."',
										'".$itemid."';";
					$dbtransactions->query($query);
					$items = $dbtransactions->count_rows(); 	// Total de elementos
					if ($items > 0) {
						$my_row=$dbtransactions->get_row();
						//$actionerrorid 		= $my_row['Error']; 
						$connectionname	= $my_row['ConnectionName']; 
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
			{ errormessage += "\n- Ingrese un nombre de la sucursal!."; }
			

				if(document.orveefrmhelpdesk.zipcode.value != "") {
					if(document.orveefrmhelpdesk.zipcode.value.length < 5)
						{ errormessage += "\n- El código postal debe tener 5 dígitos!."; }
					if(document.orveefrmhelpdesk.zipcode.value.length > 5)
						{ errormessage += "\n- Ingrese un código postal válido!."; }
				}
				if(WithoutSelectionValue(document.orveefrmhelpdesk.state))
					{ errormessage += "\n- Seleccione un estado!."; }
				if(WithoutContent(document.orveefrmhelpdesk.colony.value))
					{ errormessage += "\n- Ingrese una colonia!."; }
				if(WithoutContent(document.orveefrmhelpdesk.city.value))
					{ errormessage += "\n- Ingrese una ciudad o población!."; }
				if(WithoutContent(document.orveefrmhelpdesk.county.value))
					{ errormessage += "\n- Ingrese un municipio o delegación!."; }
				if(WithoutContent(document.orveefrmhelpdesk.zipcode.value))
					{ errormessage += "\n- Ingrese un código postal!."; }
				if(WithoutContent(document.orveefrmhelpdesk.address.value))
					{ errormessage += "\n- Ingrese una calle y número!."; }

	
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para editar la sucursal, por favor: ' + errormessage);
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
                <input name="s" type="hidden" value="connectionsstore" />
                <input name="a" type="hidden" value="update" />
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
                                Editar Sucursal
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>

                  <tr>
                    <td>

                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/bulletleft.png" />&nbsp;
                            <a href="?m=helpdesk&s=connections&a=view&n=<?php echo $connectionid; ?>&t=connections">Regresar a Conexi&oacute;n</a>
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
                    Sucursal<br />
                    <span class="textMedium"> 
                    <?php echo $my_row['StoreName']; ?> [<?php echo $my_row['StoreId']; ?>]
                    </span><br />
                    </td>
                  </tr>

                  <tr>
                    <td>
                     Nombre<br/>
                    <input name="addressname" id="addressname" type="text" size="50" class="inputtextrequired" value="<?php echo $my_row['StoreName']; ?>" /><br />
                    <span class="textHint">
                    &middot; Nombre de la sucursal.<br />
                    </span>
                    </td>
                  </tr>
                                   
                 <tr>
                    <td>
                    Direcci&oacute;n<br />
                     <span class="textHint">
                     &middot; Direcci&oacute;n completa de la sucursal.<br />
                     </span>
                        <table style="padding-left:40px;">
                              <tr>
                                <td>
                                Estado<br />
                                <select name="state" id="state" class="selectbasic">
                                    <option value="">[Seleccione un Estado]</option>
                                    <?php
										$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements 
																'AddressStates','".$my_row['StoreStateId']."';";
										$dbconnectionalternate->query($query);
										while($my_rowalternate=$dbconnectionalternate->get_row()){ 
											if ($my_rowalternate['ItemIsSelected'] == 1) {
												echo "<option value='".$my_rowalternate['ItemId']."' selected='selected'>";
												echo "&nbsp;".$my_rowalternate['Item']."</option>";
											} else {
												echo "<option value='".$my_rowalternate['ItemId']."'>";
												echo "&nbsp;".$my_rowalternate['Item']."</option>";
											}
										}
                                    ?>
                                </select><br />
                                <span class="textHint"> &middot; Estado de la direcci&oacute;n.</span>
                                </td>
                             </tr>
                             <tr>
                                <td>
                                Calle y N&uacute;mero<br />
                                <input name="address" id="address" type="text" class="inputtext" size="50" value="<?php echo trim($my_row['StoreAddress']); ?>" /><br />
                                <span class="textHint"> &middot; Calle y n&uacute;mero exterior e interior de la direcci&oacute;n.</span>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                Colonia<br />
                                <input name="colony" id="colony" type="text" class="inputtext" size="50" value="<?php echo trim($my_row['StoreColony']); ?>" /><br />
                                <span class="textHint"> &middot; Colonia de la direcci&oacute;n.</span>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                Ciudad<br />
                                <input name="city" id="city" type="text" class="inputtext" size="50" value="<?php echo trim($my_row['StoreCity']); ?>" /><br />
                                <span class="textHint"> &middot; Ciudad o Poblaci&oacute;n de la direcci&oacute;n.</span>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                Municipio<br />
                                <input name="county" id="county" type="text" class="inputtext" size="50" value="<?php echo trim($my_row['StoreCounty']); ?>" /><br />
                                <span class="textHint"> &middot; Municipio o delegaci&oacute;n de la direcci&oacute;n.</span>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                C&oacute;digo Postal<br />
                                <input name="zipcode" id="zipcode" type="text" class="inputtext" size="10" value="<?php echo trim($my_row['StoreZipCode']); ?>" />&nbsp;&nbsp;&nbsp;<img src="images/bulletright.png" />&nbsp;<a href="http://www.correosdemexico.gob.mx/ServiciosLinea/Paginas/ccpostales.aspx" target="_blank">Buscador C&oacute;digos Postales</a><br />
                                <span class="textHint"> &middot; C&oacute;digo Postal de la direcci&oacute;n.</span>
                                </td>
                              </tr>                        
                        </table>
                        
                    </td>
                  </tr>
 
                  <tr>
                    <td>
                     Email<br/>
                    <input name="addressemail" id="addressemail" type="text" size="50" class="inputtextrequired" value="<?php echo $my_row['StoreEmail']; ?>" /><br />
                    <span class="textHint">
                    &middot; Email del Contacto.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                     Tel&eacute;fono<br/>
                    <input name="addressphone" id="addressphone" type="text" class="inputtextrequired" value="<?php echo $my_row['StorePhone']; ?>" /><br />
                    <span class="textHint">
                    &middot; Tel&eacute;fono del Contacto.<br />
                    </span>
                    </td>
                  </tr>
                  
                   <tr>
                    <td>
                    Ubicaci&oacute;n<br/>
                    <input name="addresslatitude" id="addresslatitude" type="text" class="inputtextrequired" value="<?php echo $my_row['StoreLatitude']; ?>" />, 
                    <input name="addresslongitude" id="addresslongitude" type="text" class="inputtextrequired" value="<?php echo $my_row['StoreLongitude']; ?>" /><br />
                    <span class="textHint">
                    &middot; Ubicaci&oacute;n de la Sucursal.<br />
                    &middot; e.g. 19.36192, -99.18272
                    </span>
                    </td>
                  </tr>
                 
                  <tr>
                    <td>
                     C&oacute;digo<br/>
                    <input name="storecode" id="storecode" type="text" class="inputtext" value="<?php echo $my_row['StoreCode']; ?>" /><br />
                    <span class="textHint">
                    &middot; C&oacute;digo de la sucursal.<br />
                    </span>
                    </td>
                  </tr>
                      
                       <tr>
                        <td>
                        Notas<br />
                        <textarea name="addressnotes" id="addressnotes" cols="80" rows="3" title="Notas del Contacto" maxlength="250" style="font-size:10px;"></textarea><br />
                        <span class="textHint"> &middot; Notas u observaciones de la sucursal.</span>                            
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

