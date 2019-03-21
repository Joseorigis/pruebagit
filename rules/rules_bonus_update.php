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


		// itemquery
			$itemquery = '';
			if (isset($_GET['q'])) {
				$itemquery = setOnlyLetters($_GET['q']);
			}

		
		// rule variables set
			// rulename
				$rulename = '';
				$ruledesc = '';
				if (isset($_GET['rulename'])) { $rulename = setOnlyText($_GET['rulename']); }
			// rulecode
				$rulecode = '';
				if (isset($_GET['rulecode'])) { $rulecode = setOnlyText($_GET['rulecode']); }
			// ruletype
				$ruletype = 'ordinary';
				if (isset($_GET['ruletype'])) {
					$ruletype = setOnlyLetters($_GET['ruletype']);
					if ($ruletype == '') { $ruletype = 'ordinary'; }
				}
				$ruletype = strtolower($ruletype);
	
	
			// ruleitem
				$ruleitem = '';
				if (isset($_GET['itemslist'])) { 
					$ruleitem = setOnlyText($_GET['itemslist']);
//				} else {
//					$actionerrorid = 1;
				}
			// ruleitemunits
				$ruleunits = '9999';
				if (isset($_GET['units'])) { 
					$ruleunits = setOnlyNumbers($_GET['units']); 
				}
				if ($ruleunits == '') { $ruleunits = '9999'; }
			// ruleitembonus
				$ruleitemrewardtype = '';
				$ruleitemreward = '';
				if (isset($_GET['itemreward'])) { 
					$ruleitemrewardtype = setOnlyNumbers($_GET['itemreward']);
					if ($ruleitemrewardtype == "0") { $ruleitemreward = setOnlyText($_GET['itemrewardlist']); }
				}
				if ($ruleitemreward == '' && $ruleitemrewardtype == "0") { $actionerrorid = 1; }
			// ruleitembonusunits
				$ruleunitsreward = '0';
				if (isset($_GET['unitsreward'])) { 
					$ruleunitsreward = setOnlyNumbers($_GET['unitsreward']); 
				}
				if ($ruleunitsreward == '') { $ruleunitsreward = '0'; }
			// rulelimit
				$rulelimit = '9999';
				$rulelimittype = 'endless';
				if (isset($_GET['rangeto'])) { 
					$rulelimit = setOnlyNumbers($_GET['rangeto']);
				}
				if ($rulelimit !== '9999') { $rulelimittype = 'year'; }
			// ruleconnection
				$connectionid = '1';
				if (isset($_GET['connection'])) { 
					$connectionid = setOnlyNumbers($_GET['connection']); 
				}
				if ($connectionid == '') { $connectionid = '1'; }
			// rulelist
				$rulelist	= '0';
				$rulelistid = '1';
				if (isset($_GET['rulelist'])) { $rulelist = setOnlyNumbers($_GET['rulelist']); }
				if ($rulelist == '1') { 
					if (isset($_GET['rulelistid'])) { $rulelistid = setOnlyNumbers($_GET['rulelistid']); }
					if ($rulelistid == '') { $rulelistid = '1'; }
				}
			// ruleactivationdate
				$ruleactivation = setOnlyNumbers($_GET['ruleactivation']);
				//$ruleactivation = str_replace("/","",$ruleactivation);
				$ruleactivationdate = substr($ruleactivation,4,4).substr($ruleactivation,2,2).substr($ruleactivation,0,2);
					if (isValidDate($ruleactivationdate) == 0) {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de inicio ingresada no es v&aacute;lida!<br />";
					}
			// ruleexpirationdate
				$ruleexpiration = setOnlyNumbers($_GET['ruleexpiration']);
				//$ruleexpiration = str_replace("/","",$ruleexpiration);
				$ruleexpirationdate = substr($ruleexpiration,4,4).substr($ruleexpiration,2,2).substr($ruleexpiration,0,2);
					if (isValidDate($ruleexpirationdate) == 0) {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha de fin ingresada no es v&aacute;lida!<br />";
					}

