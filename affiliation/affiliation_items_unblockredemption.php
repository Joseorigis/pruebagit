<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* interactions_x.php
* 	Descripci�n de la funci�n.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la p�gina es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=UTF-8');
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
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}


	// GET RECORD
		$cardnumber			= "0";
		$affiliationcard 	= "0"; 
		$affiliationname 	= ""; 
		$affiliatedimage 	= "images/imageuser.gif";
		$affiliatedicon  	= "images/imageuser.gif";
		$affiliationstatus  = "";
		$affiliationstatusid= "0";

			$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemStatusManage 'unblockredemption', 
								'".$itemid."',
								'0',
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$scriptactual."',
								'0';";
			$dbconnection->query($query);
			$items = $dbconnection->count_rows();
			$my_row=$dbconnection->get_row();
			$affiliationid	 	= $my_row['CardAffiliationId']; 
			$affiliationcard 	= $my_row['CardNumber']; 
			$affiliationname 	= $my_row['CardName']; 
			$affiliationstatus	= $my_row['CardStatus'];
			$affiliationstatusid= $my_row['CardStatusId'];
			$actionerrorid 		= $my_row['Error']; 


	// REFERER
		// Identificamos de donde viene... para regresarlo en caso de error
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }
	
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

                
					<?php 
                    // Si el usuario fue eliminado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
                    
                     <table border="0" cellspacing="0" cellpadding="10">
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
                                La tarjeta ha sido DESBLOQUEADA para REDENCI&Oacute;N!.<br />
                                <br />
        
                            </td>
                          </tr>  
                          </table> 
                                                 
					<?php }
                    if ($actionerrorid > 0 && $actionerrorid < 999) { 
					?>	
                          
                    	 <table border="0" cellspacing="0" cellpadding="10">
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
                                    La tarjeta NO pudo ser DESBLOQUEADA para REDENCI&Oacute;N!.<br />
                                    <br />
                                    El afiliado no fue encontrado, por favor, verifique sus datos y reintente.&nbsp;
                                    <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                <?php } else { ?>    
                                    <img src="images/iconresultwrong.png" />
<br /><br />
                                    La tarjeta NO pudo ser DESBLOQUEADA para REDENCI&Oacute;N!.<br />
                                    <br />
                                    Por favor, intente m&aacute;s tarde.&nbsp;
                                    <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                <?php } ?>    

                            </td>
                          </tr>     
                    </table>
					<?php } ?>	

                    <?php $affiliatedicon = str_replace('2', '', $affiliatedicon); ?>

                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="<?php echo $affiliatedicon; ?>" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=affiliation&s=items&a=view&n=<?php echo $affiliationid; ?>">Ver Afiliado</a>
                            </td>
                           <?php if ($actionerrorid == 0) { ?>
                            <td class="botonstandard">
                            <img src="images/bulletcancel.png" />&nbsp;
                            <a href="?m=affiliation&s=items&a=blockredemption&n=<?php echo $affiliationid; ?>">Bloquear REDENCI&Oacute;N</a>
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
