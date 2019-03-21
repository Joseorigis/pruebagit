<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
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
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}	
		

	// PARAMETER VALIDATION
		// Obtenemos el ID del item
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == "") { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = "0"; }
			}
		// Obtenemos el itemtype, el tipo de elemento a consultaar
			$itemtype = 'list';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'list'; }
			}
			$itemtype = strtoupper($itemtype);

		// actionauth 
			$actionauth = '';
			if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
			if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
			if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio

		// LISTID
			$listid = 0;
			if (isset($_GET['n'])) {
				$listid = setOnlyNumbers($_GET['n']);
				if ($listid == "") { $listid = 0; }
				if (!is_numeric($listid)) { $listid = "0"; }
			}

		// LISTNAME
			$listname = "";
			if (isset($_GET['listname'])) {
				$listname = setOnlyCharactersValid($_GET['listname']);
			}

		// LIST TYPE
			$listtype = 'list';
			if (isset($_GET['listtype'])) {
				$listtype = setOnlyLetters($_GET['listtype']);
				if ($listtype == '') { $listtype = 'list'; }
			}
			$listtype = strtoupper($listtype);

		// LIST CONTENT
			$listcontent = '';
			if (isset($_GET['listcontent'])) {
				$listcontent = str_replace("°","'",$_GET['listcontent']);
				//$listcontent = setOnlyCharactersValid($listcontent);
			}

		// LIST ENCODED
			$listencoded = 0;
			if (isset($_GET['listencoded'])) { $listencoded = 1; }


	// RECORD PROCESS...	
		// Si no hay error hasta aquí, agregamos...
		$operation = "add";
		if ($actionerrorid == 0) {
			
					// Encode the list content
					$listcontent = urlencode($listcontent);
	
					$records = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'".$operation."', 
										'".$listtype."', 
										'".$actionauth."',
										'".$listname."',
										'',
										'',
										'0',
										'".$listcontent."',
										'".$listencoded."';";//echo $query;
					$dbconnection->query($query);
					$records = $dbconnection->count_rows(); 
					if ($records > 0) {
						$my_row=$dbconnection->get_row();
						
						$actionerrorid 		= $my_row['Error']; 

					} else {
						$actionerrorid = 66;
					}

		} // if ($actionerrorid == 0)

	// REFERER
		// Identificamos de donde viene...
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
                                <img src="images/imageaffiliationlists.png" alt="Affiliated Status" title="Affiliated Status" class="imagenaffiliationuser" />
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Nueva Lista
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
     
					<?php 
					
                    // Si el usuario fue eliminado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La LISTA <span style="font-weight: bold;"><?php echo $listname; ?></span> ha sido AGREGADA!.<br />
                                <br />
                                <br />

                            </td>
                          </tr>   
                                                 
					<?php } else { ?>	
                          
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                La LISTA NO pudo ser agregada!.<br />
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
										case 252:
											echo "La lista ya existe previamente.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										case 256:
											echo "El SQL tiene errores de sintaxis!.<br />";
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
                    

                        <!--<br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/bulletpointsadd.png" />&nbsp;
                            <a href="?m=interactions&s=<?php echo $itemtype; ?>billing&a=new&t=<?php echo $itemtype; ?>">SMS Balance</a>
                            </td>
                          </tr>
                        </table>-->
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
