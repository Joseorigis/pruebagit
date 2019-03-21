<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* security_x.php
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
		if ($requestsource !== 'page') {
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
		// Si no hay error hasta aquí, procesamos...
		if ($itemid > 0) {
					
					$query  = "EXEC dbo.usp_app_SecurityUserManage 
										'".$_SESSION[$configuration['appkey']]['userid']."',
										'".$configuration['appkey']."',
										'delete', 
										'".$itemid."';";
					$dbsecurity->query($query);
					$my_row=$dbsecurity->get_row();
					$itemid	 	= $my_row['UserId']; 
					$username 	= $my_row['Username']; 
					$actionerrorid 	= $my_row['Error']; 
					
		} else {
			$actionerrorid =  99; // NOT FOUND
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
                            
                                <?php
                            
                                // Imagen en el output
                                $icono = "images/imageuser.gif";
                                if ($my_row['UserStatusId'] == 1)  { $icono = "images/imageuseractive.gif"; }
                                if ($my_row['UserStatusId'] == 3)  { $icono = "images/imageuserwarning.gif"; }
                                if ($my_row['UserStatusId'] == 6)  { $icono = "images/imageuserinactive.gif"; }
                                
                                if ($my_row['UserProfileId'] == 1) { $icono = "images/imageuseradmin.gif"; }
                                if ($my_row['UserProfileId'] == 2) { $icono = "images/imageuseradmin.gif"; }	
                            
                                ?>
                                    <table border="0">
                                      <tr>
                                        <td>
                                        <img src="<?php echo $icono; ?>" alt="User Status" title="User Status" class="imagensecurityuser" />						
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium"><?php echo $my_row['UserStatus']; ?></span><br />
                                        <?php echo $my_row['UserStatusDescription']; ?><br />
                                        <?php echo $my_row['UserProfile']; ?><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Usuario<br />
                            <span class="textMedium"><em><?php echo $my_row['Username']; ?></em></span><br />
                            </td>
                          </tr>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                El usuario <strong><?php echo $my_row['Username']; ?></strong> fue ELIMINADO!.<br />
                                <br />
        
                            </td>
                          </tr>                          
					<?php } else { ?>	
                          
                          <tr>
                            <td valign="bottom">
                            
                                    <table border="0">
                                      <tr>
                                        <td>
                                        <img src="images/imageuserinactive.gif" alt="User Status" title="User Status" class="imagensecurityuser" />						
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium"><?php echo $my_row['UserStatus']; ?></span><br />
                                        <?php echo $my_row['UserStatusDescription']; ?><br />
                                        <?php echo $my_row['UserProfile']; ?><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Usuario<br />
                            <span class="textMedium"><em><?php echo $my_row['Username']; ?></em></span><br />
                            </td>
                          </tr>
                          <tr>
                            <td>
                            
                            	<?php if ($actionerrorid == 113) { ?>
                                    <img src="images/iconresultwrong.png" /><br /><br />
                                    El usuario <strong><?php echo $my_row['Username']; ?></strong> NO pudo ser ELIMINADO!.<br />
                                    <br />
                                    El usuario no fue encontrado, por favor, verifique sus datos y reintente.<br />
                                <?php } else { ?>    
                                    <img src="images/iconresultwrong.png" /><br /><br />
                                    El usuario <strong><?php echo $my_row['Username']; ?></strong> NO pudo ser ELIMINADO!.<br />
                                    <br />
                                    Por favor, intente m&aacute;s tarde.<br />
                                <?php } ?>    

                            </td>
                          </tr>     
					<?php } ?>	
                    </table>

                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/iconuseractive.gif" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=security&s=users&a=view&n=<?php echo $itemid; ?>">Ver Usuario</a>
                            </td>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=security&s=users&a=new">Nuevo Usuario</a>
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

