<?php 
/**
*
* rules_item_check.php
*
* Consulta las reglas de negocio asociadas al item o articulo
*	+ Modificaciones YYYYMMDD. Modificación. Autor.
*
* @version 			20171001.orvee
* @category 		rules
* @package 			orvee
* @author 			raulbg <raulbg@origis.com>
* @deprecated 		none
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


	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }


			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');



	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		$item = '';
		if (isset($_GET['n'])) {
			$item = setOnlyNumbers($_GET['n']);
			//if ($item == '') { $item = 0; }
			//if (!is_numeric($item)) { $item = 0; }
		}

		// itemtype
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);

		// Obtenemos el itemtype, el tipo de elemento a consultaar
		$connectionid = '1';
		if (isset($_GET['connection'])) {
			$connectionid = setOnlyNumbers($_GET['connection']);
			if ($connectionid == '') { $connectionid = '1'; }
		}

		// Obtenemos la tarjeta a consultar
		$cardnumber = '';
		if (isset($_GET['cardnumber'])) {
			$cardnumber = setOnlyText($_GET['cardnumber']);
			if ($cardnumber == '') { $cardnumber = '0'; }
		}



		$itemsku 	= "";
		$itemname 	= "";
		$itembrand 	= "";
		$itemkey	= "";


?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		if(WithoutSelectionValue(document.orveefrmrule.itemtype1))
			{ errormessage += "\n- Ingresa un artículo a consultar!."; }
			
			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para consultar la regla, por favor: ' + errormessage);
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
               
               
        <br /><br />
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
								<span class="textMedium">Regla Negocio</span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
				</table>
        <br /><br />

		<!-- ITEM : inicio -->
		
					<?php 

						if ($item !== '') {

								$actionerrorid = 66;

								$records = 0;
								$query  = "EXEC dbo.usp_app_RulesItemCheck
													'".$_SESSION[$configuration['appkey']]['userid']."', 
													'".$configuration['appkey']."',
													'item', 
													'item', 
													'0',
													'".$item."';";//echo $query;
								$dbtransactions->query($query);
								$records = $dbtransactions->count_rows(); 
								if ($records > 0) {
									$my_row=$dbtransactions->get_row();

									$itemsku 	= $my_row['ItemSKU']; 
									$itemname 	= $my_row['ItemName']; 
									$itembrand 	= $my_row['ItemBrand']; 
									$itemkey	= $my_row['ItemKey']; 

									$actionerrorid 		= $my_row['Error']; 

								} else {

									$actionerrorid = 66;

								}

						}



					?>
					
		<!-- ITEM : fin -->     
               
                         
		<!-- RULES : inicio -->
		
					<?php 

						if ($item !== '') {
							
					?>		
							
							<table class="tableaffiliatedtab">
							  <thead>
							  <tr>
								<td colspan="4" style="border-top: 1px solid #ADB1BD">
									<span style="font-size:20px; font-weight: bold;">
									<?php echo $item; ?>
									</span><br />
									<?php if ($itemsku !== '') { ?>
										<span style="font-size:16px;">
										<?php echo $itemname; ?> [ <?php echo $itembrand; ?> ] [ <?php echo $itemkey; ?> ]
										</span>
									<?php } ?>
								</td>
								<td colspan="2" style="font-size:14px;vertical-align: bottom;border-top: 1px solid #ADB1BD">&nbsp;
									
								</td>
							  </tr>
							  <tr class="tableaffiliatedtabheadertr">
								<td class="tableaffiliatedtabheadertd">&nbsp;</td>
								<td class="tableaffiliatedtabheadertd">Regla</td>
								<td class="tableaffiliatedtabheadertd">Cadena</td>
								<td class="tableaffiliatedtabheadertd">Vigencia</td>
								<td class="tableaffiliatedtabheadertd">Lista</td>
								<td class="tableaffiliatedtabheadertd">RuleKey</td>
							  </tr>
							  <thead>
							  </thead>
							  <tbody>
							  <tr>
								<td colspan="6" style="text-align:center;background-color:#F0F6FB;border-left: 5px solid #0072C6;border-right: 5px solid #0072C6;">
								<span style="font-size: 14px; font-weight:bold;" >
								BONIFICACIONES
								</span>
								</td>
							  </tr>

								<?php 

									$records = 0;
									$query  = "EXEC dbo.usp_app_RulesItemCheck
														'".$_SESSION[$configuration['appkey']]['userid']."', 
														'".$configuration['appkey']."',
														'item', 
														'bonus', 
														'0',
														'".$item."',
														'".$connectionid."';";//echo $query;
									$dbtransactions->query($query);
									//$records = $dbtransactions->count_rows(); 
									while($my_row=$dbtransactions->get_row()){ 
												$records = $records +1 ;
												$bgcolor 	= "ffffff";
												$rulecolor 	= "FDD310";
												if ($my_row['RulePublishStatus'] !== 'ACTIVE') {
													$bgcolor 	= "f9f9f9"; 
													$rulecolor 	= "cccccc";
												}
												if ($records == 1 && $my_row['RulePublishStatus'] == 'ACTIVE') {
													$rulecolor 	= "30C806";
												}

										  ?>
											  <tr style="background-color:#<?php echo $bgcolor; ?>">
												<td align="center" style="border-left: 5px solid #<?php echo $rulecolor; ?>;">
													<?php 
													if ($records == 1 && $my_row['RulePublishStatus'] == 'ACTIVE') {
														echo "<img src='images/iconacceptblue.png' alt='Active Rule' title='Regla Aplicada' />";
													} 
													?>
												</td>
												<td>
													<span style="font-size:16px;font-weight:bold;">
													<?php echo $my_row['RuleUnits']; ?>
													</span><br />
													<span style="font-size:9px;font-style:italic;">
													* <?php echo $my_row['RuleLimit']; ?>
													</span>
												</td>
												<td>
													<?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]
												</td>
												<td>
													<span style="font-size:9px;">
													Del <?php echo $my_row['RuleActivationDate']; ?> 
													al <?php echo $my_row['RuleExpirationDate']; ?><br />
													<?php echo $my_row['RulePublishStatus']; ?>
													</span>
												</td>
												<td><?php echo $my_row['ListName'].' ['.$my_row['AffiliationListId'].']'; ?></td>
												<td><?php echo $my_row['RuleKey']; ?><br /><?php echo $my_row['RuleType']; ?></td>
											  </tr>
								<?php 
									}
							
									if ($records == 0) {
										echo "<tr><td colspan='6' style='text-align:center;'>Sin Reglas Bonificacion</td></tr>";
									}

								?>							  
							  <tr>
								<td colspan="6" style="text-align:center;background-color:#F0F6FB;border-left: 5px solid #0072C6;border-right: 5px solid #0072C6;">
								<span style="font-size: 14px; font-weight:bold;" >
								DESCUENTOS
								</span>
								</td>
							  </tr>		
								<?php 

									$records = 0;
									$query  = "EXEC dbo.usp_app_RulesItemCheck
														'".$_SESSION[$configuration['appkey']]['userid']."', 
														'".$configuration['appkey']."',
														'item', 
														'discounts', 
														'0',
														'".$item."',
														'".$connectionid."';";//echo $query;
									$dbtransactions->query($query);
									//$records = $dbtransactions->count_rows(); 
									while($my_row=$dbtransactions->get_row()){ 
												$records = $records +1 ;
												$bgcolor 	= "ffffff";
												$rulecolor 	= "FDD310";
												if ($my_row['RulePublishStatus'] !== 'ACTIVE') {
													$bgcolor 	= "f0f0f0"; 
													$rulecolor 	= "cccccc";
												}
												if ($records == 1 && $my_row['RulePublishStatus'] == 'ACTIVE') {
													$rulecolor 	= "30C806";
												}

										  ?>
											  <tr style="background-color:#<?php echo $bgcolor; ?>">
												<td align="center" style="border-left: 5px solid #<?php echo $rulecolor; ?>;">&nbsp;
													
												</td>
												<td>
													<span style="font-size:11px;font-weight:bold;"> 
													<?php echo $my_row['Rewards']; ?><br />
													<?php echo $my_row['RewardsRequired']; ?> <?php echo $my_row['RewardsTicket']; ?> 
													</span><br />
													<span style="font-size:9px;font-style:italic;">
													* <?php echo $my_row['RewardsLimit']; ?>
													</span>
												</td>
												<td>
													<?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]
												</td>
												<td>
													<span style="font-size:9px;">
													Del <?php echo $my_row['RuleActivationDate']; ?> 
													al <?php echo $my_row['RuleExpirationDate']; ?><br />
													<?php echo $my_row['RulePublishStatus']; ?>
													</span>
												</td>
												<td>
													<?php echo $my_row['ListName'].' ['.$my_row['AffiliationListId'].']'; ?><br />
													<span style="font-size:8px;color: #ff0000; font-style: italic;">
													&nbsp;&nbsp;&nbsp;* La tarjeta debe pertenecer a la lista de afiliados.
													</span>
												</td>
												<td><?php echo $my_row['RuleKey']; ?><br /><?php echo $my_row['RuleType']; ?></td>
											  </tr>
								<?php 
									}
							
									if ($records == 0) {
										echo "<tr><td colspan='6' style='text-align:center;'>Sin Reglas Descuentos</td></tr>";
									}

								?>			
								
							  <tr>
								<td colspan="6" style="text-align:center;background-color:#F0F6FB;border-left: 5px solid #0072C6;border-right: 5px solid #0072C6;">
								<span style="font-size: 14px; font-weight:bold;" >
								CONEXIONES
								</span>
								</td>
							  </tr>		
								<?php 

									$records = 0;
									$query  = "EXEC dbo.usp_app_RulesItemCheck
														'".$_SESSION[$configuration['appkey']]['userid']."', 
														'".$configuration['appkey']."',
														'item', 
														'connections', 
														'0',
														'".$item."',
														'".$connectionid."';";//echo $query;
									$dbtransactions->query($query);
									//$records = $dbtransactions->count_rows(); 
									while($my_row=$dbtransactions->get_row()){ 
												$records = $records +1 ;
												$bgcolor 	= "ffffff";
												$rulecolor 	= "30C806";

										  ?>
											  <tr style="background-color:#<?php echo $bgcolor; ?>">
												<td align="center" style="border-left: 5px solid #<?php echo $rulecolor; ?>;">&nbsp;
													
												</td>
												<td>
													<?php echo $my_row['ItemSKU']; ?>
												</td>
												<td>
													<?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]
												</td>
												<td>
													<?php echo $my_row['ConnectionType']; ?>
												</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
											  </tr>
								<?php 
									}
							
									if ($records == 0) {
										echo "<tr><td colspan='6' style='text-align:center;'>Sin Conexi&oacute;n Activa</td></tr>";
									}

								?>																		  					  							  					  
							  <tr>
								<td colspan="6" style="text-align:right;font-size: 9px;">
								* <?php echo date('Ymd H:i:s'); ?>
								</td>
							  </tr>		
							  </tbody>
							</table>
							<br />
							<br />
								
					<?php 

						}

					?>

		<!-- RULES : fin -->     
               <br /><br />                                                                          
 
		<!-- FORMULARIO BUSQUEDA : inicio -->
		<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
				$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
             
                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="item" />
                <input name="a" type="hidden" value="check" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td>
                    Art&iacute;culo<br/>
                    <input name="n" id="n" type="text" class="inputtextrequired" /><br />                    
                    <span class="textHint">
                    &middot; Art&iacute;culo o SKU a consultar.
                    </span></td>
                  </tr>
                  
                  <tr>
                    <td>
                    Cadenas<br />
                    <select name="connection" id="connection" class="selectrequired">
                        <?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'BonusConnectionsList','';";
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
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Consultar" />
                    </div>
                    </td>
                  </tr>
                </table>
				</form>
                <br />
				<br />
	    <?php } // [if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 || $_SESSION[$configuration['appkey']]['userprofileid'] == 2)] ?>
		<!-- FORMULARIO BUSQUEDA : fin -->
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

