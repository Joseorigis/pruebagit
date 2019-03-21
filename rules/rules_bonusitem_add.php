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
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);

		// actionauth 
			$actionauth = '';
			if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
			if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
			if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio
		
		// item variables set
			// itemsku
				$itemsku = '';
				if (isset($_GET['itemsku'])) { 
					$itemsku = setOnlyNumbers($_GET['itemsku']);
					if ($itemsku == '') 
						{ $actionerrorid = 2; }
				} else {
					$actionerrorid = 1;
				}
		
			// itemname
				$itemname = '';
				if (isset($_GET['itemname'])) { 
					$itemname = setOnlyCharactersValid($_GET['itemname']);
					if ($itemsku == '') 
						{ $actionerrorid = 2; }
				} else {
					$actionerrorid = 1;
				}
		
			// itembrand
				$itembrand = '';
				if (isset($_GET['itembrand'])) { 
					$itembrand = setOnlyText($_GET['itembrand']);
					//if ($itembrand == '') 
					//	{ $actionerrorid = 2; }
				} else {
					$actionerrorid = 1;
				}
		
			// connectionid
				$connectionid = '1';
				if (isset($_GET['connection'])) { 
					$connectionid = setOnlyNumbers($_GET['connection']); 
				}
				if ($connectionid == '') { $connectionid = '1'; }

			// ruleparams ... for copy
				$ruleparams  = "";
				$ruleparams .= "&t=".$itemtype;
				$ruleparams .= "&item=".$itemsku;
				$ruleparams .= "&connection=".$connectionid;


	// RECORD PROCESS...	
		// Si no hay error hasta aquí, agregamos...
		$operation = "add";
		if ($actionerrorid == 0) {
	
					// TRANSACTIONS DATABASE
					include_once('includes/databaseconnectiontransactions.php');

					$records = 0;
					$query  = "EXEC dbo.usp_app_RulesBonusItemsManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'".$operation."', 
										'crm', 
										'".$itemid."',
										'".$itemsku."',
										'".$itemname."',
										'',
										'".$itembrand."',
										'0',
										'1',
										'0',
										'".$connectionid."',
										'',
										'';";//echo $query;
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						$my_row=$dbtransactions->get_row();
						
						$itemid			 	= $my_row['ItemKey']; 
						$actionerrorid 		= $my_row['Error']; 

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
                                <img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
                                <span class="textMedium">
                                Nueva Art&iacute;culo [Regla Bonificaci&oacute;n]
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culos<br />
                    <span class="textMedium" style="font-style:italic;">
					<?php echo $itemsku; ?><br />
					<?php echo $itemname; ?>
                    </span><br />
                    <br />
                    <span style="font-size:8px;color:#f0f0f0;">
                    	<?php echo $query; ?>
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
                                El Art&iacute;culo ha sido CARGADO!.<br />
                                <br />
                                <br />
        
                            </td>
                          </tr>         
                                           
					<?php } else { ?>	
                          
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                El Art&iacute;culo NO pudo ser CARGADO!.<br />
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
                            <img src="images/bulletadd.png" />&nbsp;
                            <a href="?m=rules&s=bonusitem&a=new&connection=<?php echo $connectionid; ?>&itembrand=<?php echo $itembrand; ?>">Nuevo Art&iacute;culo</a>
                            </td>
                            <?php if ($actionerrorid == 0) { ?>
                                <td class="botonstandard">
                                <img src="images/bulletnew.png" />&nbsp;
                                <a href="?m=rules&s=bonus&a=new<?php echo $ruleparams; ?>">Nueva Regla Art&iacute;culo</a>
                                </td>
                            <?php } ?>
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

