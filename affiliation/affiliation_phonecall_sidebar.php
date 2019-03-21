<?php 
/**
*
* TYPE:
*	SIDEBAR
*
* MODULE_sidebar.php
* 	Barra de herramientas o acciones laterales de cada módulo.
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
		header("HTTP/1.0 404 Not Found"); 	
		exit();
	} 


// --------------------
// INICIO CONTENIDO
// --------------------
	
?>
            
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones Llamada</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>

						<?php if ($phonecalltype == "outbound") { ?>
                        <img src="images/bulletleft.png" />&nbsp;<a href="?m=affiliation" target="_blank">Regresar a Lista</a><br />
                        <!--<img src="images/bulletphonecall.png" />&nbsp;<a href="#" target="_blank">Siguiente Llamada</a><br />-->
                        <br />
                        <?php } ?>
                        
						<?php if (isset($_GET['n']) && $action !== "view") { ?>
                        <img src="images/bulletaffiliated.png" />&nbsp;<a href="?m=affiliation&s=items&a=view&n=<?php echo $itemid; ?>" target="_blank">Ver Afiliado</a><br />
                        <img src="images/bulletedit.png" />&nbsp;<a href="?m=affiliation&s=items&a=edit&n=<?php echo $itemid; ?>" target="_blank">Actualizar Afiliado</a><br />
                        <?php } ?>
                        <img src="images/bulletlist2.png" />&nbsp;<a href="http://historial.orbisfarma.com.mx/index.php?action=balance&key=&storeid=0&posid=0&employeeid=<?php echo $_SESSION[$configuration['appkey']]['userid']; ?>&actionauth=0&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Historial</a><br />                        

                        </td>
                        </tr>
                    </table>
                    <br /><br />                    
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones Afiliaci&oacute;n</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                        <!--<img src="images/bulletnew.png" />&nbsp;<a href="http://afiliacion.mazsalud.com/" target="_blank">Nueva Afiliaci&oacute;n</a><br />-->
                        <img src="images/bulletnew.png" />&nbsp;<a href="?m=affiliation&s=items&a=new">Nueva Afiliaci&oacute;n</a><br />
                        <!--<img src="images/bulletnew.png" />&nbsp;<a href="?m=affiliation&s=lists&a=new">Nueva Lista</a><br />-->
                        <br />
                        </td>
                        </tr>
                    </table>
                    <br /><br />
