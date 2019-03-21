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
		// Obtenemos el itemid, identificando el elemento a consultar
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}
		
		// itemtype
			$itemtype = 'connections';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'connections'; }
			}
			$itemtype = strtolower($itemtype);

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
			
		
		// IS SEARCH?	
			// Inicializamos el contador de resultados de búsqueda...
			$items = 0;
			$itemscount = 1;
			if ($itemsearch == 1) {
					$itemscount = 0;
					$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskItemOwnersSearch
										'list', '0', '', '".$itemstring."', 'itemowners', 'exact';";
					$dbtransactions->query($query);
					$itemscount = $dbtransactions->count_rows();
					
					// Depende del resultado de la búsqueda...
					switch($itemscount) {
						
							case 0: // NOT FOUND
								$itemid = 0;
								$actionerrorid = 66;
								break;
								
							case 1: // FOUND
								$my_row = $dbtransactions->get_row();
								$itemid = $my_row['ItemOwnerId'];
								$_GET['n'] = $itemid;
								$itemtype = strtoupper($my_row['ItemOwnerType']);
								$_GET['t'] = $itemtype;
								$actionerrorid = $my_row['Error'];
								if ($actionerrorid > 0) { $itemid = 0; }
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
				$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskItemOwnersManage
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$configuration['appkey']."', 
								'view', 
								'".$itemtype."', 
								'".$itemid."';";
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows(); 		
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
                                                M&aacute;s de una conexi&oacute;n fue encontrada!.</span>
                                                <br />
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Si la conexi&oacute;n no se encuentra en la lista, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
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
                                                conexi&oacute;n(es) encontrada(s) para 
                                                <span style="font-weight:bold; font-size:14px;"><?php echo $itemstring; ?></span>.
                                                </span>
                                            </span>
                                            <!--Si deseas ver m&aacute;s resultados, intenta con la b&uacute;squeda avanzada.-->
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>&nbsp;</td>
                                            <td>Conexi&oacute;n</td>
                                            <td>Ubicaci&oacute;n</td>
                                            <td>Tipo</td>
                                            <td>Fecha</td>
                                          </tr>
                                          </thead>
                                          <tbody>
                                          <?php 
                                          	$itemindex = 0;
											$cardname = "";
											
											// Generamos la lista de resultados...
											while($my_row=$dbtransactions->get_row()){ 
										 		$itemindex = $itemindex + 1;
												
												// connectionstatusimage
												$connectionstatusimage = 'bulletcolourgraylight.png';
												if ($my_row['ItemOwnerStatusDesc'] == 'BLOCKED')
													{ $connectionstatusimage = 'bulletcolouryellow.png'; }
													
												if ($my_row['ItemOwnerStatusDesc'] == 'BLOCKED INACTIVITY')
													{ $connectionstatusimage = 'bulletcolourred.png'; }
						
												if ($my_row['ItemOwnerStatusDesc'] == 'ACTIVE')
													{ $connectionstatusimage = 'bulletcolourgreen.png'; }
						
												if ($my_row['ItemOwnerStatusDesc'] == 'DELETED')
													{ $connectionstatusimage = 'bulletcolourgraydark.png'; }
												
                                          ?>
                                              <tr>
                                                <td align="right" style="font-size:8px;">
													<?php echo  $itemindex; ?>
                                                    &nbsp;
                                                    <img src="images/<?php echo $connectionstatusimage; ?>" width="8px" height="8px" alt="<?php echo $my_row['ItemOwnerStatusDesc']; ?>" title="<?php echo $my_row['ItemOwnerStatusDesc']; ?>" />
                                                    &nbsp;
                                                </td>
                                                <td style="font-size:9px;">
                                                	<a href="?m=helpdesk&s=itemowners&a=view&n=<?php echo $my_row['ItemOwnerId']; ?>&t=<?php echo strtoupper($my_row['ItemOwnerType']); ?>">
                                                    <?php echo $my_row['ItemOwnerName']; ?> [<?php echo $my_row['ItemOwnerId']; ?>]</a>
                                                </td>
                                                <td style="font-size:8px;"><?php echo $my_row['ItemOwnerKeyword']; ?></td>
                                                <td style="font-size:8px;"><?php echo $my_row['ItemOwnerType']; ?></td>
                                                <td style="font-size:8px;"><?php echo $my_row['ItemOwnerDate']; ?></td>
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
					$ConnectionActive = 0;
					$ConnectionActiveDesc = 'NA';
					$ConnectionStatus = '';
					$ConnectionApp = '';
					$ConnectionFlags = '';
					

                    // Extraemos los datos del item
                    $my_row=$dbtransactions->get_row();
					
					$ConnectionActive = 1;
					$ConnectionActiveDesc = $my_row['ItemOwnerStatusDesc'];
					$ConnectionStatus = $my_row['ItemOwnerStatusDesc'];
					
					//$ConnectionOwners = explode(",", $my_row['ConnectionOwners']);

                
                ?>                
                <table class="affiliationheader">
                  <tr>
                    <td width="10%" valign="top" align="center">
                    <img src="images/imagesettings.png" class="imagesection" alt="<?php echo ucwords($module); ?>" />
                    </td>
                    <td width="60%">
                        <table width="100%" style="border-collapse:collapse;">
                          <tr>
                            <td colspan="3" class="affiliationheadercelda">
                            <span class="affiliationheadername"><?php echo $my_row['ItemOwnerName']; ?></span><br />
                            <span class="affiliationheaderid"><?php echo $my_row['ItemOwnerProgram']; ?></span><br />
                            <span class="affiliationheaderid"><?php echo $my_row['ItemOwnerType']; ?></span><br />
                            </td>
                          </tr>
                          <tr>
                            <td class="affiliationheadercelda" width="33%">
                            Fecha Conexi&oacute;n<br />
                            <span class="textMedium"><?php echo $my_row['ItemOwnerDate']; ?></span><br />
                            </td>
                            <td class="affiliationheadercelda" width="33%">
                            Art&iacute;culos<br />
                            <span class="textMedium"><?php echo $my_row['ItemOwnerItems']; ?></span>
                            </td>
                            <td class="affiliationheadercelda" width="33%">
                            Status<br />
                            <span class="textMedium"><?php echo $ConnectionStatus; ?></span>
                            </td>
                          </tr>
                       </table>
                    </td>
                    <td width="30%" class="affiliationheadertools">
                    &nbsp;
                    </td>
                  </tr>
                </table>
                
                <br />
                        
                <ul id="helpdeskitemtabs" class="shadetabs2">
                <li>
                    <a href="#" class="selected" rel="#default">Resumen</a>
                </li>
                <li>
                    <a href="helpdesk/helpdesk_itemowners_detailcontacts.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Contactos</a>
                </li>        
                 <li>
                    <a href="helpdesk/helpdesk_itemowners_detailitems.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Art&iacute;culos</a>
                </li>        
               <li>
                    <a href="helpdesk/helpdesk_itemowners_detailconnections.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Conexiones</a>
                </li>        
                <li>
                    <a href="helpdesk/helpdesk_itemowners_detailaffiliations.php?n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>" rel="tabcontainer">Tarjetas</a>
                </li>        
                </ul>
                <div id="helpdeskitemdivcontainer" class="shadetabs2divcontainer">

                        <?php require("helpdesk/helpdesk_itemowners_detailsummary.php"); ?>
                                   
                </div>
                
                <script type="text/javascript">
                var tabs=new ddajaxtabs("helpdeskitemtabs", "helpdeskitemdivcontainer")
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
                        <td>Acciones Conexi&oacute;n</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>

                            <?php 
                                // ADMINS ONLY
                                if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
                                    $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>

                               &nbsp;
 
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
