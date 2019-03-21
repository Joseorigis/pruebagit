<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* interactions.php
* 	Página principal del módulo de administración de interacciones con los afiliados.
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

			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');

// REFERER
	// Identificamos de donde viene...
	$referer = "";
	if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
	$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
	if ($referer == "") { $referer = "index.php"; }

?>

		<script type="text/javascript">
            jQuery(document).ready(function() {
                 jQuery("abbr.timeago").timeago();
            });
        </script>

<!-- MODULO: begin -->
<table class="template">
  <tr>
  	<td>

        <!-- MODULO HEADER:begin -->
			<?php require_once('headertitle.php') ; ?>
        <!-- MODULO HEADER:end -->

    <!-- MODULO CONTENIDO: begin -->
    <table class="template">
      <tr>

		    <!-- MODULO BODY: begin -->
        <td class="templatemainbody">
        
        <br />

                        <?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								 $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>

									<ul id="rulestabs" class="shadetabs2">
									<li><a href="#" class="selected" rel="#default" title="Bonus">Bonificaciones</a></li>
									<li><a href="rules/rules_items.php?t=discounts" rel="tabcontainer" title="Discounts">Descuentos</a></li>                   
									<li><a href="rules/rules_items.php?t=points" rel="tabcontainer" title="Points">Puntos</a></li>                   
									<li><a href="rules/rules_items.php?t=warnings" rel="tabcontainer" title="Warnings">Alarmas</a></li>                   
									</ul>
									<div id="rulesdivcontainer" class="shadetabs2divcontainer">

										<?php 
										require("rules/rules_items.php");
										?>

									</div>

									<script type="text/javascript">
									var tabs=new ddajaxtabs("rulestabs", "rulesdivcontainer")
									tabs.setpersist(true)
									tabs.setselectedClassTarget("link") //"link" or "linkparent"
									tabs.init()
									</script>
						<?php } ?>
						<br /><br />
        
        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
					<!-- Incluimos el sidebar del modulo-->
                    <?php 

					// Armamos dinamicamente el nombre del sidebar
					$sidebarfile = str_replace(".php", "_sidebar.php", $modulepage);

					// Verificamos si existe el archivo
					if (file_exists($sidebarfile)) { 
						
						// Incluimos la barra lateral
						include_once($sidebarfile); 
						
					} else { 
					
						// Si no hay barra, activamos la default
						$sidebarfile = "home_sidebar.php";
						include_once($sidebarfile); 
						
					} 	
					
					?>

        </td>
            <!-- MODULO TOOLBAR: end -->

      </tr>
    </table>
    <!-- MODULO CONTENIDO: end -->

    
	<br />
	</td>
  </tr>
</table>
<!-- MODULO: end -->
