<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* interactions_x.php
* 	Descripci�n de la funci�n.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la p�gina es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=ISO-8859-15');
		// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
	}

// SCRIPT
	// Obtengo el nombre del script en ejecuci�n
	$script = __FILE__;
	$camino = get_included_files();
	$scriptactual = $camino[count($camino)-1];

// CONTAINER & IFRAME CHECK
	// Si el llamado no viene del index o contenedor principal ...PAGE NOT FOUND
	// Si el llamado no viene de una p�gina dentro del mismo dominio ...PAGE NOT FOUND
	if (!isset($_SERVER['HTTP_REFERER'])) {
		if (!isset($appcontainer)) { 
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
		}
	} else {
		
		// INCLUDES & REQUIRES
			if (!isset($appcontainer)) {
				include_once('../includes/configuration.php');	// Archivo de configuraci�n
				include_once('../includes/functions.php');	// Librer�a de funciones
			}
		
		// REQUEST SOURCE VALIDATION
			$requestsource = getRequestSource();
			if ($requestsource !== 'domain' && $requestsource !== 'page') {
				$actionerrorid = 10;
				require_once('../loginwarningtab.php');
				exit();
			}

	}
	
		// Verificamos la p�gina que se esta navegando
		if (!isset($appcontainer)) {
			
			// INIT
				// Iniciamos el controlador de SESSIONs de PHP
				session_start();
			
			// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuraci�n
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexi�n a base de datos
				include_once('../includes/functions.php');	// Librer�a de funciones

			// REDIRECT IF NOT IN IFRAME
				if (!isset($_GET['page'])) {
					echo '&nbsp;';
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

		} 
		
		// IF NO SESSION...
		if (!isset($_SESSION[$configuration['appkey']])) {		
			require_once('../loginwarningtab.php');
			exit();
		}


// --------------------
// INICIO CONTENIDO
// --------------------


	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", getCurrentPageScript());
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		setNavigationLog('navigation', 0, $listmodule.'/'.getCurrentPageScript());


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}
		

	// TRANSACTIONS DATABASE
		include_once('../includes/databaseconnectiontransactions.php');


?>

							<!-- AFFILIATION ITEM -->
							<?php
							
                                // GET RECORDS...
								$items = 0;
								$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsAddressManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."', 
										'list', 
										'".$itemtype."', 
										'".$itemid."',
										'stores';";
                                $dbtransactions->query($query);
 								$items = $dbtransactions->count_rows(); 	// Total de elementos
                            
                            ?>
                            <br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="6">Sucursales</td>
                              </tr>
                              <tr>
                                <td class="itemdetailheader">ID</td>
                                <td class="itemdetailheader">Sucursal</td>
                                <td class="itemdetailheader">Ubicaci&oacute;n</td>
                                <td class="itemdetailheader">C&oacute;digo</td>
                                <td class="itemdetailheader">Contacto</td>
                                <td class="itemdetailheader">Fecha</td>
                              </tr>
                              </thead>
                              <tbody>
                              <?php
			  				// Imprimimos en pantalla cada uno de los par�metros
							while($my_row=$dbtransactions->get_row()){ 
							
											?>
										  <tr>
											<td class="itemdetaillistelement">
                                            <a href="?m=helpdesk&s=connectionsstore&a=edit&n=<?php echo $my_row['StoreId']; ?>&connectionid=<?php echo $my_row['ConnectionId']; ?>" title="Ver Sucursal">
                                            <?php echo $my_row['StoreId']; ?>
                                            </a>
											</td>
											<td class="itemdetaillistelement">
                                            <span style="font-size:9px;">
											<?php echo $my_row['StoreName']; ?>
                                            </span>
											</td>
											<td class="itemdetaillistelement">
                                            <span style="font-size:8px;">
											<?php echo $my_row['StoreAddress']; ?><br />
											<?php echo $my_row['StoreColony']; ?>, <?php echo $my_row['StoreCity']; ?><br />
											<?php echo $my_row['StoreCounty']; ?>, <?php echo $my_row['StoreZipCode']; ?><br />
											<?php echo $my_row['StoreState']; ?>
                                            </span>
											</td>
											<td class="itemdetaillistelement">
                                            <span style="font-size:9px;">
											<?php echo $my_row['StoreCode']; ?>
                                            </span>
											</td>											
											<td class="itemdetaillistelement">
                                            <span style="font-size:8px;">
											Tel: <?php echo $my_row['StorePhone']; ?><br />
											Email: <?php echo $my_row['StoreEmail']; ?>
                                            </span>
											</td>
											<td class="itemdetaillistelement">
                                            <span style="font-size:8px;">
											<?php echo $my_row['StoreDate']; ?>
                                            </span>
											</td>
										  </tr>
										 <?php
										 
							  }
							  // Si no hay elementos a mostrar..
							  if ($items == 0) {
								  ?>
                            	<tr>
                                <td class="itemdetaillistelement" align="center" colspan="6">
                                <div align="center"><em>Sin Sucursales</em></div>
								</td>
                                </tr>
                                  <?php
							  } 
								  ?>
                            	<tr>
                                <td class="itemdetailfootnote" colspan="6">
                                * &Uacute;ltimas 10 sucursales.
								</td>
                                </tr>
                              </tbody>
                            </table>
                            <br />

                            <table class="botones2" align="center">
                              <tr>
                                <td class="botonstandard">
                                <img src="images/bulletadd.png" />&nbsp;
                                <a href="?m=helpdesk&s=connectionsstore&a=new&connectionid=<?php echo $itemid; ?>">Agregar Sucursal</a>
                                </td>
                              </tr>
                            </table> 
                            <br />
                            
                            <table width="90%" border="0" cellspacing="3" align="center">
                              <tr>
                                <td align="right">
                                <span style="color:#F0F0F0;font-style:italic;font-size:9px;">
                                |itemid:<?php echo $itemid; ?>@<?php echo getCurrentPageScript(); ?>|
                                </span>
                                </td>
                              </tr>
                            </table>
                            <br />
							<!-- AFFILIATION ITEM -->
                            