<?php 
/**
* accessdenied.php
* 	Página acceso denegado.
*
* @author Raul Gutierrez <raul.gutierrez@loyaltydrivers.com>
* @date 20110103
* @version 20110103
* @comments 
*
*/

// CONTAINER
	// Si la invocación no viene del index, PAGE NOT FOUND
	if (!isset($appcontainer)) {
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
	} 

// Obtengo el nombre del script en ejecución
	$script = __FILE__;
	$camino = get_included_files();
	$scriptactual = $camino[count($camino)-1];
	
// Identificamos de donde viene...
	$referer = "";
	if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
	$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
	if ($referer == "") { $referer = "index.php"; }

?>


<!-- MODULO: begin -->
<table class="template">
  <tr>
  	<td>


    <!-- MODULO HEADER -->
  	<span class="templatepath"><a href="index.php">Inicio</a></span><br />
    <br />&nbsp;
	<!--<span class="templatetitle"><?php echo ucwords(strtolower($module)); ?></span>-->
    <span class="templatetitle">Access Denied</span><br />


    <!-- MODULO CONTENIDO: begin -->
    <table class="template">
      <tr>


		    <!-- MODULO BODY: begin -->
        <td class="templatemainbody">

                <br />
                <br />
                
                <table class="tablemessage">
                  <tr>
                    <td bgcolor="#FF0000">&nbsp;</td>
                    <td bgcolor="#F0F0F0">			
                            <br />
                            <img src="images/iconsecurityfirewalloff.png" alt="Access Denied" />
                            <br />
                            <br />
                            <span class="textMedium">Oooops!
                            <br />
                            <br />
                            No tienes privilegios para ingresar a esta sección.</span>
                            <br />
                            <br />
                            Si necesitas acceso a está sección o tienes algún duda sobre tus privilegios, por favor, consulta con tu Administrador de Orvee CRM.
                            <br />
                            <br />
                            <img src="images/bulletleft.png" />&nbsp;<a href="<?php echo $referer; ?>" title="Regresar">Regresar</a><br />
                            <br />
                    </td>
                  </tr>
                </table>

                <br />
                <br />

        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
					<!-- Incluimos el sidebar del modulo-->
                    <?php 

					// Armamos dinamicamente el nombre del sidebar
					$sidebarfile = $module."_sidebar.php";
					
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

