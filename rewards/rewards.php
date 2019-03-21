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
        <br />


                    <table width="100%" border="0" style="border-spacing: 20px;border-bottom: 1px solid #ADB1BD;">
                      <tr>
                        <td width="50%">
					<?php

					// Obtengo el índice del paginado
						$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RewardsIndicators
													'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
													'balance', 'history';";
						$dbtransactions->query($query);
						$my_row=$dbtransactions->get_row();
						$Indicator1	    	= $my_row['Indicator1'];
						$Indicator2	    	= $my_row['Indicator2'];
						$Indicator3	    	= $my_row['Indicator3'];
					
						$timeago = $my_row['TimeAgo'];
						$corte 	 = $my_row['TimeCurrent'];
						
					?>        
        
                        <img src="https://chart.googleapis.com/chart?chxs=0,676767,18&chxt=x&chs=300x200&cht=p&chco=ADDE63|63C6DE|FF6342&chd=t:<?php echo $Indicator3; ?>,<?php echo $Indicator2; ?>,0&chl=|<?php echo $Indicator1; ?>%25&chtt=Balance&chts=676767,24" width="300" height="200" alt="BalanceHistorico" />
                        </td>
                         <td width="50%">
					<?php

					// Obtengo el índice del paginado
						$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RewardsIndicators
													'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
													'redemption', 'history';";
						$dbtransactions->query($query);
						$my_row=$dbtransactions->get_row();
						$Indicator1	    	= $my_row['Indicator1'];
						$Indicator2	    	= $my_row['Indicator2'];
						$Indicator3	    	= $my_row['Indicator3'];
						$Indicator4	    	= $my_row['Indicator4'];
						$Indicator5	    	= $my_row['Indicator5'];
					
						$timeago = $my_row['TimeAgo'];
						$corte 	 = $my_row['TimeCurrent'];
						
					?>        
        
                         <img src="https://chart.googleapis.com/chart?chxs=0,676767,18&chxt=x&chs=300x200&cht=p&chco=ADDE63|63C6DE|FF6342&chd=t:<?php echo $Indicator5; ?>,0,<?php echo $Indicator4; ?>&chl=<?php echo $Indicator3; ?>%25||&chtt=Tasa+Redencion&chts=676767,24" width="300" height="200" alt="BalanceActual" />
                         </td>
                      </tr>
                    </table>
            
                        <?php
						
						// Obtengo el índice del paginado
						$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RewardsIndicators
													'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
													'points', 'history';";
						$dbtransactions->query($query);
						$my_row=$dbtransactions->get_row();
						$Indicator1	    	= $my_row['Indicator1'];
						$Indicator2	    	= $my_row['Indicator2'];
						$Indicator3	    	= $my_row['Indicator3'];
						$Indicator4     	= $my_row['Indicator4'];
						$Indicator5	    	= $my_row['Indicator5'];
						$Indicator6	    	= $my_row['Indicator6'];
						$Indicator7	    	= $my_row['Indicator7'];
						$Indicator8	    	= $my_row['Indicator8'];
						$Indicator9	    	= $my_row['Indicator9'];

						$timeago = $my_row['TimeAgo'];
						$corte 	 = $my_row['TimeCurrent'];

						?>
                        <br />
                        <table class="modulesectiontitle">
                            <tr>
                            <td>Balance Recompensas</td>
                            </tr>
                        </table>
                        <br />
                        <table class="tableresume">
                          <tr>
                            <td>
                            Abono [+]<br />
                            <span class="textLarge"><?php echo $Indicator4; ?></span><br />
                            <span class="textLight">[TOTAL de puntos abonados o entregados]</span>
                            </td>
                            <td>
                            Redenci&oacute;n [-]<br />
                            <span class="textLarge"><?php echo $Indicator5; ?></span><br />
                            <span class="textLight">[TOTAL de puntos redimidos o cargados]</span>
                            </td>
                            <td>
                            Saldo<br />
                            <span class="textLarge"><?php echo $Indicator6; ?></span><br />
                            <span class="textLight">[Saldo o balance TOTAL en puntos]</span>
                            </td>
                          </tr>
                       </table>
						<br />
                        <div align="center">
						* Informaci&oacute;n al <?php echo $corte; ?>&nbsp;[<em><abbr class="timeago" title="<?php echo $timeago; ?>"><?php echo $timeago; ?></abbr></em>]
                        </div><br />
                        
     
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

