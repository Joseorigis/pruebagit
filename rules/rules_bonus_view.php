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
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);


			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');
				
		
				$records = 0;
				$query  = "EXEC dbo.usp_app_RulesBonusManage
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
                                Regla  <?php echo $itemtype; ?><br />
                                <?php echo $my_row['RuleName']; ?>
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Regla<br />
                    <span class="textMedium"><em>
					<?php echo $my_row['ItemGroupId']; ?>&nbsp;&nbsp;
                    [<?php echo $my_row['RulePublishStatus']; ?>]
                    </em></span><br />
                    <span class="textHint"> &middot; Status actual de la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Cadena<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]</em>
                    </span><br />
                    <span class="textHint"> 
                    &middot; Sucursales o tiendas de la regla.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culos<br />
                    <span class="textMedium">
                    	<em><?php echo str_replace(',', '<br />', $my_row['ItemsList']); ?></em>
                    </span><br />
                    <span class="textHint"> &middot; Art&iacute;culos a incluidos en la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Regla Negocio<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['RuleDescription']; ?></em>
                        <span class="textSmall"><em><?php echo $my_row['RuleBonusItem']; ?></em></span>
                    </span><br />
                    <span class="textMedium"><em><?php echo $my_row['RuleLimit']; ?></em></span><br />
                    <span class="textSmall"><em>&middot; Retenci&oacute;n Rechazos: <?php echo $my_row['RuleBonusDaysLimit']; ?></em></span><br />
                    <span class="textHint"> 
                    &middot; Regla X+1 a aplicar.<br />
                    &middot; Unidades requeridas para bonificaci&oacute;n.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Bonificaci&oacute;n<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['BonusItem']; ?></em>
                    </span><br />
                    <span class="textHint"> &middot; Art&iacute;culos a bonificar en la regla.</span>
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
                    C&oacute;digo<br />
					<span class="textMedium">
                    	key <em><?php echo $my_row['RuleKey']; ?></em><br />                 
                    	code <em><?php echo $my_row['RuleCode']; ?></em><br /> 
                    	itemgroup <em><?php echo $my_row['ItemGroupId']; ?></em><br /> 
                    </span><br />                    
                    <span class="textHint">
                    &middot; C&oacute;digo de referencia de la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Alta<br />
					<span class="textMedium"><em><?php echo $my_row['RuleCreatedDate']; ?></em></span><br />                    
                    <span class="textHint">
                    &middot; Fecha de alta de la regla.</span>
                    </td>
                  </tr>

                </table>

                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <!--<td class="botonstandard">
                            <img src="images/bulletedit.png" />&nbsp;
                            <a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>">Editar Regla</a>
                            </td>-->
                            <?php if ($my_row['RulePublishStatus'] == 'NEW') { ?>
                                <td class="botonstandard">
                                <img src="images/bulletcancel.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=delete&n=<?php echo $itemid; ?>&actionauth=<?php echo $actionauth; ?>">Eliminar Regla</a>
                                </td>
                            <?php } ?>
                            <?php if ($my_row['RulePublishStatus'] == 'ACTIVE') { ?>
                                <td class="botonstandard">
                                <img src="images/bulletedit.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&actionauth=<?php echo $actionauth; ?>&q=dates">Editar Vigencia</a>
                                </td>
                                <td class="botonstandard">
                                <img src="images/bulletstop.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=deactivate&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>&actionauth=<?php echo $actionauth; ?>"  onclick="return confirm('La Regla será DESACTIVADA y no se ejecutará más. Esta acción no puede deshacerse. Confirmas que deseas desactivarla?')">Desactivar Regla</a>
                                </td>
                            <?php } ?>

                          </tr>
                        </table>
                            
                        
						<?php } ?>
                        
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
