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

		// actionauth 
			$actionauth = '';
			if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
			if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
			if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio


		// itemtype
			$itemtype = 'warnings';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'warnings'; }
			}
			$itemtype = strtolower($itemtype);
			
			
		// rule data
			// rule name
			$rulename = "";
			if (isset($_GET['rulename'])) {
				$rulename = setOnlyText($_GET['rulename']);
				if ($rulename == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El nombre de al regla ingresado no es v&aacute;lido!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// rule code
			$rulecode = "";
			if (isset($_GET['rulecode'])) {
				$rulecode = setOnlyLetters($_GET['rulecode']);
				if ($rulecode == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El código de la regla ingresado no es v&aacute;lido!<br />";
				} 
			//} else {
			//	$actionerrorid = 1;
			}
			// connectionid
			$connectionid = "1";
			if (isset($_GET['connectionid'])) {
				$connectionid = setOnlyNumbers($_GET['connectionid']);
				if ($connectionid == "") { $connectionid = "1"; }
			}
			
			// rule object
			$ruleobject = "";
			if (isset($_GET['ruleobject'])) {
				$ruleobject = setOnlyLetters($_GET['ruleobject']);
				if ($ruleobject == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El objeto a monitorear ingresado no es v&aacute;lido!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// rule operations
			$ruleoperation = "";
			if (isset($_GET['ruleoperation'])) {
				$ruleoperation = setOnlyLetters($_GET['ruleoperation']);
				if ($ruleoperation == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;La operaci&oacute;n a realizar ingresada no es v&aacute;lida!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// rule operation units
			$ruleoperationunits = "0";
			if (isset($_GET['ruleoperationunits'])) {
				$ruleoperationunits = setOnlyNumbers($_GET['ruleoperationunits']);
				if ($ruleoperationunits == "") { $ruleoperationunits = "0"; }
			}
			if ($ruleoperationunits == "0") { 
				$actionerrorid = 2;
				$errormessage .= "&middot;&nbsp;Las unidades de la operaci&oacute;n a realizar ingresadas no son v&aacute;lidas!<br />";
			} 
			// rule schedule
			$ruleschedule = "";
			if (isset($_GET['ruleschedule'])) {
				$ruleschedule = setOnlyLetters($_GET['ruleschedule']);
				if ($ruleschedule == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El periodo a monitorear ingresado no es v&aacute;lido!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// rule distribution list
			$ruledistributionlist = "";
			if (isset($_GET['ruledistributionlist'])) {
				$ruledistributionlist = setOnlyText($_GET['ruledistributionlist']);
				$ruledistributionlist = str_replace(" ", "", $ruledistributionlist);
				if ($ruledistributionlist == "") { $ruledistributionlist = $_SESSION[$configuration['appkey']]['email']; }
				if (isValidEmailList($ruledistributionlist) == 0) { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;La lista de distribuci&oacute;n ingresada no es v&aacute;lida!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// rule type
			$ruletype = "";
			if (isset($_GET['ruletype'])) {
				$ruletype = setOnlyLetters($_GET['ruletype']);
				if ($ruletype == "") { 
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El tipo de regla ingresado no es v&aacute;lido!<br />";
				} 
			} else {
				$actionerrorid = 1;
			}
			// rule affiliation status
			$ruleactionstatusid = "1";
			if (isset($_GET['ruleactionstatusid'])) {
				$ruleactionstatusid = setOnlyNumbers($_GET['ruleactionstatusid']);
				if ($ruleactionstatusid == "") { $ruleactionstatusid == "1"; } 
			} else {
				$actionerrorid = 1;
			}
			// rule list
			$rulelistid = "0";
			if (isset($_GET['rulelist'])) { 
				$rulelistid = setOnlyNumbers($_GET['rulelist']); 
				if ($rulelistid == "") { $rulelistid = "1"; }
			}
			if ($rulelistid == "0") {
				if (isset($_GET['rulelistid'])) { 
					$rulelistid = setOnlyNumbers($_GET['rulelistid']); 
					if ($rulelistid == "") { $rulelistid = "1"; }
				}
			}
			// rule activation date
			$ruleactivationdate = "";
			if (isset($_GET['ruleactivation'])) {
				$ruleactivationdate = setOnlyNumbers($_GET['ruleactivation']);
				if (strlen($ruleactivationdate) == 8) {
					$ruleactivationdate = substr($ruleactivationdate,4,4).substr($ruleactivationdate,2,2).substr($ruleactivationdate,0,2);
					if (isValidDate($ruleactivationdate) == 0) {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de activación ingresada no es v&aacute;lida!<br />";
					}
				} else {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de activación ingresada no es v&aacute;lida!<br />";
				}
			} else {
				$actionerrorid = 1;
			}
			// rule expiration date
			$ruleexpirationdate = "";
			if (isset($_GET['ruleexpiration'])) {
				$ruleexpirationdate = setOnlyNumbers($_GET['ruleexpiration']);
				if (strlen($ruleexpirationdate) == 8) {
					$ruleexpirationdate = substr($ruleexpirationdate,4,4).substr($ruleexpirationdate,2,2).substr($ruleexpirationdate,0,2);
					if (isValidDate($ruleexpirationdate) == 0) {
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
		
		

	// RECORD PROCESS...	
		// Si no hay error hasta aquí, procesamos...
		$operation = "add";
		if ($actionerrorid == 0) {

					// Transactions Database
					include_once('includes/databaseconnectiontransactions.php');
		
					// Procesamos el registro...
					$records = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesWarningsManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'add', 
										'".$itemtype."', 
										'".$actionauth."',
										'".date('Ymd')."',
										'".$rulename."',
										'".$rulecode."',
										'".$connectionid."',
										'0',
										'".$ruleactivationdate."',
										'".$ruleexpirationdate."',
										'".$rulelistid."',
										'".$ruletype."',
										'".$ruleobject."',
										'".$ruleoperation."',
										'".$ruleoperationunits."',
										'".$ruleschedule."',
										'".$ruledistributionlist."',
										'".$ruleactionstatusid."',
										'';";
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						$my_row=$dbtransactions->get_row();
						
						$itemid			 	= $my_row['RuleId']; 
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
                            <img src="images/imagerules.png" alt="Rule Status" title="Rule Status" class="imagenaffiliationuser" />
                            </td>
                            <td width="24">&nbsp;</td>
                            <td valign="bottom">
                            <span class="textMedium">
                            Regla<br />
                            Nueva Regla Alarmas
                            </span><br />
                            </td>
                          </tr>
                        </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Regla<br />
                    <span class="textMedium"><em><?php echo $rulename; ?></em></span><br />
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
                                La REGLA ha sido CARGADA!.<br />
                                <br />
                                <br />

                            </td>
                          </tr>   
                                                 
					<?php } else { ?>	
                          
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                La REGLA NO pudo ser CARGADA!.<br />
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
										case 401:
											echo "La regla no fue encontrada.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										case 402:
											echo "La regla o su información ya existen.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										case 406:
											echo "La información de la regla es incorrecta.<br />";
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
                            <a href="?m=rules&s=<?php echo $itemtype; ?>&a=view&n=<?php echo $itemid; ?>&t=<?php echo $itemtype; ?>">Ver Regla</a>
                            </td>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=rules&s=<?php echo $itemtype; ?>&a=new&t=<?php echo $itemtype; ?>">Nueva Regla</a>
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
