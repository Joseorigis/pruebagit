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
			$itemtype = 'lists';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'lists'; }
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
					$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsSearch
										'list', '0', '', '".$itemstring."', 'lists', 'exact';";
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
								$itemid = $my_row['ListId'];
								$_GET['n'] = $itemid;
								$itemtype = strtoupper($my_row['ListType']);
								$_GET['t'] = $itemtype;
								$actionerrorid = $my_row['Error'];
								if ($actionerrorid > 0) { $itemid = 0; }
								break;
								
							default: // MORE THAN ONE
								$itemid = 0;
								$actionerrorid = 99;
								
					}					
				
			}

			
	// RECORD PROCESS...	
			// if there is record...
			if ($itemid > 0) {
					// Get Record...
					$items = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsManage 
											'".$_SESSION[$configuration['appkey']]['userid']."', 
											'".$configuration['appkey']."',
											'view', 
											'".$itemtype."', 
											'".$itemid."';";//echo $query;
					$dbconnection->query($query);
					$items = $dbconnection->count_rows();
					// Si no hubo registros, es Error
					if ($items == 0) {
						$actionerrorid =  66; // NOT FOUND
					}

		} else {
			if ($actionerrorid == 0) { $actionerrorid =  66; } // NOT FOUND
		}

							// DATABASE TRANSACTIONS ALTERNATE
								// Connecting to database to TRANSACTIONS & POINTS
								$dbconnectionalternate = new database($configuration['db1type'],
													$configuration['db1host'], 
													$configuration['db1name'],
													$configuration['db1username'],
													$configuration['db1password']);

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
                                                M&aacute;s de una lista fue encontrada!.</span>
                                                <br />
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Si la lista no se encuentra en la lista, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
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
                                                lista(s) encontrada(s) para 
                                                <span style="font-weight:bold; font-size:14px;"><?php echo $itemstring; ?></span>.
                                                </span>
                                            </span>
                                            <!--Si deseas ver m&aacute;s resultados, intenta con la b&uacute;squeda avanzada.-->
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>&nbsp;</td>
                                            <td>Lista</td>
                                            <td>Alta</td>
                                            <td>Status</td>
                                            <td>&nbsp;</td>
                                          </tr>
                                          </thead>
                                          <tbody>
                                          <?php 
                                          	$itemindex = 0;
											$cardname = "";
											
											// Generamos la lista de resultados...
											while($my_row=$dbconnection->get_row()){ 
										 		$itemindex = $itemindex + 1;
												// listid
												$listid = $my_row['ListId'];
												
                                          ?>
                                              <tr>
                                                <td align="right"><?php echo $itemindex; ?></td>
                                                <td>
													<a href="?m=affiliation&s=lists&a=view&n=<?php echo $listid; ?>">
													<span style="font-size:9px;">
													<?php echo $listid; ?>.&nbsp;
													</span>
													<span style="font-size:11px;">
													<?php echo urldecode($my_row['ListName']); ?>
													</span>
													</a>
                                                </td>
                                                <td><?php echo $my_row['ListLastDate']; ?></td>
                                                <td>NA</td>
                                                <td>
													<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
															 $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
														<a href="?m=reports&s=items&a=download&t=list&n=<?php echo $listid; ?>&fn=<?php echo urlencode($my_row['ListName']); ?>&ft=csv" target="_blank" title="Descargar Lista"><img src="images/bulletdownload.png" /></a>
													<?php } else { ?>
														&nbsp;
													<?php } ?>                                                	
                                                </td>
                                              </tr>
                                         <?php
                                          	}
                                          ?>
                                          </tbody>
                                          </table>
    
                                    <!-- LIST GRID:end -->            
                        
                                    </td>
                                  </tr>
                                </table>    
                                
					<?php 
					} //if ($actionerrorid != 99 || $itemcount < 2) 
                    ?>
             
                    
                 <?php 
                } else {
                ?>

                    
                <?php

                    // Extraemos los datos del item
                    $my_row=$dbconnection->get_row();
						$listname 		= $my_row['ListName'];
							if (isset($my_row['ListSQLQueryDecoded'])) {
								$listcontent = $my_row['ListSQLQueryDecoded'];
							}
							if (isset($my_row['ListType'])) {
								$listtype = strtolower($my_row['ListType']);
							}
							if (isset($my_row['ListEncoded'])) {
								$listencoded = $my_row['ListEncoded'];
							}
							$listcontentsql = preg_replace("[\n|\r|\n\r]", ' ', $listcontent); 
							$listcontentsql = str_replace("+", "%2B", $listcontentsql);
						
				?>
                                                                               
						<table border="0" cellspacing="0" cellpadding="10">
						  <tr>
							<td valign="bottom">

									<table border="0">
									  <tr>
										<td>
										<img src="images/imageaffiliationlists.png" alt="Affiliated Status" title="Affiliated Status" class="imagenaffiliationuser" />
										</td>
										<td width="24">&nbsp;</td>
										<td valign="bottom">
										<span class="textMedium">
										<?php echo $listname; ?> [<?php echo $itemid; ?>]<br />
										Lista
										</span><br />
										</td>
									  </tr>
									</table>

							</td>
						  </tr>

						  <tr>
							<td>
							 Nombre<br/>
							 <span class="textMedium">
							<?php echo $listname; ?><br />
								</span>
							<span class="textHint">
							&middot; Nombre de la lista.
							</span></td>
						  </tr>
						  <tr>
							<td>
							Tipo<br />
							<span class="textMedium">
							<?php echo $listtype; ?><br />
								</span>
								<span class="textHint">
							  &middot; Tipo de lista.</span>
							</td>
						  </tr>
						  <tr>
							<td>
							Contenido<br />							
							<textarea name="listcontent" id="listcontent" class="textrequired" cols="100" rows="10" maxlength="2000" style="font-size: 9px;"><?php echo $listcontent; ?></textarea><br />
							<input type="hidden" name="" id="listcontentsql" value="<?php echo $listcontentsql; ?>">
							<span class="textHint"> &middot; SQL de la lista.</span>
						</td>
						<tr>
							<td>
								Elementos<br>
								<span class="textMedium">
								<i id="listitems"><img src="images/imageloading.gif" id="loading" > Contando...</i>
								</span>							  
								<span class="textHint"> · N&uacute;mero de elementos en la lista.</span>
							</td>
						</tr>
						  <tr>
							<td>
							Interacciones / Uso<br />
							   <?php
								// GET RECORDS...
									$records = 0;
									$queryitems  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsManage 
															'".$_SESSION[$configuration['appkey']]['userid']."', 
															'".$configuration['appkey']."',
															'listusage', 
															'".$itemtype."', 
															'".$itemid."';";//echo $query;
									$dbconnectionalternate->query($queryitems);
									$records = $dbconnectionalternate->count_rows();
						
									if ($records == 0) {
										?>
										<span class="textMedium">
											Sin Uso
										</span>
										<?php
										
									} else {
								?>
								
									<div style="padding-left:20px;">
										<table class="tablelistitems">
										  <thead>
										  <tr>
											<td align="left">Uso</td>
											<td align="left">Tipo</td>
											<td align="left">Status</td>
										  </tr>
										  </thead>
										  <tbody>

						   		<?php
								// GET RECORDS...
									while($my_rowitems=$dbconnectionalternate->get_row()){ 
										?>
													  <tr>
														<td align="left">
														<a href="?m=<?php echo $my_rowitems['ItemType']; ?>&s=items&a=view&n=<?php echo $my_rowitems['ItemId']; ?>&t=<?php echo $my_rowitems['ItemSubType']; ?>" target="_blank" title="Ver Elemento">
														<?php echo $my_rowitems['ItemName']; ?> [<?php echo $my_rowitems['ItemId']; ?>]
														</a>
														</td>
														<td align="left">
														<?php echo $my_rowitems['ItemType']; ?> <?php echo $my_rowitems['ItemSubType']; ?>
														</td>
														<td align="right">
														<?php if ($my_rowitems['ItemActive'] == 1) { ?>
														ACTIVA (en uso o usada)
														<?php } else { ?>
														INACTIVA (en uso)
														<?php } ?>
														</td>
													  </tr>	                                      
										<?php
									} 
									?>								  
										  </tbody>
										  </table>
										  </div>
										<?php
									} 
									?>								  
							<span class="textHint"> &middot; Uso de la lista.</span>
							</td>
						  </tr>
						</table>       
                                                                              
							<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
									$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>                               
                            <br /><br />
                            <table class="botones2">
                              <tr>
                                <td class="botonstandard">
                                <img src="images/bulletedit.png" />&nbsp;
                                <a href="?m=affiliation&s=lists&a=edit&n=<?php echo $itemid; ?>">Editar Lista</a>
                                </td>
                              </tr>
                            </table>
                        <br /><br />
							<?php } ?>                                                   
                                                                               
                <?php 
                }  // if ($actionerrorid > 0) {
                ?>
                                                                                
            </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
            <td class="templatesidebar">
            
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones Lista</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>&nbsp;
                        
                        </td>
                        </tr>
                    </table>
                    <br /><br />                    

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
<script type="text/javascript">
function fAjax(){
    var envio = "query="+document.getElementById("listcontentsql").value;
	$.ajax({
		type: "GET",
		url: "../includes/AffiliationListCountItems.php",
		data: envio,
		cache: false,
		success: function(html){
			$("#loading").hide();
			$("#listitems").html(html).show();
		},
		error: function(html) {
			$("#loading").hide();
			alert("Ocurrio un error al tratar de contar el número de registros, favor de validar la consulta.");			
        }
	});
}
fAjax();
</script>