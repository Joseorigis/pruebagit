<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* rules_x.php
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

// CONTAINER & IFRAME CHECK
	// Si el llamado no viene del index o contenedor principal ...PAGE NOT FOUND
	// Si el llamado no viene de una página dentro del mismo dominio ...PAGE NOT FOUND
	if (!isset($_SERVER['HTTP_REFERER'])) {
		if (!isset($appcontainer)) { 
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
		}
	} else {
		
		// INCLUDES & REQUIRES
			if (!isset($appcontainer)) {
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/functions.php');	// Librería de funciones
			}
		
		// REQUEST SOURCE VALIDATION
			$requestsource = getRequestSource();
			if ($requestsource !== 'domain' && $requestsource !== 'page') {
				$actionerrorid = 10;
				require_once('../loginwarningtab.php');
				exit();
			}

	}
	
		// Verificamos la página que se esta navegando
		if (!isset($appcontainer)) {
			
			// INIT
				// Iniciamos el controlador de SESSIONs de PHP
				session_start();
			
			// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexión a base de datos
				include_once('../includes/functions.php');	// Librería de funciones

			// TRANSACTIONS DATABASE	
				include_once('../includes/databaseconnectiontransactions.php');

			// REDIRECT IF NOT IN IFRAME
				if (!isset($_GET['page'])) {
					echo '&nbsp;';
					?>
					
						<script type="text/javascript">
							<!--
							//var isInIFrame = (window.location != window.parent.location)	
							//if (!isInIFrame) { window.location = "../index.php"; }
							
							if (self == top) { window.location = "../index.php"; }
							
							-->
						</script>
					
					<?php
				}

		} 
		
		// IF NO SESSION...
		if (!isset($_SESSION[$configuration['appkey']])) {		
			require_once('../loginwarningtab.php');
			exit();
		}


// --------------------
// INICIO CONTENIDO
// --------------------

	// CURRENT PAGE SCRIPT
		$listscriptparts = explode(chr(92), $scriptactual);
		$listscript = $listscriptparts[count($listscriptparts)-1];

	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", $listscript);
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		setNavigationLog('navigation', 0, $listmodule.'/'.$listscript);


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}
		// Obtenemos el itemtype, el tipo de elemento a consultaar
		$itemtype = 'POINTS';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'POINTS'; }
		}
		$itemtype = strtoupper($itemtype);

