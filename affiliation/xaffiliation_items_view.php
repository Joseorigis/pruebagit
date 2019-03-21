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
									'list', '0', '".$affiliationcard."', '".$affiliationstring."', 'affiliated', 'exact', '';";
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
                                            <td colspan="5" bgcolor="#FFFFFF">
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
                                            <td>Ubicaci&oacute;n</td>
                                            <td>Afiliaci&oacute;n</td>
                                          </tr>
                                          </thead>
                                          <tbody>
                                          <?php 
                                          	$itemindex = 0;
											
											// Generamos la lista de resultados...
											while($my_row=$dbconnection->get_row()){ 
										 		$itemindex = $itemindex + 1;
                                          ?>
                                              <tr>
                                                <td align="right"><?php echo  $itemindex; ?></td>
                                                <td>
                                                    <?php if ($my_row['CardStatusId'] == "1") { ?>
                                                	<a href="?m=affiliation&s=items&a=view&n=<?php echo $my_row['CardAffiliationId']; ?>">
                                                    <?php echo $my_row['CardNumber']; ?></a>
                                                    <?php } else { ?>
                                                	<a href="?m=affiliation&s=items&a=view&n=<?php echo $my_row['CardAffiliationId']; ?>" title="Tarjeta Bloqueada">
                                                    <?php echo $my_row['CardNumber']; ?></a>
                                                   	&nbsp;<img src="images/security_warning.ico" width="10" height="10" alt="Tarjeta Bloqueada" />
                                                    <?php } ?>
                                                </td>
                                                <td><a href="?m=affiliation&s=items&a=view&n=<?php echo $my_row['CardAffiliationId']; ?>">
                                                    <?php echo $my_row['CardFullName']; ?></a></td>
                                                <td><?php echo  $my_row['CardPlace']; ?></td>
                                                <td><?php echo  $my_row['CardAffiliationDate']; ?></td>
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
							
							$cardfortesting = 0;

                            // Obtengo el índice del paginado
                            $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem 
                                                '".$affiliationid."', '0';";
                            $dbconnection->query($query);
                            $items = $dbconnection->count_rows();
                            $my_row=$dbconnection->get_row();
    
                            $affiliationcard = $my_row['CardNumber'];
							
                            $cardnumber 		= $my_row['CardNumber'];
							$cardstatus 		= $my_row['CardStatus'];
							$cardmembersince 	= $my_row['CardMemberSince'];
							$cardlastupdate 	= $my_row['CardLastUpdateDate'];
							$carddoctor		 	= $my_row['CardDoctor'];
							$CardBonus			= $my_row['CardBonus'];
							$CardLastInteraction = $my_row['CardLastInteraction'];
							$CardLastInteractionTimeAgo = $my_row['CardLastInteractionTimeAgo'];
							
							$cardfortesting = $my_row['CardForTesting'];
							
							$tarjetastatusid = 0;
                            $tarjetastatusid = $my_row['CardStatusId']; 
							$affiliationstatusid= $my_row['CardStatusId'];
							$cardbonuspending = $my_row['CardBonusPending'];
							$cardname = $my_row['CardName']; 
							//$tarjetatimeago = date('Y-m-d H:i').":00";
							$tarjetatimeago = $my_row['TimeAgo']; 
							
							
								// --------------------------------------------------
								// TRANSACTIONS CHECK: begin
									// Consultamos DB transacciones para notificaciones...
									$CardTransactions 		= 0;
									$CardBalance			= 0;
									$CardBalanceExtra		= 0;
									//$CardBonus				= 0;
									$CardTransfers 			= 1;
									$CardWarnings  			= 1;
									$CardTransactionsStatus = 1;
									$CardSafelist			= 0;
									
										// GET RECORDS...
										$CardBalanceDetail = '0|100';
										$items = 0;
										$query = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_pos_CardStatus
															'".$cardnumber."', '1', '0', '0';";
										$dbconnection->query($query);
										$items = $dbconnection->count_rows(); 	// Total de elementos
										if ($items > 0) {
											$mytransactionrow = $dbconnection->get_row();
											$CardBalance			= $mytransactionrow['Saldo'];
											$CardBalanceDetail		= $mytransactionrow['BalanceDetail'];
											$CardTransactionsStatus = $mytransactionrow['TarjetaStatus'];
											$CardSafelist			= $mytransactionrow['TarjetaSafelist'];
	
												// PUNTOS DOBLES SALDO
												$puntospartes = explode('|', $CardBalanceDetail);
												if (count($puntospartes) > 1) {
													$CardBalanceExtra = $puntospartes[0];
												}										
										}
								
									// LO quitamos temporalmente
								// TRANSACTIONS CHECK: end
								// --------------------------------------------------
								
															
                            // Imagen en el output
							$affiliatedimage = "images/imageuser.gif";
							if ($affiliationstatusid == 1) { $affiliatedimage = "images/imageuseractive.gif"; }
							if ($affiliationstatusid == 2) { $affiliatedimage = "images/imageuserwarning.gif"; }
							if ($affiliationstatusid == 3) { $affiliatedimage = "images/imageuserwarning.gif"; }
							if ($affiliationstatusid == 4) { $affiliatedimage = "images/imageuserinactive.gif"; }
							if ($affiliationstatusid == 6) { $affiliatedimage = "images/imageuserdeleted.gif"; }
                            
                            // HIDE NAME
                            
                            $cardnamehidden = "";
                            for ($i = 0; $i < strlen($cardname); $i++) {
                                if (substr($cardname, $i, 1) == " ") {
                                    $cardnamehidden .= " ";
                                } else {
                                    $cardnamehidden .= "&bull;";
                                    //$cardnamehidden .= "*";
                                }
                            }
							
							// PROFILES HIDE NAME
							// TBD: Move to Security Params
							if ($_SESSION[$configuration['appkey']]['userprofileid'] == 5 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 99 ) {
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
                                <img src="<?php echo $affiliatedimage; ?>" class="imagenaffiliationuser" alt="Status" title="Status" /><br />
                                </td>
                                <td width="60%">
                                    <table style="border-collapse:collapse; width:100%; height:150px;">
                                      <tr>
                                        <td colspan="3" class="affiliationheadercelda">
                                        <span class="affiliationheadername"><?php echo $cardname; ?></span><br />
                                        <span class="affiliationheaderid"><?php echo $cardnumber; ?></span>&nbsp;
                                        [<?php echo $cardstatus; ?>]
                                        <?php if ($cardfortesting == 1) { ?>
                                        	<span style="font-weight:bold;color:#FF0000;">
                                        	[TESTING CARD]
                                            </span>
                                        <?php } ?>
                                        <br />
                                        <span class="affiliationheaderid"><?php echo $carddoctor; ?></span><br />
                                        Miembro desde <?php echo $cardmembersince; ?><br />
                                        <em>Actualizado a <?php echo $cardlastupdate; ?></em>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="affiliationheadercelda" width="33%">
                                        Saldo<br />
                                        <span class="textLarge"><?php echo number_format($CardBalance,2); ?></span><br />
                                        <span style="font-size:9px; font-style:italic;">
                                        <div>
                                        * <abbr class="timeago" title="<?php echo $tarjetatimeago; ?>"><?php echo $tarjetatimeago; ?></abbr>
                                        </div>
                                        </span>
                                        </td>
                                        <td class="affiliationheadercelda" width="33%">
                                        Bonificaciones<br />
                                        <span class="textLarge"><?php echo $CardBonus; ?></span><br />
                                        <span style="font-size:9px; font-style:italic;">
                                        <div>
                                        * <abbr class="timeago" title="<?php echo $tarjetatimeago; ?>"><?php echo $tarjetatimeago; ?></abbr>
                                        </div>
                                        </span>
                                        </td>
                                        <td class="affiliationheadercelda" width="33%">
                                        &Uacute;tima Interacci&oacute;n<br />
                                        <span class="textMedium"><?php echo $CardLastInteraction; ?></span>
                                        <span style="font-size:9px; font-style:italic;">
                                        <div>
                                        * <abbr class="timeago" title="<?php echo $CardLastInteractionTimeAgo; ?>"><?php echo $CardLastInteractionTimeAgo; ?></abbr>
                                        </div>
                                        </span>
                                        </td>
                                      </tr>
                                   </table>
                                </td>
                                <td width="30%" valign="top">
                                
                                    <table style="border-collapse:collapse;width:100%;height:150px;">
                                      <tr>
                                        <td class="affiliationheadertools">
                                            <span class="affiliationheaderid">Herramientas</span><br />
                                            <!--<img src="images/bulletfullscreen.png" />&nbsp;<a href="?m=affiliation&s=items&a=view_fullscreen&n=<?php echo $affiliationid; ?>" target="_blank">Pantalla Completa</a><br />-->
											<?php if ($tarjetastatusid == 1) { ?>
                                            <img src="images/bulletblock.png" />&nbsp;<a href="?m=affiliation&s=items&a=block&n=<?php echo $affiliationid; ?>">Bloquear</a><br />
                                            <?php } ?>
                                            <?php if ($tarjetastatusid == 3) { ?>
                                            <img src="images/bulletapply.png" />&nbsp;<a href="?m=affiliation&s=items&a=unblock&n=<?php echo $affiliationid; ?>">Reactivar</a><br />
                                            <?php } ?>
                                            <img src="images/bulletrefresh.png" />&nbsp;<a href="javascript:history.go(0);">Cargar de nuevo</a><br />
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
                            <li><a href="affiliation/affiliation_items_view_transactions.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer" title="Transacciones">
                                Transacciones</a></li>
                            <li><a href="affiliation/affiliation_items_view_balancesheet.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer" title="Historial">
                                Historial</a></li>
                            <li><a href="affiliation/affiliation_items_view_items.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer">
                                Art&iacute;culos</a></li>        
                            <?php if ($cardbonuspending > 0) { ?>          
                            <li><a href="affiliation/affiliation_items_view_balancesheetbonus.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer" title="Bonificaciones">
                                <strong>Bonificaciones</strong>&nbsp;<img src="images/bulletcommentnew.png" alt="New" /></a></li>
                            <?php } ?>          
                            <li><a href="affiliation/affiliation_items_view_cards.php?n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" rel="tabcontainer">
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
                        
                         <!--<img src="images/bulletedit.png" />&nbsp;<a href="?m=affiliation&s=items&a=edit&n=<?php echo $affiliationid; ?>">Actualizar Afiliado</a><br />-->
                         <img src="images/bulletedit.png" />&nbsp;<a href="?m=affiliation&s=items&a=edit&n=<?php echo $affiliationid; ?>">Actualizar Afiliado</a><br />
						<?php if ($tarjetastatusid == 1) { ?>
                            <img src="images/bulletblock.png" />&nbsp;<a href="?m=affiliation&s=items&a=block&n=<?php echo $affiliationid; ?>">Bloquear</a><br />
                            <img src="images/bulletcancel.png" />&nbsp;<a href="?m=affiliation&s=items&a=blockredemption&n=<?php echo $affiliationid; ?>">Bloquear REDENCI&Oacute;N</a><br />

                            <!-- SAFELIST:begin -->
                            <?php if ($CardSafelist == 0) { ?>
                            	<img src="images/bulletcheck.png" />&nbsp;<a href="?m=affiliation&s=items&a=safe&n=<?php echo $affiliationid; ?>">Agregar a SAFELIST</a><br />
                            <?php } else { ?>
                                <img src="images/bulletcancel.png" />&nbsp;<a href="?m=affiliation&s=items&a=unsafe&n=<?php echo $affiliationid; ?>">Remover de SAFELIST</a><br />
                            <?php } ?>
                            <!-- SAFELIST:end -->
                            
                        <?php } ?>
                        
                        <?php if ($tarjetastatusid == 2) { ?>
                            <img src="images/bulletcheck.png" />&nbsp;<a href="?m=affiliation&s=items&a=unblockredemption&n=<?php echo $affiliationid; ?>">Desbloquear REDENCI&Oacute;N</a><br />
                        <?php } ?>
                        <?php if ($tarjetastatusid == 3) { ?>
                            <img src="images/bulletapply.png" />&nbsp;<a href="?m=affiliation&s=items&a=unblock&n=<?php echo $affiliationid; ?>">Reactivar</a><br />
                        <?php } ?>
                        
                      	<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                        
                            <img src="images/bulletcancel.png" />&nbsp;<a href="?m=affiliation&s=items&a=erase&n=<?php echo $affiliationid; ?>">Desafiliar</a><br />
                            <img src="images/bulletdelete.png" />&nbsp;<a href="?m=affiliation&s=items&a=delete&n=<?php echo $affiliationid; ?>">Eliminar</a><br />
                        <?php } ?>
                        
                            <img src="images/bulletjoin.png" />&nbsp;<a href="?m=affiliation&s=balancetransfer&a=new&t=bonus&n=<?php echo $affiliationid; ?>&cardnumberfrom=<?php echo $cardnumber; ?>">Transferir HISTORIAL</a><br />
                            <img src="images/bulletexchangerate.png" />&nbsp;<a href="?m=affiliation&s=balancetransfer&a=new&t=points&n=<?php echo $affiliationid; ?>&cardnumberfrom=<?php echo $cardnumber; ?>">Transferir SALDO</a><br />
                            <img src="images/bulletpointsadd.png" />&nbsp;<a href="?m=affiliation&s=balancepoints&a=new&t=points&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>">Aplicar Bono</a><br />
                        
							<br />
							<img src="images/bulletlist2.png" />&nbsp;<a href="http://historial.orbisfarma.com.mx/index.php?action=balance&key=&storeid=0&posid=0&employeeid=<?php echo $_SESSION[$configuration['appkey']]['userid']; ?>&actionauth=0&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Historial</a><br />                        

                        	<br /> 
                            <img src="images/bulletreward.png" />&nbsp;<a href="?m=helpdesk&s=bonusfree&a=new&t=bonusfree&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Aplicar Bonificaci&oacute;n</a><br />     
                            <img src="images/bulletmeasure.png" />&nbsp;<a href="?m=helpdesk&s=bonusrecord&a=edit&t=bonusrecord&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Actualizar Historial</a><br />     
                            <img src="images/bullethelpdesk.png" />&nbsp;<a href="?m=helpdesk&s=ticketoffline&a=new&t=ticketoffline&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Agregar Ticket</a><br />     
                        
                        	<br /> 
                            <img src="images/bulletprivacy.png" />&nbsp;<a href="https://afiliacion.orbisfarma.com.mx/avisoprivacidad.php?cardnumber=<?php echo $cardnumber; ?>" target="_blank">Aviso Privacidad</a><br />     

                            <img src="images/bulletbarcode.png" />&nbsp;<a href="?m=affiliation&s=cardnumber&a=send&t=email&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>">Enviar Tarjeta Virtual</a><br />     
                        
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
