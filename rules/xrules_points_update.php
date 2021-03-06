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

$actionerrorid = 0;
$itemid = 0;

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
										<?php echo "ReglaAcumulacion1"; ?>
                                        </span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em><?php echo "DescripciónRegla"; ?></em></span><br />
                            <br />
                            La regla de acumulaci&oacute;n de <strong>17%</strong> de equivalencia en puntos para <strong>[Marca:ALL][Genero:ALL][SubGenero:ALL][Sistema:Electrico]:[1,937 art&iacute;culos]</strong> con vigencia del <strong>jueves 31 de Marzo de 2013 al martes 31 de Diciembre de 2013</strong>, ha sido cargada para publicarse el <strong><?php echo date('d/m/Y'); ?></strong>.<br />
							<br />
                            </td>
                          </tr>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La regla ha sido CARGADA!.<br />
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
                                        <span class="textMedium">Regla<br /><?php echo "ReglaAcumulacion1"; ?></span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em><?php echo "DescripciónRegla"; ?></em></span><br />
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
                                            La regla NO pudo ser CARGADA!.<br />
                                            <br />
                                            La regla ya ha sido cargada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									case 102:
										?>    
                                            <img src="images/iconresultwrong.png" /><br /><br />
                                            La regla NO pudo ser CARGADA!.<br />
                                            <br />
                                            La regla ya ha sido cargada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									default:
										?>    
											<img src="images/iconresultwrong.png" /><br /><br />
											La regla NO pudo ser CARGADA!.<br />
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
                           <?php if ($actionerrorid == 0) { ?>
                            <td class="botonstandard">
                            <img src="images/imagerules.png" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=rules&s=points&a=view&n=<?php echo $itemid; ?>">Ver Regla</a>
                            </td>
                            <?php } ?>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=rules&s=points&a=new">Nueva Regla</a>
                            </td>
                            <td class="botonstandard">
                            <img src="images/bullettools.png" />&nbsp;
                            <a href="?m=rules">Reglas Negocio</a>
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

