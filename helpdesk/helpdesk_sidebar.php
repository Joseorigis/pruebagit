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
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
	} 

// --------------------
// INICIO CONTENIDO
// --------------------

?>
<SCRIPT type="text/javascript">
<!--

	function CheckSideSearchRequiredFields() {
		var errormessage = new String();
		
		var str = document.orveefrmsearch.q.value;
		document.orveefrmsearch.q.value = str.replace(/^\s*|\s*$/g,"");
		
		var longitud = document.orveefrmsearch.q.value.length;

		//if (isNaN(document.orveefrmsearch.q.value))
		//	{ errormessage += "\n- Ingresa un número de tarjeta a buscar!."; }

		//if ((errormessage.length == 0) && (longitud != 13))
		//	{ errormessage += "\n- Ingresa un número de tarjeta válido!."; }

		if ((errormessage.length == 0) && (longitud == 0))
			{ errormessage += "\n- Ingresa los datos de la conexión!."; }

		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para buscar la conexión, por favor: ' + errormessage);
			document.getElementById("qsearch").focus();
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			return false;
			
		}
			
		document.orveefrmsearch.submit();
		return true;
	} // end of function CheckHomeSearchRequiredFields()


//-->
</SCRIPT>

                    <?php if ($section == 'connections') { ?>
					<table class="sidebar">
                        <tr>
                        <td>
                            <form action="index.php" method="get" name="orveefrmsearch" onsubmit="return CheckSideSearchRequiredFields()">
                                <input name="m" type="hidden" value="helpdesk" />
                                <input name="s" type="hidden" value="connections" />
                                <input name="a" type="hidden" value="view" />
                                <input class="inputbusquedatext" id="qsearch" type="text" name="q" value="Buscar Conexi&oacute;n..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
                            </form>    
                        </td>
                        </tr>
                    </table>
                    <br />
					<?php } ?>                    
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                      	<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
	                        <img src="images/bullethelpdesk.png" alt="Help Desk" />&nbsp;<a href="?m=helpdesk">Help Desk</a><br />
                            <br />
	                        <img src="images/bulletadd.png" alt="Help Desk" />&nbsp;<a href="?m=helpdesk&s=connections&a=new">Nueva Conexi&oacute;n</a><br />
                            <br />
	                        <img src="images/bulletright.png" alt="Help Desk" />&nbsp;<a href="includes/task_HelpDeskTicketsOfflineAuthorizedSend.php" target="_blank">Tickets Offline AUTHORIZED</a><br />
	                        <img src="images/bulletright.png" alt="Help Desk" />&nbsp;<a href="includes/task_HelpDeskTicketsOfflineNewSend.php" target="_blank">Tickets Offline NEW</a><br />
	                        <img src="images/bulletdown.png" />&nbsp;<a href="?m=reports&s=items&a=download&t=ticketsoffline" target="_blank">Tickets Offline DOWNLOAD</a><br />
	                        <img src="images/bulletreload.png" />&nbsp;<a href="?m=helpdesk&s=connections&a=syncitems&t=marzam" target="_blank">MARZAM Activar Art&iacute;culos</a><br />
                        <?php } ?>
                        </td>
                        </tr>
                    </table>
                    <br /><br />