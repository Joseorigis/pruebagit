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


	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

			
	// ITEMNAME
			// Iniciamos variables
			//$itemid = "0";
			$itemname = "";
			$itemsearch = "number";	
			
			// Si llegamos por Q es búsqueda...
			if (isset($_GET['q'])) {
				
				// Asignamos como cardnumber
				$itemid = setOnlyText($_GET['q']);
				if ($itemid == "") { $itemid = "0"; }

				// Si no es número, activamos como búsqueda de texto o nombre
				if (!is_numeric($itemid)) { 
					$itemid = "0"; 
					$itemname = setOnlyText($_GET['q']);
					$itemsearch = "text";	
					
						// Ejecutamos la búsqueda
						$query  = "EXEC dbo.usp_app_SecurityUserSearch 
											'".$_SESSION[$configuration['appkey']]['userid']."',
											'".$configuration['appkey']."',
											'search', 
											'".$itemid."', 
											'".$itemname."',
											'".$itemname."';";
						$dbsecurity->query($query);
						$items = $dbsecurity->count_rows();
						if ($items > 0) {
							$my_row = $dbsecurity->get_row();
							$itemid = $my_row['UserId'];
						}
					
				}
	
			} // END: if (isset($_GET['q']) && $affiliationid == 0) 

			// Lanzamos la búsqueda
			$items = 0;
			$query  = "EXEC dbo.usp_app_SecurityUserManage 
								'".$_SESSION[$configuration['appkey']]['userid']."',
								'".$configuration['appkey']."',
								'view', 
								'".$itemid."';";
			$dbsecurity->query($query);
			$items = $dbsecurity->count_rows();


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
					
						// Not found...
						if ($items == 0) {
						?>
                        
                            <br />
                                
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
                                            <br />
                                            El usuario no fue encontrado!.</span>
                                            <br />
                                            <br />
                                            <br />
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            Deseas agregar al usuario <strong><?php echo $itemname; ?></strong>?<br />
                                            <br />
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <img src="images/bulletnew.png" />&nbsp;
                                            <a href="?m=security&s=users&a=new" title="Nuevo Usuarios">Nuevo Usuario</a><br />
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
            
                            <br />
                            <br />                        	
                            
                        <?php    
						} else {
						
								$my_row=$dbsecurity->get_row();
								$itemid = $my_row['UserId'];
								$userstatusid = $my_row['UserStatusId'];
								$username = $my_row['Username'];
								$userldap = $my_row['LDAPActive'];
								
								// Imagen en el output
                                $icono = "images/imageuser.gif";
                                if ($my_row['UserStatusId'] == 1)  { $icono = "images/imageuseractive.gif"; }
                                if ($my_row['UserStatusId'] == 3)  { $icono = "images/imageuserwarning.gif"; }
                                if ($my_row['UserStatusId'] == 6)  { $icono = "images/imageuserinactive.gif"; }
                                
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
								Perfil Seguridad<br />
								<span class="textMedium"><em><?php echo $my_row['UserProfile']; ?></em></span><br />
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
								<span class="textMedium"><em><?php echo $my_row['UserIPAccess']; ?></em></span><br />
								</td>
							  </tr>
							  <tr>
								<td>
								Vigencia<br />
								<span class="textMedium"><em><?php echo $my_row['PasswordExpireDate']; ?></em></span><br />
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
								<a href="?m=security&s=users&a=edit&n=<?php echo $itemid; ?>">Editar Usuario</a>
								</td>
								<td class="botonstandard">
								<img src="images/bulletlist2.png" />&nbsp;
								<a href="?m=security&s=users&a=viewlog&n=<?php echo $itemid; ?>">Bit&aacute;cora Usuario</a>
								</td>
								<?php if ($my_row['UserStatusId'] == 1) { ?>
									<td class="botonstandard">
									<img src="images/bulletblock.png" />&nbsp;
									<a href="?m=security&s=users&a=block&n=<?php echo $itemid; ?>">Bloquear Usuario</a>
									</td>
								<?php } ?>
								<?php if ($my_row['UserStatusId'] == 3) { ?>
									<td class="botonstandard">
									<img src="images/bulletcheck.png" />&nbsp;
									<a href="?m=security&s=users&a=unblock&n=<?php echo $itemid; ?>">Desbloquear Usuario</a>
									</td>
								<?php } ?>
								<?php if ($my_row['UserStatusId'] < 6) { ?>
									<td class="botonstandard">
									<img src="images/bulletdelete.png" />&nbsp;
									<a href="?m=security&s=users&a=delete&n=<?php echo $itemid; ?>">Eliminar Usuario</a>
									</td>
								<?php } ?>
							  </tr>
							</table>
							<br /><br />
                  <?php } ?>
                   
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

