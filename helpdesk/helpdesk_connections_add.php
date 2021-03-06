<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
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

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();
		// ERROR MESSAGE
		$errormessage = "";


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}


	// PARAMETER VALIDATION
		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}	

		// actionauth 
			$actionauth = '';
			if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
			if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
			if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio


		// itemtype
			$itemtype = 'connections';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'connections'; }
			}
			$itemtype = strtolower($itemtype);
			
			
		// connection data
			$connectionid = "0";
			// connectionname
			$connectionname = "";
			if (isset($_GET['connectionname'])) {
				$connectionname = setOnlyText($_GET['connectionname']);
				if ($connectionname == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El nombre de la conexi�n ingresado no es v&aacute;lido!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// connectioncode
			$connectioncode = "";
			if (isset($_GET['connectioncode'])) {
				$connectioncode = setOnlyText($_GET['connectioncode']);
			//} else {
			//	$actionerrorid = 1;
			}
			
			// connectiontype
			$connectiontype = "";
			if (isset($_GET['connectiontype'])) {
				$connectiontype = setOnlyText($_GET['connectiontype']);
				if ($connectiontype == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El tipo de conexi�n ingresado no es v&aacute;lido!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// connectionapp
			$connectionapp = "";
			if (isset($_GET['connectionapp'])) {
				$connectionapp = setOnlyText($_GET['connectionapp']);
				if ($connectionapp == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;La aplicaci�n de la conexi�n ingresada no es v&aacute;lida!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// connectionservice
			$connectionwebservice = "0";
			if (isset($_GET['connectionwebservice'])) {
				$connectionwebservice = setOnlyCharactersValid($_GET['connectionwebservice']);
				if ($connectionwebservice == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El servicio web de la conexi�n ingresada no es v&aacute;lida!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// connectionlicenses
			$connectionlicenses = "1";
			if (isset($_GET['connectionlicenses'])) { 
				$connectionlicenses = setOnlyNumbers($_GET['connectionlicenses']); 
				if ($connectionlicenses == "") { $connectionlicenses = "1"; }
			}
			// connectiondates
			$connectionactivation = "";
			if (isset($_GET['connectionactivation'])) {
				$connectionactivation = setOnlyNumbers($_GET['connectionactivation']);
				if (strlen($connectionactivation) == 8) {
					$connectionactivation = substr($connectionactivation,4,4).substr($connectionactivation,2,2).substr($connectionactivation,0,2);
					if (isValidDate($connectionactivation) == 0) {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de activaci�n ingresada no es v&aacute;lida!<br />";
					}
				} else {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de activaci�n ingresada no es v&aacute;lida!<br />";
				}
			} else {
				$actionerrorid = 1;
			}
			$connectionexpiration = "";
			if (isset($_GET['connectionexpiration'])) {
				$connectionexpiration = setOnlyNumbers($_GET['connectionexpiration']);
				if (strlen($connectionexpiration) == 8) {
					$connectionexpiration = substr($connectionexpiration,4,4).substr($connectionexpiration,2,2).substr($connectionexpiration,0,2);
					if (isValidDate($connectionexpiration) == 0) {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de vencimiento ingresada no es v&aacute;lida!<br />";
					}
				} else {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de vencimiento ingresada no es v&aacute;lida!<br />";
				}
			} else {
				$actionerrorid = 1;
			}

			// connectionextras
			$connectionextras = '';
			if (isset($_GET['singlestore'])) { 
				$connectionextras .= '|singlestore';
			}

			// connectionkey
			$connectionkey = "";
		

	// RECORD PROCESS...	
		// Si no hay error hasta aqu�, procesamos...
		$operation = "add";
		if ($actionerrorid == 0) {

					// Transactions Database
					include_once('includes/databaseconnectiontransactions.php');
		
					// Procesamos el registro...
					$records = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'".$operation."', 
										'".$itemtype."', 
										'".$actionauth."',
										'".$actionauth."',
										'".$connectionlicenses."',
										'".$connectionname."',
										'".$connectiontype."',
										'".$connectioncode."',
										'".$connectionapp."',
										'".$connectionwebservice."',
										'".$connectionactivation."',
										'".$connectionexpiration."',
										'".$connectionextras."';";
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						$my_row=$dbtransactions->get_row();
						$itemid			 	= $my_row['ConnectionId']; 
						$actionerrorid 		= $my_row['Error']; 
						if ($actionerrorid == 0){
							$connectionid	= $my_row['ConnectionId']; 
							$connectionname	= $my_row['ConnectionName']; 
							$connectionkey	= $my_row['ConnectionKey']; 
							
							// SEND NOTIFY
							if ($actionerrorid == 0) { 
								$OperationContent = $my_row;
								$OperationAction = "";
								$OperationAction = $operation;
								require_once('includes/HelpDeskConnectionKeySend.php');
							}
								
						}

					} else {
						$actionerrorid = 66;
					}
					
						
		} // if ($actionerrorid == 0)
		

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

                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagesettings.png" alt="Help Desk" title="Help Desk" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Help Desk<br />
                                Nueva Conexi&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Conexi&oacute;n<br />
                    <span class="textMedium"><em><?php echo $connectionname; ?> [<?php echo $connectionid; ?>]</em></span><br />
                    <br />
                    <span style="color:#F0F0F0;">
                    <?php echo $connectionkey; ?><br />
                    </span>
                    <br />
                    </td>
                  </tr>
     
					<?php 
					
                    // Si el usuario fue eliminado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La CONEXI&Oacute;N ha sido CARGADA!.<br />
                                <br />
                                <br />

                            </td>
                          </tr>   
                                                 
					<?php } else { ?>	
                          
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                La CONEXI&Oacute;N NO pudo ser CARGADA!.<br />
                                <br />
                                <?php
									// Error message...
									switch ($actionerrorid) {
										case 1:
											echo "La informaci&oacute;n ingresada est&aacute; incompleta.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										case 2:
											echo "La informaci&oacute;n ingresada es incorrecta.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										default:
											echo "Ocurri&oacute; un error con el procesamiento del registro.<br />";
											echo "Por favor, intente m&aacute;s tarde.<br />";
									}
								
								?>	
                                <span style="font-style:italic;">
									<?php 
                                    if (isset($errormessage)) {
                                            if ($errormessage !== "") {
                                                echo "<br />";
                                                echo $errormessage;
                                            }
                                    }
                                    ?>
                                </span>
                                <br />
                                <span style="font-style:italic;font-size:11px;color:#ADB1BD;">
								<?php echo $actionauth; ?> [Err <?php echo $actionerrorid; ?>]
                                </span>
                                <br />

                            </td>
                          </tr>     
					<?php } ?>	
                    </table>
                    

                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/bulletrules.png" />&nbsp;
                            <a href="?m=helpdesk&s=<?php echo $itemtype; ?>&a=view&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Ver Conexi&oacute;n</a>
                            </td>
                            <td class="botonstandard">
                            <img src="images/bulletadd.png" />&nbsp;
                            <a href="?m=helpdesk&s=<?php echo $itemtype; ?>contact&a=new&connectionid=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Agregar Contacto</a>
                            </td>
                            <td class="botonstandard">
                            <img src="images/bulletadd.png" />&nbsp;
                            <a href="?m=helpdesk&s=<?php echo $itemtype; ?>store&a=edit&connectionid=<?php echo $itemid; ?>&n=0&t=<?php echo $itemtype; ?>">Editar Sucursal Principal</a>
                            </td>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=helpdesk&s=<?php echo $itemtype; ?>&a=new&t=<?php echo $itemtype; ?>">Nueva Conexi&oacute;n</a>
                            </td>
                          </tr>
                        </table>
                    <br /><br />

                
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
