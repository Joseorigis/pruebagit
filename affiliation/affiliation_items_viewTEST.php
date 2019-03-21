<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
* 	Descripción de la función.
*
* 	+ 20171016. celsoim. Se Oculto opcion Actualizar Tarjeta.
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


// TBD
// TBD: AJUSTA SIDEBAR!!!!!!! con nuevas opciones que ya definiste.
//EXEC usp_app_AffiliationSearch 'list','0','','raul gutierrez','affiliated','exact';
//EXEC usp_app_AffiliationSearch 'list','0','8600000121556','raul gutierrez','affiliated','exact';
// Iconos en los medios de contacto?
// Mosaico de iconos junto al user para denotar si es o no contactable?


	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

	// ERROR ID 
		$actionerrorid = 0;
		$items = 0;
	
	// AFFILIATIONID
			// Obtenemos el ID de la afiliación
			$affiliationid = 0;
			if (isset($_GET['n'])) {
				$affiliationid = trim($_GET['n']);
				if ($affiliationid == "") { $affiliationid = 0; }
				if (!is_numeric($affiliationid)) { $affiliationid = "0"; }
			}

	// AFFILIATIONCARD or AFFILIATIONAME
			// Iniciamos variables
			$affiliationcard = "0";
			$affiliationstring = "";
			$affiliationsearch = "cardnumber";	
			
			// Si llegamos por Q es búsqueda...
			if (isset($_GET['q']) && $affiliationid == 0) {
				
				// Asignamos como cardnumber
				$affiliationcard = trim($_GET['q']);
				if ($affiliationcard == "") { $affiliationcard = "0"; }

				// Si no es número, activamos como búsqueda de texto o nombre
				if (!is_numeric($affiliationcard)) { 
					$affiliationcard = ""; 
					$affiliationstring = trim($_GET['q']);
					$affiliationsearch = "text";	
				}
	

				// Lanzamos la búsqueda
				$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationSearch
									'list', '0', '".$affiliationcard."', '".$affiliationstring."', 'affiliated', 'exact';";
				//echo $query;
				$dbconnection->query($query);
				$items = $dbconnection->count_rows();

					// Depende del resultado de la búsqueda...
					switch($items) {
						
							case 0: // NOT FOUND
								$affiliationid = 0;
								$actionerrorid = 99;
								break;
								
							case 1: // FOUND
								$my_row = $dbconnection->get_row();
								$affiliationid = $my_row['CardAffiliationId'];
								$_GET['n'] = $affiliationid;
								$actionerrorid = $my_row['Error'];
								if ($actionerrorid > 0) { $affiliationid = 0; }
								//if ($actionerrorid > 0 && $actionerrorid < 13) { $affiliationid = 0; }
								break;
								
							default: // MORE THAN ONE
								$affiliationid = 0;
								$actionerrorid = 9999;
								
					}

			} // END: if (isset($_GET['q']) && $affiliationid == 0) 


			// RECETAS & GLUCOMETROS LINK TO STORAGE [SWITCH LOGIN]
				$LinkUploadReceta  = "https://storage.orveecrm.com/siempreatulado/uploadrecetas/index.php";
				$LinkUploadReceta .= "?i=".urlencode(base64_encode($_SESSION[$configuration['appkey']]['userid']));
				$LinkUploadReceta .= "&u=".urlencode(base64_encode($_SESSION[$configuration['appkey']]['username']));
				$LinkUploadReceta .= "&k=".urlencode(base64_encode($configuration['appkey']));
	
