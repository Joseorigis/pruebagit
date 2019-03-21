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


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}
		// Obtenemos el itemtype, el tipo de elemento a consultaar
		$itemtype = 'EMAIL';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'EMAIL'; }
		}
		$itemtype = strtoupper($itemtype);


	// TRANSACTIONS DATABASE
		include_once('includes/databaseconnectiontransactions.php');

?>

							<!-- ITEM -->
							<?php
                            
                                // GET RECORDS...
								$items = 0;
								$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."', 
										'view', 
										'".$itemtype."', 
										'".$itemid."';";
                                $dbtransactions->query($query);
 								$items = $dbtransactions->count_rows(); 	// Total de elementos
                                $my_row=$dbtransactions->get_row();
								
                            ?>
                            <br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="2">Resumen Conexi&oacute;n</td>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td class="itemdetailconcept">ID:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionId']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">T&iacute;tulo:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionName']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Descripci&oacute;n:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionName']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">C&oacute;digo:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionCode']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Tipo:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionType']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Flags:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionFlags']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Medio:</td>
                                <td class="itemdetailcontent">
									<?php echo $my_row['ConnectionApp']; ?><br />
                                    <span style="font-size:10px;font-style:italic;">
										<?php echo $my_row['ConnectionService']; ?><br />
                                        <?php echo $my_row['ConnectionLicenses']; ?> licencias
                                    </span>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Status:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionActiveStatus']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Status Llave:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['ConnectionStatus']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">&nbsp;</td>
                                <td class="itemdetailcontent">
                                
                                        <table class="itemdetail">
                                        <thead>
                                          <tr>
                                            <td class="itemdetailheadercenter">
                                            Llave
                                            </td>
                                            <td class="itemdetailheader">
                                            Status
                                            </td>
                                          </tr>
                                        </thead>
                                        
                                        <tbody>
                                          <tr>
                                            <td class="itemdetailconcept">
                                            	Emitida:<br />
                                                <span style="font-size:9px; font-style:italic; font-weight:normal;">
                                                [Key Inactive, Pending Activation]
                                                </span>
                                            </td>
                                            <td class="itemdetailcontent">&nbsp;
                                            	<span style="font-size:10px;">
	                                            <img src="images/iconacceptgreen.png" alt="Activa" />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @ <?php echo $my_row['ConnectionActivationDate']; ?>
                                                </span>                                               
                                            </td>
                                          </tr>
                                          <tr>
                                            <td class="itemdetailconcept">
                                            	Activada:<br />
                                                <span style="font-size:9px; font-style:italic; font-weight:normal;">
                                                [Key Activated]
                                                </span>
                                            </td>
                                            <td class="itemdetailcontent">&nbsp;
                                            	<span style="font-size:10px;">
                                                <?php if ($my_row['ConnectionKeyActive'] == 1) { ?>
                                                    <img src="images/iconacceptgreen.png" alt="Activa" />
                                                     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @ <?php echo $my_row['ConnectionKeyActiveDate']; ?>
                                                <?php } else { ?>
                                                    <img src="images/iconcancel.png" alt="Inactiva" />
                                                <?php } ?> 
                                                </span>                                               
                                            </td>
                                          </tr>
                                          <tr>
                                            <td class="itemdetailconcept">
                                            	Operaci&oacute;n:<br />
                                                <span style="font-size:9px; font-style:italic; font-weight:normal;">
                                                [ONLINE With Operations, No Transactions]
                                                </span>
                                            </td>
                                            <td class="itemdetailcontent">&nbsp;
                                            	<span style="font-size:10px;">
                                                <?php if ($my_row['ConnectionKeyUsed'] == 1) { ?>
                                                    <img src="images/iconacceptgreen.png" alt="Activa" />
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @ <?php echo $my_row['ConnectionKeyUsedDate']; ?>
                                                <?php } else { ?>
                                                    <img src="images/iconcancel.png" alt="Inactiva" />
                                                <?php } ?>
                                                </span>                                               
                                            </td>
                                          </tr>
                                          <tr>
                                            <td class="itemdetailconcept">
                                            	Transacci&oacute;n:<br />
                                                <span style="font-size:9px; font-style:italic; font-weight:normal;">
                                                [ACTIVE With Transactions]
                                                </span>
                                            </td>
                                            <td class="itemdetailcontent">&nbsp;
                                            	<span style="font-size:10px;">
                                                <?php if ($my_row['ConnectionKeyTransaction'] == 1) { ?>
                                                    <img src="images/iconacceptgreen.png" alt="Activa" />
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @ <?php echo $my_row['ConnectionKeyTransactionDate']; ?>
                                                <?php } else { ?>
                                                    <img src="images/iconcancel.png" alt="Inactiva" />
                                                <?php } ?>
                                                </span>                                               
                                            </td>
                                          </tr>

                                            <tr>
                                            <td class="itemdetailfootnote" align="right" colspan="2">
                                            <em>* A <?php echo $my_row['ConnectionDateNow']; ?></em>
                                            </td>
                                            </tr>
                                                      
                                        </tbody>  
                                        </table>
                                        
                                </td>
                              </tr>
                              </tbody>
                            </table>
							<br /><br />
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
							<!-- ITEM -->
                            