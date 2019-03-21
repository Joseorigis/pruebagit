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
									
											
			// Si llegamos por Q es búsqueda...
			if (isset($_GET['q']) && $itemid == 0) {
				
				// Asignamos como cardnumber
				$itemid = trim($_GET['q']);
				if ($itemid == "") { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = "0"; }
			}

		// ItemTypeDesc
			$itemtypedesc = "";
			if ($itemtype == 'bonus') { $itemtypedesc = 'Bonificacion'; }
			if ($itemtype == 'discounts') { $itemtypedesc = 'Descuento'; }


		// RuleCopyLink
			$rulecopylink  = "?m=rules&s=discounts&a=new";
			$rulecopylink .= "&item=".$my_row['ItemsList'];
			$rulecopylink .= "&units=".$my_row['UnitsRequired'];
			$rulecopylink .= "&sameticket=".$my_row['RuleAtSameTicket'];
			$rulecopylink .= "&unitsreward=".$my_row['UnitsReward'];
			$rulecopylink .= "&rewardselect=".$my_row['RewardSelect'];
			$rulecopylink .= "&rewarddiscount=".$my_row['ItemRewardDiscount'];
			$rulecopylink .= "&rewarddiscountref01=".$my_row['ItemRewardReference01'];
			$rulecopylink .= "&rewarddiscountref02=".$my_row['ItemRewardReference02'];
			$rulecopylink .= "&rangeto=".$my_row['TransactionsRangeTo'];
			$rulecopylink .= "&rangeperiod=".$my_row['TransactionsRangePeriod'];
			$rulecopylink .= "&connection=".$my_row['ConnectionId'];
			$rulecopylink .= "&rewarddayslimit=".$my_row['RuleRewardDaysLimitEdit'];
			$rulecopylink .= "&rulelistid=".$my_row['RuleListId'];
			$rulecopylink .= "&ruleactivation=".$my_row['RuleActivationDateEditAlternate'];
			$rulecopylink .= "&ruleexpiration=".$my_row['RuleExpirationDateEdit'];
			$rulecopylink .= "&rulename=".$my_row['RuleName'];
			if ($my_row['RewardItem'] == '9999') {
				$rulecopylink .= "&itemreward=";
			} else {
				$rulecopylink .= "&itemreward=".$my_row['RewardItem'];
			}



	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

?>

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
                                Regla <?php echo $itemtypedesc; ?>
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
							$records = $dbtransactionsalternate->count_rows();
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
                       <?php 
						if ($my_row['RulePublishStatus'] == 'ACTIVE') { 
						?>											
													<img src="images/bulletedit.png" />&nbsp;
													<a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&q=itemsedit&itemslist=<?php echo $my_rowitems['ItemSKU']; ?>&actionauth=<?php echo $actionauth; ?>">Editar Par&aacute;metros</a>
													&nbsp;|&nbsp;
													<img src="images/bulletcancel.png" />&nbsp;
													<a href="?m=rules&s=<?php echo $itemtype; ?>&a=update&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&q=itemsdelete&itemslist=<?php echo $my_rowitems['ItemSKU']; ?>&actionauth=<?php echo $actionauth; ?>" onclick="return confirm('El artículo será ELIMINADO. Esta acción no puede deshacerse. Confirmas que deseas eliminarlo?')">Eliminar</a>
                       <?php 
						} else { // if ($my_row['RulePublishStatus'] == 'ACTIVE') {
						?>
                      								&nbsp;
                       <?php 
						} // if ($my_row['RulePublishStatus'] == 'ACTIVE') {
						?>
													
													
												</td>
											  </tr>	                                      
								<?php
							} 
					?>								  
								  </tbody>
								  </table>
                    <span class="textHint"> &middot; Art&iacute;culos a incluidos en la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    &Uacute;ltima Modificaci&oacute;n<br />
					<span class="textMedium"><em><?php echo $my_row['RuleCreatedDate']; ?></em></span><br />                    
                    <span class="textHint">
                    &middot; Fecha de &uacute;ltimo cambio en la regla.</span>
                    </td>
                  </tr>

                </table>

                       
                       <?php 
						if ($my_row['RulePublishStatus'] == 'ACTIVE') { 
						?>
                        <br /><br />
                        <table class="botones2">
                          <tr>

                                <td class="botonstandard">
                                <img src="images/bulletedit.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&q=list&actionauth=<?php echo $actionauth; ?>">Editar Lista</a>
                                </td>
                                   
                                <td class="botonstandard">
                                <img src="images/bulletedit.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&q=dates&actionauth=<?php echo $actionauth; ?>">Editar Vigencia</a>
                                </td>
                               
                                <td class="botonstandard">
                                <img src="images/bulletedit.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&q=rewardselect&actionauth=<?php echo $actionauth; ?>">Editar Formula</a>
                                </td>
                          </tr>
                          <tr>
                                <td class="botonstandard">
                                <img src="images/bulletedit.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&q=rewardparams&actionauth=<?php echo $actionauth; ?>">Editar Par&aacute;metros</a>
                                </td>
                              
                                <td class="botonstandard">
                                <img src="images/bulletadd.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&q=itemsnew&actionauth=<?php echo $actionauth; ?>">Agregar Art&iacute;culos</a>
                                </td>
                              
                                <td class="botonstandard">
                                <img src="images/bulletstop.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=deactivate&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&actionauth=<?php echo $actionauth; ?>"  onclick="return confirm('La Regla será DESACTIVADA y no se ejecutará más. Esta acción no puede deshacerse. Confirmas que deseas desactivarla?')">Desactivar Regla</a>
                                </td>

                          </tr>
                          <tr>
                                <td class="botonstandard">
                                <img src="images/bulletedit1.png" />&nbsp;
                                <a href="<?php echo $rulecopylink; ?>" target="_blank">Copiar Regla</a>
                                </td>
                              
                          </tr>                          
                        </table>
                       <?php 
						} // if ($my_row['RulePublishStatus'] == 'ACTIVE') {
						?>
                            
                        
						<?php } // [if ($records == 0) {] ?>
                        
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
