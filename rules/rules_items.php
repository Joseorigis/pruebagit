<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* interactions_x.php
* 	Descripción de la función.
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

// CONTAINER & IFRAME CHECK
	// Si el llamado no viene del index o contenedor principal ...PAGE NOT FOUND
	// Si el llamado no viene de una página dentro del mismo dominio ...PAGE NOT FOUND
	if (!isset($_SERVER['HTTP_REFERER'])) {
		if (!isset($appcontainer)) { 
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
		}
	} else {

		// INCLUDES & REQUIRES
			if (!isset($appcontainer)) {
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/functions.php');	// Librería de funciones
			}
		
		// REQUEST SOURCE VALIDATION
			$requestsource = getRequestSource();
			if ($requestsource !== 'domain' && $requestsource !== 'page') {
				$actionerrorid = 10;
				require_once('../loginwarningtab.php');
				exit();
			}

	}
	
		// Verificamos la página que se esta navegando
		if (!isset($appcontainer)) {
			
			// INIT
				// Iniciamos el controlador de SESSIONs de PHP
				session_start();
			
			// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexión a base de datos
				include_once('../includes/functions.php');	// Librería de funciones
				
			// TRANSACTIONS DATABASE	
				include_once('../includes/databaseconnectiontransactions.php');

			// REDIRECT IF NOT IN IFRAME
				?>
                
					<script type="text/javascript">
                        <!--
                        //var isInIFrame = (window.location != window.parent.location)	
                        //if (!isInIFrame) { window.location = "../index.php"; }
                        
                        if (self == top) { window.location = "../index.php"; }
                        
                        -->
                    </script>
                
                <?php
		} 
		

// --------------------
// INICIO CONTENIDO
// --------------------

	// CURRENT PAGE SCRIPT
		$listscriptparts = explode(chr(92), $scriptactual);
		$listscript = $listscriptparts[count($listscriptparts)-1];
	
	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", $listscript);
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		setNavigationLog('navigation', 0, $listmodule.'/'.$listscript);

	// LIST script assembly
		$listpage = $listscript;
		$listpage = str_replace('.php','_list.php', $listpage);


	// ItemType BONUS or POINTS or WARNINGS
		$itemtype = 'BONUS';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'BONUS'; }
		}
		$itemtype = strtoupper($itemtype);
		

?>
			<!-- LIST GRID:begin -->            
                        Reglas <?php echo $itemtype; ?>
                        <br /><br />

                        <?php if ($itemtype == 'POINTS') { ?>
                        
								<?php
            
                                // Obtengo el índice del paginado
                                    $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesIndicators
													'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
													'points', '', '0';";
                                    $dbtransactions->query($query);
                                    $my_row=$dbtransactions->get_row();
                                    $Indicator1	    	= $my_row['Indicator1'];
                                    $Indicator2	    	= $my_row['Indicator2'];
                                    $Indicator3	    	= $my_row['Indicator3'];
                                
                                    $timeago = $my_row['TimeAgo'];
                                    $corte 	 = $my_row['TimeCurrent'];
                                    
                                ?>        
                                    <table class="tableresume">
                                      <tr>
                                        <td>
                                        Reglas<br />
                                        <span class="textLarge"><?php echo $Indicator1; ?></span><br />
                                        <span class="textLight">[Total de reglas activas]</span>
                                        </td>
                                        <td>
                                        Equivalencia Promedio<br />
                                        <span class="textLarge"><?php echo $Indicator2; ?>%</span><br />
                                        <span class="textLight">[Factor o Equivalencia promedio de acumulaci&oacute;n]</span>
                                        </td>
                                        <td>
                                        Equivalencia Default<br />
                                        <span class="textLarge"><?php echo $Indicator3; ?>%</span><br />
                                        <span class="textLight">[Factor o Equivalencia Default de acumulaci&oacute;n]</span>
                                        </td>
                                      </tr>
                                   </table>
	                               <br />          
                                              
                        <?php } ?>
                                           
                        <table width="100%">
                          <tr>
                            <td align="left">
                                <table >
                                  <tr>
                                    <td class="botonstandard"><img src="images/bulletnew.png" />&nbsp;<a href="?m=rules&s=<?php echo strtolower($itemtype); ?>&a=new&t=<?php echo strtolower($itemtype); ?>">Nueva Regla</a></td>
                                    
                        <?php if ($itemtype == 'BONUS') { ?>
                                    <td class="botonstandard"><img src="images/bulletadd.png" />&nbsp;<a href="?m=rules&s=<?php echo strtolower($itemtype); ?>item&a=new&t=<?php echo strtolower($itemtype); ?>">Nuevo Art&iacute;culo</a></td>
                        <?php } ?>
                                    
                                  </tr>
                                </table>
                            </td>
                            <td align="right">
                      		<form action="index.php" method="get" name="orveefrmsidesearch" onsubmit="return CheckSideSearchRequiredFields()">
                            <input name="m" type="hidden" value="rules" />
                            <input name="s" type="hidden" value="items" />
                            <input name="a" type="hidden" value="view" />
                            <input class="inputbusquedatext" id="qsearch" type="text" name="q" value="Buscar regla..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
                            </form>
                            
                            </td>
                          </tr>
                        </table>
                        <br />
                        
                        <div id="ListPlaceholder" style="height:auto;">
                        <?php 
                        require_once($listpage);
                        ?>
                        </div>

			<!-- LIST GRID:end -->            

