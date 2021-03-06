<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
* 	Descripci�n de la funci�n.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la p�gina es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=ISO-8859-15');
		// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
	}

// SCRIPT
	// Obtengo el nombre del script en ejecuci�n
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

	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }


	// ITEMID
			// Obtenemos el ID de la afiliaci�n
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = trim($_GET['n']);
				if ($itemid == "") { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = "0"; }
			}
		
			// Si llegamos por Q es b�squeda...
			if (isset($_GET['q']) && $itemid == 0) {
				
				// Asignamos como cardnumber
				$itemid = trim($_GET['q']);
				if ($itemid == "") { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = "0"; }
			}

		$actionerrorid = 0;

		$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."',
							'deactivate', 
							'ordinary', 
							'".$itemid."',
							'',
							'18',
							'',
							'',
							'',
							'',
							'';";
		$dbconnection->query($query);
		$my_row=$dbconnection->get_row();
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
										Desactivar Regla Puntos
                                        </span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em><?php echo $my_row['RuleName']; ?></em></span><br />
                            <br />
                            </td>
                          </tr>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La regla ha sido DESACTIVADA!.<br />
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
                                        <span class="textMedium">
                                        Regla<br />
										Desactivar Regla Puntos
                                        </span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em><?php echo $my_row['RuleName']; ?></em></span><br />
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
                                            La regla NO pudo ser DESACTIVADA!.<br />
                                            <br />
                                            La regla ya ha sido desactivada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									case 102:
										?>    
                                            <img src="images/iconresultwrong.png" /><br /><br />
                                            La regla NO pudo ser DESACTIVADA!.<br />
                                            <br />
                                            La regla o su configuraci&oacute;n ya ha sido cargada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									default:
										?>    
											<img src="images/iconresultwrong.png" /><br /><br />
											La regla NO pudo ser DESACTIVADA!.<br />
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
                            <img src="images/imagerules.png" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=rules&s=points&a=view&n=<?php echo $itemid; ?>">Ver Regla</a>
                            </td>
                           <?php if ($actionerrorid == 0) { ?>
                            <td class="botonstandard">
                            <img src="images/bulletplay.png" />&nbsp;
                            <a href="?m=rules&s=points&a=activate&n=<?php echo $itemid; ?>">Activar Regla</a>
                            </td>
                            <?php } ?>
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

