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
		header("HTTP/1.0 404 Not Found"); 	
		exit();
	} 


// --------------------
// INICIO CONTENIDO
// --------------------

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

		// itemquery
			$itemquery = 'none';
			if (isset($_GET['q'])) {
				$itemquery = setOnlyLetters($_GET['q']);
				if ($itemquery == '') { $itemquery = 'none'; }
			}
			$itemquery = strtolower($itemquery);


		// itemslist
			$itemslist = '';
			if (isset($_GET['itemslist'])) {
				$itemslist = setOnlyNumbers($_GET['itemslist']);
				if (!is_numeric($itemslist)) { $itemid = ''; }
			}	


			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');


							// DATABASE TRANSACTIONS ALTERNATE
								// Connecting to database to TRANSACTIONS & POINTS
								$dbtransactionsalternate = new database($configuration['db2type'],
													$configuration['db2host'], 
													$configuration['db2name'],
													$configuration['db2username'],
													$configuration['db2password']);

				
				$records = 0;
				$query  = "EXEC dbo.usp_app_RulesDiscountsManage
									'".$_SESSION[$configuration['appkey']]['userid']."', 
									'".$configuration['appkey']."',
									'view', 
									'".$itemtype."', 
									'".$itemid."';";
				$dbtransactions->query($query);
				$records = $dbtransactions->count_rows();
				if ($records > 0) {
					$my_row=$dbtransactions->get_row();
					
					$itemid			 	= $my_row['RuleId']; 
					$actionerrorid 		= $my_row['Error']; 

				} else {
					$actionerrorid = 66;
				}
					
		// EDIT params...
				$ruleeditactive = 0;

				// rulelistid
					$rulelistid = "0";
					if (isset($my_row['RuleListId'])) {
						$rulelistid = $my_row['RuleListId'];
					}

				// ruleactivation
					$ruleactivation = date('d/m/Y');
					if (isset($my_row['RuleActivationDateEdit'])) {
						$ruleactivation = ($my_row['RuleActivationDateEdit']);
						if (isValidDate($ruleactivation, "dd/mm/yyyy") == 0)
							{ $ruleactivation = date('d/m/Y'); }
					}
				// ruleexpiration
					$ruleexpiration = "31/12/2019";
					if (isset($my_row['RuleExpirationDateEdit'])) {
						$ruleexpiration = ($my_row['RuleExpirationDateEdit']);
						if (isValidDate($ruleexpiration, "dd/mm/yyyy") == 0)
							{ $ruleexpiration = "31/12/2019"; }
					}




		// ItemTypeDesc
			$itemtypedesc = "";
			if ($itemtype == 'bonus') { $itemtypedesc = 'Bonificacion'; }
			if ($itemtype == 'discounts') { $itemtypedesc = 'Descuento'; }


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

		if(WithoutContent(document.orveefrmrule.n.value))
			{ errormessage += "\n- Ingresa el numero de regla."; }

		<?php 
		if ($itemquery == 'list') {
		?>
				if(WithoutSelectionValue(document.orveefrmrule.rulelistid))
					{ errormessage += "\n- Selecciona una lista de afiliados para la regla!."; }
		<?php	
		}
		?>
			   
		<?php 
		if ($itemquery == 'dates') {
		?>
				if(WithoutContent(document.orveefrmrule.ruleactivation.value))
					{ errormessage += "\n- Selecciona un vigencia inicio!."; }

				if(WithoutContent(document.orveefrmrule.ruleexpiration.value))
					{ errormessage += "\n- Selecciona un vigencia final!."; }
		<?php	
		}
		?>			   

		<?php 
		if ($itemquery == 'itemsnew') {
		?>
				if(WithoutContent(document.orveefrmrule.itemslist.value))
					{ errormessage += "\n- Ingresa el o los artículo(s) para la regla!."; }
				if(document.orveefrmrule.unitsrewarddiscount.value == "0")
					{ errormessage += "\n- El monto de la recompensa debe ser diferente a cero!."; }
		<?php	
		}
		?>			   

		<?php 
		if ($itemquery == 'itemsedit') {
		?>
				if(WithoutContent(document.orveefrmrule.itemslist.value))
					{ errormessage += "\n- Ingresa el o los artículo(s) para la regla!."; }
				if(document.orveefrmrule.unitsrewarddiscount.value == "0")
					{ errormessage += "\n- El monto de la recompensa debe ser diferente a cero!."; }
		<?php	
		}
		?>			   
	
		<?php 
		if ($itemquery == 'rewardparams') {
		?>
				if(document.orveefrmrule.unitsrewarddiscount.value == "0")
					{ errormessage += "\n- El monto de la recompensa debe ser diferente a cero!."; }
		<?php	
		}
		?>			   

		<?php 
		if ($itemquery == 'rewardselect') {
		?>
					if(NoneWithCheck(document.orveefrmrule.rewardselect))
						{ errormessage += "\n- Seleccione una regla / formula!."; }
		<?php	
		}
		?>			   
	
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
                
                
                   			<?php  if ($records == 0) { ?>
                            
                                    <table class="tablemessage">
                                      <tr>
                                        <td bgcolor="#FF0000">&nbsp;</td>
                                        <td bgcolor="#F0F0F0">			
                                                <br />
                                                <img src="images/security_firewall_off.png" alt="Error" />
                                                <br />
                                                <br />
                                                <span class="textMedium">Oooops!
                                                <br />
                                                La regla no fue encontrada!.</span>
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Por favor, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
                                                <br />
                                                <br />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <img src="images/bulletleft.png" />&nbsp;
                                                <a href="<?php echo $referer; ?>" title="Regresar">Regresar</a><br />
                                                <br />
                    
                                        </td>
                                      </tr>
                                    </table>
                                    
                   			<?php  } else { ?>
                           
                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="a" type="hidden" value="update" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="q" type="hidden" value="<?php echo $itemquery; ?>" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                                                             
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
                                <span class="textMedium">
                                Regla <?php echo $itemtypedesc; ?><br />
                                Editar Regla
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
                    Regla<br />
                    <span class="textMedium"><em>
					<?php echo $my_row['RuleKey']; ?><br />
					<?php echo $my_row['RuleName']; ?>                 
                    </em></span><br />
                    <span class="textHint"> &middot; Nombre y c&oacute;digo de la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Cadena<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]</em>
                    </span><br />
                    <span class="textHint"> 
                    &middot; Cadena o conexi&oacute;n de la regla.<br />
                    </span>
                    </td>
                  </tr>
                  
                  <?php 
						// ---------------------------		
						// EDITAR LISTA				   
						if ($itemquery == "list") { 
						// ---------------------------		
							$ruleeditactive = 1;
					?>
					  <tr>
						<td>
						Lista / Destinatarios<br />
						<input name="rulelist" type="hidden" value="1" />
							<select name="rulelistid" id="rulelistid" class="selectrequired">
								<option value="" selected>[Selecciona Lista de Afiliados]</option>

								<?php
									// LISTS
									$queryitems  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
																			'RulesAffiliationLists','".$rulelistid."';";
									$dbtransactionsalternate->query($queryitems);

									// Imprimimos en pantalla cada uno de los parámetros
									while($my_rowitems=$dbtransactionsalternate->get_row()){ 
										if ($my_rowitems['ItemIsSelected'] == 1) {
											echo "<option value='".$my_rowitems['ItemId']."' selected='selected'>";
											echo "&nbsp;[".$my_rowitems['ItemId']."] ".urldecode($my_rowitems['Item'])."</option>";
										} else {
											echo "<option value='".$my_rowitems['ItemId']."'>";
											echo "&nbsp;[".$my_rowitems['ItemId']."] ".urldecode($my_rowitems['Item'])."</option>";
										}
									} 
								?>

							</select><br />
							<span class="textHint">
							&middot; Lista de afiliados o destinatarios de la regla y beneficios.<br />
							</span>
						</td>
					  </tr>
                  <?php } else { ?>
					  <tr>
						<td>
						Lista / Destinatarios<br />
						<span class="textMedium">
							<em><?php echo $my_row['ListName']; ?> [<?php echo $my_row['RuleListId']; ?>]</em>
						</span><br />
						<span class="textHint"> 
						&middot; Lista de afiliados o destinatarios de la regla y beneficios.<br />
						</span>
						</td>
					  </tr>
                  <?php } ?>
                  
                  <?php 
						// ---------------------------		
						// EDITAR FECHAS VIGENCIA				   
						if ($itemquery == "dates") { 
						// ---------------------------		
							$ruleeditactive = 1;
					?>
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
                  <?php } else { ?>
						  <tr>
							<td>
							Vigencia<br />
							<span class="textMedium">
								<em>Del <?php echo $my_row['RuleActivationDate']; ?> al <?php echo $my_row['RuleExpirationDate']; ?></em><br />
								<em><?php echo $my_row['RulePublishStatus']; ?></em>
							</span><br />
							<span class="textHint">
							&middot; Fecha para inicio y fin de la vigencia.</span>
							</td>
						  </tr>
                  <?php } // [if ($itemquery == "dates")] ?>
                  
                  <?php 
						// ---------------------------		
						// EDITAR FECHAS VIGENCIA				   
						if ($itemquery == "rewardselect") { 
						// ---------------------------		
							$ruleeditactive = 1;
					?>
						  <tr>
							<td>
							 <div style="padding-left:50px;">
							Regla Descuento<br />
							<div class="fieldrequired">
							<input name="rewardselect" type="radio" id="rewardselectdiscount" value="discount" <?php if ($my_row['RewardSelect'] == 'discount') { echo 'checked="checked"'; } ?> />&nbsp;Descuento [Beneficio] [discount]<br />
							<input name="rewardselect" type="radio" id="rewardselectamountbyprice" value="amountbyprice" <?php if ($my_row['RewardSelect'] == 'amountbyprice') { echo 'checked="checked"'; } ?> />&nbsp;Monto desde Precio [Beneficio / Precio] [amountbyprice]<br />
							<input name="rewardselect" type="radio" id="rewardselectamountbyreference" value="amountbyreference" <?php if ($my_row['RewardSelect'] == 'amountbyreference') { echo 'checked="checked"'; } ?> />&nbsp;Monto desde Referencia [Beneficio / Referencia] [amountbyreference]<br />
							<input name="rewardselect" type="radio" id="rewardselectamountfromprice" value="amountfromprice" <?php if ($my_row['RewardSelect'] == 'amountfromprice') { echo 'checked="checked"'; } ?> />&nbsp;Monto menos Precio [Precio-Beneficio / Precio] [amountfromprice]<br />
							<input name="rewardselect" type="radio" id="rewardselectdiscountwithbase" value="discountwithbase" <?php if ($my_row['RewardSelect'] == 'discountwithbase') { echo 'checked="checked"'; } ?> />&nbsp;Descuento con Base [Precio * Beneficio] [discountwithbase]<br />
							</div>
							<br />
								<span class="textHint">
								&middot; Regla o formula para otorgar el beneficio.<br />
								</span>
								</div>
							</td>
						  </tr>
                  <?php } else { ?>
						  <tr>
							<td>
							Recompensa / Beneficio<br />
							<span class="textMedium">
								<em><?php echo $my_row['RuleDescription']; ?></em>
							</span><br />
							<span class="textHint"> 
							&middot; Recompensa o beneficio a entregar.<br />
							</span>
							</td>
						  </tr>
                  <?php } // [if ($itemquery == "rewardselect")] ?>
                                    
                  <tr>
                    <td>
                    Art&iacute;culos<br />
								<table class="tablelistitems">
								  <thead>
								  <tr>
									<td colspan="8">Recompensas Art&iacute;culos</td>
								  </tr>
								  <tr>
									<td align="left">Art&iacute;culo</td>
									<td align="left">Marca</td>
									<td align="left">Regla</td>
									<td align="right">Monto</td>
									<td align="right">Referencia1</td>
									<td align="right">Referencia2</td>
									<td align="right">Referencia3</td>
									<td align="center">&nbsp;</td>
								  </tr>
								  </thead>
								  <tbody>
								  
                   <?php
						// GET RECORDS...
							$records = 0;
							$queryitems  = "EXEC dbo.usp_app_RulesDiscountsManage
												'".$_SESSION[$configuration['appkey']]['userid']."', 
												'".$configuration['appkey']."',
												'itemsview', 
												'".$itemtype."', 
												'".$itemid."';";//echo $queryitems;
							$dbtransactionsalternate->query($queryitems);
							while($my_rowitems=$dbtransactionsalternate->get_row()){ 
								?>
											  <tr>
												<td align="left">
												<em><?php echo $my_rowitems['ItemSKU']; ?></em>
												<br />
												<?php echo $my_rowitems['ItemName']; ?>
												</td>
												<td align="left">
												<?php echo $my_rowitems['ItemBrand']; ?>
												</td>
												<td align="left">
												<?php echo $my_row['RewardSelect']; ?>
												</td>
												<td align="right">
												<?php echo $my_rowitems['ItemRewardDiscount']; ?>
												</td>
												<td align="right">
												<?php echo $my_rowitems['ItemRewardReference01']; ?>
												</td>
												<td align="right">
												<?php echo $my_rowitems['ItemRewardReference02']; ?>
												</td>
												<td align="right">
												<?php echo $my_rowitems['ItemRewardReference03']; ?>
												</td>
												<td align="center">&nbsp;
												
												</td>
											  </tr>	                                      
								<?php
							} // [while($my_rowitems=$dbtransactionsalternate->get_row())]
					?>								  
								  </tbody>
								  </table>
                    <span class="textHint"> &middot; Art&iacute;culos a incluidos en la regla.</span>
                    </td>
                  </tr>
                  
                  <?php 
						// ---------------------------		
						// EDITAR PARAMETROS		   
						if ($itemquery == "rewardparams") { 
						// ---------------------------		
							$ruleeditactive = 1;

					?>
						  <tr>
							<td>
							 <div style="padding-left:50px;">
							  Recompensa Monto<br/>
							<input name="unitsrewarddiscount" id="unitsrewarddiscount" type="text" class="inputtextrequired" value="0" /><br />
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
							<input name="itemrewardreference01" id="itemrewardreference01" type="text" class="inputtext" size="50" value="0" /><br />
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
							<input name="itemrewardreference02" id="itemrewardreference02" type="text" class="inputtext" size="50" value="0" /><br />
							<span class="textHint">
							&middot; Referencia para la recompensa a entregar, acorde a la regla o formula [OPCIONAL].
							</span>
								</div>
							</td>
						  </tr>                 
                  <?php } // [if ($itemquery == "rewardparams")] ?>   
                                    
                  <?php 
						// ---------------------------		
						// NUEVO ARTICULO				   
						if ($itemquery == "itemsnew") { 
						// ---------------------------		
							$ruleeditactive = 1;

					?>
						  <tr>
							<td>
							Art&iacute;culo<br />
							<input name="itemslist" id="itemslist" type="text" class="inputtextrequired" size="50" max="100" value="" /><br />
							<span class="textHint"> 
								&middot; Art&iacute;culo para aplicar la regla.<br />
								&middot; Lista de SKU o c&oacute;digos separados por comas y sin espacios.<br />
								&middot; Despu&eacute;s podr&aacute;s agregar m&aacute;s art&iacute;culos.<br />
								</span>
							</td>
						  </tr>
						  <tr>
							<td>
							 <div style="padding-left:50px;">
							  Recompensa Monto<br/>
							<input name="unitsrewarddiscount" id="unitsrewarddiscount" type="text" class="inputtextrequired" value="0" /><br />
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
							<input name="itemrewardreference01" id="itemrewardreference01" type="text" class="inputtext" size="50" value="0" /><br />
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
							<input name="itemrewardreference02" id="itemrewardreference02" type="text" class="inputtext" size="50" value="0" /><br />
							<span class="textHint">
							&middot; Referencia para la recompensa a entregar, acorde a la regla o formula [OPCIONAL].
							</span>
								</div>
							</td>
						  </tr>                 
                  <?php } // [if ($itemquery == "itemsnew")] ?>      
                             
                  <?php 
						// ---------------------------		
						// EDITAR ARTICULO PARAMETROS		   
						if ($itemquery == "itemsedit") { 
						// ---------------------------		
							$ruleeditactive = 1;

					?>
						  <tr>
							<td>
							Art&iacute;culo<br />
							<span class="textMedium">
								<em><?php echo $itemslist; ?></em><input name="itemslist" id="itemslist" type="hidden" value="<?php echo $itemslist; ?>" />
							</span><br />
							<span class="textHint"> 
								&middot; Art&iacute;culo para aplicar la regla.<br />
								&middot; Lista de SKU o c&oacute;digos separados por comas y sin espacios.<br />
								&middot; Despu&eacute;s podr&aacute;s agregar m&aacute;s art&iacute;culos.<br />
								</span>
							</td>
						  </tr>
						  <tr>
							<td>
							 <div style="padding-left:50px;">
							  Recompensa Monto<br/>
							<input name="unitsrewarddiscount" id="unitsrewarddiscount" type="text" class="inputtextrequired" value="0" /><br />
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
							<input name="itemrewardreference01" id="itemrewardreference01" type="text" class="inputtext" size="50" value="0" /><br />
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
							<input name="itemrewardreference02" id="itemrewardreference02" type="text" class="inputtext" size="50" value="0" /><br />
							<span class="textHint">
							&middot; Referencia para la recompensa a entregar, acorde a la regla o formula [OPCIONAL].
							</span>
								</div>
							</td>
						  </tr>                 
                  <?php } // [if ($itemquery == "itemsedit")] ?>                                   
                              
                  <tr>
                    <td>
                    &Uacute;ltima Modificaci&oacute;n<br />
					<span class="textMedium"><em><?php echo $my_row['RuleCreatedDate']; ?></em></span><br />                    
                    <span class="textHint">
                    &middot; Fecha de &uacute;ltimo cambio en la regla.</span>
                    </td>
                  </tr>

                  <tr>
                    <td>&nbsp;
                    </td>
                  </tr>
                  
                  <?php if ($ruleeditactive == 1) { ?>
                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
                    </div>
                    </td>
                  </tr>
                  <?php } ?>
                  
                </table>
				</form>

						<?php } // [if ($records == 0) {] ?>
                        
                    <br /><br />
            
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
                        //date_min:today
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
