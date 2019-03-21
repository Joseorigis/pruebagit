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
		header("HTTP/1.0 404 Not Found"); 	
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

		// itemtype
			$itemtype = 'warnings';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'warnings'; }
			}
			$itemtype = strtolower($itemtype);

		// ruletype
			$ruletype = 'warnings';
			if (isset($_GET['t'])) {
				$ruletype = setOnlyLetters($_GET['t']);
				if ($ruletype == '') { $ruletype = 'warnings'; }
			}
			$ruletype = strtolower($ruletype);

	
		// ruledate or q
			$ruledate = 0;
			if (isset($_GET['ruledate'])) {
				$ruledate = setOnlyNumbers($_GET['ruledate']);
				if (strlen($ruledate) == 8) {
					$ruledate = substr($ruledate,4,4).substr($ruledate,2,2).substr($ruledate,0,2);
					if (isValidDate($ruledate) == 0) {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha ingresada no es v&aacute;lida!<br />";
					}
				} else {
						$actionerrorid = 2;
						$errormessage .= "&middot;&nbsp;La fecha ingresada no es v&aacute;lida!<br />";
				}
			} else {
				$actionerrorid = 1;
			}
			if ($ruledate == date('Ymd')) { $ruledate = 'today'; }


			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');



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
                 
                <form action="index.php" method="get" name="orveefrmhelpdesk" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="warnings" />
                <input name="a" type="hidden" value="list" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="n" type="hidden" value="0" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                
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
                                Regla <?php echo $ruletype; ?><br />
                                Consulta Alarmas
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td style="padding-left:50px;">

						<?php
						if (isset($_GET['ruledate'])) {
						?>
                        	Alarmas de <span style="font-style:italic; font-weight:bold;"><?php echo $ruledate; ?></span><br />
                            <table class="botones2">
                              <tr>
                                <td class="botonstandard">
                                <img src="images/bulletemailnew.png" />&nbsp;
                                <a href="includes/task_RulesWarningsNotificationsSend.php?t=<?php echo $ruledate; ?>" target="_blank">Reenviar Alarmas</a>
                                </td>
                              </tr>
                            </table>
						<?php
						} else {
						?>
                            <table class="botones2">
                              <tr>
                                <td class="botonstandard">
                                <img src="images/bulletemailnew.png" />&nbsp;
                                <a href="includes/task_RulesWarningsNotificationsSend.php" target="_blank">Reenviar Hoy</a>
                                </td>
                              </tr>
                            </table>
						<?php
						}
						?>

                    </td>
                  </tr>
                  <tr>
                    <td>
                      Fecha<br/>
                      <div>
                      <input type="text" name="ruledate" id="ruledate" value="<?php echo date('d/m/Y'); ?>" class="inputtextrequired" />
                      </div>
			          <span class="textHint">
                      &middot; Fecha de compra en el ticket.
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Buscar" />
                    </div>
                    </td>
                  </tr>
				</table>
                
                </form>
                

            <br /><br />
    
    
			<script type="text/javascript">
                var today = new Date();

                var dd = today.getDate();
                var mm = today.getMonth()+1; //January is 0!
                var yyyy = today.getFullYear();
                
                if(dd<10){dd='0'+dd}
                if(mm<10){mm='0'+mm} 
                today = dd+'/'+mm+'/'+yyyy;
            
                //http://jdpicker.paulds.fr/?p=demo
                $(document).ready(function(){
                    $('#ruledate').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        date_max:today
                    });
            
                });
            </script>                
                    
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