?>

							<!-- RULES ITEM -->
							<?php
                            
                                // Obtengo el contenido del Item
								$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_Interactions".$itemtype."Manage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."', 
										'view', 
										'".$itemtype."', 
										'".$itemid."';";
                                $dbconnection->query($query);
                                $my_row=$dbconnection->get_row();
                            
                            ?>
                            <br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="2">Resultados / M&eacute;tricas</td>
                              </tr>
                              </thead>
                              <tbody>
						<?php if ($my_row['InteractionResult'] !== 'INACTIVE') { ?>
                              <tr>
                                <td class="itemdetailconcept"><?php echo $itemtype;?></td>
                                <td class="itemdetailcontent">
									<?php echo $my_row['InteractionId']; ?>.<?php echo $my_row['InteractionSentId']; ?>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Rango Fechas:</td>
                                <td class="itemdetailcontent">
									<?php echo $my_row['InteractionDate']; ?> 
									a <?php echo $my_row['InteractionDateLast']; ?>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Lista:</td>
                                <td class="itemdetailcontent">
									<?php echo number_format($my_row['InteractionResultListed']); ?>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Enviados:</td>
                                <td class="itemdetailcontent">
									<?php echo number_format($my_row['InteractionResultSent']); ?>
                                </td>
                              </tr>
                              <?php if ($itemtype == 'EMAIL') { ?>
                                  <tr>
                                    <td class="itemdetailconcept">Rebotados:</td>
                                    <td class="itemdetailcontent">
                                        <?php echo number_format($my_row['InteractionResultRebounded']); ?><br />
                                        &nbsp;&nbsp;&nbsp;&middot;<em><?php echo number_format(($my_row['InteractionResultRebounded']*100)/$my_row['InteractionResultSent'],2); ?> %</em><br />
                                    </td>
                                  </tr>
                              <?php } ?>
                              <tr>
                                <td class="itemdetailconcept">Entregados:</td>
                                <td class="itemdetailcontent">
									<?php echo number_format($my_row['InteractionResultDelivered']); ?><br />
                                    &nbsp;&nbsp;&nbsp;&middot;<em><?php echo number_format(($my_row['InteractionResultDelivered']*100)/$my_row['InteractionResultSent'],2); ?> %</em><br />
                                </td>
                              </tr>
                              <?php if ($itemtype == 'EMAIL') { ?>
                                  <tr>
                                    <td class="itemdetailconcept">Abierto:</td>
                                    <td class="itemdetailcontent">
                                        <?php echo number_format($my_row['InteractionResultOpened']); ?><br />
                                        &nbsp;&nbsp;&nbsp;&middot;<em><?php echo number_format(($my_row['InteractionResultOpened']*100)/$my_row['InteractionResultDelivered'],2); ?> %</em><br />
                                     </td>
                                  </tr>
                                  <tr>
                                    <td class="itemdetailconcept">Clicks:</td>
                                    <td class="itemdetailcontent">
                                        <?php echo number_format($my_row['InteractionResultClicks']); ?><br />
                                        &nbsp;&nbsp;&nbsp;&middot;<em><?php echo number_format(($my_row['InteractionResultClicks']*100)/($my_row['InteractionResultDelivered']-$my_row['InteractionResultRebounded']),2); ?> %</em><br />
                                     </td>
                                  </tr>
                                  <tr>
                                    <td class="itemdetailconcept">Clicks &Uacute;nicos:</td>
                                    <td class="itemdetailcontent">
                                        <?php echo number_format($my_row['InteractionResultClicked']); ?><br />
                                        &nbsp;&nbsp;&nbsp;&middot;<em><?php echo number_format(($my_row['InteractionResultClicked']*100)/($my_row['InteractionResultDelivered']-$my_row['InteractionResultRebounded']),2); ?> %</em><br />
                                    </td>
                                  </tr>
                              <?php } ?>
                              <tr>
                                <td class="itemdetailconcept">Resultado:</td>
                                <td class="itemdetailcontent">
									<?php echo $my_row['InteractionResult']; ?>
                                </td>
                              </tr>
						<?php } else { ?>
                            	<tr>
                                <td class="itemdetaillistelement" colspan="2" align="center">
                                <div align="center"><em>Sin resultados</em></div>
								</td>
                                </tr>
						<?php } ?>
                              </tbody>
                            </table>
							<br /><br />
                            <!--<table class="buttonsbarborder">
                              <tr>
                                <td class="buttonstandard"><img src="images/bulletdown.png" />&nbsp;<a href="../../ftp/CRMResultsExport/emarketing/InteractionResults<?php echo $itemtype; ?><?php echo $itemid; ?>_<?php echo date("Ymd"); ?>.csv">Descargar Resultados</a></td>
                              </tr>
                            </table>
                            <br />-->
						<?php if ($itemtype == 'EMAIL') { ?>    
                        <table width="80%" align="center">
                        <tr>
                        <td>                        
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="2">Resultados / M&eacute;tricas Clicks</td>
                              </tr>
                                  <tr>
                                    <td class="itemdetailheadercenter">
                                    Clicks
                                    </td>
                                    <td class="itemdetailheader">
                                    Destino
                                    </td>
                                  </tr>
                              </thead>
                              <tbody>
                              
								<?php
								$items = 0;
								$clicks = 0;
								// Obtengo el contenido del Item
								$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_Interactions".$itemtype."Manage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."', 
										'viewclicks', 
										'".$itemtype."', 
										'".$itemid."';";
								$dbconnection->query($query);
								while($my_row=$dbconnection->get_row()){
									$items = $items + 1;
									$clicks = $clicks + $my_row['InteractionResultClicks'];
                                ?>
                                  <tr>
                                    <td class="itemdetailconcept">
                                    	<span class="textMedium">
                                        <?php echo number_format($my_row['InteractionResultClicks']); ?>
                                        </span>
                                    </td>
                                    <td class="itemdetailcontent">
                                        <?php echo $my_row['InteractionSectionId']; ?>.&nbsp;
                                        <?php echo $my_row['InteractionSectionCampaign']; ?>&nbsp;-&nbsp;
										<?php echo $my_row['InteractionSection']; ?>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="font-size:9px;font-style: italic;color:#999999;">
                                        &middot; A <?php echo $my_row['InteractionLastDate']; ?>.
                                        </span>
                                        <br />
                                        <span style="font-size:9px;font-style:italic;">
                                        <a href="<?php echo $my_row['InteractionSectionRedirect']; ?>" target="_blank">
                                        <?php echo $my_row['InteractionSectionRedirect']; ?></a>
                                        </span>
                                    </td>
                                  </tr>
								<?php } ?>
								<?php if ($items == 0) { ?>
                                        <tr>
                                        <td class="itemdetaillistelement" colspan="2" align="center">
                                        <div align="center"><em>Sin resultados</em></div>
                                        </td>
                                        </tr>
                                <?php } else { ?>
                                  <tr bgcolor="#F0F0F0">
                                    <td class="itemdetailconcept">
                                    	<span class="textMedium">
                                        <?php echo number_format($clicks); ?>
                                        </span>
                                    </td>
                                    <td class="itemdetailcontent">
                                    	<span style="font-weight:bold;">
                                        CLICKS TOTALES
                                        </span>
                                    </td>
                                  </tr>
                                <?php } ?>
                              </tbody>
                            </table>
							<br />
                        </td>
                        </tr>
                        </table>    
						<?php } ?>
                            <table width="90%" border="0" cellspacing="3" align="center">
                              <tr>
                                <td align="right">
                                <span style="color:#F0F0F0;font-style:italic;font-size:9px;">
                                |itemid:<?php echo $itemid; ?>@<?php echo getCurrentPageScript(); ?>|
                                </span>
                                </td>
                              </tr>
                            </table>
                            <br />
							<!-- RULES ITEM -->
