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
		// actionauth 
			$actionauth = '';
			if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
			if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
			if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio

		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}	

			$cardnumber = "";
			if (isset($_GET['cardnumber'])) {
				$cardnumber = setOnlyText($_GET['cardnumber']);
				//$cardnumber = setOnlyNumbers($_GET['cardnumber']);
				//if (isValidNumber($cardnumber, "EAN13") == 0) {
				//	$actionerrorid = 2;
				//	$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
				//}
				if ($cardnumber == "") {
					$actionerrorid = 2;
					$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
				}
			} else {
				$actionerrorid = 1;
			}

		// itemtype
			$itemtype = 'bonusrecord';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonusrecord'; }
			}
			$itemtype = strtolower($itemtype);

			if (isset($_GET['itemtype'])) {
				$itemtype = setOnlyLetters($_GET['itemtype']);
				if ($itemtype == '') { $itemtype = 'delete'; }
			}
			$itemtype = strtolower($itemtype);

			$transactionid = 0;
			if (isset($_GET['n'])) {
				$transactionid = setOnlyNumbers($_GET['q']);
				if ($transactionid == '') { $transactionid = 0; }
				if (!is_numeric($transactionid)) { $transactionid = 0; }
			}	


		// ticket params
			$connectionid = "0";
			$casenotes = "";
			$casemonitor = "";
			$itemsku = "0";
			$itemunits = "0";



		// ticket action
			$operation = "delete";


	// RECORD PROCESS...
		$casenumber = "0";
		//$casemonitor = "helpdesk@orbisfarma.com.mx";
		$recordid = "0";
		$connectionname = "";
		
		// Si no hay error hasta aquí, ejecutamos...
		//$operation = $itemtype;
		if ($actionerrorid == 0) {
		
					// Procesamos el registro...
					$records = 0;
					$query  = " SET ANSI_NULLS ON;SET ANSI_WARNINGS ON;";
					$query .= " EXEC dbo.usp_app_HelpDeskBonusRecordManage
											'".$_SESSION[$configuration['appkey']]['userid']."',
											'".$configuration['appkey']."',
											'".$operation."', 
											'".$actionauth."',
											'".$transactionid."',
											'".$cardnumber."',
											'".$casemonitor."',
											'".$connectionid."',
											'".$itemsku."',
											'".$itemunits."',
											'".$casenotes."';";
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						while($my_row=$dbtransactions->get_row()){ 
							//$my_row=$dbtransactions->get_row();
							$actionerrorid = $my_row['Error']; 
							$errormessage .= $my_row['OperationMessage']; 
						}
					} else {
						$actionerrorid = 99;
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
                                Historial Actualizaci&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Tarjeta<br />
                    <span class="textMedium"><em><?php echo $cardnumber; ?></em></span><br />
                    <br />
                    Actualizaci&oacute;n HISTORIAL<br />
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
                                LA TRANSACCI&Oacute;N ha sido ELIMINADA!.<br />
                                <br />
                                <br />
                                <span style="font-style:italic;">
                                La modificaci&oacute;n ser&aacute; reflejada en la siguiente visita de la tarjeta.
                                </span>

                            </td>
                          </tr>   
                                                 
					<?php } else { ?>	
                          
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                La TRANSACCI&Oacute;N NO pudo ser ELIMINADA!.<br />
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
										case 923:
											echo "La bonificaci&oacute;n ya fue agregada anteriormente.<br />";
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
                            <img src="images/bulletaffiliated.png" />&nbsp;
                            <a href="?m=affiliation&s=items&a=view&q=<?php echo $cardnumber; ?>">Ver Afiliado</a>
                            </td>
                            <td class="botonstandard">
                            <img src="images/bulletreward.png" />&nbsp;
                            <a href="?m=helpdesk&s=bonusrecord&a=edit&t=bonusrecord&n=<?php echo $itemid; ?>&cardnumber=<?php echo $cardnumber; ?>">Actualizar Historial</a>
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
