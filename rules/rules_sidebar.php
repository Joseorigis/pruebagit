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

		// itemtype
			//$itemtype = 'warnings';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				//if ($itemtype == '') { $itemtype = 'warnings'; }
			}
			if (isset($itemtype)) {
				$itemtype = strtolower($itemtype);
			} else {
				$itemtype = '';
			}

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
			{ errormessage += "\n- Ingresa los datos de la regla!."; }

		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para buscar la regla, por favor: ' + errormessage);
			document.getElementById("qsearch").focus();
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			return false;
			
		}
			
		document.orveefrmsidesearch.submit();
		return true;
	} // end of function CheckHomeSearchRequiredFields()


//-->
</SCRIPT>    

                        <?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								 $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
								
					<table class="sidebar">
                        <tr>
                        <td>
                      	<form action="index.php" method="get" name="orveefrmsidesearch" onsubmit="return CheckSideSearchRequiredFields()">
                            <input name="m" type="hidden" value="rules" />
                            <input name="s" type="hidden" value="items" />
                            <input name="a" type="hidden" value="view" />
                        	<input class="inputbusquedatext" id="qsearch" type="text" name="q" value="Buscar regla..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
                        </form>
                        </td>
                        </tr>
                    </table>
                    
						<?php } ?>
                   
                    <br />
                                       
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones Reglas Negocio</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                        <img src="images/bullettools.png" />&nbsp;<a href="?m=rules">Reglas Negocio</a><br /> 
                        
                      	<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                        
                        <!-- NEW RULE by ItemType -->   
                        <?php if ($itemtype !== "" && $section !== "") { ?>
                            <img src="images/bulletadd.png" />&nbsp;<a href="?m=rules&s=<?php echo $section; ?>&a=new&t=<?php echo $section; ?>">Nueva Regla</a><br />
                        <?php } // if ($itemtype !== "" && $section !== "") ?>
						<br />
                        
                        <!-- SIDEBAR RULES HOME -->
                        <?php if ($section == "") { ?>

                            <img src="images/bulletpills.png" />&nbsp;<a href="?m=rules&s=item&a=check">Consultar Reglas</a><br />
                            <img src="images/bulletequivalence.png" />&nbsp;<a href="?m=rules&s=pointsitem&a=check">Consultar Equivalencia</a><br />
                            <!--<img src="images/bulletnew.png" />&nbsp;<a href="?m=rules&s=points&a=new">Nueva Regla</a><br />-->
                            <img src="images/bulletequivalencedefault.png" />&nbsp;<a href="?m=rules&s=pointsdefault&a=edit">Equivalencia Default</a><br />
                            <img src="images/bulletgroups.png" />&nbsp;<a href="?m=rules&s=pointsleftover&a=edit">Regla Sobrantes</a><br />
                            <!-- Si hay reglas desaparece? -->
                            <img src="images/bulletoff.png" />&nbsp;<a href="?m=rules&s=pointseveryone&a=edit">Regla Todos</a><br />
                            <!-- Y el factor de conversión de puntos??? -->
                          
                            <?php 
                                // ADMINS ONLY
                                if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
                                    $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                                <br />
                                <img src="images/bulletplay.png" />&nbsp;<a href="?m=rules&s=points&a=publish">Publicar Reglas</a><br />
                                <img src="images/bulletemailnew.png" />&nbsp;<a href="includes/task_RulesWarningsNotificationsSend.php" target="_blank">Reenviar Alarmas Hoy</a><br />
                                <img src="images/bulletappointment.png" />&nbsp;<a href="?m=rules&s=warnings&a=list&t=warnings">Consultar Alarmas</a><br />
                                <img src="images/bulletdown.png" />&nbsp;<a href="index.php?m=reports&s=items&a=download&t=ruleswarningslog&d=week" target="_blank">Descargar Alarmas (Semana)</a><br />
                            <?php } ?>

                        <?php } // if ($section == "") ?>
  
                        <!-- SIDEBAR RULES WARNINGS -->
                        <?php if ($section == "warnings") { ?>
                          
                            <?php 
                                // ADMINS ONLY
                                if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
                                    $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                                <img src="images/bulletemailnew.png" />&nbsp;<a href="includes/task_RulesWarningsNotificationsSend.php" target="_blank">Reenviar Alarmas Hoy</a><br />
                                <img src="images/bulletappointment.png" />&nbsp;<a href="?m=rules&s=warnings&a=list&t=warnings>">Consultar Alarmas</a><br />
                            <?php } ?>

                        <?php } // if ($section == "warnings") ?>
                      
						<br />
                        <img src="images/bulletsettings.png" alt="Configuracion Local" />&nbsp;<a href="?m=<?php echo $module; ?>&s=settings&a=view&t=local">Configuraci&oacute;n Local</a><br />
                        <img src="images/bulletsettings.png" alt="Configuracion Global" />&nbsp;<a href="?m=<?php echo $module; ?>&s=settings&a=view&t=global">Configuraci&oacute;n Global</a><br />
						<br />
                        <img src="images/bullethelpdesk.png" alt="Help Desk" />&nbsp;<a href="?m=helpdesk" target="_blank">Help Desk</a><br />
 
                         <?php } ?>
                       
                        </td>
                        </tr>
                    </table>
                    <br /><br />
