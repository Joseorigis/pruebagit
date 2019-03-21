<?php 
// ***************************************************************************************
// Encadenar combo de itemtype1 con itembonus
// Uso o no el de itemowner?, o mejor marcas?, o como separo Sanofi, Astra, etc!!!
// Precheck de como se cargará la regla?
// ***************************************************************************************

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
		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}
				
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

		// units
			$units = 0;
			if (isset($_GET['units'])) {
				$units = setOnlyNumbers($_GET['units']);
				if ($units == '') { $units = 0; }
			}
		// unitsbonus
			$unitsreward = 1;
			if (isset($_GET['unitsreward'])) {
				$unitsreward = setOnlyNumbers($_GET['unitsreward']);
				if ($unitsreward == '') { $unitsreward = 1; }
			}
		// rangeto
			$rangeto = 9999;
			if (isset($_GET['rangeto'])) {
				$rangeto = setOnlyNumbers($_GET['rangeto']);
				if ($rangeto == '') { $rangeto = 9999; }
			}
		// connection
			$connection = 0;
			if (isset($_GET['connection'])) {
				$connection = setOnlyNumbers($_GET['connection']);
				if ($connection == '') { $connection = 0; }
			}
		// rulelistid
			$rulelistid = 0;
		// ruleactivation
			$ruleactivation = date('d/m/Y');
			if (isset($_GET['ruleactivation'])) {
				$ruleactivation = ($_GET['ruleactivation']);
				if (isValidDate($ruleactivation, "dd/mm/yyyy") == 0)
					{ $ruleactivation = date('d/m/Y'); }
			}
		// ruleexpiration
			$ruleexpiration = "31/12/2019";
			if (isset($_GET['ruleexpiration'])) {
				$ruleexpiration = ($_GET['ruleexpiration']);
				if (isValidDate($ruleexpiration, "dd/mm/yyyy") == 0)
					{ $ruleexpiration = "31/12/2019"; }
			}

		// rulename
			$rulename = '';
			if (isset($_GET['rulename'])) { $rulename = setOnlyText($_GET['rulename']); }


		// rule init values			
			$rulecode = createRandomString(8);
			if ($rulename == '') {
				$rulename = 'NuevaRegla'.strtoupper($itemtype).''.substr('0000'.rand(1,10), -4);
			}

		// ItemTypeDesc
			$itemtypedesc = "";
			if ($itemtype == 'bonus') { $itemtypedesc = 'Bonificacion'; }
			if ($itemtype == 'discounts') { $itemtypedesc = 'Descuento'; }


