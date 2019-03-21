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

	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		$item = '0';
		if (isset($_GET['n'])) {
			$item = setOnlyNumbers($_GET['n']);
			if ($item == '') { $item = 0; }
			if (!is_numeric($item)) { $item = 0; }
		}

		// itemtype
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);

		// Obtenemos el itemtype, el tipo de elemento a consultaar
		$connectionid = '0';
		if (isset($_GET['connection'])) {
			$connectionid = setOnlyNumbers($_GET['connection']);
			if ($connectionid == '') { $connectionid = '0'; }
		}


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
                <br />

                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="bonusitem" />
                <input name="a" type="hidden" value="check" />
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
								<span class="textMedium">Consultar Regla</span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>

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

                <?php if ($item !== '0') {
					
							// GET RECORDS...
							$query  = "EXEC dbo.usp_app_RulesBonusManage
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$configuration['appkey']."',
								'check', 
								'item', 
								'0',
								'',
								'',
								'".$item."',
								'".$connectionid."';";
							$dbtransactions->query($query);
							$my_row=$dbtransactions->get_row();

				?>

                            	<!-- LIST GRID:begin -->            
                                    <table class="tablelistitems">
                                      <thead>
                                      <tr>
                                        <td colspan="6">
                                        <span style="font-size:12px;">
                                        <?php echo $item; ?><br />
                                        <?php echo $my_row['ItemName']; ?> [<?php echo $my_row['ItemBrand']; ?>]
                                        </span>
                                        </td>
                                      </tr>
 									<?php
									
									$items = 0;
									// GET RECORDS...
									$query  = "EXEC dbo.usp_app_RulesBonusManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'check', 
										'rule', 
										'0',
										'',
										'',
										'".$item."',
										'".$connectionid."';";//echo $query;
									$dbtransactions->query($query);
															
									?>
                                     <tr>
                                        <td align="center">&nbsp;</td>
                                        <td>#</td>
                                        <td>Regla</td>
                                        <td>Cadena</td>
                                        <td>Vigencia</td>
                                        <td>Alta</td>
                                      </tr>
                                      </thead>
                                      <tbody>
                                
									<?php
									$bgcolor = "ffffff";
                                    while($my_row=$dbtransactions->get_row()){ 
											$items = $items + 1;
											
											$bgcolor 	= "ffffff";
											$rulecolor 	= "FDD310";
											if ($my_row['RulePublishStatus'] !== 'ACTIVE') {
												$bgcolor 	= "f0f0f0"; 
												$rulecolor 	= "cccccc";
											}
											if ($items == 1 && $my_row['RulePublishStatus'] == 'ACTIVE') {
												$rulecolor 	= "30C806";
											}
												
									  ?>
										  <tr style="background-color:#<?php echo $bgcolor; ?>">
											<td align="center" style="border-left: 5px solid #<?php echo $rulecolor; ?>;">
												<?php 
                                                if ($items == 1 && $my_row['RulePublishStatus'] == 'ACTIVE') {
                                                    echo "<img src='images/iconacceptblue.png' alt='Active Rule' title='Regla Aplicada' />";
                                                } else {
                                                    echo $items; 
                                                }
                                                ?>
                                            </td>
											<td>
                                            	<a href="?m=rules&s=bonus&a=view&t=bonus&n=<?php echo $my_row['RuleBonusId']; ?>" target="_blank" title="Ver Regla">
												<?php echo $my_row['RuleBonusId']; ?><br />
                                                </a>
                                                <?php echo $my_row['RuleKey']; ?>
											</td>
											<td>
                                                <span style="font-size:14px;font-weight:bold;">
												<?php echo $my_row['RuleUnits']; ?><br />
                                                </span>
                                                <span style="font-size:9px;font-style:italic;">
												* <?php echo $my_row['RuleLimit']; ?>
                                                </span>
                                            </td>
											<td>
												<?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]
											</td>
											<td>
                                            	Del <?php echo $my_row['RuleActivationDate']; ?> 
                                                al <?php echo $my_row['RuleExpirationDate']; ?><br />
                                                <?php echo $my_row['RulePublishStatus']; ?>
                                            </td>
											<td><?php echo $my_row['RuleCreatedDate']; ?></td>
										  </tr>
                                          
									 <?php
									  }
									  if ($items == 0) {
										?>
										<tr>
										<td align="center" colspan="6">
										<div align="center"><em>Sin Regla</em></div>
										</td>
										</tr>
										<?php
									  }
									  ?>
                                        <tr>
                                        <td class="itemdetailfootnote" colspan="6">
                                        * Las reglas NUEVAS se pueden consultar al d&iacute;a siguiente.
                                        </td>
                                        </tr>
									  </tbody>
									  </table>
                                      <span style="font-size:8px;">
                                      &middot; <?php echo date('Y-m-d H:i:s'); ?>
                                      </span>
                                      <br />
                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=rules&s=<?php echo $itemtype; ?>&a=new&item=<?php echo $item; ?>">Nueva Regla Bonificaci&oacute;n</a>
                            </td>
                          </tr>
                        </table>
                    <br /><br />
                                      
								<!-- LIST GRID:end -->   
                
                <?php } ?>

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

