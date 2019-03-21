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
		
		
		// Procesamos los campos enviados...
			// userid
			$NewUser['userid'] 			= $itemid;
				
			// username
			$NewUser['username'] = '';
			if (isset($_GET['username'])) { $NewUser['username'] = setOnlyText($_GET['username']); } 
			if  ($NewUser['username'] == '') { $actionerrorid = 2; } // Obligatorio

			// email
			$NewUser['email'] = '';
			if (isset($_GET['email'])) { 
				if (isValidSecurityEmail($_GET['email']) == 1) {
					$NewUser['email'] = trim($_GET['email']);
				}
			}
			if  ($NewUser['email'] == '') { $actionerrorid = 2; } // Obligatorio
			
			// name & lastname
			$NewUser['name'] = '';
			if (isset($_GET['name'])) { $NewUser['name'] = setOnlyName($_GET['name']); } 
			if  ($NewUser['name'] == '') { $actionerrorid = 2; } // Obligatorio

			$NewUser['lastname'] = '';
			if (isset($_GET['lastname'])) { $NewUser['lastname'] = setOnlyName($_GET['lastname']); } 
			if  ($NewUser['lastname'] == '') { $actionerrorid = 2; } // Obligatorio

			// profile
			$NewUser['profileid'] = '0';
			if (isset($_GET['userprofileid'])) { $NewUser['profileid'] = setOnlyNumbers($_GET['userprofileid']); }
			if ($NewUser['profileid'] == '') { $NewUser['profileid'] = '0'; }
		
			// expiry date
			$NewUser['passwordexpiry'] = '';
			if (isset($_GET['expiredate'])) { 
				if (isValidDate($_GET['expiredate']) == 1) {
					$NewUser['passwordexpiry'] = trim($_GET['expiredate']);
				}
			}
			if  ($NewUser['passwordexpiry'] == '') { $actionerrorid = 2; } // Obligatorio

			// ip
			$NewUser['ipaccess'] = '*.*.*.*';
			if (isset($_GET['ipaccess'])) { $NewUser['ipaccess'] = setOnlyCharactersValid($_GET['ipaccess']); }
			if ($NewUser['ipaccess'] == '') { $NewUser['ipaccess'] = '*.*.*.*'; }

			// other 
			$NewUser['passwordchange'] 	= 0;
			if (isset($_GET['changepassword'])) { $NewUser['passwordchange'] = 1; }
			
					
	// RECORD PROCESS...	
		// Si no hay error hasta aquí, agregamos...
		if ($actionerrorid == 0) {
		
				// Generamos el nuevo password y lo encriptamos...
					//$NewUser['password'] = createRandomString(8);
					//$NewUser['passwordencripted'] = encryptPassword($NewUser['password']);
		
				// Agregamos el USER a la aplicación...
					$query  = "EXEC dbo.usp_app_SecurityUserManage
										'".$_SESSION[$configuration['appkey']]['userid']."',
										'".$configuration['appkey']."',
										'update', 
										'".$NewUser['userid']."', 
										'".$NewUser['username']."',
										'',
										'".$NewUser['passwordexpiry']."',
										'".$NewUser['passwordchange']."',
										'".$NewUser['name']."',
										'".$NewUser['lastname']."',
										'".$NewUser['email']."',
										'".$NewUser['profileid']."',
										'".$NewUser['ipaccess']."',
										'';";
					$dbsecurity->query($query);
					$my_row=$dbsecurity->get_row();
					$itemid	 	= $my_row['UserId']; 
					$username 	= $my_row['Username']; 
					$userstatus = $my_row['UserStatusDescription']; 
					$userexpiry = $my_row['UserExpiryDate']; 
					$userldap 	= $my_row['LDAPActive'];
					//$userdate	= $my_row['UserFecha']; 
					$actionerrorid 	= $my_row['Error']; 
					
					// Si el USER fue agregado exitosamente...
					if ($actionerrorid == 0) {
						
						
						// EMAIL FROM & TO
							$EmailMessage['From'] 	  = $configuration['adminemail'];
							$EmailMessage['FromName'] = $configuration['adminname'];
							$EmailMessage['To']   	  = $NewUser['email'];
							$EmailMessage['ReplyTo']  = $configuration['adminreplyto'];
							$EmailMessage['Cc']  	  = $configuration['admincc'];
							$EmailMessage['Bcc']  	  = $configuration['adminbcc'];
						
						// EMAIL HEADERS
							$EmailMessage['Headers']  = "";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$itemid."."."********"."@".$configuration['appkey']."\r\n";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: "."********"."\r\n";
	
						// EMAIL CONTENT					
							$EmailMessage['Subject'] = "Tu Acceso Actualizado";				
							$EmailMessage['Content'] = "templates/UserEdit.html";
							$EmailMessage['Body']	 = implode('', file($EmailMessage['Content']));
							$EmailMessage['Body'] = str_replace("|NAME|", ucwords($NewUser['name']), $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|USERNAME|", $username, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|PASSWORD|", "********", $EmailMessage['Body']);
							//$EmailMessage['Body'] = str_replace("|ACCESSURL|", $_SESSION[$configuration['appkey']]['appurl'], $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|ACCESSURL|", strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL())), $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|USERSTATUS|", $userstatus, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|EXPIRYDATE|", $userexpiry, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|USERDATE|", date('d/m/Y H:i:s'), $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|APP|", strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL())), $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|SOURCE|", $configuration['appkey'], $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|MOREINFO|", '', $EmailMessage['Body']);
						
							// Enviamos notificación de nuevo acceso
							$EmailMessageSent = sendAppEmailMessage($EmailMessage);
							
							// Si la notificación pudo ser enviada...
							if ($EmailMessageSent == 0) {
								// Password No Entregado
								$actionerrorid = 112;
								$actionerrorid = 0;
							}
						
					} else {
						
						// Definir acciones de error...
						$EmailMessageSent = 0;
						
					} // if ($actionerrorid == 0)
					
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
                    // Si el usuario fue agregado con exito....
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
                                El usuario <strong><?php echo $my_row['Username']; ?></strong> fue ACTUALIZADO!.<br />
                                <br />
								<?php if ($EmailMessageSent == 1) { ?>
                                    La notificaci&oacute;n de cambio ha sido enviada a <strong><em><?php echo $NewUser['email']; ?></em></strong>!.
                                <?php } else { ?>	
                                    <span style="color:#F00;">La notificaci&oacute;n de cambio no pudo ser enviada a 
                                    <strong><em><?php echo $NewUser['email']; ?></em></strong>, 
                                    por favor, intente enviarla de nuevo a atrav&eacute;s de Recuperar Contrase&ntilde;a!.</span>
                                <?php } ?>	
        
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
                            <span class="textMedium"><em><?php echo $my_row['username']; ?></em></span><br />
                            </td>
                          </tr>
                          <tr>
                            <td>
                            
                            	<?php if ($actionerrorid == 110) { ?>
                                    <img src="images/iconresultwrong.png" /><br /><br />
                                    El usuario <strong><?php echo $my_row['Username']; ?></strong> NO pudo ser ACTUALIZADO!.<br />
                                    <br />
                                    El email o nombre de usuario no pueden ser utilizados, por favor, verifique sus datos y reintente.<br />
                                <?php } else { ?>    
                                    <img src="images/iconresultwrong.png" /><br /><br />
                                    El usuario <strong><?php echo $my_row['Username']; ?></strong> NO pudo ser ACTUALIZADO!.<br />
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
					<?php 
                    // Si el usuario fue agregado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
                            <td class="botonstandard">
                            <img src="images/bulletedit.gif" />&nbsp;
                            <a href="?m=security&s=users&a=edit&n=<?php echo $itemid; ?>">Editar Usuario</a>
                            </td>
	                        <?php if ($userldap == 0) { ?>
                            <td class="botonstandard">
                            <img src="images/bulletright.png" />&nbsp;
                            <a href="?m=loginpasswordrecover" target="_blank">Recuperar Contrase&ntilde;a</a>
                            </td>
							<?php } ?>
                            <td class="botonstandard">
                            <img src="images/bulletdelete.png" />&nbsp;
                            <a href="?m=security&s=users&a=delete&n=<?php echo $itemid; ?>">Eliminar Usuario</a>
                            </td>
					<?php }  ?>	
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

