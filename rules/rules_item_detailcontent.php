<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* rules_x.php
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
		$itemtype = 'POINTS';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'POINTS'; }
		}
		$itemtype = strtoupper($itemtype);

?>
configuraciones adhoc o de cada regla, en lenguaje coloquial?.<br>
bonus: 
points:
warnings:

							<!-- RULES ITEM -->
							<?php
                            
                                // Obtengo el contenido del Item
								$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_Interactions".$itemtype."Manage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."', 
										'view', 
										'".$itemtype."', 
										'".$itemid."';";
                                $dbconnection->query($query);
                                $my_row=$dbconnection->get_row();
                            
                            ?>
                            <br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="2">Contenido</td>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td class="itemdetailconcept">C&oacute;digo:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['RuleCode']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Color???:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['RuleCode']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Lista???:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['RuleCode']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Regla:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['RuleCode']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Regla Params:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['RuleCode']; ?></td>
                              </tr>
                              
                              <?php if ($itemtype == 'EMAIL') { ?>
                              
                                      <tr>
                                        <td class="itemdetailconcept">Remitente:</td>
                                        <td class="itemdetailcontent"><?php echo $my_row['InteractionFromName']; ?> [<?php echo $my_row['InteractionFrom']; ?>]</td>
                                      </tr>
                                      <tr>
                                        <td class="itemdetailconcept">Asunto:</td>
                                        <td class="itemdetailcontent"><?php echo $my_row['InteractionSubject']; ?></td>
                                      </tr>
                                      <tr>
                                        <td class="itemdetailconcept">Contenido:</td>
                                        <td class="itemdetailcontent">
                                            <a href="<?php echo $my_row['InteractionContent']; ?>" target="_blank">
                                            <?php echo $my_row['InteractionContent']; ?>
                                            </a>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td colspan="2" align="center" style="padding:10px 0px 10px 0px;">
                                            <iframe seamless src="<?php echo $my_row['InteractionContent']; ?>" width="640" height="320" scrolling="auto"></iframe>
                                        </td>
                                      </tr>
							  <?php } ?>			
                              <?php if ($itemtype == 'SMS') { ?>
                                      <tr>
                                        <td class="itemdetailconcept">Remitente:</td>
                                        <td class="itemdetailcontent"><?php echo $my_row['InteractionFrom']; ?></td>
                                      </tr>
                                      <tr>
                                        <td class="itemdetailconcept">Contenido:</td>
                                        <td class="itemdetailcontent">
                                            <?php echo $my_row['InteractionContent']; ?>
                                        </td>
                                      </tr>
							  <?php } ?>			
                                                                
                              </tbody>
                            </table>
							<br /><br />
                            <!--<table class="buttonsbarborder">
                              <tr>
                                <td class="buttonstandard"><img src="images/bulletedit.png" />&nbsp;<a href="?m=interactions&s=items&a=edit&n=<?php echo $itemid; ?>">Cambiar Contenido</a></td>
                                <td class="buttonstandard"><img src="images/bulletlist2.png" />&nbsp;<a href="?m=interactions&s=items&a=edit&n=<?php echo $itemid; ?>">Editar Contenido</a></td>
                              </tr>
                            </table>
                            <br />-->
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
							<!-- RULES ITEM -->
