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

                    <table class="sidebar">
                        <tr>
                        <td>
                        <!--<form action="javascript:ajaxListPageSearch('security/security_users_list.php', '1');" method="get" name="frmbusquedaside">-->
                        <form action="index.php" method="get" name="frmbusquedaside">
                        <input name="m" type="hidden" value="security" />
                        <input name="s" type="hidden" value="users" />
                        <input name="a" type="hidden" value="view" />
                        <input class="inputsidebarbusquedatext" id="qsearch" type="text" name="q" value="Buscar usuario..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
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
                    <img src="images/bulletnew.png" />&nbsp;<a href="?m=security&s=users&a=new">Nuevo Usuario</a><br />
                    </td>
                    </tr>
                </table>
                <br /><br />

            