?>

		<script type="text/javascript">
            // Spanish
            jQuery.timeago.settings.strings = {
                prefixAgo: "Hace",
                prefixFromNow: "dentro de",
                suffixAgo: "",
                suffixFromNow: "",
                seconds: "menos de un minuto",
                minute: "un minuto",
                minutes: "unos %d minutos",
                hour: "una hora",
                hours: "%d horas",
                day: "un día",
                days: "%d días",
                month: "un mes",
                months: "%d meses",
                year: "un año",
                years: "%d años"
                };
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
                    if ($actionerrorid > 0) {
					?>
                    
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
                                                El afiliado no fue encontrado!.</span>
                                                <br />
                                                <br />
                                                <?php if ($affiliationcard <> "0" && $affiliationcard <> "" ) { ?>
                                                	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    Deseas afiliar la tarjeta <strong><?php echo $affiliationcard; ?></strong>?<br />
                                                    <br />
                                                	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <img src="images/bulletnew.png" />&nbsp;
                                                    <a href="?m=affiliation&s=items&a=new" title="Afiliar">Nueva Afiliaci&oacute;n</a><br />
                                                    <br />
                                                    <br />
                                                <?php } ?>
    
                                                <?php if ($affiliationstring <> "" ) { ?>
                                                 	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    Deseas afiliar a <strong><?php echo $affiliationstring; ?></strong>?<br />
                                                    <br />
                                                	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <img src="images/bulletnew.png" />&nbsp;
                                                    <a href="?m=affiliation&s=items&a=new" title="Afiliar">Nueva Afiliaci&oacute;n</a><br />
                                                    <br />
                                                    <br />
                                                <?php } ?>
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                De lo contrario, por favor, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
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
                                                M&aacute;s de un afiliado fue encontrado!.</span>
                                                <br />
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Si el afiliado no se encuentra en la lista, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
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
                                            <td colspan="6" bgcolor="#FFFFFF">
                                            <span style="font-style:normal;font-weight:normal;">
                                                Resultados de la b&uacute;squeda<br />
                                                <span style="font-style:italic; font-size:12px;">
                                                <span style="font-weight:bold; font-size:14px;"><?php echo $items; ?></span> 
                                                afiliado(s) encontrado(s) para 
                                                <span style="font-weight:bold; font-size:14px;"><?php echo $affiliationstring; ?></span>.
                                                </span>
                                            </span>
                                            <!--Si deseas ver m&aacute;s resultados, intenta con la b&uacute;squeda avanzada.-->
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>&nbsp;</td>
                                            <td>Tarjeta</td>
                                            <td>Nombre</td>
                                            <td>Fecha Nac</td>
                                            <td>Ubicaci&oacute;n</td>
                                            <td>Afiliaci&oacute;n</td>
                                          </tr>
                                          </thead>
                                          <tbody>
                                          <?php 
                                          	$itemindex = 0;
											$cardname = "";
											
											// Generamos la lista de resultados...
											while($my_row=$dbconnection->get_row()){ 
										 		$itemindex = $itemindex + 1;
												$cardname = $my_row['CardName'];
												if ($_SESSION[$configuration['appkey']]['userprofileid'] == 5 ) {
													$cardname = "&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;";
												}
                                          ?>
                                              <tr>
                                                <td style="font-size:9px;" align="right"><?php echo  $itemindex; ?></td>
                                                <td style="font-size:9px;">
                                                    <?php if ($my_row['CardStatusId'] == "1") { ?>
                                                	<a href="?m=affiliation&s=items&a=view&n=<?php echo $my_row['CardAffiliationId']; ?>">
                                                    <?php echo $my_row['CardNumber']; ?></a>
                                                    <?php } else { ?>
                                                	<a href="?m=affiliation&s=items&a=view&n=<?php echo $my_row['CardAffiliationId']; ?>" title="Tarjeta Bloqueada">
                                                    <?php echo $my_row['CardNumber']; ?></a>
                                                   	&nbsp;<img src="images/security_warning.ico" width="10" height="10" alt="Tarjeta Bloqueada" />
                                                    <?php } ?>
                                                </td>
                                                <td style="font-size:9px;"><a href="?m=affiliation&s=items&a=view&n=<?php echo $my_row['CardAffiliationId']; ?>">
                                                    <?php echo $cardname; ?></a></td>
                                                <td style="font-size:9px;"><?php echo  $my_row['CardBirthDate']; ?></td>
                                                <td style="font-size:9px;"><?php echo  $my_row['CardPlace']; ?></td>
                                                <td style="font-size:8px;"><?php echo  $my_row['CardAffiliationDate']; ?></td>
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
                                    		<span style="font-size:14px;font-style:italic;">
                                            Deseas afiliar a <span class="textMedium"><?php echo $affiliationstring; ?></span>?
                                            </span>
                                            <br />
                                            <table class="botones2">
                                              <tr>
                                                <td class="botonstandard">
                                                <img src="images/bulletleft.png" />&nbsp;
                                                <a href="<?php echo $referer; ?>" title="Regresar">Regresar</a>
                                                </td>
                                                <td class="botonstandard">
                                                <img src="images/bulletnew.png" />&nbsp;
                                                <a href="?m=affiliation&s=items&a=new" title="Afiliar">Nueva Afiliaci&oacute;n</a>
                                                </td>
                                              </tr>
                                            </table>
                                        	<br /><br />                                          
                                                              
                                    </td>
                                  </tr>
                                </table>

                   			<?php  } ?>
            
                            <br />
                            <br />
                    	
                     <?php 
					} else {
					?>
                   
                        <!-- ITEM DATA:begin -->
                        <?php
							$cardbonuspending = 0;
						
                            // Obtengo el índice del paginado
                            $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem 
                                                '".$affiliationid."', '0';";
                            //echo '<br>'.$query;
							$dbconnection->query($query);
                            $items = $dbconnection->count_rows();
                            $my_row=$dbconnection->get_row();
    
                            $affiliationcard = $my_row['Tarjeta'];
							$cardnumber 		= $my_row['Tarjeta'];
							$tarjetastatusid = 0;
                            $tarjetastatusid = $my_row['TarjetaStatusId']; 
							$tarjetamigrada  = $my_row['CardMigrationStatus']; 
							

								// --------------------------------------------------
								// CHECK BONUS PENDING: begin
										include_once('includes/databaseconnectiontransactions.php');
										
										$cardbonuspending = 0;
										
										$querybonus  = "EXEC dbo.usp_app_AffiliationItem 
															'0', '".$cardnumber."';";
										$dbtransactions->query($querybonus);
										$itembonus = $dbtransactions->count_rows();
										if ($itembonus > 0) {
											$my_rowbonus=$dbtransactions->get_row();
											$cardbonuspending = $my_rowbonus['CardBonusPending'];
										}
								// CHECK BONUS PENDING: end									
								// --------------------------------------------------
							
							
							$BajaMotivo = "";
							if ($my_row['TarjetaStatusId'] !== 1) {
								$BajaMotivo .= "<br />";
								$BajaMotivo .= "<span style='color:#f00;font-style:italic;'>";
								$BajaMotivo .= "BAJA<br />";
								$BajaMotivo .= $my_row['BajaMotivo'];
								$BajaMotivo .= "</span>";
							}
    
                            // Imagen en el output
                            $affiliatedimage = "images/imageuser.gif";
                            $affiliatedicon = "images/bulletapply.png";
                            if ($my_row['TarjetaStatusId'] == 1) { $affiliatedimage = "images/imageuseractive.gif"; }
                            if ($my_row['TarjetaStatusId'] == 2) { $affiliatedimage = "images/imageuseractive.gif"; }
                            if ($my_row['TarjetaStatusId'] == 3) { $affiliatedimage = "images/imageuserwarning.gif"; }
                            if ($my_row['TarjetaStatusId'] == 4) { $affiliatedimage = "images/imageuserinactive.gif"; }
                            if ($my_row['TarjetaStatusId'] == 6) { $affiliatedimage = "images/imageuserdeleted.gif"; }
                            if ($my_row['TarjetaStatusId']  > 1) { $affiliatedicon = "images/bulletblock.png"; }
                            
                            // HIDE NAME
                            $cardname = $my_row['Nombre'];
                            $cardnamehidden = "";
                            for ($i = 0; $i < strlen($my_row['Nombre']); $i++) {
                                if (substr($my_row['Nombre'], $i, 1) == " ") {
                                    $cardnamehidden .= " ";
                                } else {
                                    $cardnamehidden .= "&bull;";
                                    //$cardnamehidden .= "*";
                                }
                            }
							
							// PROFILES HIDE NAME
							// TBD: Move to Security Params
							if ($_SESSION[$configuration['appkey']]['userprofileid'] == 5 ) {
								$cardname = $cardnamehidden;
							}
                        
                        ?>
                        <!-- ITEM DATA:end -->
                            
						<?php if ($items == 0) { ?>

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
                                                El elemento no fue encontrado!.</span>
                                                <br />
                                                <br />
                                                 	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    Deseas afiliar una tarjeta?<br />
                                                    <br />
                                                	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <img src="images/bulletnew.png" />&nbsp;
                                                    <a href="?m=affiliation&s=items&a=new" title="Afiliar">Nueva Afiliaci&oacute;n</a><br />
                                                    <br />
                                                    <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                De lo contrario, por favor, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
                                                <br />
                                                <br />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <img src="images/bulletleft.png" />&nbsp;
                                                <a href="<?php echo $referer; ?>" title="Regresar">Regresar</a><br />
                                                <br />
                    
                                        </td>
                                      </tr>
                                    </table>
                            
						<?php } else { ?>
                            <!-- ITEM HEADER:begin -->
                            <!--<table class="affiliationheader" style="border-bottom: 3px solid #ffff00; border-top: 3px solid #ffff00;background-color:#fff6bf;">-->
                            <!--<table class="affiliationheader" style="border-bottom: 3px solid #FF0000; border-top: 3px solid #FF0000; background-color:#FFCFCA;">-->
                            <!--<table class="affiliationheader" style="border-bottom: 3px solid #0072C6;">-->
                            <table class="affiliationheader">
                              <tr>
                                <td width="10%" valign="top" align="center">
                                <img src="<?php echo $affiliatedimage; ?>" class="imagenaffiliationuser"  alt="Status" title="Status" /><br />
                                <?php echo $BajaMotivo; ?>
                                </td>
                                <td width="60%">
                                    <table style="border-collapse:collapse; width:100%; height:150px;">
                                      <tr>
                                        <td colspan="3" class="affiliationheadercelda">
                                        <span class="affiliationheadername"><?php echo $cardname; ?></span><br />
                                        <span class="affiliationheaderid"><?php echo $my_row['Tarjeta']; ?></span>&nbsp;
                                        [<?php echo $my_row['TarjetaStatus']; ?>]<br />
                                        Miembro desde <?php echo $my_row['MiembroDesde']; ?><br />
                                        <em>Actualizado a <?php echo $my_row['AfiliacionActualizacion']; ?></em><br />
                                        <?php echo $my_row['Poblacion']; ?>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="affiliationheadercelda" width="33%">
                                        &Uacute;tima Interacci&oacute;n<br />
                                        <span class="textMedium"><?php echo $my_row['AfiliacionActualizacion']; ?></span>
                                        </td>
                                        <td class="affiliationheadercelda" width="33%">
                                        Productos<br />
                                        <span class="textLarge"><?php echo $my_row['Productos']; ?></span>
                                        </td>
                                        <td class="affiliationheadercelda" width="33%">
                                        Bonificaciones<br />
                                        <span class="textLarge"><?php echo $my_row['Canjes']; ?></span>
                                        </td>
                                      </tr>
                                   </table>
                                </td>
                                <td width="30%" valign="top">
                                
                                    <table style="border-collapse:collapse;width:100%;height:150px;">
                                      <tr>
                                        <td class="affiliationheadertools">
                                            <span class="affiliationheaderid">Herramientas</span><br />
                           					<!--<img src="images/bulletbarcode.png" />&nbsp;<a href="?m=affiliation&s=itemscard&a=edit&n=<?php echo $affiliationid; ?>">Actualizar Tarjeta</a><br />-->
                                            <!--<img src="images/bulletfullscreen.png" />&nbsp;<a href="?m=affiliation&s=items&a=view_fullscreen&n=<?php echo $affiliationid; ?>" target="_blank">Pantalla Completa</a><br />-->
                                        </td>
                                      </tr>
                                   </table>
                                
                                </td>
                              </tr>
                            </table>
                            <!-- ITEM HEADER:end -->
                    
                            <br />
                            
                            <!-- ITEM CONTENT:begin -->
                            <ul id="affiliationitemtabs" class="shadetabs2">
                            <li><a href="#" class="selected" rel="#default">Perfil</a></li>
                            <li><a href="affiliation/affiliation_items_view_interactions.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer" title="Interacciones">
                                Interacciones</a></li>
                            <li><a href="affiliation/affiliation_items_view_prescription.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $affiliationcard; ?>" rel="tabcontainer" title="Medicamentos & Padecimientos">
                                Medicamentos & Padecimientos</a></li>        
                            <li><a href="affiliation/affiliation_items_view_activities.php?n=<?php echo $affiliationid; ?>" rel="tabcontainer" title="Actividades">
                                Actividades</a></li>
                            <?php if ($cardbonuspending > 0) { ?>          
                                <li><a href="affiliation/affiliation_items_view_balancesheet.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer" title="Historial con Bonificaciones Pendientes">
                                    <strong>Transacciones</strong>&nbsp;<img src="images/bulletcommentnew.png" alt="New" /></a></li>
                            <?php } else { ?>          
                                <li><a href="affiliation/affiliation_items_view_balancesheet.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer" title="Historial">
                                    Transacciones</a></li>
                            <?php } ?> 
                            <li><a href="affiliation/affiliation_items_view_cards.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $affiliationcard; ?>" rel="tabcontainer">
                                Tarjetas</a></li>
                            </ul>
                            <div id="affiliationitemdivcontainer" class="shadetabs2divcontainer">
        
                                    <?php require("affiliation/affiliation_items_view_profile.php"); ?>
                                               
                            </div>
                            
                            <script type="text/javascript">
                            var tabs=new ddajaxtabs("affiliationitemtabs", "affiliationitemdivcontainer")
                            tabs.setpersist(true)
                            tabs.setselectedClassTarget("link") //"link" or "linkparent"
                            tabs.init()
                            </script>
                            <!-- ITEM CONTENT:end -->
                    
                            <br />
                            <br />
						<?php } ?>
                            
                     <?php 
					} 
					?>
                   
                    
            </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
            <td class="templatesidebar">
            
        			<?php if ($actionerrorid == 0) { ?>
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones Afiliado</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                        
					<?php //if ($tarjetastatusid == 1) { ?>    
                                      
                            <!--<img src="images/bulletbarcode.png" />&nbsp;<a href="?m=affiliation&s=itemscard&a=edit&n=<?php echo $affiliationid; ?>">Actualizar Tarjeta</a><br />-->
                            
					<?php //} ?>    

						<?php if ($tarjetastatusid == 1) { ?>
						<img src="images/bulletedit.png" />&nbsp;<a href="?m=affiliation&s=items&a=edit&n=<?php echo $affiliationid; ?>">Actualizar Afiliado</a><br />
                        <img src="images/bulletblock.png" />&nbsp;<a href="?m=affiliation&s=items&a=block&n=<?php echo $affiliationid; ?>">Aplicar Baja</a><br />
                        <?php } ?>
                        <?php if ($tarjetastatusid == 3) { ?>
                        <img src="images/bulletapply.png" />&nbsp;<a href="?m=affiliation&s=items&a=unblock&n=<?php echo $affiliationid; ?>">Reactivar</a><br />
                        <?php } ?>

                            <img src="images/bulletcomplaint.png" />&nbsp;<a href="?m=affiliation&s=quejatecnica&a=new&n=<?php echo $affiliationid; ?>">Queja T&eacute;cnica</a><br />
                            <img src="images/bulletredcross.png" />&nbsp;<a href="?m=affiliation&s=farmacovigilancia&a=new&n=<?php echo $affiliationid; ?>">Farmacovigilancia</a><br />
                        
                        
                        <!--<img src="images/bulletgrey.png" />&nbsp;Iniciar Interacci&oacute;n<br />
                        <img src="images/bulletgrey.png" />&nbsp;Agregar a Lista<br />-->

                          <img src="images/bulletphonecallout.png" />&nbsp;<a href="?m=affiliation&s=itemsphonecall&a=new&n=<?php echo $affiliationid; ?>&interactionid=19">Nueva Llamada SALIDA</a><br />
                        
                         <img src="images/bulletphonecall.png" />&nbsp;<a href="?m=affiliation&s=itemsphonecall&a=new&n=<?php echo $affiliationid; ?>">Nueva Llamada ENTRADA</a><br />
  						
                            <img src="images/bulletbarcodenew.png" />&nbsp;<a href="?m=affiliation&s=cardnumber&a=send&t=email&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>">Enviar Tarjeta Virtual</a><br />     
						<img src="images/bulletpills.png" />&nbsp;<a href="?m=affiliation&s=itemsproduct&a=new&n=<?php echo $affiliationid; ?>">Agregar Medicamento</a><br />                            
                        
                        
                        	<br /> 
                            <img src="images/bulletprivacy.png" />&nbsp;<a href="http://www.asofarma.com.mx/Aviso_Privacidad.pdf" target="_blank">Aviso Privacidad</a><br />     
                         <br />
                        <img src="images/bullethelpdesk.png" />&nbsp;<a href="?m=helpdesk&s=ticketoffline&a=new&t=ticketoffline&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Agregar Ticket</a><br />     
                      	<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								 $_SESSION[$configuration['appkey']]['userprofileid'] == 2 ||
								  $_SESSION[$configuration['appkey']]['userprofileid'] == 4) { ?>
                            <img src="images/bulletreward.png" />&nbsp;<a href="?m=helpdesk&s=bonusfree&a=new&t=bonusfree&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Aplicar Bonificaci&oacute;n</a><br />     
                        <?php } ?>

                      	<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1) { ?>
                            <img src="images/bulletjoin.png" />&nbsp;<a href="?m=affiliation&s=balancetransfer&a=new&t=bonus&n=<?php echo $affiliationid; ?>&cardnumberfrom=<?php echo $cardnumber; ?>">HISTORIAL Transferir A</a><br />                          
                            <img src="images/bulletsplit.png" />&nbsp;<a href="?m=affiliation&s=balancetransfer&a=new&t=bonus&n=<?php echo $affiliationid; ?>&cardnumberto=<?php echo $cardnumber; ?>">HISTORIAL Transferir De</a><br />                                                      
                        	<img src="images/bulletcard.png" />&nbsp;<a href="?m=affiliation&s=releasecard&a=new&n=<?php echo $affiliationid; ?>&cardnumberfrom=<?php echo $cardnumber; ?>">HISTORIAL Liberar</a>
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
