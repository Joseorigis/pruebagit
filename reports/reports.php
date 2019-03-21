<?php
/**
*
* TYPE:
*	INDEX REFERENCE
*
* reports.php
* 	Página principal del módulo de seguridad y administración de usuarios.
*
* @author 
* @date 
* @version 
* @comments 
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

	// TBD: Botones despues de block, unblock, etc checar iconos y cuales deben estar

?>
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


			<!-- LIST GRID:begin -->            
                <table class="tablelist">
                  <thead>
                  <tr>
                    <td width="40%">Reporte</td>
                    <td width="15%">Tipo</td>
                    <td width="15%">Feed</td>
                    <td width="30%">&nbsp;</td>
                  </tr>
                  </thead>
                  <tbody>
                      <tr>
                        <td><a href="?m=reports&s=cubes">
						Cubos & BI</a><br />
                        Plataforma de Cubos & BI.</td> 
                        <td>Din&aacute;mico</td>
                        <td>Daily</td>
                        <td><img src="images/imagepowerbilogo.jpg" width="32" height="32" /></td>
                      </tr>
                      
                  </tbody>
                </table>
			<!-- LIST GRID:end -->     


        <br />
        <br />
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

