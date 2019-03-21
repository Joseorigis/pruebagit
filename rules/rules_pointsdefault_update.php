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

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
		if ($requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}


	// POINTS DEFAULT SETTINGS
		$PointsDefault = '0';
		if (isset($_GET['equivalence'])) {
			$PointsDefault = setOnlyNumbers($_GET['equivalence']);
			if ($PointsDefault == '') { $PointsDefault  = '0'; }
		}
		$PointsItemsOnly = 1;
		if (isset($_GET['equivalence'])) {
			$PointsItemsOnly = setOnlyNumbers($_GET['itemsonly']);
			if ($PointsItemsOnly == '') { $PointsItemsOnly  = '1'; }
		}
		$actionerrorid = 0;
		
		$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."',
							'update', 
							'default', 
							'0',
							'',
							'0',
							'0',
							'".$PointsDefault."',
							'',
							'',
							'".$PointsItemsOnly."';";
		$dbtransactions->query($query);
		$items = $dbtransactions->count_rows();
		$my_row=$dbtransactions->get_row();
		$PointsDefault	 = $my_row['PointsEquivalence'];
		$PointsItemsOnly = $my_row['ItemsOnly'];
		$ActivationDate  = $my_row['PointsActivationDate'];
		$ExpirationDate  = $my_row['PointsExpirationDate'];
		$actionerrorid 	 = $my_row['Error']; 
		
		$PointsItems = "";
		if ($PointsItemsOnly == "1") {
			$PointsItems = "Solo Cat&aacute;logo";
		}
		if ($PointsItemsOnly == "0") {
			$PointsItems = "Fuera Cat&aacute;logo";
		}

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

                <table border="0" cellspacing="0" cellpadding="10">
                
					<?php 
					
                    // Si el usuario fue eliminado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
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
                                        Regla<br />
                                        Actualizar Regla Equivalencia Default
                                        </span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em>Equivalencia Default</em></span><br />
                            <br />
                            La regla de Equivalencia Default de acumulaci&oacute;n de <strong><?php echo $PointsDefault; ?>%</strong> de equivalencia en puntos para <strong><?php echo $PointsItems; ?></strong> con vigencia del <strong><?php echo $ActivationDate; ?> al <?php echo $ExpirationDate; ?></strong>, ha sido actualizada.<br />
							<br />
                            </td>
                          </tr>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La regla ha sido ACTUALIZADA!.<br />
                                <br />
        
                            </td>
                          </tr>         
                                           
					<?php } else { ?>	
                          
                          <tr>
                            <td valign="bottom">
                            
                                    <table border="0">
                                      <tr>
                                        <td>
                       					<img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium">Regla<br />Actualizar Regla Equivalencia Default</span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em>Equivalencia Default</em></span><br />
                            <br />
                            </td>
                          </tr>
                          <tr>
                            <td>
                            
                            
                            	<?php 
								
								switch ($actionerrorid) {
									case 14:
										?>    
                                            <img src="images/iconresultwrong.png" /><br /><br />
                                            La regla NO pudo ser ACTUALIZADA!.<br />
                                            <br />
                                            La regla ya ha sido cargada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									case 102:
										?>    
                                            <img src="images/iconresultwrong.png" /><br /><br />
                                            La regla NO pudo ser ACTUALIZADA!.<br />
                                            <br />
                                            La regla ya ha sido cargada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									default:
										?>    
											<img src="images/iconresultwrong.png" /><br /><br />
											La regla NO pudo ser ACTUALIZADA!.<br />
											<br />
											Por favor, intente m&aacute;s tarde.&nbsp;
											<em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
								}

								?>

                            </td>
                          </tr>     
					<?php } ?>	
                    </table>
                    
                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=rules&s=points&a=new">Nueva Regla</a>
                            </td>
                          </tr>
                        </table>
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

