<?php
/**
*
* TYPE:
*	INCLUDE REFERENCE
*
* loginform.php
* 	Formulario de login para la aplicación.
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
		//header ('X-Frame-Options: DENY');
		header ('X-Frame-Options: SAMEORIGIN');
        //ini_set('session.cookie_httponly', 1);
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

	// Controlo hacia donde hay que redirigir al usuario una vez loggeado
	$redirect = "";
	if (isset($_GET['m'])) {
		$redirect = "?m=".setOnlyAppParamChars($_GET['m']);
		if (isset($_SERVER['QUERY_STRING'])) { $redirect = "?".setOnlyCharactersValid($_SERVER['QUERY_STRING']); }
	}
	
	// Administramos los mensajes de error o notificación al visitante
	$loginmessage = "Ingresa tu usuario y contrase&ntilde;a.";
	
	//global $loginmessageid;
	
		// logout
		if ($loginerrorid == 66) {
			$loginmessage = "<p class='messageinfo'>
								Tu sesi&oacute;n ha finalizado!.
								</p>";
		}
		
		// logout
//		if ($loginerrorid == 100) {
//			$loginmessage = "<p class='messageinfo'>
//								Tu sesi&oacute;n ha expirado!.
//								</p>";
//		}

		// login failed
		if ($loginerrorid > 100 && $loginerrorid < 200) {
			$loginmessage = "<p class='messageerror'>
								<strong>Oooops!</strong><br />
								La informaci&oacute;n de usuario o contrase&ntilde;a introducida no es correcta!.&nbsp;
								<em>[Err ".$loginerrorid."]</em>
								</p>";
		}

		// login failed blocked user
		if ($loginerrorid == 104) {
			$loginmessage = "<p class='messageerror'>
								<strong>Oooops!</strong><br />
								El usuario introducido ha sido BLOQUEADO!.&nbsp;
								<em>[Err ".$loginerrorid."]</em>
								</p>";
		}

	// Si todavía hay username en cookie
//	$cookieusername = "";
//	if (isset($_COOKIE[$configuration['appkey']])) {
//		if ($_COOKIE[$configuration['appkey']]['Username'] <> "a" && $_COOKIE[$configuration['appkey']]['Username'] <> "") {
//			$cookieusername = $_COOKIE[$configuration['appkey']]['Username'];
//		}
//	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $configuration['apptitle']; ?> | Iniciar Sesión</title>

	<?php if ($databaseerrorid > 0) { ?>
    <meta http-equiv="refresh" content="<?php echo $databaseerrorrefresh; ?>">
    <?php } ?>
    
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png" />
    <link href="style.css" rel="stylesheet" type="text/css" />
	<!--<link href="dropdownstyles.css" rel="stylesheet" type="text/css">-->
    
    <!--<link rel="stylesheet" type="text/css" href="includes/ajaxtabs/ajaxtabs.css" />-->
   		<script type="text/javascript" src="includes/jquery.min.js"></script>
		<script type="text/javascript" src="includes/formcheck.js"></script>
		<script type="text/javascript" src="includes/jquery.tipsy.js"></script>
        <script type="text/javascript" src="includes/jquery.timeago.js"></script>
		<script type="text/javascript" src="includes/jquery.jdpicker.js"></script>

       
<script type="text/javascript" language="JavaScript">
<!--
function CheckRequiredFields() {
	var errormessage = new String();
	// Put field checks below this point.
	
	if(WithoutContent(document.orveefrmlogin.usernamelogin.value))
		{ errormessage += "- Ingresa tu usuario!.<br />"; }
	if(WithoutContent(document.orveefrmlogin.passwordlogin.value))
		{ errormessage += "- Ingresa tu contraseña!.<br />"; }
	
	// Put field checks above this point.
	if(errormessage.length > 2) {
		var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
		var contenidofooter = "</p>";
		//alert('Por favor: ' + errormessage);
		document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
		return;
		}
	//document.orveefrmlogin.submit();
	//document.forms["orveefrmlogin"].submit();
	//return true;
	document.forms[0].submit();
	return;
	
} // end of function CheckRequiredFields()


// Para detectar si le dieron ENTER a la forma...
function enterKey(evt) { 
  var evt = (evt) ? evt : event 
  var charCode = (evt.which) ? evt.which : evt.keyCode 
  if (charCode == 13) { 
    CheckRequiredFields(); // Lo mandamos a validar la forma
  } 
} 

//-->
</script>

</head>
<body onload="if(top.location!=self.location) top.location=self.location;document.body.onkeypress=enterKey;">

<!-- ERRORS & WARNINGS: begin -->
	<?php if ($databaseerrorid > 0) { ?>
        <table width="100%" border="0" cellspacing="3" bgcolor="#ff0000">
        <td align="center" style="font-size:14px; color:#fff; padding: 8px 4px 8px 4px;">
            <strong>Oooops!</strong>&nbsp; 
            Perdimos conexión con la base de datos, reintentaremos en <?php echo $databaseerrorrefresh; ?> segundos!. 
            <a href='index.php'>Intentar ahora</a>
        </td>
        </tr>
        </table>
    <?php } else { ?>
    	<br />
    <?php } ?>
<!-- ERRORS & WARNINGS: end -->

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
                  
            <!-- MODULO CONTENIDO: begin -->
              <tr>
        
                    <!-- MODULO BODY: begin -->
                <td class="templatemainbody">
    
                        <br />
                    <?php
                        $randomtoken = base64_encode( openssl_random_pseudo_bytes(32));
                        $_SESSION['csrfToken']=$randomtoken;
                    ?>
			    <form action="index.php<?php echo $redirect; ?>" method="post" name="orveefrmlogin" autocomplete="off">
                    <input type="hidden" name="csrfToken" value="<?php echo($_SESSION['csrfToken']); ?>" />
                        <table width="100%" border="0" cellspacing="5" cellpadding="10" align="center">
                          <tr>
                            <td width="30%" valign="top" align="center"><img src="images/security_firewall_on.png" alt="Iniciar Sesión" /> 
                            </td>
                            <td width="70%">
                            <span class="templatetitle">Iniciar Sesi&oacute;n</span>
                            <br /><br />
                            Usuario<br />
                            <input name="usernamelogin" id="usernamelogin" type="text" class="inputlogin" size="30" title="Ingresa tu usuario" onkeypress="return CheckCharactersOnly(event,loginchars);" value="" autocomplete="off" autocapitalize="off" autocorrect="off" /><br /><br />
                            Contrase&ntilde;a<br />
                            <input name="passwordlogin" id="passwordlogin" type="password" class="inputlogin" size="30" title="Ingresa tu contraseña" onkeypress="return CheckCharactersOnly(event,loginchars);" /><br /><br />
                                <table align="left">
                                  <tr>
                                    <td class="botonlogin" onclick="javascript:CheckRequiredFields();"><img src="images/bulletcheck.png" alt="Iniciar Sesión" />&nbsp;<a href="javascript:CheckRequiredFields();">Iniciar Sesi&oacute;n</a></td>
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
                            <img src="images/bulletright.png" alt="Recuperar Contraseña" />&nbsp;<a href="?m=loginpasswordrecover">Recuperar Contrase&ntilde;a</a>
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
                
            <table class="headerfooter">
              <tr>
                <td align="center" class="textInvisible">
				|<?php if (isset($logintype)) { echo $logintype."@".$loginerrorid; } ?>|
                </td>
              </tr>
            </table>        
       
</body>
</html>