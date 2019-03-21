<?php
/**
* loginform.php
*	Despliega el loginform
*
* @author Raul Gutierrez 
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


	// INIT
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
		
	
	// INCLUDES & REQUIRES 
		include_once('includes/configuration.php');	// Archivo de configuración
		include_once('includes/functions.php');	// Librería de funciones
		include_once('includes/database.class.php');	// Class para el manejo de base de datos
		include_once('includes/databaseconnection.php');	// Conexión a base de datos
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $configuration['apptitle']; ?> | Inicio Sesión</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="apple-touch-icon.png" />
<link href="style.css" rel="stylesheet" type="text/css" />

</head>
<body onload="if(top.location!=self.location) top.location=self.location;">

    <br /><br /><br />
    
    <table class="logincontainer">
    
              <!-- TITLE: begin -->
              <tr>
                <td class="containertitle" align="left">
            
                    <table class="containertitlehead" align="left">
                      <tr>
                        <td class="containertitleheadcelda"><img src="images/applicationlogo.png" /></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                    </table>
            
                </td>
              </tr>
              <!-- TITLE: end -->
                  
            <!-- MODULO CONTENIDO: begin -->
              <tr>
        
                    <!-- MODULO BODY: begin -->
                <td bgcolor="#FFFFFF" style="padding: 25px 10px 25px 50px;">
    
                <br />
    
                <table class="tablemessage" align="left">
                  <tr>
                    <td bgcolor="#FF0000">&nbsp;</td>
                    <td bgcolor="#FFFFFF" align="left">			
                            <br />
                            <img src="images/iconsecurityfirewalloff.png" alt="Access Denied" />
                            <br />
                            <br />
                            <span class="textMedium">Oooops!
                            <br />
                            <br />
                            No es posible Iniciar Sesi&oacute;n para tu usuario.</span>
                            <br />
                            <br />
                            Espera por favor, en un momento ser&aacute;s dirigido a Iniciar Sesi&oacute;n...
                            <br />
                            <br />
                            <img src="images/bulletright.png" />&nbsp;<a href="index.php" title="Iniciar Sesión">Iniciar Sesi&oacute;n</a><br />
                            <br />

                    </td>
                  </tr>
                </table>

                <br />
    
                </td>
                    <!-- MODULO BODY: end -->
        
              </tr>          
            <!-- MODULO CONTENIDO: end -->
    </table>
    
    <br />
</body>
</html>