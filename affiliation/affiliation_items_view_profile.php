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

		// Cardnumber
		//$cardnumber = '';
		if (isset($_GET['cardnumber'])) {
			$cardnumber = setOnlyNumbers($_GET['cardnumber']);
			if (!is_numeric($cardnumber)) { $cardnumber = ''; }
		}

?>

							<!-- AFFILIATION ITEM PERFIL -->
							<?php
                            
                                // Obtengo el índice del paginado
                                $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem '".$itemid."', '0';";
                                $dbconnection->query($query);
                                $my_row=$dbconnection->get_row();
                            
                            ?>
                            <br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="2">Afiliaci&oacute;n</td>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td class="itemdetailconcept">Miembro desde:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['CardMemberSince']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Fecha Afiliaci&oacute;n:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['CardAffiliationDate']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Medio Afiliaci&oacute;n:</td>
                                <td class="itemdetailcontent">
                                <?php echo $my_row['AffiliationUser']; ?><br />
                                <span style="font-size:10px;font-style:italic;">
								&nbsp;&nbsp;&nbsp;
                                @ <?php echo $my_row['AffiliationPlace']; ?>
                                </span>
                                </td>
                              </tr>
                              </tbody>
                            </table>
                            <br /><br /><br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="2">Generales</td>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td class="itemdetailconcept">Genero:</td>
                                <td class="itemdetailcontent"><?php echo $my_row['CardGender']; ?></td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Fecha Nacimiento:</td>
                                <td class="itemdetailcontent">
									<?php echo $my_row['CardBirthDate']; ?><br />
									<em>&middot;&nbsp;<?php echo $my_row['CardAge']; ?> a&ntilde;os</em>
                                </td>
                              </tr>
                              </tbody>
                            </table>
                            <br /><br /><br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="2">Contacto</td>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td class="itemdetailconcept">Permiso Contacto:</td>
                                <td class="itemdetailcontent">
								<?php if ($my_row['CardContactPermission'] == 1) { ?>

                                    <table width="100%" border="0">
                                      <tr>
                                        <td align="left">
                                            <img src="images/iconacceptblue.png" alt="OK" />
                                        </td>
                                        <td align="right">
        
                                            <span style="font-size:9px;">
                                            <a href="?m=affiliation&s=itemspermission&a=update&n=<?php echo $itemid; ?>&permission=0" title="Eliminar Permiso Contacto">
                                            <img src="images/bulletremove.png" />&nbsp;ELIMINAR
                                            </a>
                                            </span>                                
                                            
                                        </td>
                                      </tr>
                                    </table>
                                    
								<?php } else { ?>
                                
                                    <table width="100%" border="0">
                                      <tr>
                                        <td align="left">
                                            <img src="images/iconcancel.png" alt="No contactar" />
                                        </td>
                                        <td align="right">
        
                                            <span style="font-size:9px;">
                                            <a href="?m=affiliation&s=itemspermission&a=update&n=<?php echo $itemid; ?>&permission=1" title="Activar Permiso Contacto">
                                            <img src="images/bulletcheck.png" />&nbsp;ACTIVAR
                                            </a>
                                            </span>                                
                                            
                                        </td>
                                      </tr>
                                    </table>
                                    
								<?php } ?>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Email:</td>
                                <td class="itemdetailcontent">
								<?php echo $my_row['CardEmail']; ?> 
								<?php if (trim($my_row['CardEmail']) == "") { ?>
                                 	&nbsp;<img src="images/iconcancel.png" alt="Preferencia Contacto Inactiva" title="No ha proporcionado" />
								<?php } else { ?>
									<?php if ($my_row['CardContactPermission'] == 1 && isValidEmail(trim($my_row['CardEmail']))) { ?>
                                        &nbsp;<img src="images/iconacceptgreen.png" alt="Preferencia Contacto Activa" title="Preferencia Contacto Activa" />
                                    <?php } else { ?>
                                        &nbsp;<img src="images/iconremove.png" alt="Preferencia Contacto Inactiva" title="Preferencia Contacto Inactiva" />
                                    <?php } ?>
								<?php } ?>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Tel&eacute;fono:</td>
                                <td class="itemdetailcontent">
								<?php echo $my_row['CardContactPhone']; ?>
								<?php if (trim($my_row['CardContactPhone']) == "") { ?>
                                 	&nbsp;<img src="images/iconcancel.png" alt="Preferencia Contacto Inactiva" title="No ha proporcionado" />
								<?php } else { ?>
									<?php if ($my_row['CardContactPermission'] == 1 && strlen(trim($my_row['CardContactPhone'])) > 6) { ?>
                                        &nbsp;<img src="images/iconacceptgreen.png" alt="Preferencia Contacto Activa" title="Preferencia Contacto Activa" />
                                    <?php } else { ?>
                                        &nbsp;<img src="images/iconremove.png" alt="Preferencia Contacto Inactiva" title="Preferencia Contacto Inactiva" />
                                    <?php } ?>
								<?php } ?>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailconcept">Celular:</td>
                                <td class="itemdetailcontent">
								<?php echo $my_row['CardCellularPhone']; ?> 
								<?php if (trim($my_row['CardCellularPhone']) == "") { ?>
                                 	&nbsp;<img src="images/iconcancel.png" alt="Preferencia Contacto Inactiva" title="No ha proporcionado" />
								<?php } else { ?>
									<?php if ($my_row['CardContactPermission'] == 1 && strlen(trim($my_row['CardCellularPhone'])) > 6) { ?>
                                        &nbsp;<img src="images/iconacceptgreen.png" alt="Preferencia Contacto Activa" title="Preferencia Contacto Activa" />
                                    <?php } else { ?>
                                        &nbsp;<img src="images/iconremove.png" alt="Preferencia Contacto Inactiva" title="Preferencia Contacto Inactiva" />
                                    <?php } ?>
								<?php } ?>
                                </td>
                              </tr>
                              <tr>
                                <td class="itemdetailfootnote" colspan="2"><img src="images/iconacceptgreen.png" alt="Preferencia Contacto Activa" title="Preferencia Contacto Activa" /> Preferencias Contacto Activas</td>
                              </tr>
                              </tbody>
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
                            <!--<table class="botones">
                              <tr>
                                <td class="botonstandard"><img src="images/bulletedit.png" />&nbsp;<a href="#">Editar Perfil</a></td>
                              </tr>
                            </table>
                            <br /><br />-->
							<!-- AFFILIATION ITEM PERFIL -->
                            