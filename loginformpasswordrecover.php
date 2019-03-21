<?php
/**
*
* TYPE:
*	INCLUDE REFERENCE
*
* loginformpasswordrecover.php
* 	Formulario para recuperación de contraseña.
*
* @version 
*
*/

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
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
			$actionerrorid = 10;
			unset($_POST);
			//include_once("accessdenied.php"); 
			//exit();
		}


	// Controlo hacia donde hay que redirigir al usuario una vez loggeado
	$redirect = "";
	if (isset($_GET['m'])) {
		// Obtengo el modulo que se require
		$redirect = "?m=".setOnlyAppParamChars($_GET['m']);
	}
	
	// Administramos los mensajes de error o notificación al visitante
	$loginmessage = "Ingresa el email de tu cuenta.";
	
	// PWD RECOVER
	if (isset($_POST['action'])) {
		
		// Extraemos el email para envío de pwd...
		$emailpwd = $_POST['email']; 
		
		// Validamos el email ingresado...
		if (isValidSecurityEmail($emailpwd) == 1) {
		
				// Generamos el nuevo password y lo encriptamos...
					$NewUser['password'] = createRandomString(8);
					$NewUser['passwordencripted'] = encryptPassword($NewUser['password']);
		
				// Agregamos el USER a la aplicación...
					$query  = "EXEC dbo.usp_app_SecurityUserManage 
										'0',
										'".$configuration['appkey']."',
										'passwordrecover', 
										'0',
										'',
										'".$NewUser['passwordencripted']."',
										'',
										'1',
										'',
										'',
										'".$emailpwd."',
										'',
										'',
										'0';";
					$dbsecurity->query($query);
					$my_row=$dbsecurity->get_row();
					$userid	 	= $my_row['UserId']; 
					$username 	= $my_row['Username']; 
					$name 		= $my_row['Name']; 
					$userstatus = $my_row['UserStatusDescription']; 
					$userexpiry = $my_row['UserExpiryDate']; 
					//$userdate	= $my_row['UserFecha']; 
					$actionerrorid 	= $my_row['Error']; 
		
					// Si el USER fue agregado exitosamente...
					if ($actionerrorid == 0) {
						
						// EMAIL FROM & TO
							$EmailMessage['From'] 	  = $configuration['adminemail'];
							$EmailMessage['FromName'] = $configuration['adminname'];
							$EmailMessage['To']   	  = $emailpwd;
							$EmailMessage['ReplyTo']  = $configuration['adminreplyto'];
							$EmailMessage['Cc']  	  = $configuration['admincc'];
							$EmailMessage['Bcc']  	  = $configuration['adminbcc'];
			
						// EMAIL HEADERS
							$EmailMessage['Headers']  = "";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$userid.".".$NewUser['passwordencripted']."@".$configuration['appkey']."\r\n";
							$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$NewUser['passwordencripted']."\r\n";
							
						// EMAIL CONTENT					
							$EmailMessage['Subject'] = "Tu Nueva Contraseña";				
							$EmailMessage['Content'] = "templates/UserPassword.html";
							$EmailMessage['Body']	 = implode('', file($EmailMessage['Content']));
							$EmailMessage['Body'] = str_replace("|NAME|", ucwords($name), $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|USERNAME|", $username, $EmailMessage['Body']);
							$EmailMessage['Body'] = str_replace("|PASSWORD|", $NewUser['password'], $EmailMessage['Body']);
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
							
							if ($EmailMessageSent == 1) {
								// Definir error
								$actionerrorid = 0;
							} else {
								// Definir error
								$actionerrorid = 111;
							}
						
					} else {
						// Definir acciones de error...
						$EmailMessageSent = 0;
					} // if ($actionerrorid == 0)

		} else {
			// Definir error
			$actionerrorid = 111;
			$emailpwd = setOnlyCharactersValid($_POST['email']); 
		}

		// Mensaje a mostrar...
		if ($actionerrorid == 0) {
			
			if ($EmailMessageSent == 1) {
				$loginmessage  = "<p class='messageinfo'>La contrase&ntilde;a ha sido enviada a ";
				$loginmessage .= "<em><strong>".$emailpwd."</strong></em>!<br /></p>";
			} else {
				$loginmessage  = "<p class='messagealert'><strong>Oooops!</strong><br />";
				$loginmessage .= "El email con la contrase&ntilde;a nueva, no pudo ser enviado a ";
				$loginmessage .= "<em><strong>".$emailpwd."</strong></em>, por favor, reintente!<br /></p>";
			}
	
		} else {
			$loginmessage = "<p class='messageerror'>
								<strong>Oooops!</strong><br />
								El email <em><strong>".$emailpwd."</strong></em> no pertenece a ningún usuario!.&nbsp;
								<em>[Err ".$actionerrorid."]</em>
								</p>";
								
			if ($actionerrorid == 115) {
					$loginmessage = "<p class='messageerror'>
										<strong>Oooops!</strong><br />
										La contrase&ntilde;a del email <em><strong>".$emailpwd."</strong></em> no puede ser recuperada!. <br />
										Por favor, contacta a tu Administrador de Dominio.&nbsp;
										<em>[Err ".$actionerrorid."]</em>
										</p>";
			}
		}


	} // if (isset($_POST['action']))
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $configuration['apptitle']; ?> | Recuperar Contraseña</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="apple-touch-icon.png" />
<link href="style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="includes/formcheck.js"></script>

<script type="text/javascript" language="JavaScript">
<!--
function CheckRequiredFields() {
	var errormessage = new String();
	// Put field checks below this point.
	
	if(WithoutContent(document.orveefrmpwdrecover.email.value))
		{ errormessage += "- Ingresa el email de tu cuenta!.<br />"; }
	
	// Put field checks above this point.
	if(errormessage.length > 2) {
		var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
		var contenidofooter = "</p>";
		//alert('Por favor: ' + errormessage);
		document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
		return false;
		}
	//document.loginform.submit();
	//var contenidoheader = "<p class='messageinfo'>Tu contrase&ntilde;a ha sido enviada!<br />";
	//var contenidofooter = "</p>";
	//alert('Por favor: ' + errormessage);
	//document.loginformpwdrecover.email.value = "";
	//document.getElementById("loginresult").innerHTML = contenidoheader+contenidofooter;
	
	//document.orveefrmpwdrecover.submit();
	document.forms["orveefrmpwdrecover"].submit();
	return true;
} // end of function CheckRequiredFields()
//-->
</script>

</head>
<body onload="if(top.location!=self.location) top.location=self.location;">

    <br />
    <br /><br />
    
    <table class="logincontainer">
    
              <!-- TITLE: begin -->
              <tr>
                <td class="containertitle" colspan="2">
            
                    <table class="containertitlehead">
                      <tr>
                        <td valign="middle" align="left">
                        &nbsp;&nbsp;&nbsp;
                        <span style="color:#FFF; font-size:16px; font-style:bold;">
						<?php echo $configuration['instancefirstname']; ?></span><br />
                        &nbsp;&nbsp;&nbsp;
                        <span style="color:#FFF; font-size:32px; font-style:bold;">
						<?php echo $configuration['instancelastname']; ?></span>
                        </td>
                        <td>&nbsp;</td>
                        <td class="containertitleheadcelda" align="right">
                        <a href="?m=home"><img src="images/applicationlogo.png" alt="ApplicationLogo" title="Ir a Inicio" /></a>
                        &nbsp;&nbsp;&nbsp;
                        </td>
                      </tr>
                    </table>
            
                </td>
              </tr>
              <!-- TITLE: end -->

          <!-- MENU: begin -->
            <tr>
            <td class="containermenu" colspan="2">

                    <table class="containermenuitems">
                      <tr class="backgroundmenuline">
                        <td><img src="images/spacer.gif" alt="" height="5px" /></td>
                      </tr>
                    </table>

            </td>
         	</tr>
          <!-- MENU: end -->
              
			  <?php if ($actionerrorid == 115) { ?>
                    <tr bgcolor="#FFF200">
                    <td align="center" style="font-size:12px; color:#000; padding: 8px 8px 8px 8px;" colspan="2">
                        <img src="images/security_warning.ico" alt="ApplicationLogo" />&nbsp;
                        <!--<strong>Oooops!</strong>&nbsp;Tu contrase&ntilde;a est&aacute; pr&oacute;xima a expirar!.&nbsp;-->
                        <strong>Oooops!</strong>&nbsp;Para poder recuperar tu contrase&ntilde;a debes acudir con tu Administrador de Dominio.
                    </td>
                    </tr>
			  <?php } ?>

            <!-- MODULO CONTENIDO: begin -->
              <tr>
        
                    <!-- MODULO BODY: begin -->
                <td class="templatemainbody">
    
                        <br />
			    <form action="index.php<?php echo $redirect; ?>" method="post" name="orveefrmpwdrecover">
                        <input name="action" type="hidden" value="recover" />
                		<input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                        <table width="100%" border="0" cellspacing="5" cellpadding="10" align="center">
                          <tr>
                            <td width="30%" valign="top" align="center"><img src="images/security_warning.png" alt="Contraseñas" /> 
                            </td>
                            <td width="70%">
                            <span class="templatetitle">Recuperar Contrase&ntilde;a</span>
                            <br /><br />
                            Email<br />
                            <input name="email" id="email" type="text" class="inputlogin" size="50" title="Ingresa el email de tu cuenta" /><br /><br />
                                <table align="left">
                                  <tr>
                                    <td class="botonlogin" onclick="javascript:CheckRequiredFields();"><img src="images/bulletapply.png" />&nbsp;<a href="javascript:CheckRequiredFields();">Recuperar Contrase&ntilde;a</a></td>
                                  </tr>
                                </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td width="30%" valign="top" align="center">&nbsp;</td>
                            <td width="70%">
                            <div id="loginresult">
                          	<?php echo $loginmessage;  ?>
                          	</div>
                            </td>
                          </tr>
                        </table>
			    </form>
    
                </td>
                    <!-- MODULO BODY: end -->
        
        
                    <!-- MODULO TOOLBAR: begin -->
                <td class="loginsidebar">

                            <table class="sidebar">
                            <tr>
                            <td>
                            <img src="images/bulletright.png" alt="Iniciar Sesión" />&nbsp;<a href="index.php">Iniciar Sesi&oacute;n</a>
                            <br />
                            <img src="images/bulletoff.png" alt="Soporte" />&nbsp;<a href="?m=loginpasswordsupport">Soporte</a><br />
                            </td>
                            </tr>
                        	</table>
            
                </td>
                    <!-- MODULO TOOLBAR: end -->
        
              </tr>          
            <!-- MODULO CONTENIDO: end -->
            
      <tr class="logincontainerfooter">
        <td colspan="2" class="textWhite">&nbsp;</td>
      </tr>
    
    </table>
    
    <br />
    
        <!-- FOOTER: begin -->
            <?php require("footer.php"); ?>
        <!-- FOOTER: end -->

</body>
</html>