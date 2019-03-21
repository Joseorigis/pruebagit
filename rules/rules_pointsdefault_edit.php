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


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
		if ($requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}

	
	// POINTS DEFAULT SETTINGS
		$PointsDefault = "0";
		$PointsItemsOnly = 1;
		$actionerrorid = 0;
		
		$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."',
							'view', 
							'default', 
							'0',
							'',
							'0';";
		$dbtransactions->query($query);
		$items = $dbtransactions->count_rows();
		$my_row=$dbtransactions->get_row();
		$PointsDefault	 = $my_row['PointsEquivalence'];
		$PointsItemsOnly = $my_row['ItemsOnly'];
		$actionerrorid 	 = $my_row['Error']; 

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		if(WithoutSelectionValue(document.orveefrmrule.equivalence))
			{ errormessage += "\n- Selecciona una equivalencia!."; }
		if(NoneWithCheck(document.orveefrmrule.itemsonly))
			{ errormessage += "\n- Selecciona un tipo de aplicación!."; }
			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para actualizar la regla, por favor: ' + errormessage);
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
                <input name="s" type="hidden" value="pointsdefault" />
                <input name="a" type="hidden" value="update" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <input name="equivalencedefault" type="hidden" value="<?php echo $PointsDefault; ?>" />
                <input name="itemsonlydefault" type="hidden" value="<?php echo $PointsItemsOnly; ?>" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">Actualizar Regla Equivalencia Default</span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Acumulaci&oacute;n<br />
                    <select name="equivalence" id="equivalence" class="selectrequired">
	                    <option value="">[Selecciona un Factor]</option>
						<?php
							for ($i=0;$i<101;$i++) {
								if ($PointsDefault == $i) {
                                    echo "<option value='".$i."' selected='selected'>";
									echo "".$i."%</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i."%</option>";
								} 
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Factor DEFAULT de acumulaci&oacute;n en puntos.<br />
                    &middot; Porcentaje de conversi&oacute;n de dinero a puntos.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Aplicaci&oacute;n<br />
                    <div class="fieldrequired">
                    <input name="itemsonly" type="radio" value="1" <?php if ($PointsItemsOnly == 1) { echo 'checked="checked"'; } ?> />&nbsp;Solo Cat&aacute;logo Maestro<br />
                        <span class="textHint"> 
                        &middot; Solo art&iacute;culos dentro del Cat&aacute;logo Maestro y que NO tengan Equivalencia.<br />
                        </span>
					<input name="itemsonly" type="radio" value="0" <?php if ($PointsItemsOnly == 0) { echo 'checked="checked"'; } ?> />&nbsp;Fuera Cat&aacute;logo<br />
                        <span class="textHint"> 
                        &middot; Cualquier art&iacute;culo que NO tenga Equivalencia.<br />
                        </span>
					</div>
                    <span class="textHint"> 
                    &middot; Aplicar factor default a...<br />
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

