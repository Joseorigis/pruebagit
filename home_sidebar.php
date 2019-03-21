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
		
		var str = document.orveefrmsidesearch.q.value;
		document.orveefrmsidesearch.q.value = str.replace(/^\s*|\s*$/g,"");
		
		var longitud = document.orveefrmsidesearch.q.value.length;

		//if (isNaN(document.orveefrmsidesearch.q.value))
		//	{ errormessage += "\n- Ingresa un número de tarjeta a buscar!."; }

		//if ((errormessage.length == 0) && (longitud != 13))
		//	{ errormessage += "\n- Ingresa un número de tarjeta válido!."; }

		if ((errormessage.length == 0) && (longitud == 0))
			{ errormessage += "\n- Ingresa los datos del afiliado!."; }

		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para buscar al afiliado, por favor: ' + errormessage);
			document.getElementById("qsearch").focus();
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			return false;
			
		}
			
		document.orveefrmsidesearch.submit();
		return true;
	} // end of function CheckHomeSearchRequiredFields()


//-->
</SCRIPT>                   
                    <table class="sidebar">
                        <tr>
                        <td>
                      	<form action="index.php" method="get" name="orveefrmsidesearch" onsubmit="return CheckSideSearchRequiredFields()">
                            <input name="m" type="hidden" value="affiliation" />
                            <input name="s" type="hidden" value="items" />
                            <input name="a" type="hidden" value="view" />
                        	<input class="inputbusquedatext" id="qsearch" type="text" name="q" value="Buscar afiliado..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
                        </form>
                        </td>
                        </tr>
                    </table>
                    <br /><br />
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                        <img src="images/bulletnew.png" />&nbsp;<a href="index.php?m=affiliation&s=items&a=new">Nueva Afiliaci&oacute;n</a><br />
                        <!--<img src="images/bulletnew.png" />&nbsp;<a href="?m=affiliation&s=items&a=new">Nueva Afiliaci&oacute;n</a><br />-->
                        </td>
                        </tr>
                    </table>
                    <br /><br />
