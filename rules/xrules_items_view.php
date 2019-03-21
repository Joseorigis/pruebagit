<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* interactions_x.php
* 	Descripción de la función.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la página es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=UTF-8');
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


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}
		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'BONUS';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'BONUS'; }
		}
		$itemtype = strtoupper($itemtype);

		// Obtenemos el itemstring, en el caso de búsqueda
		$itemstring = '';
		$itemsearch = 0;
		if (isset($_GET['q'])) {
			$itemstring = setOnlyText($_GET['q']);
			$itemsearch = 1;
			if ($itemstring == '') { 
				$itemstring = ''; 
				$itemsearch = 0;
			}
		}
		
		// Inicializamos el contador de resultados de búsqueda...
		$items = 0;
		$itemscount = 1;
		
	
		// IS SEARCH?	
			if ($itemsearch == 1) {
					$itemscount = 0;
					$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesSearch
										'list', '0', '', '".$itemstring."', 'rules', 'exact';";
					$dbconnection->query($query);
					$itemscount = $dbconnection->count_rows();
					
					// Depende del resultado de la búsqueda...
					switch($itemscount) {
						
							case 0: // NOT FOUND
								$itemid = 0;
								$actionerrorid = 66;
								break;
								
							case 1: // FOUND
								$my_row = $dbconnection->get_row();
								$itemid = $my_row['RuleKey'];
								$_GET['n'] = $itemid;
								$itemtype = strtoupper($my_row['RuleType']);
								$_GET['t'] = $itemtype;
								$actionerrorid = $my_row['Error'];
								if ($actionerrorid > 0) { $itemid = 0; }
							$itemid = 0;
								break;
								
							default: // MORE THAN ONE
								$itemid = 0;
								$actionerrorid = 99;
								
					}					
				
			}

			
	// GET RECORD
		// Si el ItemId es válido, consultamos a la base de datos...
		if ($itemid > 0) {
			
			// Indicador de registros a procesar
			$items = 0;
			
			// Obtengo el registro del Item
			$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_Rules".$itemtype."Manage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."', 
							'view', 
							'".$itemtype."', 
							'".$itemid."';";
			$dbconnection->query($query);
			$items = $dbconnection->count_rows(); 		
			// Si no hubo registros, es Error
			if ($items == 0) {
				$actionerrorid =  66; // NOT FOUND
			}
						
		} else {
			if ($actionerrorid == 0) { $actionerrorid =  66; } // NOT FOUND
		}


	// REFERER
		// Identificamos de donde viene... para regresarlo en caso de error
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }
	

