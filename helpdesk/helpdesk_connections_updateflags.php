<?php
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
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
			
		// itemtype
			$itemtype = 'na';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'na'; }
			}
			$itemtype = strtolower($itemtype);

			// connectioncode
			$connectionflag = "";
			if (isset($_GET['connectionflag'])) {
				$connectionflag = setOnlyText($_GET['connectionflag']);
			//} else {
			//	$actionerrorid = 1;
			}

		// ticket action
			$operation = "activateflag";

			
		// CONNECTION SEARCH
			$connectionid  			= $itemid;
			$connectionname			= '';
			$connectionsallowed 	= 0;
			$connectionsused 		= 0;
			$connectionsusedlast 	= 0;
			
					// SET RECORDS...
					$items = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDeskConnectionsManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'".$operation."', 
										'".$itemtype."', 
										'".$itemid."',
										'".$actionauth."',
										'".$connectionflag."';";
					$dbtransactions->query($query);
					$items = $dbtransactions->count_rows(); 	// Total de elementos
					if ($items > 0) {
						$my_row=$dbtransactions->get_row();
						$actionerrorid 		= $my_row['Error']; 
						if ($actionerrorid == 0) {
							$connectionname 	= $my_row['ConnectionName']; 
						}
						
					} else {
						$actionerrorid = 2;
					}


		
	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }
	
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
                                Flags Conexi&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Conexi&oacute;n<br />
                    <span class="textMedium"> 
                    <?php echo $connectionname; ?> [<?php echo $connectionid; ?>]
                    </span><br />
                    </td>
                  </tr>
                
						<?php 
                        // Si el usuario fue eliminado con exito....
                        if ($actionerrorid == 0) { 
                        ?>
                              <tr>
                                <td>
            
                                    <img src="images/iconresultok.png" /><br /><br />
                                    La CONEXI&Oacute;N ha sido ACTUALIZADA!.<br />
                                    <br />
                                    <br />
                                </td>
                              </tr>                          

                        <?php } else { ?>	
                              
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                La CONEXI&Oacute;N NO pudo ser ACTUALIZADA!.<br />
                                <br />
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
                        <img src="images/bulletconfigure.png" />&nbsp;
                        <a href="?m=helpdesk&s=connections&a=view&n=<?php echo $itemid; ?>">Ver Conexi&oacute;n</a>
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

