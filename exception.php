<?php
/**
* loginform.php
*	Despliega el loginform
*
* @author Raul Gutierrez <raul.gutierrez@loyaltydrivers.com>
* @date 20110103
* @version 20110103
* @comments 
*
*/

// HTML headers
	header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
	header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
	header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
	header ('Pragma: no-cache');	// HTTP/1.0


	// WARNINGS & ERRORS
		//ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');


	// INCLUDES & REQUIRES 
		include_once('includes/configuration.php');	// Archivo de configuración
		//include_once('includes/database.class.php');	// Class para el manejo de base de datos
		//include_once('includes/databaseconnection.php');	// Conexión a base de datos
		include_once('includes/functions.php');	// Librería de funciones


	// INIT
		// Iniciamos el controlador de SESSIONs de PHP
		session_start();
		// Los modulos buscan estan variable, si no está no abren.
		$appcontainer = 1;
		// Indicador del status de login...
		$loginerrorid = 0;
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];


	// Controlo hacia donde hay que redirigir al usuario una vez loggeado
	$redirect = "";
	if (isset($_GET['m'])) {
		$redirect = "?m=".setOnlyAppParamChars($_GET['m']);
		if (isset($_SERVER['QUERY_STRING'])) { $redirect = "?".$_SERVER['QUERY_STRING']; }
	}
	
	// Administramos los mensajes de error o notificación al visitante
	$loginmessage = "Hubo un error!.";
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $configuration['apptitle']; ?> | Iniciar Sesión</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="apple-touch-icon.png" />
<link href="style.css" rel="stylesheet" type="text/css" />

</head>
<body onload="if(top.location!=self.location) top.location=self.location;document.body.onkeypress=enterKey;">
    <br /><br /><br />
    
    <table class="logincontainer">
    
              <!-- TITLE: begin -->
              <tr>
                <td class="containertitle" colspan="2">
            
                    <table class="containertitlehead">
                      <tr>
                        <td class="containertitleheadcelda" align="left"><img src="images/applicationlogo.gif" alt="ApplicationLogo" /></td>
                        <td>&nbsp;</td>
                        <td valign="bottom" align="right">
                        <!--<span style="color:#FFF; font-size:40px; font-style:bold;"><?php echo $configuration['instancename']; ?></span>-->
                        &nbsp;&nbsp;&nbsp;
                        </td>
                      </tr>
                    </table>
            
                </td>
              </tr>
              <!-- TITLE: end -->
                  
            <!-- MODULO CONTENIDO: begin -->
              <tr>
        
                    <!-- MODULO BODY: begin -->
                <td class="templatemainbody">
    
                        <br />
                        <table width="100%" border="0" cellspacing="5" cellpadding="10" align="center">
                          <tr>
                            <td width="30%" valign="top" align="center"><img src="images/security_firewall_off.png" alt="Iniciar Sesión" /> 
                            </td>
                            <td width="70%">
                            <span class="templatetitle">Exception</span>
                            <br /><br />
                            Usuario<br />
                            <input name="usernamelogin" id="usernamelogin" type="text" class="inputlogin" size="30" title="Ingresa tu usuario" onkeypress="return CheckCharactersOnly(event,loginchars);" value="" /><br /><br />
                            Contrase&ntilde;a<br />
                            <input name="passwordlogin" id="passwordlogin" type="password" class="inputlogin" size="30" title="Ingresa tu contraseña" onkeypress="return CheckCharactersOnly(event,loginchars);" /><br /><br />
                                <table align="left">
                                  <tr>
                                    <td class="botonlogin" onclick="javascript:CheckRequiredFields();"><img src="images/bulletapply.png" alt="Iniciar Sesión" />&nbsp;<a href="javascript:CheckRequiredFields();">Iniciar Sesi&oacute;n</a></td>
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
    
                </td>
                    <!-- MODULO BODY: end -->
        
        
                    <!-- MODULO TOOLBAR: begin -->
                <td class="loginsidebar">

                            <table class="sidebar">
                            <tr>
                            <td>
                            <img src="images/bulletright.png" alt="Recuperar Contraseña" />&nbsp;<a href="?m=loginpasswordrecover">Recuperar Contrase&ntilde;a</a>
                            <br />
                            <img src="images/bulletoff.png" alt="Soporte" />&nbsp;<a href="?m=loginsupport">Soporte</a><br />
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