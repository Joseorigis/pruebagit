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

			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');

	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }


	// ITEMID
			// Obtenemos el ID de la afiliación
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = trim($_GET['n']);
				if ($itemid == "") { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = "0"; }
			}
		
			// Si llegamos por Q es búsqueda...
			if (isset($_GET['q']) && $itemid == 0) {
				
				// Asignamos como cardnumber
				$itemid = trim($_GET['q']);
				if ($itemid == "") { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = "0"; }
			}

		$actionerrorid = 0;
		$ruletype = "";

		$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."',
							'view', 
							'ordinary', 
							'".$itemid."',
							'',
							'18',
							'',
							'',
							'',
							'',
							'';";
		$dbtransactions->query($query);
		$items = $dbtransactions->count_rows();
		$my_row=$dbtransactions->get_row();
		$ruletype = $my_row['RuleType'];
		$actionerrorid = $my_row['Error'];

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
                
                
                   			<?php  if ($items == 0) { ?>
                            
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
                                Regla  <?php echo $ruletype; ?><br />
                                <?php echo $my_row['RuleName']; ?>
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Status<br />
                    <span class="textMedium"><em><?php echo $my_row['RulePublishStatus']; ?></em></span><br />
                    <span class="textHint"> &middot; Status actual de la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culos<br />
                    <span class="textMedium"><em><?php echo $my_row['ItemsCount']; ?> art&iacute;culos</em></span><br />
                    <span class="textSmall"><em><?php echo $my_row['RuleDescription']; ?></em></span><br />
                    <span class="textHint"> &middot; Art&iacute;culos a incluidos en la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Sucursal<br />
                    <span class="textMedium"><em><?php echo $my_row['RuleStores']; ?></em></span><br />
                    <span class="textHint"> 
                    &middot; Sucursales o tiendas de la regla.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Equivalencia<br />
                    <span class="textMedium"><em><?php echo $my_row['PointsEquivalence']; ?>%</em></span><br />
                    <span class="textHint"> 
                    &middot; Factor de acumulaci&oacute;n en puntos.<br />
                    &middot; Porcentaje de conversi&oacute;n de dinero a puntos.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    C&oacute;digo<br />
					<span class="textMedium"><em><?php echo $my_row['RuleCode']; ?></em></span><br />                    
                    <span class="textHint">
                    &middot; C&oacute;digo de referencia de la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Vigencia<br />
					<span class="textMedium"><em>Del <?php echo $my_row['PointsActivationDate']; ?> al <?php echo $my_row['PointsExpirationDate']; ?></em></span><br />                    
                    <span class="textHint">
                    &middot; Fecha para inicio y fin de la vigencia.</span>
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
                            <td class="botonstandard">
                            <img src="images/bulletedit.png" />&nbsp;
                            <a href="?m=rules&s=<?php echo $ruletype; ?>&a=edit&n=<?php echo $itemid; ?>">Editar Regla</a>
                            </td>
                            <?php if ($my_row['RulePublishStatus'] == 'NEW') { ?>
                                <td class="botonstandard">
                                <img src="images/bulletcancel.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $ruletype; ?>&a=delete&n=<?php echo $itemid; ?>">Eliminar Regla</a>
                                </td>
                            <?php } ?>
                            <?php if ($my_row['RulePublishStatus'] == 'ACTIVE') { ?>
                                <td class="botonstandard">
                                <img src="images/bulletstop.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $ruletype; ?>&a=deactivate&n=<?php echo $itemid; ?>">Desactivar Regla</a>
                                </td>
                            <?php } ?>
                            <?php if ($my_row['RulePublishStatus'] == 'STOPPED') { ?>
                                <td class="botonstandard">
                                <img src="images/bulletplay.png" />&nbsp;
                                <a href="?m=rules&s=<?php echo $ruletype; ?>&a=activate&n=<?php echo $itemid; ?>">Activar Regla</a>
                                </td>
                                <td class="botonstandard">
                                <img src="images/bulletcancel.png" />&nbsp;
                                <a href="?m=rules&s=points&a=delete&n=<?php echo $itemid; ?>">Eliminar Regla</a>
                                </td>
                            <?php } ?>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=rules&s=<?php echo $ruletype; ?>&a=new">Nueva Regla</a>
                            </td>

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
