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
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);

		// itemquery
			$itemquery = '';
			if (isset($_GET['q'])) {
				$itemquery = setOnlyLetters($_GET['q']);
			}



		// GET RECORD...
				$records = 0;
				$query  = "EXEC dbo.usp_app_RulesBonusManage
									'".$_SESSION[$configuration['appkey']]['userid']."', 
									'".$configuration['appkey']."',
									'view', 
									'".$itemtype."', 
									'".$itemid."';";
				$dbtransactions->query($query);
				$records = $dbtransactions->count_rows();
				if ($records > 0) {
					$my_row=$dbtransactions->get_row();
					
					$itemid			 	= $my_row['RuleId']; 
					$actionerrorid 		= $my_row['Error']; 

				} else {
					$actionerrorid = 66;
				}

				// ruleactivation
					$ruleactivation = date('d/m/Y');
					if (isset($my_row['RuleActivationDateEdit'])) {
						$ruleactivation = ($my_row['RuleActivationDateEdit']);
						if (isValidDate($ruleactivation, "dd/mm/yyyy") == 0)
							{ $ruleactivation = date('d/m/Y'); }
					}
				// ruleexpiration
					$ruleexpiration = "31/12/2019";
					if (isset($my_row['RuleExpirationDateEdit'])) {
						$ruleexpiration = ($my_row['RuleExpirationDateEdit']);
						if (isValidDate($ruleexpiration, "dd/mm/yyyy") == 0)
							{ $ruleexpiration = "31/12/2019"; }
					}

	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();


		if(WithoutContent(document.orveefrmrule.ruleactivation.value))
			{ errormessage += "\n- Selecciona un vigencia inicio!."; }

		if(WithoutContent(document.orveefrmrule.ruleexpiration.value))
			{ errormessage += "\n- Selecciona un vigencia final!."; }
			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para actualizar la regla, por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			//document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Afiliación en proceso, por favor, espere un momento...</em>";
			
			return false;
			}
		//document.orveefrmuser.submit();
		return true;
	} // end of function CheckRequiredFields()


//-->
</SCRIPT>

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

                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="a" type="hidden" value="update" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="q" type="hidden" value="<?php echo $itemquery; ?>" />
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
                                Regla  <?php echo $itemtype; ?><br />
                                <?php echo $my_row['RuleName']; ?>
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Regla<br />
                    <span class="textMedium"><em>
					<?php echo $my_row['ItemGroupId']; ?>&nbsp;&nbsp;
                    [<?php echo $my_row['RulePublishStatus']; ?>]
                    </em></span><br />
                    <span class="textHint"> &middot; Status actual de la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Cadena<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]</em>
                    </span><br />
                    <span class="textHint"> 
                    &middot; Sucursales o tiendas de la regla.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culos<br />
                    <span class="textMedium">
                    	<em><?php echo str_replace(',', '<br />', $my_row['ItemsList']); ?></em>
                    </span><br />
                    <span class="textHint"> &middot; Art&iacute;culos a incluidos en la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Regla Negocio<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['RuleDescription']; ?></em>
                        <span class="textSmall"><em><?php echo $my_row['RuleBonusItem']; ?></em></span>
                    </span><br />
                    <span class="textMedium"><em><?php echo $my_row['RuleLimit']; ?></em></span><br />
                    <span class="textSmall"><em>&middot; Retenci&oacute;n Rechazos: <?php echo $my_row['RuleBonusDaysLimit']; ?></em></span><br />
                    <span class="textHint"> 
                    &middot; Regla X+1 a aplicar.<br />
                    &middot; Unidades requeridas para bonificaci&oacute;n.<br />
                    </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Bonificaci&oacute;n<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['BonusItem']; ?></em>
                    </span><br />
                    <span class="textHint"> &middot; Art&iacute;culos a bonificar en la regla.</span>
                    </td>
                  </tr>
                                    
                  <tr>
                    <td>
                      Vigencia Inicio<br/>
                      <div><input type="text" name="ruleactivation" id="ruleactivation" value="<?php echo $ruleactivation; ?>" class="inputtextrequired" readonly /></div>
			            <span class="textHint">
                    &middot; Fecha programada para inicio de la vigencia.</span></td>
                  </tr>
                  <tr>
                    <td>
                      Vigencia Fin<br/>
                      <div><input type="text" name="ruleexpiration" id="ruleexpiration" value="<?php echo $ruleexpiration; ?>" class="inputtextrequired" readonly /></div>
			            <span class="textHint">
                    &middot; Fecha programada para fin o termino de la vigencia.</span></td>
                  </tr>
                  
                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
                    </div>
                    </td>
                  </tr>
                </table>
				</form>
                
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
                    $('#ruleactivation').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        //date_min:today
                    });
                    $('#ruleexpiration').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        date_min:today
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

