<?php 
/**
*
* rules_discounts_new.php
*
* Formulario para cargar regla de descuentos
*	+ Modificaciones 20170921. raulbg. Documentacion Inicial
*
* @version 		20170921.orvee
* @category 	rules
* @package 		orvee
* @author 		raulbg <raulbg@origis.com>
* @deprecated 	none
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
			$itemtype = 'discounts';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'discounts'; }
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
			if (isset($_GET['rulename'])) { 
				$rulename = setOnlyText($_GET['rulename']); 
				if ($rulename !== '') { $rulename = 'Copia de '.$rulename; }
			}

		// itemreward
			$itemreward = '';
			if (isset($_GET['itemreward'])) {
				$itemreward = setOnlyNumbers($_GET['itemreward']);
			}
		// rewardselect
			$rewardselect = 'discount';
			if (isset($_GET['rewardselect'])) {
				$rewardselect = setOnlyLetters($_GET['rewardselect']);
				if ($rewardselect == '') { $rewardselect = 'discount'; }
			}
			$rewardselect = strtolower($rewardselect);
		// list
			$rulelistid = '0';
			if (isset($_GET['rulelistid'])) {
				$rulelistid = setOnlyNumbers($_GET['rulelistid']);
				if ($rulelistid == '') { $rulelistid = '0'; }
			}
		// rangeperiod
			$rangeperiod = 'year';
			if (isset($_GET['rangeperiod'])) {
				$rangeperiod = setOnlyLetters($_GET['rangeperiod']);
				if ($rangeperiod == '') { $rangeperiod = 'year'; }
			}
			$rangeperiod = strtolower($rangeperiod);
		// rule params
			$itemrewarddiscount = "0";
			if (isset($_GET['rewarddiscount'])) {
				$itemrewarddiscount = setOnlyCharactersValid($_GET['rewarddiscount']);
				if ($itemrewarddiscount == '') { $itemrewarddiscount = '0'; }
			}
			$itemrewarddiscountref01 = "0";
			if (isset($_GET['rewarddiscountref01'])) {
				$itemrewarddiscountref01 = setOnlyCharactersValid($_GET['rewarddiscountref01']);
				if ($itemrewarddiscountref01 == '') { $itemrewarddiscountref01 = '0'; }
			}
			$itemrewarddiscountref02 = "0";
			if (isset($_GET['rewarddiscountref02'])) {
				$itemrewarddiscountref02 = setOnlyCharactersValid($_GET['rewarddiscountref02']);
				if ($itemrewarddiscountref02 == '') { $itemrewarddiscountref02 = '0'; }
			}
			$sameticket = '0';
			if (isset($_GET['sameticket'])) {
				$sameticket = setOnlyNumbers($_GET['sameticket']);
				if ($sameticket == '') { $sameticket = '0'; }
				if ($sameticket !== '0' && $sameticket !== '1')  { $sameticket = '0'; }
			}
			$rewarddayslimit = '9999';
			if (isset($_GET['rewarddayslimit'])) {
				$rewarddayslimit = setOnlyNumbers($_GET['rewarddayslimit']);
				if ($rewarddayslimit == '') { $rewarddayslimit = '9999'; }
			}


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
		var rulelist =  document.orveefrmrule.rulelist.value;
		var conntodas =  document.orveefrmrule.connection.value;
	
		if(WithoutContent(document.orveefrmrule.itemslist.value))
			{ errormessage += "\n- Ingresa el o los artículo(s) para la regla!."; }

		if(WithoutSelectionValue(document.orveefrmrule.units))
			{ errormessage += "\n- Selecciona las unidades requeridas!."; }
			
		if((itemrewarddiff) && WithoutContent(document.orveefrmrule.itemrewardlist.value))
			{ errormessage += "\n- Ingresa el o los artículos a la regla!."; }
	
		if(WithoutSelectionValue(document.orveefrmrule.connection) || conntodas == 0)
			{ errormessage += "\n- Selecciona la cadena!."; }
	
		if((rulelist == '1') && WithoutSelectionValue(document.orveefrmrule.rulelistid))
			{ errormessage += "\n- Selecciona una lista de afiliados para la regla!."; }

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
				{ errormessage += "\n- El monto de la recompensa debe ser diferente a cero!."; }
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
                        &middot; Despu&eacute;s podr&aacute;s agregar m&aacute;s art&iacute;culos.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Unidades<br />
                    <select name="units" id="units" class="selectrequired">
							<option value="0">[Sin Acumular]</option>                   
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
                    <div style="padding-left:50px;">
                    Unidades Acumulaci&oacute;n
                    <div class="fieldrequired">
                    <input name="sameticket" type="radio" id="sameticketno" value="0" <?php if ($sameticket == '0') { echo 'checked="checked"'; } ?> />&nbsp;Ordinaria (M&uacute;ltiples Tickets)<br />
					<input name="sameticket" type="radio" id="sameticketyes" value="1" <?php if ($sameticket == '1') { echo 'checked="checked"'; } ?> />&nbsp;Mismo Ticket
                    </div>
                    </div>
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
                    Regla Descuento<br />
                    <div class="fieldrequired">
                    <input name="rewardselect" type="radio" id="rewardselectdiscount" value="discount" <?php if ($rewardselect == 'discount') { echo 'checked="checked"'; } ?> />&nbsp;Descuento [Beneficio] [discount]<br />
					<input name="rewardselect" type="radio" id="rewardselectamountbyprice" value="amountbyprice" <?php if ($rewardselect == 'amountbyprice') { echo 'checked="checked"'; } ?> />&nbsp;Monto desde Precio [Beneficio / Precio] [amountbyprice]<br />
					<input name="rewardselect" type="radio" id="rewardselectamountbyreference" value="amountbyreference" <?php if ($rewardselect == 'amountbyreference') { echo 'checked="checked"'; } ?> />&nbsp;Monto desde Referencia [Beneficio / Referencia] [amountbyreference]<br />
					<input name="rewardselect" type="radio" id="rewardselectamountfromprice" value="amountfromprice" <?php if ($rewardselect == 'amountfromprice') { echo 'checked="checked"'; } ?> />&nbsp;Monto menos Precio [Precio-Beneficio / Precio] [amountfromprice]<br />
                    <input name="rewardselect" type="radio" id="rewardselectdiscountwithbase" value="discountwithbase" <?php if ($rewardselect == 'discountwithbase') { echo 'checked="checked"'; } ?> />&nbsp;Descuento con Base [Precio * Beneficio] [discountwithbase]<br />
                    </div>
					<br />
                        <span class="textHint">
                        &middot; Regla o formula para otorgar el beneficio.<br />
                        </span>
						</div>
                    </td>
                  </tr>

                  <tr>
                    <td>
                     <div style="padding-left:50px;">
                      Recompensa Monto<br/>
                    <input name="unitsrewarddiscount" id="unitsrewarddiscount" type="text" class="inputtextrequired" value="<?php echo $itemrewarddiscount; ?>" /><br />
                    <span class="textHint">
                    &middot; Recompensa o Beneficio a entregar, acorde a la regla o formula.<br />
                    &middot; Para descuentos, ponerlo en decimales (e.g. 50% = 0.5).<br />
                    &middot; Para montos, ponerlo sin formato (e.g. 1256.49).<br />
                    </span>
						</div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                     <div style="padding-left:100px;">
                      Recompensa Referencia 1<br/>
                    <input name="itemrewardreference01" id="itemrewardreference01" type="text" class="inputtext" size="50" value="<?php echo $itemrewarddiscountref01; ?>" /><br />
                    <span class="textHint">
                    &middot; Referencia para la recompensa a entregar, acorde a la regla o formula [OPCIONAL].
                    </span>
						</div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                     <div style="padding-left:100px;">
                      Recompensa Referencia 2<br/>
                    <input name="itemrewardreference02" id="itemrewardreference02" type="text" class="inputtext" size="50" value="<?php echo $itemrewarddiscountref02; ?>" /><br />
                    <span class="textHint">
                    &middot; Referencia para la recompensa a entregar, acorde a la regla o formula [OPCIONAL].
                    </span>
						</div>
                    </td>
                  </tr>
                                  
                                   
                  <tr>
                    <td>
                    Recompensa Art&iacute;culo<br />
                    <div class="fieldrequired">
                    <input name="itemreward" type="radio" id="itemrewardsame" value="9999" <?php if ($itemreward == '') { echo 'checked="checked"'; } ?> />&nbsp;Mismo(s) Art&iacute;culo(s)<br />
					<input name="itemreward" type="radio" id="itemrewarddiff" value="0" <?php if ($itemreward !== '') { echo 'checked="checked"'; } ?> />&nbsp;Diferente(s) Art&iacute;culo(s):&nbsp;<input name="itemrewardlist" id="itemrewardlist" type="text" class="inputtext" size="50" max="100" value="<?php echo $itemreward; ?>" />
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
									echo "".$i." unidades de ".strtolower($itemtypedesc)."</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." unidades de ".strtolower($itemtypedesc)."</option>";
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
						<div style="padding-left:50px;">
						L&iacute;mite Rango Tiempo<br />
						<div class="fieldrequired">
						<input name="rangeperiod" type="radio" id="rangeperiod" value="year" <?php if ($rangeperiod == 'year') { echo 'checked="checked"'; } ?> />&nbsp;Anual<br />
						<input name="rangeperiod" type="radio" id="rangeperiod" value="semester" <?php if ($rangeperiod == 'semester') { echo 'checked="checked"'; } ?> />&nbsp;Semestral<br />
						<input name="rangeperiod" type="radio" id="rangeperiod" value="month" <?php if ($rangeperiod == 'month') { echo 'checked="checked"'; } ?> />&nbsp;Mensual<br />
						<input name="rangeperiod" type="radio" id="rangeperiod" value="forever" <?php if ($rangeperiod == 'forever') { echo 'checked="checked"'; } ?> />&nbsp;Total o Para Siempre<br />
						<input name="rangeperiod" type="radio" id="rangeperiod" value="firsttime" <?php if ($rangeperiod == 'firsttime') { echo 'checked="checked"'; } ?> />&nbsp;Una Vez<br />
						</div>
						<br />
						<span class="textHint">
						&middot; Rango de tiempo para el l&iacute;mite de entrega de recompensas.<br />
						</span>
						</div>
                    </td>
                  </tr>
                 
                  <tr>
                    <td>
                    Cadena<br />
                    <select name="connection" id="connection" class="selectrequired">
 	                    <option value="">[Seleccione Cadena]</option>
                       <?php
					   		$items = 0;
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'RulesConnectionsListUsed', '".$connection."';";
                            $dbtransactions->query($query);
							$items = $items + $dbtransactions->count_rows();
                            while($my_row=$dbtransactions->get_row()){ 
                                if ($my_row['ItemIsSelected'] == 1) {
                                    echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
									$connection = '0';
                                } else {
                                    echo "<option value='".$my_row['ItemId']."'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
                                }
                            }
                        ?>
                        <?php 
							if ($items > 0) {
								echo "<option value='0'>";
								echo "&nbsp;------------------------------------------------------------------------------------</option>";
							}
						?>
                       <?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'RulesConnectionsList', '".$connection."';";
                            $dbtransactions->query($query);
 							$items = $items + $dbtransactions->count_rows();
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
	                    <option value="9999" <?php if ($rewarddayslimit == '9999') { echo 'selected="selected"'; } ?> >[Ilimitado]</option>
	                    <option value="15" <?php if ($rewarddayslimit == '15') { echo 'selected="selected"'; } ?> >15 días</option>
	                    <option value="30" <?php if ($rewarddayslimit == '30') { echo 'selected="selected"'; } ?> >30 días</option>
	                    <option value="60" <?php if ($rewarddayslimit == '60') { echo 'selected="selected"'; } ?> >60 días</option>
	                    <option value="90" <?php if ($rewarddayslimit == '90') { echo 'selected="selected"'; } ?> >90 días</option>
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
                    <input name="rulelist" type="radio" value="0" />&nbsp;Todos los afiliados<br />
					<input name="rulelist" type="radio" value="1" checked="checked" />&nbsp;Solo a estos afiliados o lista:&nbsp; 
                    	<select name="rulelistid" id="rulelistid" class="selectbasic">
	                    	<option value="" selected>[Selecciona Lista de Afiliados]</option>
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