?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();

		// Item Bonus SKU List
		var itemrewarddiff =  document.getElementById('itemrewarddiff').checked;
		var conntodas =  document.orveefrmrule.connection.value;

		if(WithoutContent(document.orveefrmrule.itemslist.value))
			{ errormessage += "\n- Ingresa el o los artículo(s) para la regla!."; }

		if(WithoutSelectionValue(document.orveefrmrule.units))
			{ errormessage += "\n- Selecciona las unidades requeridas!."; }
			
		if((itemrewarddiff) && WithoutContent(document.orveefrmrule.itemrewardlist.value))
			{ errormessage += "\n- Ingresa el o los artículos a la regla!."; }

		if(WithoutContent(document.orveefrmrule.ruleactivation.value))
			{ errormessage += "\n- Selecciona un vigencia inicio!."; }

		if(WithoutContent(document.orveefrmrule.ruleexpiration.value))
			{ errormessage += "\n- Selecciona un vigencia final!."; }
			
		if(WithoutContent(document.orveefrmrule.rulename.value))
			{ errormessage += "\n- Ingresa un nombre para la regla!."; }
			
		<?php 
		if ($itemtype == 'discounts') {
		?>
			if(document.orveefrmrule.unitsrewarddiscount.value == "0")
				{ errormessage += "\n- El monto de la recompensa debe ser mayor a cero!."; }
		<?php	
		}
		?>
	
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para agregar la regla, por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			//document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Afiliación en proceso, por favor, espere un momento...</em>";
			
			return false;
			}
		//document.orveefrmuser.submit();
	
		// failsafe por activar a todas...
		if (conntodas == 1) {
			return confirm('La regla de activara a TODAS las cadenas. Confirmas que deseas activarla?');
		} else {
			return true;
		}
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
                <input name="s" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="a" type="hidden" value="add" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <input name="ruletype" type="hidden" value="ordinary" />
                <input name="sameticket" type="hidden" value="0" />
                <input name="rewardselect" type="hidden" value="last" />
                <input name="rangeperiod" type="hidden" value="year" />
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
                               <?php echo $itemtypedesc; ?><br />
                                Nueva Regla
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>&nbsp;
                    
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Art&iacute;culo<br />
                    <input name="itemslist" id="itemslist" type="text" class="inputtextrequired" size="50" max="100" value="<?php echo $item; ?>" /><br />
                    <span class="textHint"> 
                   		&middot; Art&iacute;culo para aplicar la regla.<br />
                        &middot; Lista de SKU o c&oacute;digos separados por comas y sin espacios.<br />
                        </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Unidades<br />
                    <select name="units" id="units" class="selectrequired">
						<?php
							if ($itemtype == 'bonus') { echo "<option value=''>[X]</option>"; }
							for ($i=1;$i<17;$i++) {
								if ($i == $units) {
                                    echo "<option value='".$i."' selected='selected'>";
									echo "".$i." unidades</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." unidades</option>";
								}
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Unidades requeridas para obtener recompensa o beneficio.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Recompensa / Beneficio<br />
                    <select name="unitsreward" id="unitsreward" class="selectrequired">
	                    <option value="0">[Y]</option>
						<?php
							for ($i=1;$i<17;$i++) {
								if ($i == $unitsreward) {
                                    echo "<option value='".$i."' selected='selected'>";
									echo "".$i." unidades de ".strtolower($itemtypedesc)."</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." unidades de ".strtolower($itemtypedesc)."</option>";
								}
							}
                        ?>
                    </select>
                    <br />
                    <span class="textHint"> 
                    &middot; Unidades a otorgar de recompensa o beneficio.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    <div style="padding-left:50px;">
                    Recompensa / Beneficio Descuento<br />
                    <select name="unitsrewarddiscount" id="unitsrewarddiscount" class="selectrequired">
	                    <option value="0">[No Aplica]</option>
						<?php
							$j = 0;
							for ($i=1;$i<11;$i++) {
								$j = $i*10;
                                    echo "<option value='".($j/10)."'>";
									echo "".$j."% descuento</option>";
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Descuento en vez de bonificaci&oacute;n. [MIFARMA]<br />
                    </span>
                    </div>
                    </td>
                  </tr>
                                   
                  <tr>
                    <td>
                    Recompensa Art&iacute;culo<br />
                    <div class="fieldrequired">
                    <input name="itemreward" type="radio" id="itemrewardsame" value="9999" checked="checked" />&nbsp;Mismo(s) Art&iacute;culo(s)<br />
					<input name="itemreward" type="radio" id="itemrewarddiff" value="0" />&nbsp;Diferente(s) Art&iacute;culo(s):&nbsp;<input name="itemrewardlist" id="itemrewardlist" type="text" class="inputtext" size="50" max="100"/>
                    </div>
					<br />
                        <span class="textHint">
                        &middot; Art&iacute;culo(s) a otorgar de recompensa o beneficio.<br />
                        &middot; Lista de SKU o c&oacute;digos separados por comas y sin espacios.<br />
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    L&iacute;mite<br />
                    <select name="rangeto" id="rangeto" class="selectrequired">
	                    <option value="9999">[Ilimitado]</option>
						<?php
							for ($i=1;$i<33;$i++) {
								if ($i == $rangeto) {
                                    echo "<option value='".$i."' selected='selected'>";
									echo "".$i." unidades de ".strtolower($itemtypedesc)." cada 12 meses</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." unidades de ".strtolower($itemtypedesc)." cada 12 meses</option>";
								}
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; L&iacute;mite de unidades de la recompensa.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Cadena<br />
                    <select name="connection" id="connection" class="selectrequired">
                        <?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'BonusConnectionsList','".$connection."';";
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
                    &middot; Cadena o Conexi&oacute;n para aplicar al regla.<br />
                    </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    <div style="padding-left:50px;">
                    Retenci&oacute;n Recompensa<br />
                    <select name="rewarddayslimit" id="rewarddayslimit" class="selectrequired">
	                    <option value="9999" selected>[Ilimitado]</option>
	                    <option value="15">15 días</option>
	                    <option value="30">30 días</option>
	                    <option value="60">60 días</option>
	                    <option value="90">90 días</option>
                    </select><br />
                    <span class="textHint"> 
                    &middot; D&iacute;as l&iacute;mite de retenci&oacute;n de la recompensa en caso de rechazo.<br />
                    </span>
                    </div>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Destinatarios<br />
                    <input name="rulelist" type="radio" value="0" checked="checked" />&nbsp;Todos los afiliados<br />
					<input name="rulelist" type="radio" value="1" />&nbsp;Solo a estos afiliados o lista:&nbsp; 
                    	<select name="rulelistid" id="rulelistid" class="selectbasic">
							<?php
                                // LISTS
								$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
																		'RulesAffiliationLists','".$rulelistid."';";
                                $dbtransactions->query($query);
                                while($my_row=$dbtransactions->get_row()){ 
									if ($my_row['ItemIsSelected'] == 1) {
										echo "<option value='".$my_row['ItemId']."' selected='selected'>";
										echo "&nbsp;[".$my_row['ItemId']."] ".urldecode($my_row['Item'])."</option>";
									} else {
										echo "<option value='".$my_row['ItemId']."'>";
										echo "&nbsp;[".$my_row['ItemId']."] ".urldecode($my_row['Item'])."</option>";
									}
                                } 
                            ?>
                        </select><br />
                        <span class="textHint">
                        &middot; Lista de afiliados beneficiarios de la regla.
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                      Vigencia Inicio<br/>
                      <div><input type="text" name="ruleactivation" id="ruleactivation" value="<?php echo $ruleactivation; ?>" class="inputtextrequired" readonly /></div>
			            <span class="textHint">
                    &middot; Fecha programada para inicio de la vigencia.</span></td>
                  </tr>
                  <tr>
                    <td>
                      Vigencia Fin<br/>
                      <div><input type="text" name="ruleexpiration" id="ruleexpiration" value="<?php echo $ruleexpiration; ?>" class="inputtextrequired" readonly /></div>
			            <span class="textHint">
                    &middot; Fecha programada para fin o termino de la vigencia.</span></td>
                  </tr>
                  
                  <tr>
                    <td>
                      C&oacute;digo<br/>
                    <input name="rulecode" id="rulecode" type="text" class="inputtext" value="<?php echo $rulecode; ?>" /><br />
                    <span class="textHint">
                    &middot; C&oacute;digo de referencia para la nueva regla.
                    </span></td>
                  </tr>
                  
                  <tr>
                    <td>
                      Nombre<br/>
                    <input name="rulename" id="rulename" type="text" class="inputtextrequired" size="50" value="<?php echo $rulename; ?>" /><br />
                    <span class="textHint">
                    &middot; Nombre para la nueva regla.
                    </span></td>
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
                
			<script type="text/javascript">
                var today = new Date();

                var dd = today.getDate();
                var mm = today.getMonth()+1; //January is 0!
                var yyyy = today.getFullYear();
                
                if(dd<10){dd='0'+dd}
                if(mm<10){mm='0'+mm} 
                today = dd+'/'+mm+'/'+yyyy;
            
                //http://jdpicker.paulds.fr/?p=demo
                $(document).ready(function(){
                    $('#ruleactivation').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        date_min:today
                    });
                    $('#ruleexpiration').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        date_min:today
                    });
            
                });
            </script>                
                
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

