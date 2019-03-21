<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* affiliation.php
* 	Página principal del módulo de administración de afiliados.
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
                    
                        // Obtengo el índice del paginado
						$query  = "EXEC dbo.usp_app_SecurityUserManage 
											'".$_SESSION[$configuration['appkey']]['userid']."',
											'".$configuration['appkey']."',
											'view', 
											'".$_SESSION[$configuration['appkey']]['userid']."';";
                        $dbsecurity->query($query);
                        $my_row=$dbsecurity->get_row();
                        $userid = $_SESSION[$configuration['appkey']]['userid'];
                        
                        // Imagen en el output
                        $icono = "images/imageuser.gif";
                        if ($my_row['UserStatusId'] == 1) { $icono = "images/imageuseractive.gif"; }
                        if ($my_row['UserStatusId'] == 3) { $icono = "images/imageuserwarning.gif"; }
                        if ($my_row['UserStatusId'] == 6) { $icono = "images/imageuserinactive.gif"; }
                        
                        if ($my_row['UserProfileId'] == 1) { $icono = "images/imageuseradmin.gif"; }
                        if ($my_row['UserProfileId'] == 2) { $icono = "images/imageuseradmin.gif"; }	
                    
                    ?>
                    <table border="0" cellspacing="0" cellpadding="10">
                      <tr>
                        <td valign="bottom">
                        
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
                        Nombre<br />
                        <span class="textMedium"><em><?php echo $my_row['Name']; ?>&nbsp;<?php echo $my_row['LastName']; ?></em></span><br />
                        </td>
                      </tr>
                      <tr>
                        <td>
                        Email<br />
                        <span class="textMedium"><em><?php echo $my_row['Email']; ?></em></span><br />
                        </td>
                      </tr>
                      <tr>
                        <td>
                        IP Acceso<br />
                        <span class="textMedium"><em>*.*.*.*</em></span><br />
                        </td>
                      </tr>
                      <tr>
                        <td>
                        Vigencia<br />
                        <span class="textMedium"><em>Indefinida</em></span><br />
                        </td>
                      </tr>
                      <tr>
                        <td>
                        &Uacute;ltimo Acceso<br />
                        <span class="textMedium"><em><?php echo $my_row['UserLastAccess']; ?></em></span><br />
                        </td>
                      </tr>
                      <tr>
                        <td>
                        Alta<br />
                        <span class="textMedium"><em><?php echo $my_row['UserCreationDate']; ?></em></span><br />
                        </td>
                      </tr>
                    </table>
                    <br /><br />
                    <table class="botones2">
                      <tr>
                        <td class="botonstandard">
                        <img src="images/bulletedit.gif" />&nbsp;
                        <a href="?m=security&s=users&a=edit&n=<?php echo $userid; ?>">Editar Mis Datos</a>
                        </td>
                        <td class="botonstandard">
                        <img src="images/bulletoff.png" />&nbsp;
                        <a href="?m=security&s=users&a=viewlog&n=<?php echo $userid; ?>">Mi Bit&aacute;cora</a>
                        </td>
                        <td class="botonstandard">
                        <img src="images/bulletoff.png" />&nbsp;
                        <a href="?m=security&s=users&a=passwordchange&n=<?php echo $userid; ?>">Cambiar Mi Contrase&ntilde;a</a>
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
					$sidebarfile = str_replace(".php", "_sidebar.php", $modulepage);

					// Verificamos si existe el archivo
					if (file_exists($sidebarfile)) { 
						
						// Incluimos la barra lateral
						include_once($sidebarfile); 
						
					} else { 
					
						// Si no hay barra, activamos la default
						$sidebarfile = "home_sidebar.php";
						include_once($sidebarfile); 
						
					} 	
					
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