//					// Fechas que no sean mayor o menor
//					if ($ruleactivation > $ruleexpirationdate && $actionerrorid == 0) {
//						$actionerrorid = 2;
//						$errormessage .= "&middot;&nbsp;La fecha de inicio no puede ser mayor a la final!<br />";
//					}


			// rulebonusdayslimit
				$rulerewarddayslimit = '9999';
				if (isset($_GET['rewarddayslimit'])) { 
					$rulerewarddayslimit = setOnlyNumbers($_GET['rewarddayslimit']); 
				}
				if ($rulerewarddayslimit == '') { $rulerewarddayslimit = '9999'; }

			// ruleitembonusdiscount
				$ruleunitsrewarddiscount = '0';
				if (isset($_GET['unitsrewarddiscount'])) { 
					$ruleunitsrewarddiscount = setOnlyCharactersValid($_GET['unitsrewarddiscount']); 
					if (!is_numeric($ruleunitsrewarddiscount)) { $ruleunitsrewarddiscount = '0'; }
				}
				if ($ruleunitsrewarddiscount == '') { $ruleunitsrewarddiscount = '0'; }

			// rulerewardselect
				$rulerewardselect = 'last';
				if (isset($_GET['rewardselect'])) { 
					$rulerewardselect = setOnlyCharactersValid($_GET['rewardselect']); 
				}
				if ($rulerewardselect == '') { $rulerewardselect = 'last'; }


			// ruleparams ... for copy
				$ruleparams  = "";
				$ruleparams .= "&t=".$itemtype;
				$ruleparams .= "&units=".$ruleunits;
				$ruleparams .= "&unitsreward=".$ruleunitsreward;
				$ruleparams .= "&rangeto=".$rulelimit;
				$ruleparams .= "&connection=".$connectionid;
				$ruleparams .= "&ruleactivation=".$_GET['ruleactivation'];
				$ruleparams .= "&ruleexpiration=".$_GET['ruleexpiration'];


	// RECORD PROCESS...	
		// Si no hay error hasta aquí, agregamos...
		$operation = "update";
		if ($actionerrorid == 0) {
	
					// TRANSACTIONS DATABASE
					include_once('includes/databaseconnectiontransactions.php');

					if ($operation == "add")
						{ $itemid = $actionauth; }
			
					$records = 0;
					$query  = "EXEC dbo.usp_app_RulesBonusManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'".$operation.$itemquery."', 
										'".$itemtype."', 
										'".$itemid."', 
										'".$rulename."',
										'".$ruledesc."',
										'".$rulecode."',
										'".$connectionid."',
										'0',
										'".$ruleactivationdate."',
										'".$ruleexpirationdate."',
										'".$rulelistid."',
										'".$ruleitem."',
										'".$ruleunits."',
										'".$ruleitemrewardtype."',
										'".$ruleitemreward."',
										'".$ruleunitsreward."',
										'".$rulelimittype."',
										'".$rulelimit."',
										'".$rulerewarddayslimit."',
										'".$ruleunitsrewarddiscount."',
										'".$rulerewardselect."',
										'';";//echo $query;
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						$my_row=$dbtransactions->get_row();
						
						$itemid			 	= $my_row['RuleId']; 
						$actionerrorid 		= $my_row['Error']; 
						$rulename			= $my_row['RuleName'];

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
                                Regla  <?php echo $itemtype; ?><br />
                                <?php echo $rulename; ?>
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
                                La regla ha sido ACTUALIZADA!.<br />
                                <br />
                                <br />
        
                            </td>
                          </tr>         
                                           
					<?php } else { ?>	
                          
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                La REGLA NO pudo ser ACTUALIZADA!.<br />
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
										case 411:
											echo "Alguno de los artículos de la regla no son válidos.<br />";
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
                            <?php if ($actionerrorid == 0) { ?>
                                <td class="botonstandard">
                                <img src="images/imagerules.png" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                                <a href="?m=rules&s=<?php echo $itemtype; ?>&a=view&n=<?php echo $itemid; ?>">Ver Regla</a>
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

