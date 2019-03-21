<?php 
/**
* template.php
* 	Administra el login del usuario.
*	Despliega el loginform y procesa el login
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
		header("HTTP/1.0 404 Not Found"); 	
		exit();
	} 

// Obtengo el nombre del script en ejecución
	$script = __FILE__;
	$camino = get_included_files();
	$scriptactual = $camino[count($camino)-1];

?>


<!-- MODULO: begin -->
<table class="template">
  <tr>
  	<td>


    <!-- MODULO HEADER -->
  	<span class="templatepath"><a href="index.php">Inicio</a> >  <?php echo ucwords(strtolower($module)); ?></span><br />
    <br />&nbsp;
	<span class="templatetitle"><?php echo ucwords(strtolower($module)); ?></span><br />


    <!-- MODULO CONTENIDO: begin -->
    <table class="template">
      <tr>


		    <!-- MODULO BODY: begin -->
        <td class="templatemainbody">
                <br />
                CONTENIDO
                <br />
        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                        <img src="images/bulletnew.png" />&nbsp;<a href="#">Nuevo Usuario</a><br />
                        </td>
                        </tr>
                    </table>
                    <br /><br />
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Recientes</td>
                        </tr>
                    </table>
                    <br /><br />
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Frecuentes</td>
                        </tr>
                    </table>
                    <br />
            
        
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