?>
		<script type="text/javascript">
            jQuery(document).ready(function() {
                 jQuery("abbr.timeago").timeago();
            });
        </script>

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
                <?php 
                // ERROR en el procesamiento...
                if ($actionerrorid > 0) {
                ?>
  
  
					<?php 
                    // ERROR, elemento no encontrado...
                    if ($actionerrorid !== 99 || $itemscount < 2) {
                    ?>
             
                        <br />
                        <br />
                            
                            <table class="tablemessage">
                              <tr>
                                <td bgcolor="#FF0000">&nbsp;</td>
                                <td bgcolor="#F0F0F0">			
                                        <br />
                                        <img src="images/security_firewall_off.png" alt="Elemento No Encontrado" />
                                        <br />
                                        <br />
                                        <span class="textMedium">Oooops!
                                        <br />
                                        <br />
                                        El elemento no fue encontrado!.</span>
                                        <br />
                                        <br />
                                        Por favor, valida la informaci&oacute;n que ingresaste o seleccionaste y reintenta nuevamente.
                                        <br />
                                        <br />
                                        <img src="images/bulletleft.png" alt="Regresar" />
                                            &nbsp;<a href="<?php echo $referer; ?>" title="Regresar">Regresar</a>
                                        <br />
                                        <br />
            
                                </td>
                              </tr>
                            </table>
        
                        <br />
                        <br />
					<?php 
                    // ERROR, mostramos resultados de la búsqueda...
					} else {
								// SEARCH RESULTS...
                    ?>
             
                                     <table class="tablemessage">
                                      <tr>
                                        <td bgcolor="#FFFF00">&nbsp;</td>
                                        <td bgcolor="#F0F0F0">			
                                                <br />
                                                <img src="images/security_warning.png" alt="Warning" />
                                                <br />
                                                <br />
                                                <span class="textMedium">Oooops!
                                                <br />
                                                M&aacute;s de una regla fue encontrada!.</span>
                                                <br />
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Si la regla no se encuentra en la lista, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <img src="images/bulletleft.png" />&nbsp;
                                                <a href="<?php echo $referer; ?>" title="Regresar">Regresar</a><br />
                                                <br />
                   
                                        </td>
                                      </tr>
                                    </table>
                                    <br />
									<br />

								<table width="95%" border="0" cellspacing="0" align="center">
                                  <tr>
                                    <td>

                                    <!-- LIST GRID:begin -->   
                                        <table class="tablelistitems">
                                          <thead>
                                          <tr>
                                            <td colspan="5" bgcolor="#FFFFFF">
                                            <span style="font-style:normal;font-weight:normal;">
                                                Resultados de la b&uacute;squeda<br />
                                                <span style="font-style:italic; font-size:12px;">
                                                <span style="font-weight:bold; font-size:14px;"><?php echo $itemscount; ?></span> 
                                                regla(s) encontrada(s) para 
                                                <span style="font-weight:bold; font-size:14px;"><?php echo $itemstring; ?></span>.
                                                </span>
                                            </span>
                                            <!--Si deseas ver m&aacute;s resultados, intenta con la b&uacute;squeda avanzada.-->
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>&nbsp;</td>
                                            <td>Regla</td>
                                            <td>Tipo</td>
                                            <td>Configuraci&oacute;n</td>
                                            <td>Status</td>
                                          </tr>
                                          </thead>
                                          <tbody>
                                          <?php 
                                          	$itemindex = 0;
											$cardname = "";
											
											// Generamos la lista de resultados...
											while($my_row=$dbconnection->get_row()){ 
										 		$itemindex = $itemindex + 1;
                                          ?>
                                              <tr>
                                                <td align="right"><?php echo  $itemindex; ?></td>
                                                <td>
                                                	<a href="?m=rules&s=items&a=view&n=<?php echo $my_row['RuleKey']; ?>&t=<?php echo strtoupper($my_row['RuleType']); ?>">
                                                    <?php echo $my_row['RuleKey']; ?></a><br>
                                                    <span style="font-size:8px">
													<?php echo $my_row['RuleName']; ?>
                                               		</span>
                                                </td>
                                                <td><?php echo $my_row['RuleType']; ?></td>
                                                <td>&middot; <?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]<br />
                                                	&middot; <?php echo $my_row['ListName']; ?> [<?php echo $my_row['AffiliationListId']; ?>]</td>
                                                <td>De <?php echo $my_row['RuleActivationDate']; ?> a <?php echo $my_row['RuleExpirationDate']; ?><br />
                                                    <?php echo $my_row['RulePublishStatus']; ?></td>
                                              </tr>
                                         <?php
                                          	}
                                          ?>
                                          </tbody>
                                          </table>
    
                                    <!-- LIST GRID:end -->            
                        
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>
                                            <br /><br />
                                            <table class="botones2">
                                              <tr>
                                                <td class="botonstandard">
                                                <img src="images/bulletleft.png" />&nbsp;
                                                <a href="<?php echo $referer; ?>" title="Regresar">Regresar</a>
                                                </td>
                                                <td class="botonstandard">
                                                <img src="images/bulletnew.png" />&nbsp;
                                                <a href="?m=rules&s=bonus&a=new&t=bonus">Nueva Regla Bonificaci&oacute;n</a>
                                                </td>
                                                <td class="botonstandard">
                                                <img src="images/bulletnew.png" />&nbsp;
                                                <a href="?m=rules&s=discounts&a=new&t=discounts">Nueva Regla Descuento</a>
                                                </td>
                                              </tr>
                                            </table>
                                        	<br /><br />                                          
                                                              
                                    </td>
                                  </tr>
                                </table>    
                                
                                        
					<?php 
					} //if ($actionerrorid != 99 || $itemcount < 2) 
                    ?>
             
                    
                 <?php 
                } else {
                ?>
               
                <!-- INTERACTION HEADER:begin -->
                <?php

                    // Extraemos los datos del item
                    $my_row=$dbconnection->get_row();
					$interactionstatusid = 0;
					$interactionstatusid = trim($my_row['InteractionStatusId']);
                
                ?>                
                <table class="affiliationheader">
                  <tr>
                    <td width="10%" valign="top" align="center">
                    <img src="images/imageinteractions.png" class="imagesection" alt="<?php echo ucwords($module); ?>" />
                    </td>
                    <td width="60%">
                        <table width="100%" style="border-collapse:collapse;">
                          <tr>
                            <td colspan="3" class="affiliationheadercelda">
                            <span class="affiliationheadername"><?php echo $my_row['InteractionName']; ?></span><br />
                            <span class="affiliationheaderid"><?php echo $my_row['InteractionMedia']; ?></span><br />
                            <?php echo $my_row['InteractionDescription']; ?><br />
                            <span class="affiliationheaderid"><?php echo $my_row['InteractionStatus']; ?></span><br />
                            Creada en <?php echo $my_row['InteractionCreationDate']; ?><br />
                            </td>
                          </tr>
                          <tr>
                            <td class="affiliationheadercelda" width="33%">
                            Fecha Interacci&oacute;n<br />
                            <span class="textMedium"><?php echo $my_row['InteractionDate']; ?></span><br />
                            <?php if ($my_row['InteractionDateTimeAgo'] !== 'TBD') { ?>
                                <span style="font-size:9px; font-style:italic;">
                                <div>
                                * <abbr class="timeago" title="<?php echo $my_row['InteractionDateTimeAgo']; ?>"><?php echo $my_row['InteractionDateTimeAgo']; ?></abbr>
                                </div>
                                </span>
                            <?php } ?>
                            </td>
                            <td class="affiliationheadercelda" width="33%">
                            Lista<br />
                            <span class="textMedium"><?php echo number_format($my_row['InteractionListCount'],0); ?></span>
                            </td>
                            <td class="affiliationheadercelda" width="33%">
                            Resultado<br />
                            <span class="textMedium"><?php echo $my_row['InteractionResult']; ?></span>
                            </td>
                          </tr>
                       </table>
                    </td>
                    <td width="30%" class="affiliationheadertools">
                        <span class="affiliationheaderid">Acciones</span><br />
                        <?php if ($my_row['InteractionStatusId'] == 1) { ?>
                            <img src="images/bulletplay.png" alt="Activar" />&nbsp;<a href="?m=interactions&s=items&a=schedule&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Activar Interacci&oacute;n</a><br />
                        <?php } ?>
                        <?php if ($my_row['InteractionStatusId'] == 2 || $my_row['InteractionStatusId'] == 3) { ?>
                            <img src="images/bulletstop.png" alt="Detener" />&nbsp;<a href="?m=interactions&s=items&a=deactivate&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" onclick="return confirm('La Interacción será DETENIDA y no se enviará más. Esta acción no puede deshacerse. Confirmas que deseas detenerla?')">Detener Interacci&oacute;n</a><br />
                        <?php } ?>  
                        <?php if ($my_row['InteractionStatusId'] == 4) { ?>
                            <img src="images/bulletdelete.png" alt="Reiniciar" />&nbsp;<a href="?m=interactions&s=items&a=resetwarning&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Reiniciar Interacci&oacute;n</a><br />
                        <?php } ?>                            
                        <?php if ($itemtype == "EMAIL") { ?>
                        <img src="images/bulletright.png" alt="Probar" />&nbsp;<a href="?m=interactions&s=items&a=try&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Enviar Prueba</a><br />
                        <?php } ?>  
                    </td>
                  </tr>
                </table>
                
                <br />
                        
                <ul id="interactionsitemtabs" class="shadetabs2">
                <li>
                    <a href="#" class="selected" rel="#default">Resumen</a>
                </li>
                <li>
                    <a href="interactions/interactions_item_detailrecipients.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Destinatarios</a>
                </li>        
                <li>
                    <a href="interactions/interactions_item_detailcontent.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Contenido</a>
                </li>        
                <li>
                    <a href="interactions/interactions_item_detailresults.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Resultados</a>
                </li>        
                <!--
                <li>
                    <a href="interactions/interactions_item_detaildelivery.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Delivery</a>
                </li>        
                -->
                </ul>
                <div id="interactionsitemdivcontainer" class="shadetabs2divcontainer">

                        <?php require("interactions/interactions_item_detailsummary.php"); ?>
                                   
                </div>
                
                <script type="text/javascript">
                var tabs=new ddajaxtabs("interactionsitemtabs", "interactionsitemdivcontainer")
                tabs.setpersist(true)
                tabs.setselectedClassTarget("link") //"link" or "linkparent"
                tabs.init()
                </script>
        
                <br />
    
                <?php 
                }  // if ($actionerrorid > 0) {
                ?>
                   
        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
				<?php if ($itemid > 0) { ?>
        
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones Interacci&oacute;n</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                        
                        <!--<img src="images/bulletright.png" />&nbsp;<a href="?m=interactions&s=items&a=send&n=<?php echo $itemid; ?>">Env&iacute;o Alterno / Copiar en Nueva</a><br />-->
                        
                        <!--<img src="images/bulletcancel.png" />&nbsp;<a href="?m=interactions&s=items&a=delete&n=<?php echo $itemid; ?>">Eliminar Interacci&oacute;n</a><br />-->

                            <?php if ($interactionstatusid == 1) { ?>
                            	<img src="images/bulletedit.png" alt="Editar" />&nbsp;<a href="?m=interactions&s=items&a=edit&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Editar Interacci&oacute;n</a><br />
                            <?php } ?>
                            <?php if ($interactionstatusid == 2 || $interactionstatusid == 3 || $interactionstatusid == 4) { ?>
                            	<img src="images/bulletedit.png" alt="Editar" />&nbsp;<em>Editar Interacci&oacute;n</em><br />
                            <?php } ?>  

                            <?php if ($itemtype == "EMAIL") { ?>
	                        <img src="images/bulletright.png" alt="Editar" />&nbsp;<a href="?m=interactions&s=items&a=try&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Enviar Prueba</a><br />
                            <?php } ?>  
                        
                        </td>
                        </tr>
                    </table>
                    <br /><br />
                    
				<?php } ?>
                    
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
