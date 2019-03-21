<?php 
/**
* pagenotfound.php
* 	P�gina inicio de la aplicaci�n
*
* @author Raul Gutierrez <raul.gutierrez@loyaltydrivers.com>
* @date 20110103
* @version 20110103
* @comments 
*
*/

// CONTAINER
	// Si la invocaci�n no viene del index, PAGE NOT FOUND
	if (!isset($appcontainer)) {
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
	} 

// Obtengo el nombre del script en ejecuci�n
	$script = __FILE__;
	$camino = get_included_files();
	$scriptactual = $camino[count($camino)-1];

// Identificamos de donde viene...
	$referer = "";
	if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
	$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
	if ($referer == "") { $referer = "index.php"; }

// Obtenemos a donde quiere llegar
	$QueryStringHeader = "";
	if (isset($_SERVER['QUERY_STRING'])) { $QueryStringHeader = setOnlyCharactersValid(urldecode($_SERVER['QUERY_STRING'])); }

?>


<!-- MODULO: begin -->
<table class="template">
  <tr>
  	<td>


    <!-- MODULO HEADER -->
  	<span class="templatepath"><a href="index.php">Inicio</a></span><br />
    <br />&nbsp;
	<span class="templatetitle">Page Not Found</span><br />


    <!-- MODULO CONTENIDO: begin -->
    <table class="template">
      <tr>


		    <!-- MODULO BODY: begin -->
        <td class="templatemainbody">

                <br />
                <br />
                
                <table class="tablemessage">
                  <tr>
                    <td bgcolor="#FFFF00">&nbsp;</td>
                    <td bgcolor="#F0F0F0">			
                            <br />
                            <img src="images/security_warning.png" alt="Access Denied" />
                            <br />
                            <br />
                            <span class="textMedium">Oooops!
                            <br />
                            <br />
                            La secci&oacute;n que estas buscando no fue encontrada.</span>
                            <br />
                            <br />
                            Si necesitas acceso a est&aacute; secci&oacute;n o tienes alguna duda sobre tus privilegios, por favor, consulta con tu Administrador de OrveeCRM.
                            <br />
                            <br />
                            <img src="images/bulletleft.png" />&nbsp;<a href="<?php echo $referer; ?>" title="Regresar">Regresar</a><br />
							<br />
                            <span style="color:#ADB1BD;font-size:9px;font-style:italic;">
                            <?php echo $referer; ?>
                            <br />
                            <?php echo $QueryStringHeader; ?>
                            <br />
                            </span>
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
					include($sidebarfile);
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

