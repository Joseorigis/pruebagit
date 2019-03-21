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

	// Administramos los mensajes de error o notificación al visitante
	$loginmessage = "Soporte T&eacute;cnico OrveeCRM";
	$actionerrorid = 0;
	$sessiontimeout = '30';
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $configuration['apptitle']; ?> | Mensaje</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="apple-touch-icon.png" />
<link href="style.css" rel="stylesheet" type="text/css" />

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
              

            <!-- MODULO CONTENIDO: begin -->
              <tr>
        
                    <!-- MODULO BODY: begin -->
                <td class="templatemainbody">
    
                        <br />
			    <form action="index.php<?php echo $redirect; ?>" method="post" name="orveefrmpwdrecover">
                        <input name="action" type="hidden" value="recover" />
                        <table width="100%" border="0" cellspacing="5" cellpadding="10" align="center">
                          <tr>
                            <td width="30%" valign="top" align="center"><img src="images/security_warning.png" alt="Contraseñas" /> 
                            </td>
                            <td width="70%">
                            <span class="templatetitle">Inicio Sesi&oacute;n</span>
                            <br /><br />
                            Ya iniciaste sesi&oacute;n previamente.<br />
                            <br />
                            Por favor, finaliza tu sesi&oacute;n previa en la opci&oacute;n de Salir.<br />
                            <br />
                            Tu sesi&oacute;n previa se liberar&aacute; por inactividad en <?php echo $sessiontimeout; ?> minutos y podr&aacute;s iniciar sesi&oacute;n de nuevo.<br />
                            <br />
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
                            <img src="images/bulletright.png" alt="Recuperar Contraseña" />&nbsp;<a href="?m=loginpasswordrecover">Recuperar Contrase&ntilde;a</a>
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