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


		// Leer de securiry params como debe ser el username y el pwd en composición

	// Inicializamos el marcador de error en la acción...
		$actionerrorid = 0;
		
		$affiliatedimage = "images/imageuser.gif";
		$affiliatedicon  = "images/imageuser.gif";
		$affiliationstatus = "";
		
	// Si falta algún parámetro (isset) enviados... ERROR
		if (!isset($_GET['n'])) {

			// Datos Invalidos
			$actionerrorid = 111;
			
		} else {
		
			// Asignamos variables del querystring...
			$affiliationid	= trim($_GET['n']);
			unset($_GET['n']);
			
		}

		// Si no hay error hasta aquí, agregamos...
		if ($actionerrorid == 0) {
		
				// Agregamos el USER a la aplicación...
					$query = "EXEC dbo.usp_app_AffiliationItemStatusManage 'erase', 
										'".$affiliationid."',
										'0',
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$scriptactual."';";
					$dbconnection->query($query);
					$items = $dbconnection->count_rows();
					$my_row=$dbconnection->get_row();
					$affiliationid	 	= $my_row['CardAffiliationId']; 
					$affiliationcard 	= $my_row['CardNumber']; 
					$affiliationname 	= $my_row['CardName']; 
					$affiliationstatus	= $my_row['CardStatus'];
					$affiliationstatusid= $my_row['CardStatusId'];
					$actionerrorid 		= $my_row['Error']; 
					
		} // if ($actionerrorid == 0)

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
                            
                                <?php
                            
								// Imagen en el output
								$affiliatedimage = "images/imageuser.gif";
								if ($affiliationstatusid == 1) { $affiliatedimage = "images/imageuseractive.gif"; }
								if ($affiliationstatusid == 3) { $affiliatedimage = "images/imageuserwarning.gif"; }
								if ($affiliationstatusid == 4) { $affiliatedimage = "images/imageuserinactive.gif"; }
								if ($affiliationstatusid == 6) { $affiliatedimage = "images/imageuserdeleted.gif"; }
								if ($affiliationstatusid  > 1) { $affiliatedicon  = "images/bulletblock.png"; }
								$affiliatedicon = $affiliatedimage;

                                ?>
                                    <table border="0">
                                      <tr>
                                        <td>
                       					<img src="<?php echo $affiliatedimage; ?>" class="imagenaffiliationuser" alt="Affiliated Status" title="Affiliated Status" />
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium">TARJETA<br /><?php echo $affiliationstatus; ?></span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Tarjeta<br />
                            <span class="textMedium"><em><?php echo $affiliationcard; ?></em></span><br />
                            <br />
                            Afiliado<br />
                            <span class="textMedium"><em><?php echo $affiliationname; ?></em></span><br />
                            </td>
                          </tr>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La tarjeta ha sido ELIMINADA!.<br />
                                <br />
        
                            </td>
                          </tr>                          
					<?php } else { ?>	
                          
                          <tr>
                            <td valign="bottom">
                            
                                    <table border="0">
                                      <tr>
                                        <td>
                       					<img src="<?php echo $affiliatedimage; ?>" class="imagenaffiliationuser" alt="Affiliated Status" title="Affiliated Status" />
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium">TARJETA<br /><?php echo $affiliationstatus; ?></span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Tarjeta<br />
                            <span class="textMedium"><em><?php echo $affiliationcard; ?></em></span><br />
                            <br />
                            Afiliado<br />
                            <span class="textMedium"><em><?php echo $affiliationname; ?></em></span><br />
                            </td>
                          </tr>
                          <tr>
                            <td>
                            
                            	<?php if ($actionerrorid == 201) { ?>
                                    <img src="images/iconresultwrong.png" />
<br /><br />
                                    La tarjeta NO pudo ser ELIMINADA!.<br />
                                    <br />
                                    El afiliado no fue encontrado, por favor, verifique sus datos y reintente.&nbsp;
                                    <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                <?php } else { ?>    
                                    <img src="images/iconresultwrong.png" />
<br /><br />
                                    La tarjeta NO pudo ser ELIMINADA!.<br />
                                    <br />
                                    Por favor, intente m&aacute;s tarde.&nbsp;
                                    <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                <?php } ?>    

                            </td>
                          </tr>     
					<?php } ?>	
                    </table>
                    
                    <?php $affiliatedicon = str_replace('2', '', $affiliatedicon); ?>

                        <br /><br />
                        <table class="botones2">
                          <tr>
                           <?php if ($actionerrorid > 0) { ?>
                            <td class="botonstandard">
                            <img src="<?php echo $affiliatedicon; ?>" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=affiliation&s=items&a=view&n=<?php echo $affiliationid; ?>">Ver Afiliado</a>
                            </td>
                            <?php } ?>
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
